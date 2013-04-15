<?php

class Model_Table_EnqueteGroup extends Zend_Db_Table_Abstract
{
    protected $_name = 'enquete_group';
 
    protected $_referenceMap    = array(
        'enquete' => array(
            'columns'           => array('enquete'),
            'refTableClass'     => 'Model_Table_Enquetes',
            'refColumns'        => array('id'),
        ),
        'group' => array(
            'columns'           => array('group'),
            'refTableClass'     => 'Model_Table_Groups',
            'refColumns'        => array('id')
        )
    );
}
