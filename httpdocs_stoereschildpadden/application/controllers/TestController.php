<?php

class TestController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$item = Model_Table_Menuitems::getDefaultItem();
		echo('<pre>' . "\n");
		var_dump($item);
		echo('</pre>' . "\n");
		exit;
		exit;
    }

    public function usergroupsAction()
    {
		$id=21;
		$user = Model_User::getInstance($id);
		
		$userName = $user->getFullName();
		
		
		$groups = $user->getGroups();
		echo "<p>De groepen van user $id: $userName</p>\n";
		echo "<pre>";
		var_dump($groups);
		echo "</pre>";
		
		echo "<p>De groepsNamen van user $id: $userName</p>\n";
		$namen = $user->getGroupNames();
		echo "<pre>";
		var_dump($namen);
		echo "</pre>";

		echo "<p>De groepsObjecten van user $id: $userName)</p>\n";
		$ids = $user->getGroupObjects();
		echo "<pre>";
		var_dump($ids);
		echo "</pre>";
		
		if ($user->isGod()) 
		{
			echo "<p>Deze user is goddelijk.</p>\n";
		}
		else
		{
			echo "<p>Deze user is een gewone sterveling.</p>\n";
		}
		
		die('eind van de action');
    }

    public function userrolesAction()
    {
		$id=21;
		$user = Model_User::getInstance($id);
		
		$userName = $user->getFullName();
		
		
		$roles = $user->getRoles();
		echo "<p>De rollen van user $id: $userName</p>\n";
		echo "<pre>";
		var_dump($roles);
		echo "</pre>";
		
		echo "<p>De rolNamen van user $id: $userName</p>\n";
		$namen = $user->getRoleNames();
		echo "<pre>";
		var_dump($namen);
		echo "</pre>";

		echo "<p>De rolObjecten van user $id: $userName)</p>\n";
		$ids = $user->getRoleObjects();
		echo "<pre>";
		var_dump($ids);
		echo "</pre>";
		
		if ($user->isGod()) 
		{
			echo "<p>Deze user is goddelijk.</p>\n";
		}
		else
		{
			echo "<p>Deze user is een gewone sterveling.</p>\n";
		}
		
		die('eind van de action');
    }

    public function queryAction()
    {
		$tbl = new Model_Table_Pages();
		$select = $tbl->select()
			->setIntegrityCheck(false)
		    ->from('pages')
		    ->join('groups','pages.owner=groups.id')
		    ->order(array('groups.name','pages.menutitle'));
	    echo $select;
	    exit;
        
    }
}

