<?php

class Model_Table_PageGroup extends Zend_Db_Table_Abstract
{
    protected $_name = 'page_group';
 
    protected $_referenceMap    = array(
        'page' => array(
            'columns'           => array('page'),
            'refTableClass'     => 'Model_Table_Pages',
            'refColumns'        => array('id'),
        ),
        'group' => array(
            'columns'           => array('group'),
            'refTableClass'     => 'Model_Table_Groups',
            'refColumns'        => array('id')
        )
    );
}
