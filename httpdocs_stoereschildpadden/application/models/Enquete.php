<?php
class Model_Enquete extends Zend_Db_Table_Row_Abstract
{
	protected $_tableClass = 'Model_Table_Enquetes';
	protected $groups;
	public $questions;

	static function getInstance($id=null)
	{
		$table = new Model_Table_Enquetes;
		if ($id) 
		{
			$enquete = $table->find($id)->current();
			$enquete->loadGroups();
		} 
		else 
		{
			$enquete = $table->createRow();
		}
		return $enquete;
	}
	
	public function save()
	{
		// ===================================
		// = require $this->groups to be set =
		// ===================================
		if (!isset($this->id)) 
		{
			throw new Zend_Exception('Fout bij het opslaan van deze enquete, ID niet bekend.');
		}
		
		parent::save();
	
		// remove existing group relations from the db.
		$id = $this->id;
		$relations = new Model_Table_EnqueteGroup();
		$relations->delete('enquete = '.intval($this->id));

		// store the current groups rellations
		$relationTable = new Model_Table_EnqueteGroup();
		foreach($this->groups as $group)
		{
			$relation = $relationTable->createRow();
			$relation->group = $group;
			$relation->enquete = $id;
			$relation->save();
		}
	}

	public function loadGroups()
	{
		// ===============================================================
		// = haal de groepen waar deze enquete lid van is uit de database =
		// ===============================================================
		$relations = $this->findDependentRowset('Model_Table_EnqueteGroup');
		$groups = array();
		foreach($relations as $relation)
		{
			$groups[] = $relation->group;
		}
		$this->groups = $groups;
	}
	
	public function setFromArray($array)
	{
		parent::setfromArray($array);

		if(is_array($array['groups']))
		{
			$this->groups = $array['groups'];
		}
		else
		{
			$this->groups = array();
		}
	}

	public function toArray()
	{
		$array = parent::toArray();
		$array['groups'] = $this->groups;
		return $array;
	}
	
	public function getGroups()
	{
		return $this->groups;
	}

	public function setGroups($array)
	{
		$this->groups = $array;
	}

	public function loadQuestions($enqueteId=null)
	{
		if ($enqueteID == null) 
		{
			$enqueteID = $this->id;
		}
		
		$table = new Model_Table_Questions();
		$select = $table->select()
			->where('enqueteId = ?', intval($this->id))
			->order('id');
		$this->questions = $table->fetchAll($select);
	}

	public function parseYaml()
	{
		$yamlPath = APPLICATION_PATH . '/enquetefiles/' . $this->id . '.yml';
		$yaml = new Yaml_Parser();
		try
		{
		  $value = $yaml->parse(file_get_contents($yamlPath));
		}
		catch (InvalidArgumentException $e)
		{
		  // an error occurred during parsing
		  echo "Fout bij het lezen van de vragenfile: ".$e->getMessage();
		}
		return $value['vragen'];
	}

	public function importYamlQuestions()
	{
		// ============================================================================
		// = $tmpQuestions bevat in arrayvorm de ongenummerde vragen uit de yaml file =
		// ============================================================================
		$tmpQuestions = $this->parseYaml();

		// ====================
		// = nummer de vragen =
		// ====================
		$count = 1;
		foreach ($tmpQuestions as $question) {
			if(!in_array($question['type'],array('info','kop')))
			{
				$question['number'] = $count++;
			}
			else
			{
				$question['number'] = null;
			}
			$questions[]= $question;
		}
		
		// remove existing questions for this enquete;
		$table = new Model_Table_Questions();
		$table->delete('enqueteId=' . $this->id);
		
		foreach($questions as $qData)
		{
			$question = Model_Question::getInstance();
			$question->enqueteId = $this->id;
			$question->setFromArray($qData);
			$question->save();
		}
		return true;
	}
}
	
