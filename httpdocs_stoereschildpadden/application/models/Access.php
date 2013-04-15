<?php
class Model_Access
{
	public $user;

	public function __construct()
	{
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			// =======================
			// = Get the active user =
			// =======================
			$userId = $auth->getIdentity()->id;
			$this->user = Model_User::getInstance($userId);
		}
		else
		{
			return false;
		}
	}
	
	public function isLoggedin()
	{
		if($this->user)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isEdit()
	{
		if ($this->isGod()) 
		{
			return true;
		}
		
		
        // onderstaand is tijdelijk: blokkeert ale groepsmogelijkheden !!!
		if($this->user)
		{
		    return true;
		}
		
		if(!$this->resource)
		{
			throw new Zend_Exception("Fout: Resource is niet opgegeven.");
		}
		
		switch ($this->resourceClass)
		{
			case 'Model_Page' :
				// edit allowed when current user is resourceOwner owner
				$return = $this->isOwner();
				break;
				
			case 'Model_User' :
				// edit allowed when current user is resourceOwner owner
				$return = $this->isUserEdit();
				break;
				
			default :
				throw new Zend_Exception("FOUT: Kan de toegang niet testen: onbekend resource type.");
		}
		
		return $return;
		
	}

	private function isUserEdit()
	{
		if ($this->isGod()) 
		{
			return true;
		}

		if (!$this->resourceClass == 'Model_User') 
		{
			throw new Zend_Exception('Fout: Resource is geen user.');
		}
		
		$administrator = $this->user;
		$user = $this->resource;
		
		if(!$this->hasRole('administrator'))
		{
			return false;
		}
		
		// ================================================================
		// = check the intersection of the user groups and te page groups =
		// ================================================================
		$intersection = array_intersect($user->getGroups(),$administrator->getGroups());
		if (count($intersection) > 0 or count($user->getGroups()) < 1 ) 
		{
			return true;
		}
		
		return false;
	}

	public function isRead($resource=null)
	{
	    if($resource) $this->setResource($resource);
	    
		if ($this->isGod()) 
		{
			return true;
		}

		if(!$this->resource)
		{
			throw new Zend_Exception("Fout: Resource is nog niet gezet.");
		}
		
		switch ($this->resourceClass)
		{
			case 'Model_Page' :
				$return = $this->isPageRead();
				break;
				
			default :
				throw new Zend_Exception("FOUT: Kan de toegang niet testen: onbekend resource type.");
		}
		
		return $return;
	}
	
	public function hasRole($requestedRole)
	{
		if ($this->isGod()) 
		{
			return true;
		}
		
		if($this->isLoggedIn() and in_array($requestedRole, $this->user->getRoleNames()))
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}

	public function isPageRead()
	{
		if (!$this->resourceClass == 'Model_Page') 
		{
			throw new Zend_Exception('Fout: Resource is geen pagina.');
		}

		if (!$user = $this->user) 
		{
		    // ===============================================
		    // = no user looged in ->user is in group public =
		    // ===============================================
		    $grp = Model_Group::getInstance('publiek');
		    $user = new Model_User();
		    $user->setGroups(array($grp->id));
		}
		
		$page = $this->resource;
		
		if(count($page->getGroups()) < 1)
		{
			// ==========================
			// = page is open to anyone =
			// ==========================
			throw new Zend_Exception('Fout: Geen enkele groep heeft toegang tot pagina ' . $this->page->id . ': ' . $this->page->title);
		}
		else
		{
			// ================================================================
			// = check the intersection of the user groups and te page groups =
			// ================================================================
			$intersection = array_intersect($page->getGroups(),$user->getGroups());

			if (count($intersection) > 0) 
			{
				return true;
			}
			return false;
		}
	}

	public function isOwner()
	{
		if ($this->isGod()) 
		{
			return true;
		}
		
		$user = $this->getUser();
		$resource = $this->resource;
		
		if(
			$this->hasRole('editor')
			and
			in_array($resource->owner, $user->getGroups())
		)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isInGroup($groupId)
	{
		if(in_array($groupId, $this->getUserGroups()))	return true;
		else 											return false;
		
	}

	public function setUser($user)
	{
		if(!get_class($user) == 'Model_User')
		{
			throw new Zend_Exception('Fout: Geen geldige user opgegeven');
		}
		
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}
	
	public function setResource($resource)
	{
		$this->resource = $resource;
		$this->resourceClass = get_class($resource);
	}

	public function getResource($resource)
	{
		return $this->resource;
	}

	public function getResourceClass($resource)
	{
		return $this->resourceClass;
	}

	public function getUserGroups()
	{
		return $this->user->getGroups();
	}

	public function isSelfDelete($user)
	{
		if (!get_class($user) == 'Model_User') 
		{
			throw new Zend_Exception('Fout: Argument moet een "Model_User" object zijn');
		}
		
		if ($user->id == $this->user->id) 
		{
			// =============================================================
			// = The current user tries to remove himself from the system =
			// =============================================================
			return true;
		}
		else
		{
			return false;
		}
		
	}

	public function isGod()
	{
		if($this->isLoggedin() and in_array('god',$this->user->getRoleNames()))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}