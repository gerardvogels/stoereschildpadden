<?php

class Form_GroupStartPage extends Zend_Form
{

    public function init()
    {
	
        $options = array(
            'fout' => 'FOUT: de opties voor deze selector zijn niet goed gezet'
        );
		$element = $this->createElement('select', 'startPage')
			->setLabel('Startpagina:')
			->addMultiOptions($options);
		$this->addElement($element);
		
        $this->addElement('submit', 'submit', array('label' => 'OKE'));		
	}
















}
