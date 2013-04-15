<?php
class Model_Question extends Zend_Db_Table_Row_Abstract
{
	protected $_tableClass = 'Model_Table_Questions';

	public $mcAnswers;

	static function getInstance($id=null)
	{
		$table = new Model_Table_Questions;

		if ($id) 
		{
			$item = $table->find($id)->current();
			if($item->type == 'mc')
			{
				$this->getMcAnswers();
			}
		} 
		else 
		{
			$item = $table->createRow();
		}
		return $item;
	}
	
	public function getMcAnswers()
	{
		$table = new Model_Table_McAnswers();
		$select = $table->select()
			->where('questionId = ?', $this->id)
			->order('number');
		$rows = $table->fetchAll($select);
		$mcAnswers = array();
		foreach($rows as $row)
		{
			$mcAnswers[] = $row;
		}
		return $mcAnswers;
	}

	public function setFromArray($array)
	{
		parent::setFromArray($array);
		
		if (isset($array['mcAntwoorden'])) 
		{
			$this->mcAnswers = array();
			$number = 1;
			foreach($array['mcAntwoorden'] as $answer)
			{
				$mcanswer = Model_McAnswer::getInstance();
				$mcanswer->text = $answer;
				if($this->id)
				{
					$mcanswer->questionId = $this->id;
				}
				$mcanswer->number = $number++;
				$this->mcAnswers[] = $mcanswer;
			}
		}
	}
	
	public function save()
	{
		parent::save();
		
		if(is_array($this->mcAnswers) and count($this->mcAnswers) > 0)
		{
			foreach($this->mcAnswers as $mcanswer)
			{
				$mcanswer->questionId = $this->id;
				$mcanswer->save();
			}
		}
	}

}
	