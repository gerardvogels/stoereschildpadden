<?php

class Model_Table_AgendaItems extends Zend_Db_Table_Abstract
{
	protected $_name = 'agendaitems';
	protected $_rowClass = 'Model_AgendaItem';

    protected $_referenceMap    = array(
        'agenda' => array(
            'columns'           => array('agenda'),
            'refTableClass'     => 'Model_Table_Agendas',
            'refColumns'        => array('id'),
        )
	);
	
}
