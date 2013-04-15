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
