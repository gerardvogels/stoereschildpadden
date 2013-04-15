<?php
class Model_Menu
{
	protected $menuList = array();
	
//  public function __construct($tabGroup=null, $page=null, $order='slug')
//  {
// // echo "<p>tabgroup = $tabGroup->id</p>\n";
// // echo "<p>page = $page->id</p>\n";
// // echo "<p>order = $order</p>\n";
// // exit;
//      $this->buildMenu($tabGroup->id,$parentId='0',$order,$level='0',$maxLevel='6');
//      if($page) $this->markActivePath($page->id);
//      return $this;
//  }
	
    public function getItem($id = null)
    {
         $table = new Model_Table_Menuitems;
         if ($id) 
         {
                return $table->find($id)->current();
         } 
         else 
         {
             return $table->createRow();
         }
    }

	function buildMenu($activeMenuId = null, $parent = 0,$level=0, $maxLevel = 10, $adminMode = true, $lang=null )
	{
		// Haal de menuids op die in het pad naar het huidige menuid zitten.
		if(isset($activeMenuId))
		{
			$currentMenuItem = Model_Menuitem::getInstance($activeMenuId);
			$menuItemBranch = $currentMenuItem->getIdBranch();
		}
		else
		{
			$menuItemBranch = array();
		}

		$text = "\n";
		
        // prevent endless looping for development
		if ($level>100) return;
		
        // Haal alle menuitems die onder de huidige parent vallen
		$tbl = new Model_Table_Menuitems();
		$select = $tbl->select()->where('parentid = ?', $parent)->order('sequencenumber');
		$menuItems = $tbl->fetchAll($select);
		$activePass = false;
		
		if (count($menuItems) > 0 and $level < $maxLevel) {
            $text = str_repeat("\t", 2*$level) . '<ul>' . "\n";         
            foreach($menuItems as $item)
			{
			    $active = '';

                // build the submenu for the current level
                // activePassed indicates if the active page is in this tree
                list($childText,$activePassed) = $this->buildMenu($activeMenuId, $item->id, $level+1,$maxLevel, $adminMode);

				if (in_array($item->id,$menuItemBranch)) 
				{
					$active = 'class="active"';
					$activePass = true;
				}
				else
				{
    				$active = '';
					$activePass = false;
				}
				
				if ($level <= $maxLevel-1) 
				{
				    
				    if ($adminMode == true) 
				    {
				        $up               = '<img src="/public/media/img/admin/arrow_up.png" alt="omhoog">';
				        $down             = '<img src="/public/media/img/admin/arrow_down.png" alt="omlaag">';
				        $delimg           = '<img src="/public/media/img/admin/delete.png" alt="omlaag">';
				        $editimg          = '<img src="/public/media/img/admin/editpage.png" alt="bewerk pagina">';
				        $moveUp           = " <a href=\"/menu/moveup/id/$item->id\">$up</a> ";
				        $moveDown         = " <a href=\"/menu/movedown/id/$item->id\">$down</a> ";
						$confirm          = 'class="confirm" data:confirmation="Weet u zeker dat u menuitem ' . $item->getPath() . ' wilt verwijderen?"';
				        $delete           = " <a href=\"/menu/deleteitem/id/$item->id\" $confirm>$delimg</a> ";
                        // $link             = " <a href=\"/menu/edititem/id/$item->id\"><span> [" . $level . '] ' . $item->menutext . "</span></a>";
                        $link             = " <a href=\"/menu/edititem/id/$item->id\"><span>" . $item->menutext . "</span></a>";
    				    $editpage         = " <a href=\"/page/edit/id/$item->target\">$editimg</a>";
    				    $buttonDiv        = "<div class='buttons'>" . $moveDown . $moveUp . $editpage . $delete . "</div>";
				    }
				    else
				    {
    				    $link      = "<a href=\"/page/show/m/$item->id\"><span>" . $item->menutext . "</span></a>";
				        $moveUp    = "";
				        $moveDown  = "";
				        $buttonDiv = "";
				    }
				    
    				$text .= str_repeat("\t", 2*$level+1) . "<li $active>\n";
    				$text .= str_repeat("\t", 2*$level+2) . $buttonDiv . $link . "\n";

    				if (stristr($childText,'<ul')) 
    				{
    				    $text .= $childText;
    				}
    				$text .= str_repeat("\t", 2*$level+1) . "</li>\n";
				}
			}
			$text .= str_repeat("\t", 2*$level) . "</ul>\n";
		}
		return array($text,$activePass);
	}

    public function getList()
    {
     return $this->menuList;
    }

	public function markActivePath($pageId)
	{
		$reverseOutput = array();
		$reverseInput = array_reverse($this->menuList,true);
		$inPath = 0;
		foreach($reverseInput as $item)
		{
		    $itemId = $item['id'];
			if($item['id'] == $pageId) $inPath = 1;
			$item['inPath'] = $inPath;
		    $reverseOutput[$itemId] = $item;
			if($inPath == 1 and $item['level'] < 1) $inPath = 0;
		}
		$this->menuList = array_reverse($reverseOutput, true);
		return $this->menuList;
	}
	
    static function numberAll()
    {
        $tbl = new Model_Table_Menuitems();
        $items = $tbl->fetchAll();
        $isNumbered = array();
        foreach ($items as $item) {
            $parentId = $item->parentid;
            if (!$isNumbered[$parentId]) 
            {
                Model_Menu::renumber($parentId);
                $isNumbered[$parentId] = true;
            }
        }
    }
    
    static function renumber($menuId)
    {
        $tbl = new Model_Table_Menuitems();
        $select = $tbl->select()->where('parentid = ?', $menuId)->order('sequencenumber');
        $menuItems = $tbl->fetchAll($select);
        $loopNumber = 1;
        foreach ($menuItems as $item) {
            $item->sequencenumber = $loopNumber;
            $item->save();
            $loopNumber++;
        }
    }
    
    public function buildTabMenu($selectedMenuItemId)
    {
        $tbl = new Model_Table_Menuitems();
        $select = $tbl->select()
            ->where('parentid = ?', 0)
            ->order('sequencenumber');
        $tabItems = $tbl->fetchAll($select);
        $selectedItem = Model_Menuitem::getInstance($selectedMenuItemId);
        $activeTree = $selectedItem->getIdBranch();
        $activeTabId = $activeTree[0];
        $list = '<ul class="groupTabList">' . "\n";
        foreach($tabItems as $tabItem)
        {
            if($tabItem->id == $activeTabId)
            {
                $active = 'active';
            }
            else
            {
                $active = '';
            }
            $list .= '  <li class="groupTabItem ' . $active . '">' . "\n";
            $list .= '      ' . $tabItem->menutext . "\n";
            $list .= '  </li>' . "\n";
        }
        $list .= '</ul>' . "\n";
        return $list;
    }
    


}