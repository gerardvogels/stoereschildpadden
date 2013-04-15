<?php

class Model_Table_Pages extends Zend_Db_Table_Abstract
{
	protected $_name = 'pages';
	protected $_dependentTables = array('Model_Table_PageGroup');
	protected $_rowClass = 'Model_Page';
	
	public function getPagesForGroup($group_inp,$order='menutitle')
	{

		// ==============================
		// = parsen van input parameter =
		// ==============================
		if(is_object($group_inp))
		{
			$group = $group_inp;
		}
		elseif(is_numeric($group_inp))
		{
			$group = Model_Group::getInstance($group_inp);
		}
		elseif(is_string($group_inp))
		{
			$group = Model_Group::getInstanceByName($group_inp);
		}
		else
		{
			throw new Zend_Exception('Fout: parameter groep moet een id (int) of een object zijn');
		}
		
		// =======================
		// = Haal de pagina's op =
		// =======================
		$select = $this->select('pages.*')
					->joinLeft('page_group','page_group.page = pages.id',array())
					->where('page_group.group = ?', $group->id)
					->order('pages.' . $order);
		$pages = $this->fetchAll($select);
		return $pages;
	}
	
	public function getOwnedBy($group_inp,$order='menutitle')
	{

		// ==============================
		// = parsen van input parameter =
		// ==============================
		if(is_object($group_inp))
		{
			$groupId = $group_inp->id;
		}
		elseif(is_numeric($group_inp))
		{
			$groupId = $group_inp;
		}
		elseif(is_string($group_inp))
		{
			$group = Model_Group::getInstanceByName($group_inp);
			$groupId = $group->id;
		}
		else
		{
			throw new Zend_Exception('Fout: parameter groep moet een id (int) of een object zijn');
		}
		
		// =======================
		// = Haal de pagina's op =
		// =======================
		$select = $this->select()
					->where('owner = ?', $groupId)
					->order($order);
		$pages = $this->fetchAll($select);
		return $pages;
	}
	
	
	
}
