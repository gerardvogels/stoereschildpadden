<?php

class Form_AgendaItem extends Zend_Form
{

    public function init()
    {

	    $this->setMethod('post');
	    $access = new Model_Access();
		
		// ======
		// = id =
		// ======
		$id = $this->createElement('hidden','id')->setDecorators(array('ViewHelper'));
		$this->addElement($id);
		
		// =========
		// = title =
		// =========
		$element = $this->createElement('text','title')
							->setRequired(true)
							->setLabel('titel (korte omschrijving)')
							->addFilter('StringTrim');
		$this->addElement($element);
		
		// ========
		// = date =
		// ========
		$element = $this->createElement('text','date')
							->setRequired(true)
							->setLabel('datum (dd-mm-jjjj)')
							->addValidator(new Zend_Validate_Date(array('format' => 'd.M.yyyy')));
		$this->addElement($element);
		
		// =============
		// = starttime =
		// =============
		$element = $this->createElement('text','starttime')
							->setRequired(true)
							->setLabel('aanvangstijd (h:m)');
		$this->addElement($element);
		
		// ============
		// = duration =
		// ============
		$element = $this->createElement('text','duration')
							->setRequired(false)
							->setLabel('tijdsduur (h:m)');
		$this->addElement($element);
		
		// ============
		// = location =
		// ============
		$element = $this->createElement('textarea','location')
							->setRequired(false)
							->setLabel('locatie');
		$this->addElement($element);
		
		// ===============
		// = description =
		// ===============
		$element = $this->createElement('textarea','description')
							->setRequired(false)
							->setLabel('Beschrijving van de inhoud van het evenement');
		$this->addElement($element);

		// =========
		// = owner =
		// =========
		$groupIds = $access->getUserGroups();
		$element = $this->createElement('select', 'owner')
			->setRequired(true)
			->setLabel("Onder welke groep (tab) valt deze dit agendaitem ?");
		$table = new Model_Table_Groups();
		// toon de groepen waar de huidige user lid van is
		foreach($groupIds as $groupId)
		{
			$group = $table->find($groupId)->current();
		    if ($group->name != 'publiek') 
		    {
    			$element->addMultiOption($group->id, $group->name);
		    }
		}
		$this->addElement($element);
		
		// ==========
		// = groups =
		// ==========
		$element = $this->createElement('MultiCheckbox', 'groups')
			->setLabel('De groepen die dit agendaitem mogen zien');
		
		$tbl = new Model_Table_Groups();
		$groupRows = $tbl->fetchAll();
		foreach($groupRows as $group)
		{
			$element->addMultiOption($group->id, $group->name);
		}
		$this->addElement($element);
		
	}
	
	
}
