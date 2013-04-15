<?php

class Model_Table_Vars extends Zend_Db_Table_Abstract
{
	protected $_name = "vars";
    protected $_rowClass = 'Model_Var';
    
    public function getAll()
    {
        $select = $this->select()
                    ->order('slug');
        return $this->fetchAll();
    }
}

