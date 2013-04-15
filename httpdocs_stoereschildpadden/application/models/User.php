<?php
class Model_User extends Zend_Db_Table_Row_Abstract
{
	protected $_tableClass = 'Model_Table_Users';
	protected $groups;
	protected $groupNames;
	protected $groupObjects;
	protected $roles;
	protected $roleNames;
	protected $roleObjects;

	static function getInstance($id=null)
	{
		$table = new Model_Table_Users;
		if ($id) 
		{
			$user = $table->find($id)->current();
			$user->loadGroups();
			$user->loadRoles();
		} 
		else 
		{
			$user = $table->createRow();
		}
		return $user;
	}
	
	public function save()
	{
		parent::save();
		$this->storeGroups();
		$this->storeRoles();
	}

	public function getFullName()
	{
		if(isset($this->tussenvoegsel) and $this->tussenvoegsel != '')
		{
			$fullName = ucfirst($this->voornaam) . ' ' . $this->tussenvoegsel . ' ' . ucfirst($this->achternaam);
		}
		else
		{
			$fullName = ucfirst($this->voornaam) . ' ' . ucfirst($this->achternaam);
		}
		return $fullName;
	}

	public function addToGroup($groupId)
	{
		$relations = new Model_Table_UserGroup;
		$relation = $relations->createRow();
		$relation->user = $tjis->id;
		$relation->group = $groupId;
		$relation->save();
		return $relation;
	}

	public function setFromArray($array)
	{
		parent::setfromArray($array);
		$this->setGroups($array['groups']);
		$this->setRoles($array['roles']);
	}


	// ====================================
	// = afhandeling van groepsinformatie =
	// ====================================
	
	public function loadGroups($order='name')
	{
		$this->groupObjects = array();
		$this->groups = array();
		$this->groupNames = array();
		$tbl = new Model_Table_Groups();
		$select = $tbl->select()
		    ->where('`name` != ?', 'god')
		    ->order($order);
		$groupObjects = $this->findManyToManyRowset('Model_Table_Groups','Model_Table_UserGroup',null,null,$select);
		foreach($groupObjects as $object)
		{
			$this->groupObjects[] = $object;
			$this->groupNames[] = $object->name;
			$this->groups[] = $object->id;
		}
	}
	
	public function setGroups($idArray=array())
	{
		if(!is_array($idArray) or count($idArray) < 1) return;
		$this->groups = $idArray;
		
		$table= new Model_Table_Groups();
		$select = $table->select()->where('id IN(?)', $this->groups);
		$this->groupObjects = $table->fetchAll($select);
		
		$this->groupNames = array();
		foreach($this->groupObjects as $object)
		{
			$this->groupNames[] = $object->name;
		}
	}

	public function getGroups()
	{
		return $this->groups;
	}
	
	public function getGroupNames()
	{
		return $this->groupNames;
	}
	
	public function getGroupObjects()
	{
		return $this->groupObjects;
	}
	
	public function storeGroups()
	{
		$relations = new Model_Table_UserGroup();
		$relations->delete('user = '.intval($this->id));
		foreach($this->groups as $groupId)
		{
			if(!is_numeric($groupId)) throw new Zend_Exception("Fout: $groupId is niet numeriek en kan geen ID zijn.");
			$relation = $relations->createRow();
			$relation->group = $groupId;
			$relation->user = $this->id;
			$relation->save();
		}
	}
	
	public function getFellowGroupMemberObjects()
	{
		$fellowGroupMemberObjects = array();
		$tbl = new Model_Table_Users();
		$myGroupIds = $this->getGroups();

		$tbl = new Model_Table_UserGroup();
		$select = $tbl->select()->where("`group` IN(?)", $myGroupIds);
		$o_relations = $tbl->fetchAll($select);

		$userIds = array();
		foreach($o_relations as $relation)
		{
			$userIds[$relation->user] = $relation->user;
		}

		$tbl = new Model_Table_Users();
		$select =$tbl->select()
					->where('`id` IN(?)',$userIds)
					->order('achternaam')
					->order('voornaam');
		$o_fellowGroupMembers = $tbl->fetchAll($select);

		$a_fellowGroupMembers = array();
		foreach($o_fellowGroupMembers as $o_member)
		{
			if(!$o_member->isGod())
			{
				$a_fellowGroupMembers[] = $o_member;
			}
		}
		return $a_fellowGroupMembers;
	}


	// ==================================
	// = afhandeling van rol-informatie =
	// ==================================
	
	public function loadRoles($order='name')
	{
		$this->roleObjects = array();
		$this->roles = array();
		$this->roleNames = array();
		$tbl = new Model_Table_Roles();
		$select = $tbl->select()->order($order);
		$roleObjects = $this->findManyToManyRowset('Model_Table_Roles','Model_Table_UserRole',null,null,$select);
		foreach($roleObjects as $object)
		{
			$this->roleObjects[] = $object;
			$this->roleNames[] = $object->name;
			$this->roles[] = $object->id;
		}
	}
	
	public function setRoles($idArray=array())
	{
		if(!is_array($idArray) or count($idArray) < 1) return;
		$this->roles = $idArray;
		
		$table= new Model_Table_Roles();
		$select = $table->select()->where('id IN(?)', $this->roles);
		$this->roleObjects = $table->fetchAll($select);
		
		$this->roleNames = array();
		foreach($this->roleObjects as $object)
		{
			$this->roleNames[] = $object->name;
		}
	}

	public function getRoles()
	{
		return $this->roles;
	}
	
	public function getRoleNames()
	{
		return $this->roleNames;
	}
	
	public function getRoleObjects()
	{
		return $this->roleObjects;
	}
	
	public function storeRoles()
	{
		$relations = new Model_Table_UserRole();
		$relations->delete('user = '.intval($this->id));
		foreach($this->roles as $roleId)
		{
			if(!is_numeric($roleId)) throw new Zend_Exception("Fout: $roleId is niet numeriek en kan geen ID zijn.");
			$relation = $relations->createRow();
			$relation->role = $roleId;
			$relation->user = $this->id;
			$relation->save();
		}
	}
	
	public function toArray()
	{
		$array = parent::toArray();
		$array['groups'] = $this->getGroups();
		$array['roles'] = $this->getRoles();
		return $array;
	}

	// ===============================================
	// = Is this this very special omni potent user? =
	// ===============================================
	public function isGod()
	{
		$tbl = new Model_Table_Roles();
		$select = $tbl->select()->where('name=?','god');
		if(!$tblRows = $this->findManyToManyRowset($tbl,'Model_Table_UserRole',null,null,$select))
		{
			throw new Zend_Exception('Fout bij het bepalen van uw goddelijkheid');
		}
		if($tblRows->count() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
	
