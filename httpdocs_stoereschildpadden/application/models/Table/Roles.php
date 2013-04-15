<?php

class Model_Table_Roles extends Zend_Db_Table_Abstract
{
	protected $_name = 'roles';
	protected $_dependentTables = array('Model_Table_UserRole');
}
