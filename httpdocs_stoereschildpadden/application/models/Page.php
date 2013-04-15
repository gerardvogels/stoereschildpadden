<?php
class Model_Page extends Zend_Db_Table_Row_Abstract
{
	protected $_tableClass = 'Model_Table_Pages';
	protected $groups;

	static function getInstance($id=null)
	{
		$table = new Model_Table_Pages;
		if(!$id)
		{
		    $page = $table->createRow();
		}
	    elseif(is_numeric($id))
	    {
	        $page = $table->find($id)->current();
	    }
	    else
	    {
	        $select = $table->select()->where('`slug` = ?', $id);
	        $page = $table->fetchRow($select);
	    }
	    if(is_numeric($page->id))
	    {
	        $page->loadGroups();
	    }
		return $page;
	}
	
	public function save()
	{
		parent::save();
	
		// remove all exsisting group relations for this page from the db
		if (isset($this->id)) 
		{
			$id = $this->id;
			$relations = new Model_Table_PageGroup();
			$relations->delete('page = '.intval($this->id));
		}
		else
		{
			throw new Zend_Exception('Fout bij het opslaan van deze pagina, ID niet bekend.');
		}

		// store the current groups
		$relationTable = new Model_Table_PageGroup();
		foreach($this->groups as $group)
		{
			$relation = $relationTable->createRow();
			$relation->group = $group;
			$relation->page = $id;
			$relation->save();
		}

	}

	public function loadGroups()
	{
		// =======================================
		// = haal de groepen voor deze pagina op =
		// =======================================
		$relations = $this->findDependentRowset('Model_Table_PageGroup');
		$groups = array();
		foreach($relations as $relation)
		{
			$groups[] = $relation->group;
		}
		$this->groups = $groups;
	}
	
	public function setFromArray($array)
	{
		parent::setfromArray($array);
		// zet de groups
		if(is_array($array['groups']))
		{
			$this->groups = $array['groups'];
		}
		else
		{
			$this->groups = array();
		}
	}

	public function toArray()
	{
		$array = parent::toArray();
		$array['groups'] = $this->groups;
		return $array;
	}
	
	public function getGroups()
	{
		return $this->groups;
	}

	public function setGroups($array)
	{
		$this->groups = $array;
	}

}
	
