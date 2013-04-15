<?php

class GroupController extends Zend_Controller_Action
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
		$this->view->title = 'Groepsbeheer';
		if(!$this->access->isGod() and !$this->access->hasRole('administrator'))
		{
			$this->view->sysMessages .= "<p>FOUT: U hebt onvoldoende rechten om de groepen te beheren.</p>";
			return $this->_forward('noauth','error');
		}
        $this->view->jQuery()->addJavascriptFile('/js/confirm_delete.js');
		
		$tbl = new Model_Table_Groups();
		if($this->access->isGod()) $this->view->groups = $tbl->fetchAllByPosition();
		else $this->view->groups = $this->access->getUser()->getGroupObjects();

		$this->view->flowMenuItems = array(
			"groep toevoegen" => '/group/new'
		);
    }

    public function moveAction()
    {
        if(!$direction = $this->getRequest()->getParam('d'))
        {
            throw new Zend_Exception('FOUT: geen richtig voor de move meegegeven');
        }
        if(!$groupID = $this->getRequest()->getParam('g'))
        {
            throw new Zend_Exception('FOUT: Geen groepsID meegegeven');
        }
        $userID = $this->access->getUser()->id;
        
        Model_GroupTabs::move($direction,$groupID, $userID);
        
        return $this->_redirect('/group/index');
    }

    public function newAction()
    {
		$this->view->title = 'Groep toevoegen';

		if(!$this->access->isGod() and !$this->access->hasRole('administrator'))
		{
			$this->view->sysMessages .= "<p>FOUT: U hebt onvoldoende rechten om de groepen te beheren.</p>";
			return $this->_forward('noauth','error');
		}
		
		$form = new Form_Group;
		$form->removeElement('id');

		if($this->getRequest()->isPost())
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				$groupTbl = new Model_Table_Groups();
				$group = $groupTbl->createRow();
				$group->setFromArray($form->getValues());
				$currentLastPosition = Model_GroupTabs::getLastPosition();
				$group->position = (int)$currentLastPosition +1;
				$group->save();
				$group->addUser($this->view->user->id);
				$this->_redirect('/group/index');
			}
		}

	 	$form->setAction('/group/new');
		$form->addElement('submit', 'submit', array('label' => 'OK'));
		$this->view->form = $form;
		$this->view->flowMenuItems = array(
			"groepsoverzicht" => '/group/index'
		);
    }

    public function deleteAction()
    {
		if(!$this->access->hasRole('administrator'))
		{
			$this->view->sysMessages .= "<p>FOUT: U hebt onvoldoende rechten om de groepen te beheren.</p>";
			return $this->_forward('noauth','error');
		}
		
		$id = $this->getRequest()->getParam('id');
		
		$group = Model_Group::getInstance($id);
		
		if(!$this->access->isGod() and !$this->access->isInGroup($id))
		{
			$this->view->sysMessages .= "<p>FOUT: U bent geen lid van de groep $group->name</p>";
			return $this->_forward('noauth','error');
		}

		$table = new Model_Table_Groups();
		$table->delete("id=$id");
		$this->_redirect('/group/index');
    }

    public function editAction()
    {
        $this->view->title = "Bewerk groep";

		if(!$this->access->isGod() and !$this->access->hasRole('administrator'))
		{
			$this->view->sysMessages .= "<p>FOUT: U hebt onvoldoende rechten om de groepen te beheren.</p>";
			return $this->_forward('noauth','error');
		}
		
        $this->view->jQuery()->addJavascriptFile('/js/confirm_delete.js');

		$table = new Model_Table_Groups();
		$id = $this->getRequest()->getParam('id');

		if(!$this->access->isGod() and !$this->access->isInGroup($id))
		{
			$this->view->sysMessages .= "<p>FOUT: U bent geen lid van de groep $group->name</p>";
			return $this->_forward('noauth','error');
		}

		$row = $table->find($id)->current();

		$form = new Form_Group;
		$form->addElement('submit', 'submit', array('label' => 'Sla Op'));

		if($this->getRequest()->isPost())
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				// sla de waarden uit het form op
				$table = new Model_Table_Groups();
				$row->setFromArray($form->getValues());
				$row->save();
				$this->_redirect('/group/index');
			}
		}

		$form->populate($row->toArray());
		$this->view->form = $form;
		$this->view->flowMenuItems = array(
			'groepsbeheer' => '/group/index',
			'verwijder' => '/groep/delete/id/' . $id
		);
    }

    public function startpageAction()
    {
 		$this->view->title = 'Startpagina kiezen';
        $id = $this->getRequest()->getParam('id');
        $group = Model_Group::getInstance($id);
        $this->view->group = $group;

		if(!$this->access->isGod() and !$this->access->hasRole('administrator'))
		{
			$this->view->sysMessages .= "<p>FOUT: U hebt onvoldoende rechten om de startpagina te wijzigen..</p>";
			return $this->_forward('noauth','error');
		}
		
		$form = new Form_GroupStartPage();
		if ($group->startPage) 
		{
		    $form->getElement('startPage')->setValue($group->startPage);
		}
		
        // ================
        // = Save de POST =
        // ================
		if($this->getRequest()->isPost())
		{
		    $group->startPage = $this->getRequest()->getParam('startPage');
		    $group->save();
		    return $this->_forward('index','group');
		}

        // =======================
        // = initialize the form =
        // =======================
		$form = new Form_GroupStartPage();

		if ($group->startPage) 
		{
		    $form->getElement('startPage')->setValue($group->startPage);
		}
		
		// ========================================================================
		// = Haal de pagina's op voor deze groep en stop deze in de select opties =
		// ========================================================================
		
		$tbl = new Model_Table_Pages();
		$select = $tbl->select()
		    ->where('owner = ?', $id)
		    ->order('menutitle');

		if($pages = $tbl->fetchAll($select))
		{
		    $options = array();
		    foreach ($pages as $page)
		    {
		        $options[$page->id] = $page->title;
		    }
		}
		else
		{
		    $options = array('fout' => 'Er zijn nog geen pagina\'s voor deze groep beschikbaar.');
		}
		
		$form->getElement('startPage')->setMultiOptions($options);
		
		$this->view->form = $form;
		$this->view->flowMenuItems = array(
		    'Overzicht groepen'    => '/group/index',
		    'Overzicht pagina\'s' => '/page/index',
		    'Pagina toevoegen'     => '/page/new',
	    );
    }
}





