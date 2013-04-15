<?php

class AdminController extends Zend_Controller_Action
{

    public function init()
    {
    }
    
    public function indexAction()
    {
        return $this->_redirect('/menu/wijzig');
    }
}
