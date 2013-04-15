<?php

class Model_Table_Enquetes extends Zend_Db_Table_Abstract
{
	protected $_name = 'enquetes';
	protected $_rowClass = 'Model_Enquete';
	protected $_dependentTables = array('Model_Table_EnqueteGroup');
}
