<?php

class PageController extends Zend_Controller_Action
{

    public function init()
    {
		$this->access = new Model_Access();
		$this->view->user = $this->access->getUser();

        // ================================================================
        // = Parse the page parameter and find out which tab it belongs to=
        // ================================================================
                    if($id = $this->getRequest()->getParam('id'))
                    {
            $this->page = Model_Page::getInstance($id);
            $this->view->page = $this->page;
            // $this->tabGroup = Model_Group::getInstance($this->page->owner);
            // $this->view->tabGroup = $this->tabGroup;
                    }
        // $tabs= new Model_AdminTabs();
        // $this->view->adminTabs = $tabs->getList();
        
        //         $menu = new Model_Menu();
        //         // $menu->buildMenu($activeMenuId = null, $parent = 0,$level=0, $maxLevel = 10, $adminMode = false, $lang=null );
        // list($tabMenu,$activePassed) = $menu->buildMenu('',0,0,1, false);
        //         $this->view->tabs = $tabMenu;
    }

    public function indexmetgroepenAction()
    {
        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');
        $this->view->jQuery()->addJavascriptFile('/js/confirm_delete.js');

		$this->view->title = 'paginabeheer';
        $tabs= new Model_AdminTabs();
        $this->view->adminTabs = $tabs->getList();

		if(!$this->access->isGod() and !$this->access->hasRole('editor'))
		{
			$this->view->sysMessages .= "<p>FOUT: onvoldoende rechten om pagina's te beheren.</p>";
			return $this->_forward('noauth','error');
		}
		
		
		$pageTable = new Model_Table_Pages();
		if($this->access->isGod())
		{
			// god mag alle pagina's zien
			$select = $pageTable->select()
			    ->setIntegrityCheck(false)
			    ->from('pages')
			    ->join('groups','groups.id=pages.owner',array())
				->order(array('groups.name','pages.menutitle'));
			$this->view->pages = $pageTable->fetchAll($select);
		}
		else
		{
			// alle pagina's waarvan de ownergroep een van de groepen van de hiudige gebruiker is.
			$select = $pageTable->select()
			    ->setIntegrityCheck(false)
			    ->from('pages')
			    ->join('groups','groups.id=pages.owner',array())
				->where('owner IN(?)', $this->view->user->getGroups())
				->order(array('groups.name','pages.title'));
			$this->view->pages = $pageTable->fetchAll($select);
		}

		$this->view->flowMenuItems = array(
			"pagina toevoegen" => '/page/new',
			'groepstoewijzing' => '/page/groepstoewijzing'
		);
    }

    public function indexAction()
    {
        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');
        $this->view->jQuery()->addJavascriptFile('/js/confirm_delete.js');

		$this->view->title = 'paginabeheer';
        $tabs= new Model_AdminTabs();
        $this->view->adminTabs = $tabs->getList();

		if(!$this->access->isGod() and !$this->access->hasRole('editor'))
		{
			$this->view->sysMessages .= "<p>FOUT: onvoldoende rechten om pagina's te beheren.</p>";
			return $this->_forward('noauth','error');
		}
		
		$pageTable = new Model_Table_Pages();
		$select = $pageTable->select()
			->order('title');
		$this->view->pages = $pageTable->fetchAll($select);
		$this->view->flowMenuItems = array(
			"pagina toevoegen" => '/page/new',
		);
    }

    public function newAction()
    {
        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');
		
		// ================
		// = link tinymce =
		// ================
        $this->view->jQuery()->addJavascriptFile('/js/tiny_mce/jquery.tinymce.js');
        $this->view->jQuery()->addJavascriptFile('/js/tinymce_init.js');
		
		$this->view->title = 'nieuwe pagina';
        $tabs= new Model_AdminTabs();
        $this->view->adminTabs = $tabs->getList();

		$this->view->flowMenuItems = array(
			"paginabeheer" => '/page/index'
		);
		
		if(!$this->access->isGod() and !$this->access->hasRole('editor'))
		{
			$this->view->sysMessages .= "<p>FOUT: onvoldoende rechten om een pagina aan te maken.</p>";
			return;
		}
		
		$form = new Form_Page();
		$form->removeElement('id');
		$this->view->form = $form;
		$form->addElement('submit', 'submit', array('label' => 'OKE'));

		// ===============================
		// = Check and save after submit =
		// ===============================
		if($this->getRequest()->isPost())
		{
			if(true) 
			{
				// @todo: probleem met de validationals ik een parent page opgeef.
				$form->isValid($this->getRequest()->getPost());
				$page = Model_Page::getInstance();
				$page->setFromArray($form->getValues());
				$page->save();
				$this->_redirect('/page/index');
			}
		}
		
        // // ==================================================================
        // // = Add the appropriate options to the parent selector             =
        // // ==================================================================
        // $tbl = new Model_Table_Pages();
        // $select = $tbl->select()->order('slug');
        // $pageRows = $tbl->fetchAll($select);
        // foreach($pageRows as $pageRow)
        // {
        //  $form->getElement('parent')
        //      ->addMultiOption($pageRow->id, 'onder "' . $pageRow->slug . '"');
        // }
        //  
		$this->view->form = $form;
    }

    public function deleteAction()
    {
        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');

		$this->view->title = "verwijder pagina";
        $tabs= new Model_AdminTabs();
        $this->view->adminTabs = $tabs->getList();

		if (!$id = $this->getRequest()->getParam('id')) 
		{
			$this->view->sysMessages .= "<p>Fout bij verwijderen: geen ID opgegeven.</p>\n";
			return false;
		}
		
		$this->view->flowMenuItems = array(
			'paginabeheer'  => '/page/index',
			'nieuwe pagina' => '/page/new',
			'wijzig pagina' => '/page/edit/id/' . $id,
		);
		
		$this->access->setResource($this->page);
		if(!$this->access->isGod() and !$this->access->hasRole('editor'))
		{
			$this->view->sysMessages = "<p>Fout: U hebt onvoldoende rechten om deze pagina te verwijderen</p>\n";
			return;
		}
		
		$this->page->delete();
				
		$this->_redirect('/page/index');
    }

    public function editAction()
    {
        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');
        $this->view->jQuery()->addJavascriptFile('/js/confirm_delete.js');
        $this->view->jQuery()->addJavascriptFile('/js/tiny_mce/plugins/imagemanager/js/mcimagemanager.js');
        $this->view->jQuery()->addJavascriptFile('/js/tiny_mce/plugins/filemanager/js/mcfilemanager.js');

		// ================
		// = link tinymce =
		// ================
        $this->view->jQuery()->addJavascriptFile('/js/tiny_mce/jquery.tinymce.js');
        $this->view->jQuery()->addJavascriptFile('/js/tinymce_init.js');
		
		$this->view->title = "bewerk pagina";
        $tabs= new Model_AdminTabs();
        $this->view->adminTabs = $tabs->getList();

		if (!$id = $this->getRequest()->getParam('id')) 
		{
			$this->view->sysMessages .= "<p>Fout bij wijzigen van een pagina: geen ID opgegeven.</p>\n";
			return false;
		}
		
		$this->view->flowMenuItems = array(
			'paginabeheer'          => '/page/index',
			'nieuwe pagina'         => '/page/new',
			'verwijder deze pagina' => '/page/delete/id/' . $id,
		);

		$this->access->setResource($this->page);
		if(!$this->access->isGod() and !$this->access->isEdit())
		{
			$this->view->sysMessages = "<p>Fout: U hebt onvoldoende rechten om deze pagina te wijzigen</p>\n";
			return;
		}

		$form = new Form_Page();
		$form->addElement('submit', 'submit', array('label' => 'OKE'));
		
		// ===============================
		// = Check and save after submit =
		// ===============================
		if ($this->getRequest()->isPost()) {
			// @todo: probleem met de validation als ik een parent page opgeef.
			if ($form->isValid($_POST) or true) {

				// overschrijf de gegevens in de db met de gegevens uit het form
				$this->page->setFromArray($form->getValues());
				$this->page->save();
                $this->_redirect('/page/index');
			}
		}

		// Vul de het formulier met gegevens uit de db.
		$pageData = $this->page->toArray();
		$form->populate($pageData);
		$this->view->form = $form;

    }

    public function groepstoewijzingAction()
    {
        $this->view->layout()->setLayout('admin');
		$this->view->headLink()->appendStylesheet('/css/codip_admin.css');
		$this->view->title = "Groepsindeling pagina's";

        $tabs= new Model_AdminTabs();
        $this->view->adminTabs = $tabs->getList();

		$this->view->flowMenuItems = array(
			'paginabeheer'  => '/page/index',
			'nieuwe pagina' => '/page/new',
		);
		
		if(!$this->access->isGod() and !$this->access->hasRole('editor'))
		{
			$this->view->sysMessages .= "<p>FOUT: onvoldoende rechten om pagina's te beheren.</p>";
			return;
		}
		
		
		
		if ($this->getRequest()->isPost())
		{
			foreach ($_POST['pageGrp'] as $pageId => $groups) {
				$page = Model_Page::getInstance($pageId);
				$relations=array();
				foreach($groups as $grpId => $yon)
				{
					if($yon == 'yes')
					{
						$relations[] = $grpId;
					}
				}
				$page->setGroups($relations);
				$page->save();
			}
		}
		
        $pagesPerGroup = array();
        
        // ===============================================================
        // = Haal de pagina's op van de groepen waar de user lid van is. =
        // ===============================================================
        if($this->view->user->isGod())
        {
            $tbl = new Model_Table_Groups();
            $select = $tbl->select()
                ->order('name');
            $userGroups = $tbl->fetchAll($select);
        }
        else
        {
            $userGroups = $this->view->user->getGroupObjects();
        }
        
        foreach($userGroups as $group)
        {
            $tbl = new Model_Table_Pages();
            $pages = $tbl->getOwnedBy($group->id);
            if(count($pages) > 0)
            {
                $pagesPerGroup[$group->name] = $pages;
            }
        }
        $this->view->pagePerGroup = $pagesPerGroup;

		$grpTbl = new Model_Table_Groups();
		$select = $grpTbl->select()
			->order('name');
		$this->view->groups = $grpTbl->fetchAll($select);
    }
    
    public function previewAction()
    {
        $this->view->layout()->setLayout('layout');
		$this->view->headLink()->appendStylesheet('/css/codip_pages.css');

        // =============================
        // = create the tab menu =
        // =============================
        $menu = new Model_Menu();
        // $menu->buildMenu($activeMenuId = null, $parent = 0,$level=0, $maxLevel = 10, $adminMode = false, $lang=null );
        list($tabMenu,$activePassed) = $menu->buildMenu('',0,0,1, false);
        $this->view->tabs = $tabMenu;
		if(!$pageId = $this->getRequest()->getParam('id'))
		{
            return $this->_forward('noid');
		}

        $this->page = Model_Page::getInstance($pageId);
        $this->view->page = $this->page;

		$this->view->title = $this->page->title;
		
		$this->access->setResource($this->view->page);

		if(!$this->access->isGod() and !$this->access->isRead())
		{
            $this->_forward('noauth','error');
			return;
		}
		$this->view->pageMenu = '
		<ul>
			<li >
				<a href="/page/index"><span>paginaoverzicht</span></a>
			</li>
		</ul>
		';

    }

	public function showAction()
	{
        $this->view->layout()->setLayout('layout');
		$this->view->headLink()->appendStylesheet('/css/codip_pages.css');

 		if(!$menuId = $this->getRequest()->getParam('m'))
		{
            return $this->_forward('noid');
            // throw new Zend_Exception('Fout bij het tonen van een pagina: geen id opgegeven');
		}

       // =============================
        // = create the tab menu =
        // =============================
        $menu = new Model_Menu();
        // $menu->buildMenu($activeMenuId = null, $parent = 0,$level=0, $maxLevel = 10, $adminMode = false, $lang=null );
        list($tabMenu,$activePassed) = $menu->buildMenu($menuId,0,0,1, false);
        $this->view->tabs = $tabMenu;

        $menuItem = Model_Menuitem::getInstance($menuId);

        $this->page = Model_Page::getInstance($menuItem->target);
        $this->view->page = $this->page;

		$this->view->title = $this->page->title;
		
		$this->access->setResource($this->view->page);
		
        // toegangrechten controle is voorlopig gedisabled
		if(false and !$this->access->isGod() and !$this->access->isRead())
		{
            $this->_forward('noauth','error');
			return;
		}
		
        $menuBranch = $menuItem->getIdBranch();
        $menu = new Model_Menu();
        // $menu->buildMenu($activeMenuId = null, $parent = 0,$level=0, $maxLevel = 10, $adminMode = false, $lang=null );
		list($menuList,$activePassed) = $menu->buildMenu($menuId,$menuBranch[0],1,10, false);
		$this->view->pageMenu = $menuList;
		
	}
	
	public function noidAction()
	{
	    $this->view->title = 'Ongeldige pagina opgegeven';
	}
}











