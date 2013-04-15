<?php

class Form_Group extends Zend_Form
{

    public function init()
    {
	    $this->setMethod('post');
		$id = $this->createElement('hidden','id')->setDecorators(array('ViewHelper'));
		$this->addElement($id);
		
		$element = $this->createElement('text','name')
							->setRequired(true)
							->setLabel('Naam van de groep')
							->addFilter('StringTrim')
							->addValidator('Db_NoRecordExists',false,
								array(
									'table'=>'groups',
									'field'=>'name',
									'messages'=>'Er bestaat al een groep met deze naam.'
								)
							);
		$this->addElement($element);
	}
}
