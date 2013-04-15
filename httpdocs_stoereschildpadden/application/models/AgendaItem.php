<?php
class Model_AgendaItem extends Zend_Db_Table_Row_Abstract
{
	protected $_tableClass = 'Model_Table_AgendaItems';
	
    protected $groups = null;

	static function getInstance($id=null)
	{
		$table = new Model_Table_AgendaItems;
		if ($id) 
		{
			$item = $table->find($id)->current();

            //  haal de groepen op die dit item mogen zien
			$relations = new Model_Table_AgendaitemGroup();
			$select = $relations->select()
			    ->where('agendaitem = ?', $id);
			$rows = $relations->fetchAll($select);
			$item->groups = array();
			foreach($rows as $row)
			{
			    $item->groups[] = $row->group;
			}
		} 
		else 
		{
			$item = $table->createRow();
			$item->groups = array();
		}
		return $item;
	}
	
	public function setFromFormValues($values)
	{
		$values['starttimestamp'] = $this->getStartStamp($values['date'], $values['starttime']);
		$this->groups = $values['groups'];
		$values['duration'] = $this->durationToSeconds($values['duration']);
		$this->setFromArray($values);
	}
	
	public function getStartStamp($startdate,$starttime)
	{
		// ======================================
		// = assuming defualt timezone to be set =
		// =======================================
		$date = new Zend_date(0);
		$date->setDate($startdate,'d-M-yyyy');
		$date->setTime($starttime,'h:m');
		$stamp = $date->getTimeStamp();
		return $stamp;
	}
	
	public function durationToSeconds($duration)
	{
		// =========================================
		// = duration should be in h:mm or similar =
		// =========================================
		$duration = preg_replace('/[,\. \-_]/', ':', $duration);
		$array = explode(':', $duration);
		if(count($array) < 1 or count($array) > 2)
		{
			throw new Zend_Exception('Ongeldig tijdsduur opgegeven');
		}
		$seconds = intval($array[0])*3600;
		if(isset($array[1]))
		{
			$seconds += $array[1]*60;
		}
		return $seconds;
	}
	
	public function getFormValues()
	{
		$values = $this->toArray();
		$date = new Zend_Date($this->starttimestamp);
		$values['date'] = $date->get('d-M-yyyy');
		$values['starttime'] = $date->get(Zend_Date::HOUR) . ":" . $date->get(Zend_Date::MINUTE);
		
		$time = $this->secondsToTime($this->duration);
		$values['duration'] = $time['h'] . ':' . sprintf('%02d',$time['m']);
		$values['groups'] = $this->groups;
		return $values;
	}
	
	public function secondsToTime($seconds)
	{
		$time['s'] = $seconds%60;
		$remainder = $seconds - $time['s'];
		$time['m'] = ($remainder%3600)/60;
		$remainder = $remainder - $time['m']*60;
		$time['h'] = $remainder/3600;
		return $time;
	}

    public function save()
    {
        parent::save();
        $relations = new Model_Table_AgendaitemGroup();
        $relations->delete('agendaitem = ' . $this->id);
        if(is_array($this->groups))
        {
            foreach($this->groups as $group)
            {
                $relation = $relations->createRow();
                $relation->agendaitem = $this->id;
                $relation->group = $group;
                $relation->save();
            }
        }
    }
	
}
	