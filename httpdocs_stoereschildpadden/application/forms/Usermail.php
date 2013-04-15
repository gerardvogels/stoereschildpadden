<?php

class Form_Usermail extends Zend_Form
{

    public function init()
    {
	    $this->setMethod('post');
		// ======
		// = id =
		// ======
		$id = $this->createElement('hidden','id')->setDecorators(array('ViewHelper'));
		$this->addElement($id);
		
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
		
    }


}

