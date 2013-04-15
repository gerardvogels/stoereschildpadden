<?php

class Model_Table_Users extends Zend_Db_Table_Abstract
{
	protected $_name = 'users';
	protected $_dependentTables = array('Model_Table_UserGroup');
	protected $_rowClass = 'Model_User';
}
