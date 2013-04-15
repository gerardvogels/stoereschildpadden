<?php

class Form_Page extends Zend_Form
{

    public function init()
    {
	
		$access = new Model_Access();

	    $this->setMethod('post');
		// ======
		// = id =
		// ======
		$id = $this->createElement('hidden','id')->setDecorators(array('ViewHelper'));
		$this->addElement($id);
		
		// =========
		// = title =
		// =========
		$element = $this->createElement('text', 'title')
			->setLabel('Pagina Titel')
			->setRequired(true)
			->addFilter('StringTrim');
		$this->addElement($element);
		
		// ========
		// = slug =
		// ========
		$element = $this->createElement('text', 'slug')
			->setLabel('Unieke naam')
			->setRequired(true)
			->addFilter('StringTrim')
			->addValidator('Db_NoRecordExists',false,
				array(
					'table'=>'pages',
					'field'=>'slug',
					'messages'=>'Er bestaat al een pagina met deze naam. Deze waarde moet uniek zijn.'
				)
			);

		$this->addElement($element);
		
		// =============
		// = menutitle =
		// =============
		$element = $this->createElement('text', 'menutitle')
			->setLabel("Naam die in de menu's moet komen")
			->setRequired(true)
			->addFilter('StringTrim');
		$this->addElement($element);
		
		// ==========
		// = groups =
		// ==========
		$element = $this->createElement('MultiCheckbox', 'groups')
			->setLabel('Selecteer de groepen die deze pagina mogen zien');
		
		$tbl = new Model_Table_Groups();
		$groupRows = $tbl->fetchAll();
		foreach($groupRows as $group)
		{
			$element->addMultiOption($group->id, $group->name);
		}
		$this->addElement($element);
		
		// =========
		// = owner =
		// =========
		$groupIds = $access->getUserGroups();
		$element = $this->createElement('select', 'owner')
			->setRequired(true)
			->setLabel("Onder welke groep (tab) valt deze deze pagina?");
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
		
		// ==================
		// = No Menu at all =
		// ==================
		$element = $this->createElement('checkbox','notinmenus')
			->setLabel("Plaats een vink om deze pagina te verbergen in het menu");
		$this->addelement($element);
		
		// ==========
		// = parent =
		// ==========
		$groups = $access->getUser()->getGroups();
		$tbl = new Model_Table_Pages();
		$select = $tbl->select()
						->where('`owner` IN(?)', $groups)
						->order('slug');
		$pageRows = $tbl->fetchAll($select);
		$element = $this->createElement('select','parent')
			->setLabel('selecteer de plaats in de hierarchie van het paginamenu')
			->addMultiOption(null,'top van het menu');
		$this->addElement($element);
		
		// ===========
		// = content =
		// ===========
		$element = $this->createElement('textarea', 'content')
			->setLabel('Pagina inhoud')
			->setRequired(true)
			->setAttrib('class', 'tinymce');
		$this->addElement($element);
		
	}
















}
