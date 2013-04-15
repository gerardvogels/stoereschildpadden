<?php
class Form_GroupSelectionFilter extends Zend_Form
{
    public function init()
	{
		
		$this->setMethod('post');

		// ==================================
		// = Selectievakjes voor de groepen =
		// ==================================
		$table = new Model_Table_Groups();
		$groups = $table->fetchAll();
		$element = $this->createElement('MultiCheckbox', 'groups')
			->setLabel('Groepen');
		$element->addMultiOption('allemaal', 'Alle Groepen');	
		foreach($groups as $group)
		{
			$element->addMultiOption($group->id, $group->name);
		}
		$this->addElement($element);
		
		// ==========
		// = submit =
		// ==========
		$element = $this->createElement('submit', 'Selecteer');
		$this->addElement($element);
	}
}

