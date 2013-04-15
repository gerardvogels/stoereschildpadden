<?php

class Form_ImportQuestions extends Zend_Form
{

    public function init()
    {
	    $this->setMethod('post');
		$this->setAttrib('enctype', 'multipart/form-data');
		
		// =============
		// = enqueteId =
		// =============
		$element = $this->createElement('hidden','enqueteId')->setDecorators(array('ViewHelper'));
		$this->addElement($element);
		
        // ====================
        // = yaml file upload =
        // ====================
        $element = $this->createElement('file', 'yamlfile')
        	->setLabel('Bestand met de vragen voor deze enquete')
        	->setRequired(true)
        	->setDestination(APPLICATION_PATH . '/enquetefiles')
        	->addValidator('Count', false, 1)
      		->addValidator('Size', false, 102400);
        $this->addElement($element);

		
	}
















}
