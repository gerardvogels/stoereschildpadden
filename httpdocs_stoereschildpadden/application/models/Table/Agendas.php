<?php

class Model_Table_Agendas extends Zend_Db_Table_Abstract
{
	protected $_name = 'agendas';
	protected $_rowClass = 'Model_Agenda';
	protected $_dependentTables = array('Model_Table_AgendaGroup');
	
}
