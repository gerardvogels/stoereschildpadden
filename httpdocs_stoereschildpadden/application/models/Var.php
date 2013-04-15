<?php

class Model_Var extends Zend_Db_Table_Row_Abstract
{
    protected $_tableClass = 'Model_Table_Vars';

	static function getValue($name)
	{
	    $tbl = new Model_Table_Vars();
	    $row = $tbl->find($name)->current();
	    $value = $row->value;
	    if(strstr($value,';'))
	    {
	        return(explode(';',$value));
	    }
	    return($value);
	}

	static function getValues($name)
	{
	    $tbl = new Model_Table_Vars();
	    $row = $tbl->find($name)->current();
	    $value = $row->value;
	    return(explode(';',$value));
	}

}

