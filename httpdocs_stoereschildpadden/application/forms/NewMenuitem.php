<?php

class Form_NewMenuitem extends Zend_Form
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
        // Kies de beschikbare parents
        $tbl = new Model_Table_Menuitems();
        $select = $tbl->select()
            ->order('sequencenumber');
        $parents = $tbl->fetchAll($select);
        foreach($parents as $parent)
        {
            $options[$parent->id] = $parent->getPath();
        }
        asort($options);
        $element->addMultiOptions($options);
        $this->addElement($element);
		
		
		// ==========
		// = target =
		// ==========
		# Haal alle pagina's op
		$tbl = new Model_Table_Pages();
		$pages = $tbl->fetchAll();
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

