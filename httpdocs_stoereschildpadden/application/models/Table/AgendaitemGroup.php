<?php

class Model_Table_AgendaitemGroup extends Zend_Db_Table_Abstract
{
    protected $_name = 'agendaitem_group';
 
    protected $_referenceMap    = array(
        'agendaitem' => array(
            'columns'           => array('agendaitem'),
            'refTableClass'     => 'Model_Table_Agendaitems',
            'refColumns'        => array('id'),
        ),
        'group' => array(
            'columns'           => array('group'),
            'refTableClass'     => 'Model_Table_Groups',
            'refColumns'        => array('id')
        )
    );
}
