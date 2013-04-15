<?php

class Form_User extends Zend_Form
{
	
	protected $access;
	
	public function setAccess($access)
	{
		$this->access = $access;
	}

    public function init()
    {
		$access = new Model_Access();
		
	    $this->setMethod('post');
		// ======
		// = id =
		// ======
		$id = $this->createElement('hidden','id')->setDecorators(array('ViewHelper'));
		$this->addElement($id);
		
		// ============
		// = voornaam =
		// ============
		$element = $this->createElement('text', 'voornaam')
			->setLabel('Voornaam')
			->addFilter('StringTrim');
		$this->addElement($element);
		
		// =================
		// = tussenvoegsel =
		// =================
		$element = $this->createElement('text', 'tussenvoegsel')
			->setLabel('Tussenvoegsel')
			->addFilter('StringTrim');
		$this->addElement($element);
		
		// ==============
		// = achternaam =
		// ==============
		$element = $this->createElement('text', 'achternaam')
			->setLabel('Achternaam')
			->setRequired(true)
			->addFilter('StringTrim');
		$this->addElement($element);
		
		// =========
		// = email =
		// =========
		$element = $this->createElement('text', 'email')
			->setLabel('E-mail adres')
			->setRequired(true)
			->addFilter('StringTrim')
			->addVAlidator('EmailAddress')
			->addValidator('Db_NoRecordExists',false,
				array(
					'table'=>'users',
					'field'=>'email',
					'messages'=>'Er bestaat al een gebruiker met dit email adres'
				)
			);
		$this->addElement($element);
		
		// ============
		// = password =
		// ============
		$element = $this->createElement('password', 'password')
			->setLabel('Password')
			->setRequired(true)
			->addFilter('StringTrim');
		$this->addElement($element);
		
		// ========
		// = info =
		// ========
		$element = $this->createElement('textarea', 'info')
			->setLabel('Extra informatie')
			->setRequired(false);
		$this->addElement($element);
		
		// ==========
		// = groups =
		// ==========
		
		if ($access->getUser())	$groupRows = $access->getUser()->getGroupObjects();
		else 					$groupRows = array();
		
		$element = $this->createElement('MultiCheckbox', 'groups')
						->setLabel('Organisaties');
		foreach($groupRows as $group)
		{
			$element->addMultiOption($group->id, $group->name);
		}
		$this->addElement($element);
		
		// =========
		// = roles =
		// =========
		if($access->isGod())	$where = true;
		else 					$where = "name != 'god'";

		$table = new Model_Table_Roles();
		$select = $table->select()
			->where($where)
			->order('name');
		$roles = $table->fetchAll($select );
		$element = $this->createElement('MultiCheckbox', 'roles')
			->setLabel('Rollen');
		foreach($roles as $role)
		{
			$element->addMultiOption($role->id, $role->name);
		}
		$this->addElement($element);
		
	}
















}
