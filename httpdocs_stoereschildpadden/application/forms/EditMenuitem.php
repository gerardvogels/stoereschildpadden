<?php

class Form_EditMenuitem extends Zend_Form
{

    public function init()
    {
	    $this->setMethod('post');
	    
        // ==========
        // = parent =
        // ==========
        $element =  $this->createElement('select','parentid')
            ->setLabel('Selecteer de parent voor dit item');
        $element->addMultiOption(0,'Geen Parent');
		$element->setRegisterInArrayValidator(false);
        // De beschikbare parents moeten in de controller worden gezet
        $this->addElement($element);
		
		// ==========
		// = target =
		// ==========
		# Haal alle pagina's op
		$tbl = new Model_Table_Pages();
		$select = $tbl->select()->order('title');
		$pages = $tbl->fetchAll($select);
		$element =  $this->createElement('select','target')
    					->setLabel('Selecteer de bestemming (pagina)');
    	foreach ($pages as $page) {
    	   $element->addMultiOption($page->id,$page->title);
    	}
    	$this->addElement($element);
		
		// ============
		// = menutext =
		// ============
		$element =  $this->createElement('text','menutext')
    					->setLabel('Text die in het menu komt')
    					->addFilter('StringTrim')
    					->setRequired('true');
		$this->addElement($element);
		
        // =======
        // = OKE =
        // =======
        $element = $this->createElement('submit','Oke');
        $this->addElement($element);
    }

}

