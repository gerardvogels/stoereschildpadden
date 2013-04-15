<?php

class MenuController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->layout()->setLayout('admin');

		$this->access = new Model_Access();
		$this->view->user = $this->access->getUser();

        $tabs= new Model_AdminTabs();
        $this->view->adminTabs = $tabs->getList();

    }

    public function indexAction()
    {
        $this->_forward('wijzig');
    }

    public function newitemAction()
    {

        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');

        $this->view->title = 'Nieuw menuitem aanmaken';
        $item = Model_Menuitem::getInstance();
        $form = new Form_NewMenuitem();
        if ($this->getRequest()->isPost()) 
        {
			if ($form->isValid($_POST))
			{
			    $this->view->sysmessage = 'formulier is goed ingevuld';
			    $item->setFromArray($form->getValues());
			    $item->save();
			    $menu = new Model_Menu();
			    $menu->numberAll();
			    return $this->_redirect('/menu/wijzig');
			}
			else
			{
			    $this->view->sysmessage = 'Het formulier is niet volledig juist ingevuld';
			}
        }
        $this->view->form = $form;
        $this->view->flowMenuItems = array(
            'menubeheer'       => '/menu/index',
        );
    }

    public function edititemAction()
    {

        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');

        if(!$id = $this->getRequest()->getParam('id'))
        {
            $this->view->sysmessage = "Menuitem kan niet worden geopend. Geen id opgegeven";
            return;
        }

        $item = Model_Menu::getItem($id);
        $form = new Form_EditMenuitem();
        if ($this->getRequest()->isPost()) 
        {
			if ($form->isValid($_POST))
			{
			    $this->view->sysmessage = 'formulier is goed ingevuld';
			    $item->setFromArray($form->getValues());
			    $item->save();
			    $menu = new Model_Menu();
			    $menu->numberAll();
			    return $this->_redirect('/menu/wijzig');
			}
			else
			{
			    $this->view->sysmessage = 'Het formulier is niet volledig juist ingevuld';
			}
        }
        $this->view->title = 'Bewerk menuitem ' . $item->getPath();
        $form->populate($item->toArray());

        // zet juiste parent opties voor het select element
        $tbl = new Model_Table_Menuitems();
        $select = $tbl->select()
            ->order('sequencenumber');
        $parents = $tbl->fetchAll($select);
        foreach($parents as $parent)
        {
            if(!in_array($item->id, $parent->getIdBranch()))
            {
                $options[$parent->id] = $parent->getPath();
            }
        }
        asort($options);
        $form->getElement('parentid')->addMultiOptions($options);
        
        $this->view->form = $form;
        $this->view->flowMenuItems = array(
            'terug naar admin' => '/admin/index',
            'menubeheer'       => '/menu/index',
            'nieuw menuitem'   => '/menu/newitem',
        );
    }

    public function deleteitemAction()
    {
        $this->view->title = 'Menuitem verwijderen';
        $this->view->flowMenuItems = array(
            'terug naar admin' => '/admin/index',
            'uitloggen'        => '/user/logout',
            'blokbeheer'       => '/block/index',
            'paginabeheer'     => '/page/index',
            'menubeheer'       => '/menu/index',
            'nieuw menuitem'   => '/menu/new',
        );
        
        if(!$id = $this->getRequest()->getParam('id'))
        {
            $this->view->sysmessage = "Menuitem kan niet worden verwijderd. Geen id opgegeven";
            return;
        }
        
        $item = Model_Menu::getItem($id);
        $item->delete();
        return $this->_redirect('/menu/index');
    }

    public function wijzigAction()
    {
        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');

		if(!$this->access->isGod() and !$this->access->hasRole('editor'))
		{
			$this->view->sysMessages .= "<p>FOUT: onvoldoende rechten om de menu's te beheren.</p>";
			return $this->_forward('noauth','error');
		}
		
        $this->view->title = 'Menuoverzicht';
        $menu = new Model_Menu();
		$array = $menu->buildMenu('',0,0,1000, true);
		$menu =  $array[0];
        $this->view->flowMenuItems = array(
            'menubeheer'       => '/menu/index',
            'nieuw menuitem'   => '/menu/newitem',
        );
		$this->view->menu = $menu;
    }

    public function testAction()
    {
        $tbl = new Model_Table_Menuitems();
        $select = $tbl->select()
            ->where('id != ?', 0)
            ->order('sequencenumber');
        $items = $tbl->fetchAll($select);
        foreach($items as $item)
        {
            echo "<p>" . $item->getPath() . "</p>\n";
        }
        exit;
    }

    public function moveupAction()
    {
        if(!$id = $this->getRequest()->getParam('id'))
        {
            $this->view->sysmessage = "Menuitem kan niet worden geopend. Geen id opgegeven";
            return;
        }

        $item = Model_Menuitem::getInstance($id);
        $item->moveUp();
        return $this->_redirect('/menu/wijzig');
    }

    public function movedownAction()
    {
        if(!$id = $this->getRequest()->getParam('id'))
        {
            $this->view->sysmessage = "Menuitem kan niet worden geopend. Geen id opgegeven";
            return;
        }
        $item = Model_Menuitem::getInstance($id);
        $item->moveDown();
        return $this->_redirect('/menu/wijzig');
    }

}















