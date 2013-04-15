<?php

class AgendaController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');

        $tabs= new Model_AdminTabs();
        $this->view->adminTabs = $tabs->getList();

		$this->access = new Model_Access();
		$this->view->user = $this->access->getUser();
    }

    public function indexAction()
    {
		$this->view->title = 'index agendaitems';
		$table = new Model_Table_agendaitems();
		$select = $table->select()
			->order('starttimestamp');
		$rows = $table->fetchAll($select);
		$this->view->items = $rows;
		$this->view->flowMenuItems = array(
			"nieuw item"       => '/agenda/additem',
			'groepstoewijzing' => '/agenda/groepstoewijzing'
		);
    }

    public function newAction()
    {
		$this->view->title = 'nieuwe agenda';
		$form = new Form_Agenda();
		$form->removeElement('id');
		$this->view->form = $form;
		$form->addElement('submit', 'submit', array('label' => 'OKE'));

		if($this->getRequest()->isPost())
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				$agenda = Model_Agenda::getInstance();
				$agenda->setFromArray($form->getValues());
				$agenda->save();
				$this->_redirect('/agenda/index');
			}
		}
		
		$this->view->form = $form;
		
		$this->view->flowMenuItems = array(
			"index agenda's" => '/agenda/index'
		);
    }

    public function showAction()
    {
		if (! $id = $this->getRequest()->getParam('id')) 
		{
			throw new Zend_Exception('Agenda kan niet worden getoond: geen ID opgegeven');
		}
		
		$agenda = Model_Agenda::getInstance($id);
		$this->view->title = $agenda->name;
		$agenda->loadItems();
		$this->view->agenda = $agenda;
		$this->view->flowMenuItems = array(
			'nieuw item' => '/agenda/additem/id/' . $id,
			"overzicht agenda's" =>"/agenda/index",
			"bewerk deze agenda" => "/agenda/edit/id/" . $id
			);
    }

    public function deleteAction()
    {
		$id = $this->getRequest()->getParam('id');
		$table = new Model_Table_Agendas();
		$table->delete("id=$id");
		$this->_redirect('/agenda/index');
    }

    public function editAction()
    {
		$this->view->title = "agenda aanpassen";
		$id = $this->getRequest()->getParam('id');
		$form = new Form_Agenda();
		$form->addElement('submit', 'submit', array('label' => 'OKE'));
		
		$agenda = Model_Agenda::getInstance($id);

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {

				// overschrijf de gegevens in de db met de gegevens uit het form
				$agenda->setFromArray($form->getValues());
				$agenda->save();
				$this->_redirect('/agenda/index');
			}
		}

		// Vul de het formulier met gegevens uit de db.
		$agendaData = $agenda->toArray();
		$form->populate($agendaData);
		$this->view->form = $form;


		$this->view->flowMenuItems = array(
			"overzicht agenda's"    => '/agenda/index',
			'nieuwe agenda'         => '/agenda/new',
			'verwijder deze agenda' => '/agenda/delete/id/' . $id,
		);
    }

    public function groepstoewijzingAction()
    {
		$this->view->title = "groepsindeling agenda's";
		
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
		// = Haal alle aangevinkte agenda's op en update de database accordingly =
		// =======================================================================
		if ($this->getRequest()->isPost() and $this->getRequest()->getParam('submit') == 'submit')
		{
			foreach ($_POST['agendaGrp'] as $agendaId => $groups) {
				$agenda = Model_Agenda::getInstance($agendaId);
				$relations=array();
				foreach($groups as $grpId => $yon)
				{
					if($yon == 'yes')
					{
						$relations[] = $grpId;
					}
				}
				$agenda->setGroups($relations);
				$agenda->save();
			}
		}
		
		// ===========================================================================
		// = Haal de pagina's op die getoond moeten worden (zie form groupSelection) =
		// ===========================================================================
		$agendaTbl = new Model_Table_Agendas();
		if(in_array('allemaal',$groupSelection))
		{
			$select = $agendaTbl->select()
				->order('name');
			$agendas = $agendaTbl->fetchAll($select);
		}
		else
		{
			$select = $agendaTbl->select()
				->setIntegrityCheck(false)
				->from('agendas')
				->joinLeft('agenda_group','agenda_group.agenda = agendas.id',array())
				->where('agenda_group.group IN (?)', $groupSelection)
				->order('agendas.name');
			$agendas = $agendaTbl->fetchAll($select);
		}

		// Remove duplicate abonnee entries
		$selectedAgendas = array();
		foreach($agendas as $row)
		{
			if(!$selectedAgendas[$row->id])
			{
				$selectedAgendas[$row->id] = $row;
			}
		}
		$this->view->agendas = $selectedAgendas;
		
		// Haal alle groepen op
		$grpTbl = new Model_Table_Groups();
		$this->view->groups = $grpTbl->fetchAll();

		$this->view->flowMenuItems = array(
			'agendabeheer'  => '/agenda/index',
			'nieuwe agenda' => '/agenda/new',
		);
    }

    public function additemAction()
    {
		$access = new Model_Access();
        if(!$access->isGod() and !$access->hasRole('editor'))
        {
			$this->view->sysMessages .= "<p>FOUT: onvoldoende rechten om agendaitems aan te maken.</p>";
			return $this->_forward('noauth','error');
        }
        
		$this->view->title = 'Nieuw agendaitem';
		$currentUser = $access->getUser();

		$form = new Form_AgendaItem($currentUser);
		$form->removeElement('id');
		$form->addElement('submit', 'submit', array('label' => 'OKE'));
		
		
		if($this->getRequest()->isPost())
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				$agendaItem = Model_AgendaItem::getInstance();
				$agendaItem->setFromFormValues($form->getValues());
				$agendaItem->save();
                // $this->_redirect('/agenda/show/id/'. $agenda->id);
			}
		}
        $this->view->form = $form;
		$this->view->flowMenuItems = array(
	        'item index'=> '/agenda/index',
		);
    }

    public function edititemAction()
    {
		if(!$id = $this->getRequest()->getParam('id'))
		{
			throw new Zend_Exception('Er is geen agendaitem opgegeven');
		}
		
		$agendaItem = Model_AgendaItem::getInstance($id);
		$this->view->agendaItem = $agendaItem;
		$this->view->title = "bewerk agendaitem";
		$form = new Form_AgendaItem();
		$form->addElement('submit', 'submit', array('label' => 'OKE'));
		if($this->getRequest()->isPost())
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				$values = $form->getValues();
				$agendaItem->setFromFormValues($values);
				$agendaItem->save();
				$this->_redirect('/agenda/show/id/' . $agendaItem->agendaId);
			}
		}
		$values = $agendaItem->getFormValues();
		$form->populate($values);
		$this->view->form = $form;
		
    }

    public function deleteitemAction()
    {
		if(!$id = $this->getRequest()->getParam('id'))
		{
			throw new Zend_Exception('Fout bij het verwijderen van item: Er is geen agendaitem opgegeven');
		}
		
		$item = Model_AgendaItem::getInstance($id);
		$agendaId = $item->agendaId;
		
		$table = new Model_Table_AgendaItems();
		$table->delete('id = ' . $id);
		
		$this->_redirect('/agenda/show/id/' . $agendaId);
		
    }

    public function showitemAction()
    {
		if(!$id = $this->getRequest()->getParam('id'))
		{
			throw new Zend_Exception('Fout: Er is geen agendaitem opgegeven');
		}
		$this->view->title = "agenda item";
		$item = Model_AgendaItem::getInstance($id);
		$this->view->item = $item;
		$this->view->flowMenuItems = array(
				"overzicht agenda's" => "/agenda/index",
				'toon agenda'        => '/agenda/show/id/' . $item->agendaId,
				'bewerk dit item'    => '/agenda/edititem/id/' . $item->id
			);
    }
}



















