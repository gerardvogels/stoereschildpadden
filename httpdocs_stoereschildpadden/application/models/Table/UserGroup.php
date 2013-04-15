<?php

class Model_Table_UserGroup extends Zend_Db_Table_Abstract
{
    protected $_name = 'user_group';
 
    protected $_referenceMap    = array(
        'user' => array(
            'columns'           => array('user'),
            'refTableClass'     => 'Model_Table_Users',
            'refColumns'        => array('id'),
        ),
        'group' => array(
            'columns'           => array('group'),
            'refTableClass'     => 'Model_Table_Groups',
            'refColumns'        => array('id')
        )
    );
}
