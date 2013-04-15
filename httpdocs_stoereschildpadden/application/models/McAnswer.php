<?php
class Model_McAnswer extends Zend_Db_Table_Row_Abstract
{
	protected $_tableClass = 'Model_Table_McAnswers';

	static function getInstance($id=null)
	{
		$table = new Model_Table_McAnswers();
		
		if ($id) 
		{
			$item = $table->find($id)->current();
		} 
		else 
		{
			$item = $table->createRow();
		}
		return $item;
	}
	
}
	 