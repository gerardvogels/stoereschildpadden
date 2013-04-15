<?php

class Model_Table_UserRole extends Zend_Db_Table_Abstract
{
    protected $_name = 'user_role';
 
    protected $_referenceMap    = array(
        'user' => array(
            'columns'           => array('user'),
            'refTableClass'     => 'Model_Table_Users',
            'refColumns'        => array('id'),
        ),
        'group' => array(
            'columns'           => array('role'),
            'refTableClass'     => 'Model_Table_Roles',
            'refColumns'        => array('id')
        )
    );
}
