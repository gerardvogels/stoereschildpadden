<?php

class EnqueteController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->title = 'index enquetes';
		$table = new Model_Table_Enquetes();
		$select = $table->select()
			->order('title');
		$rows = $table->fetchAll($select);
		$this->view->enquetes = $rows;
		$this->view->flowMenuItems = array(
			"nieuwe enquete" => '/enquete/new',
			'groepstoewijzing' => '/enquete/groepstoewijzing'
		);
    }

    public function newAction()
    {
		$this->view->title = 'nieuwe enquete';
		$form = new Form_Enquete();
		$form->removeElement('id');
		$this->view->form = $form;
		$form->addElement('submit', 'submit', array('label' => 'OKE'));

		if($this->getRequest()->isPost())
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				$enquete = Model_Enquete::getInstance();
				$enquete->setFromArray($form->getValues());
				$enquete->save();
				$this->_redirect('/enquete/index');
			}
		}
		
		$this->view->form = $form;
		
		$this->view->flowMenuItems = array(
			"index enquete's" => '/enquete/index'
		);
    }

    public function groepstoewijzingAction()
    {
		$this->view->title = "groepsindeling enquete's";
		
		// default groupSelection
		$groupSelection = array();
		$groupSelection[] = null;
		
		// ==========================================
		// = Laat het groupSelection formulier zien =
		// ==========================================
		$gsForm = new Form_GroupSelectionFilter();
		if ($this->getRequest()->getParam('groups')){
			$groupSelection = $this->getRequest()->getParam('groups');
		}
		else
		{
			$groupSelection = array('allemaal');
		}
		$tmp['groups'] = $groupSelection;
		$gsForm->populate($tmp);
		$this->view->gsForm = $gsForm;
		
		// =======================================================================
		// = Haal alle aangevinkte enquete's op en update de database accordingly =
		// =======================================================================
		if ($this->getRequest()->isPost() and $this->getRequest()->getParam('submit') == 'submit')
		{
			foreach ($_POST['enqueteGrp'] as $enqueteId => $groups) {
				$enquete = Model_Enquete::getInstance($enqueteId);
				$relations=array();
				foreach($groups as $grpId => $yon)
				{
					if($yon == 'yes')
					{
						$relations[] = $grpId;
					}
				}
				$enquete->setGroups($relations);
				$enquete->save();
			}
		}
		
		// ===========================================================================
		// = Haal de enquete's op die getoond moeten worden (zie form groupSelection) =
		// ===========================================================================
		$enqueteTbl = new Model_Table_Enquetes();
		if(in_array('allemaal',$groupSelection))
		{
			$select = $enqueteTbl->select()
				->order('title');
			$enquetes = $enqueteTbl->fetchAll($select);
		}
		else
		{
			$select = $enqueteTbl->select()
				->setIntegrityCheck(false)
				->from('enquetes')
				->joinLeft('enquete_group','enquete_group.enquete = enquetes.id',array())
				->where('enquete_group.group IN (?)', $groupSelection)
				->order('enquetes.title');
			$enquetes = $enqueteTbl->fetchAll($select);
		}

		// Remove duplicate abonnee entries
		$selectedEnquetes = array();
		foreach($enquetes as $row)
		{
			if(!$selectedEnquetes[$row->id])
			{
				$selectedEnquetes[$row->id] = $row;
			}
		}
		$this->view->enquetes = $selectedEnquetes;
		
		// Haal alle groepen op
		$grpTbl = new Model_Table_Groups();
		$this->view->groups = $grpTbl->fetchAll();

		$this->view->flowMenuItems = array(
			"index enquete's"  => '/enquete/index',
			'nieuwe enquete' => '/enquete/new',
		);
        // action body
    }

    public function editAction()
    {
		$this->view->title = "enquete aanpassen";
		$id = $this->getRequest()->getParam('id');
		$form = new Form_Enquete();
		$form->addElement('submit', 'submit', array('label' => 'OKE'));
		
		$enquete = Model_Enquete::getInstance($id);

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {

				// overschrijf de gegevens in de db met de gegevens uit het form
				$enquete->setFromArray($form->getValues());
				$enquete->save();
				$this->_redirect('/enquete/index');
			}
		}

		// Vul de het formulier met gegevens uit de db.
		$enqueteData = $enquete->toArray();
		$form->populate($enqueteData);
		$this->view->form = $form;


		$this->view->flowMenuItems = array(
			"overzicht enquete's"    => '/enquete/index',
			'nieuwe enquete'         => '/enquete/new',
			'verwijder deze enquete' => '/enquete/delete/id/' . $id,
			"importeer vragen"       => "/enquete/importquestions/id/" . $id,
			"toon de yaml file"      => "/enquete/showyamlfile/id/" . $id
		);
    }

    public function deleteAction()
    {
		$id = $this->getRequest()->getParam('id');
		$table = new Model_Table_Enquetes();
		$table->delete("id=$id");
		$this->_redirect('/enquete/index');
    }

    public function importquestionsAction()
    {
		if (! $id = $this->getRequest()->getParam('id')) 
		{
			throw new Zend_Exception('Geen enquete id ingevuld');
		}
		
		$enquete = Model_Enquete::getInstance($id);
		$this->view->title = 'importeer vragen voor "' . $enquete->title . '"';
		$this->view->enquete = $enquete;
		$form = new Form_ImportQuestions();
		$form->addElement('submit', 'submit', array('label' => 'OKE'));

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				// =====================================================
				// = Zet de geuploade file op de juiste plek en zet de =
				// = vragen in de database.							   =
				// =====================================================
				if ($form->yamlfile->isUploaded() or true) 
				{
					$form->yamlfile->receive();
					$src = $form->yamlfile->getFileName();
					$dest = APPLICATION_PATH . '/enquetefiles/' . $id . '.yml';
					unlink($dest);
					rename($src,$dest);
					$enquete->importYamlQuestions();
				}
			}
		}

		$this->view->form = $form;
		$this->view->flowMenuItems = array(
			"overzicht enquetes"                => "/enquete/index",
			"toon enquete " . $enquete->title   => "/enquete/show/id/" . $id,
			"bewerk enquete " . $enquete->title => "/enquete/edit/id/" . $id,
			"toon YAML"                         => "/enquete/showyamlfile/id/" . $id,
		);
    }

    public function ymlexampleAction()
    {
		$this->view->title = 'yaml voorbeeld';
		$this->view->document = file_get_contents(APPLICATION_PATH . '/docs/example.yml');
    }

    public function previewAction()
    {
		$this->view->title = 'toon enquete';
		if(! $id = $this->getRequest()->getParam('id'))
		{
			throw new Zend_Exeption('Fout bij het tonen van de enquete: Geen ID opgegeven.');
		}
		
		$enquete = Model_Enquete::getInstance($id);
		$enquete->loadQuestions();
		$this->view->enquete = $enquete;
		$this->view->flowMenuItems = array(
			"overzicht enquete's"                   => '/enquete/index',
			"edit deze enquete"                     => "/enquete/edit/id/" . $id,
			"importeer de vragen voor deze enquete" => "/enquete/importquestions/id/" . $id,
			"toon YAML"                             => "/enquete/showyamlfile/id/" . $id,
			"verwijder deze enquete"                => "/enquete/delete/id/" . $id,
		);

    }

	public function showyamlfileAction()
	{
		if(!$id = $this->getRequest()->getParam('id'))
		{
			throw new $fileContents('Fout bij het importeren van de vragen: geen enquete id opgegeven.');
		}

		$this->view->title = "importeer vragen uit yaml bestand";
		
		$enquete = Model_Enquete::getInstance($id);
		$this->view->enqueteTitle = $enquete->title;
		
		$file = APPLICATION_PATH . "/enquetefiles/" . $id . ".yml";
		$fileContents = file_get_contents($file);
		$this->view->fileContents = $fileContents;

		$this->view->flowMenuItems = array(
			"overzicht enquete's"                   => '/enquete/index',
			"edit deze enquete"                     => "/enquete/edit/id/" . $id,
			"importeer de vragen voor deze enquete" => "/enquete/importquestions/id/" . $id,
			"toon enquete"                          => "/enquete/preview/id/" . $id,
			"verwijder deze enquete"                => "/enquete/delete/id/" . $id,
		);
	}


}

















