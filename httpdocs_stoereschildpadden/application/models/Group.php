<?php
class Model_Group extends Zend_Db_Table_Row_Abstract
{
	protected $_tableClass = 'Model_Table_Groups';
	protected $users;

	static function getInstance($id=null)
	{
		$table = new Model_Table_Groups;
		if(!$id)
		{
		    $group = $table->createRow();
		}		
	    elseif(is_numeric($id))
	    {
	        $group = $table->find($id)->current();
	    }
	    else
	    {
	        $select = $table->select()->where('`name` = ?', $id);
	        $group = $table->fetchRow($select);
	    }
		return $group;
	}
	
	public function addUser($userId)
	{
		$relations = new Model_Table_UserGroup;
		$relation = $relations->createRow();
		$relation->user = $userId;
		$relation->group = $this->id;
		$relation->save();
		return $relation;
	}
	
	static function getIdByName($name)
	{
		$tbl = new Model_Table_Groups();
		$select = $tbl->select()->where('`name` = ?', $name);
		$group = $tbl->fetchRow($select);
		return $group->id;
	}
	
	public function getStartPageName()
	{
	    $page = Model_Page::getInstance($this->startPage);
	    if(!$return = $page->title)
	    {
	        $return = 'startpagina kiezen';
	    }
	    return $return;
	}

}
	
