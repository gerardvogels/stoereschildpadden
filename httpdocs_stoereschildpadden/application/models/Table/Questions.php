<?php

class Model_Table_Questions extends Zend_Db_Table_Abstract
{
	protected $_name = 'questions';
	protected $_rowClass = 'Model_Question';

    protected $_referenceMap    = array(
        'enquete' => array(
            'columns'           => array('enqueteId'),
            'refTableClass'     => 'Model_Table_Agendas',
            'refColumns'        => array('id'),
        )
	);
	
}
