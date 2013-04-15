<?php

class Model_Table_Groups extends Zend_Db_Table_Abstract
{
	protected $_name = 'groups';
	protected $_rowClass = 'Model_Group';
	protected $_dependentTables = array('Model_Table_UserGroup');
	
	public function getMyTargetGroups()
	{
		$access = new Model_Access();
		return $access->getUser()->getGroups();
	}
	
	public function fetchAllByPosition()
	{
	    $select = $this->select()
	        ->order('position');
	    $rows= $this->fetchAll($select);
	    return $rows;
	}
}
