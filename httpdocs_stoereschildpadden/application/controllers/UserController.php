<?php

class UserController extends Zend_Controller_Action
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


	function getRelevantUsers()
	{
		// ============================================================
		// = selecteer de users die in de index getoond moeten worden =
		// ============================================================
		$relevantUsers = array();
		if($this->access->isGod())
		{
			// god mag iedereen zien
			$table = new Model_Table_Users();
			$select = $table->select()
				->order('achternaam')
				->order('voornaam');
			$rows = $table->fetchAll($select);
			$relevantUsers = $rows;
		}
		else
		{
			// alleen de groepsgenoten
			$relevantUsers = $this->access->getUser()->getFellowGroupMemberObjects();
			
		}
		return $relevantUsers;
	}


	public function getRelevantGroups()
	{
		$relevantGroups = array();

		if($this->access->isGod())
		{
			$tbl = new Model_Table_Groups();
			$select = $tbl->select()->order(`name`);
			$relevantGroups = $tbl->fetchAll($select);
		}
		else
		{
			$relevantGroups = $this->access->getUser()->getGroupObjects();
		}
		
		return $relevantGroups;
	}


    public function indexAction()
    {

		$this->view->title = 'gebruikersbeheer';

		if(!$this->access->isGod() and !$this->access->hasRole('administrator'))
		{
			$this->view->sysMessages .= "<p>FOUT: U hebt onvoldoende rechten om de gebruikers te beheren.</p>";
			return $this->_forward('noauth','error');
		}
		
        $this->view->jQuery()->addJavascriptFile('/js/confirm_delete.js');
		$this->view->users = $this->getRelevantUsers();
		
		$this->view->flowMenuItems = array(
			"gebruiker toevoegen" => '/user/new',
			'groepstoewijzing' => '/user/groepstoewijzing'
		);
    }

    public function newAction()
    {

		if(!$this->access->hasRole('administrator'))
		{
			$this->view->sysMessages .= "<p>FOUT: onvoldoende rechten om gebruikers aan te maken.</p>";
			return $this->_forward('noauth','error');
		}
		
		$this->view->title = 'Nieuwe gebruiker';
		$form = new Form_User();
		$form->addElement('submit', 'submit', array('label' => 'OKE'));
		$form->removeElement('id');
		
		if($this->getRequest()->isPost())
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				$user = Model_User::getInstance();
				$user->setFromArray($form->getValues());
				$user->save();
				$this->_redirect('/user/index');
			}
		}
		$this->view->form = $form;
		
		$this->view->flowMenuItems = array(
			"gebruikersbeheer" => '/user/index'
		);
    }

    public function deleteAction()
    {
		if (!$this->access->hasRole('administrator')) 
		{
			$this->view->sysMessages .= "<p>Fout: U hebt onvoldoiende rechten om gebrujikers te verwijderen</p>\n";
			return false;
		}
		
		$id = $this->getRequest()->getParam('id');
		
		$this->view->flowMenuItems = array(
			'gebruikersbeheer'         => '/user/index',
			'voeg gebruiker toe'       => '/user/new',
			'verwijder deze gebruiker' => '/user/delete/id/' . $id,
			'wijzig e-mail adres'      => '/user/editemail/id/' . $id
		);

		$user = Model_User::getInstance($id);
		$this->access->setResource($user);
		if (!$this->access->isEdit()) 
		{
			$this->view->sysMessages .= "<p>Fout: U mag gebruiker " . $user->getFullName() . " niet verwijderen.</p>\n";
			return false;
		}
		
		if($this->access->isSelfDelete($user))
		{
			$this->view->sysMessages .= "<p>Fout: Het is niet mogelijk jezelf uit het systeem te verwijderen.</p>\n";
			return false;
		}
		$user->delete();
		$this->_redirect('/user/index');
    }

    public function editAction()
    {
		$this->view->title = "Bewerk gebruikersgegevens";
		$id = $this->getRequest()->getParam('id');
        $this->view->jQuery()->addJavascriptFile('/js/confirm_delete.js');

		// haal de huidige abonneegegevens uit de db
		$user = Model_User::getInstance($id);
		$this->access->setResource($user);
		if (!$this->access->isEdit())
		{
			$this->view->sysMessages .= "<p>Fout: U hebt onvoldoende rechten om de gegevens van " . $user->getFullName() . " te wijzigen.</p>\n";
			return false;
		}

		$this->view->flowMenuItems = array(
			"gebruikersbeheer" => '/user/index',
			"gebruiker toevoegen" => '/user/new',
			'groepstoewijzing' => '/user/groepstoewijzing',
			"verwijder deze gebriuker" => '/user/delete/id/' . $id,
		);

		$form = new Form_User();
		$form->removeElement('email');
		$form->removeElement('password');
		$form->addElement('submit', 'submit', array('label' => 'OKE'));
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {

				// overschrijf de abonneegegevens met de gegevens uit het form
				$user->setFromArray($form->getValues());
				$user->save();
				$this->_redirect('/user/index');
			}
		}

		// Vul de het formulier met gegevens uit de db.
		$userData = $user->toArray();
		$form->populate($userData);
		$this->view->form = $form;
		$this->view->userName = $user->getFullName();

    }

    public function editemailAction()
    {
		$this->view->title = "Wijzig e-mailadres";
		$id = $this->getRequest()->getParam('id');
		// haal de huidige abonneegegevens uit de db
		$user = Model_User::getInstance($id);
		
		$this->access->setResource($user);
		if(!$this->access->isEdit() and !$this->access->isOwnUserData())
		{
			$this->view->sysMessages .= "<p>Fout: U hebt onvoldoende rechten om het email adres van " . $user->getFullName() . " te wijzigen.</p>\n";
			return false;
		}
		
		$this->view->username = $user->getFullName();
		
		$form = new Form_Usermail();
		$form->setAction('/user/editemail');
		$form->addElement('submit', 'submit', array('label' => 'OKE'));
		

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {

				// overschrijf de abonneegegevens met de gegevens uit het form
				$user->setFromArray($form->getValues());
				$user->save();
				$this->_redirect('/user/index');
			}
		}

		// Vul de het formulier met gegevens uit de db.
		$userData = $user->toArray();
		$form->populate($userData);
		$this->view->form = $form;

		$this->view->flowMenuItems = array(
			'gebruikersbeheer'   => '/user/index',
			'voeg gebruiker toe' => '/user/new',
			'bewerk gebruikersgegevens' => '/user/edit/id/' . $id
		);

        
    }

    public function groepstoewijzingAction()
    {
		$this->view->title = 'Groepsindeling';
		
		$this->view->flowMenuItems = array(
			'gebruikersbeheer'    => '/user/index',
			"gebruiker toevoegen" => "/user/new"
		);
		
		if (!$this->access->isGod() and !$this->access->hasRole('administrator')) 
		{
			$this->view->sysMessages .= "<p>U hebt niet voldoende rechten om de groepsindelingen te wijzigen.</p>\n";
			return false;
		}


		// ==================================
		// = Verwerk de ingevoerde gegevens =
		// ==================================
		if ($this->getRequest()->isPost())
		{
			foreach ($_POST['usrgrp'] as $userId => $groups) {
				$user = Model_User::getInstance($userId);
				$relations=array();
				foreach($groups as $grpId => $yon)
				{
					if($yon == 'yes')
					{
						$relations[] = $grpId;
					}
				}
				$user->setGroups($relations);
				$user->save();
			}
		}
		
		
		$this->view->users = $this->getRelevantUsers();
		$this->view->groups = $this->getRelevantGroups();
		
    }

    public function loginAction()
    {
		$this->view->title = 'login';
		$form = new Form_User();
		$form->setAction('/user/login');
		$form->removeElement('voornaam');
		$form->removeElement('tussenvoegsel');
		$form->removeElement('achternaam');
		$form->removeElement('info');
		$form->removeElement('roles');
		$form->removeElement('groups');
		$form->getElement('email')->removeValidator('Db_NoRecordExists');
		$form->addElement('submit', 'submit', array('label' => 'LOGIN'));
		if ($this->_request->isPost() and $form->isValid($_POST)) {
			$data = $form->getValues();
			
			$db = Zend_Db_Table::getDefaultAdapter();
			$authAdapter = new Zend_Auth_Adapter_DbTable($db, 'users','email', 'password');
			$authAdapter->setIdentity($data['email']);
			$authAdapter->setCredential($data['password']);
			$result = $authAdapter->authenticate();
			if ($result->isValid()) 
			{
				$auth = Zend_Auth::getInstance();
				$storage = $auth->getStorage();
				$storage->write($authAdapter->getResultRowObject(
					array('id', 'voornaam', 'tussenvoegsel', 'achternaam', 'email')));
					
				// ==============================
				// = login to the image manager =
				// ==============================
    			$_SESSION['isLoggedIn'] = true;
    			$_SESSION['user'] = $_POST['login'];
    			// Override any config option
    			//$_SESSION['imagemanager.filesystem.rootpath'] = 'some path';
    			//$_SESSION['filemanager.filesystem.rootpath'] = 'some path';
				
				return $this->_redirect('/menu/index');
			} 
			else 
			{
				$this->view->loginMessage = "Sorry, your username or
					password was incorrect";
			}
		}
		$this->view->form = $form;
    }

    public function testAction()
    {
		$id = null;
		$user=Model_User::getInstance($id);
		$userData = $user->toArray();
		echo "<pre>";
		var_dump($userData);
		echo "</pre>";
		die('Eind van de test bereikt.');
    }

    public function logoffAction()
    {
		$this->view->title = 'afmelden';
		$this->view->user = $this->access->getUser();
	    $authAdapter = Zend_Auth::getInstance();
	    $authAdapter->clearIdentity();
	    return $this->_redirect('/user/login');
    }


}

















