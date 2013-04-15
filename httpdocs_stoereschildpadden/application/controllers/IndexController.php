<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$defaultmenuitem = Model_Table_Menuitems::getDefaultItem();
        $this->_redirect('/page/show/m/' . $defaultmenuitem->id);
    }

}





