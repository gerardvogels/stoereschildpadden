<?php

class Model_Table_McAnswers extends Zend_Db_Table_Abstract
{
	protected $_name = 'mcanswers';
	protected $_rowClass = 'Model_McAnswer';

    protected $_referenceMap    = array(
        'enquete' => array(
            'columns'           => array('questionId'),
            'refTableClass'     => 'Model_Table_Questions',
            'refColumns'        => array('id'),
        )
	);
}

