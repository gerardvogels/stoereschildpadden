<?php

class Form_Enquete extends Zend_Form
{

    public function init()
    {
	    $this->setMethod('post');
		// ======
		// = id =
		// ======
		$id = $this->createElement('hidden','id')->setDecorators(array('ViewHelper'));
		$this->addElement($id);
		
		// ========
		// = title =
		// ========
		$element = $this->createElement('text', 'title')
			->setLabel('Titel van de enquete')
			->addFilter('StringTrim');
		$this->addElement($element);
		
		// ========
		// = info =
		// ========
		$element = $this->createElement('textarea', 'info')
			->setLabel('Extra informatie, wordt bij aanvang van de enquete getoond. (optioneel)')
			->setRequired(false);
		$this->addElement($element);
		
		// ==========
		// = groups =
		// ==========
		$table = new Model_Table_Groups();
		$select = $table->select()
			->order('name');
		$groupRows = $table->fetchAll($select );
		$element = $this->createElement('MultiCheckbox', 'groups')
			->setLabel('Organisaties');
		foreach($groupRows as $group)
		{
			$element->addMultiOption($group->id, $group->name);
		}
		$this->addElement($element);
		
	}
















}
