<?php

class Model_Menuitem extends Zend_Db_Table_Row_Abstract
{
    protected $_tableClass = 'Model_Table_Menuitems';
    

	static function getInstance($id=null)
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
	
	static function getAllItems()
	{
		$tbl = new Model_Table_Menuitems();
		return $tbl->fetchAll();
	}

	public function saveMenuitem()
	{
		$this->save();
	}
	
	public function getForm()
	{
	    $form = new Form_Menuitem();
	    if ($this->id) 
	    {
	       $form->populate($this->toArray());
	    }
	    return $form;
	}

    public function getParent()
    {
        return $this->getInstance($this->parentid);
    }
    
    public function moveUp ()
    {
        if(!$this->sequencenumber)
        {
            $menu = new Model_Menu();
            $menu->numberAll();
        }
        $position = $this->sequencenumber;
        if ($position <= 1)
        {
         // this is already the first item
         return false;
        } 
        else 
        {
            $tbl = new Model_Table_Menuitems();
            $select = $tbl->select()
                ->where('parentid = ?', $this->parentid)
                ->where('sequencenumber < ?', $this->sequencenumber)
                ->order('sequencenumber DESC');
            $switchItem = $tbl->fetchRow($select);
            $oldPosition = $this->sequencenumber;
            $newPosition = $switchItem->sequencenumber;
            $this->sequencenumber = $newPosition;
            $this->save();
            $switchItem->sequencenumber = $oldPosition;
            $switchItem->save();
            return true;
        }
    }

    private function _getLastPosition ()
    {
        $tbl = new Model_Table_Menuitems();
        $db = $tbl->getAdapter();
        $query = "SELECT MAX(sequencenumber) as lastPosition from menuitems where parentid = '" . $this->parentid . "'";
        $result = $db->fetchRow($query);
        $lastItem = $result['lastPosition'];
        return $lastItem;
    }

    public function moveDown ()
    {
        if(!$this->sequencenumber)
        {
            $menu = new Model_Menu();
            $menu->numberAll();
        }
        $oldPosition = $this->sequencenumber;
        $lastPosition = $this->_getLastPosition();
        if ($oldPosition >= $lastPosition)
        {
            return false;
        }

        $tbl = new Model_Table_Menuitems();
        $select = $tbl->select()
            ->where('parentid = ?', $this->parentid)
            ->where('sequencenumber > ?', $oldPosition )
            ->order('sequencenumber ASC');
        $switchItem = $tbl->fetchRow($select);
        $newPosition = $switchItem->sequencenumber;
        $this->sequencenumber = $newPosition;
        $this->save();
        $switchItem->sequencenumber = $oldPosition;
        $switchItem->save();
        return true;
    }
  
    public function getPath()
    {
       $sep = '/';
       if ($this->parentid && $this->parentid > 0) {
           // if the node has a parent, find it
           $parent = Model_Menuitem::getInstance($this->parentid);

           return $parent->getPath() . $sep . $this->menutext;
       } else {
           // this is a top node, so the path is only its name
           return $this->menutext;
       }
    }
    
    public function getIdBranch()
	{
		if ($this->parentid && $this->parentid > 0) 
		{
			$parent = Model_Menuitem::getInstance($this->parentid);
			if (!get_class ($parent) == "Model_Menuitem") 
			{
				die('FOUT: Geen menuitem, parent-id = ' . $this->parentid . '<br> Neem contact op met vogels-it.');
			}
			$array = $parent->getIdBranch();
			$array[] = $this->id;
			return $array;
		} 
		else 
		{
			// this is a top node, so the path is only its name
			return array($this->id);
		}
	}

	public function buildPathBak()
	{
	    $sep = '/';
	    $this->path = $this->menutext;
	    $idbranch = array();
	    $idbranch[] = $this->id;
	    $parentid = $this->parentid;
	    $watchdog = 0;
	    while($parentid != 0 )
	    {
	        if ($watchdog > 100) 
	        {
	           die('Dit lijkt een oneindige loop te worden');
	        }
	        $watchdog++;
	        $idbranch[] = $parentid;
	        $parent = Model_Menuitem::getInstance($parentid);
	        $this->path = $parent->menutext . $sep . $this->path;
	        $parentid = $parent->parentid;
	    }
	    $this->idbranch = serialize(array_reverse($idbranch));
	}
	
    public function delete()
    {
        // verwijder eventuele children
        $tbl = new model_Table_Menuitems();
        $select = $tbl->select()
            ->where('parentid = ?', $this->id);
        $items = $tbl->fetchAll($select);
        foreach($items as $item)
        {
            $item->delete();
        }
        // verwijder dit item zelf
        parent::delete();
    }
}

