<?php
class Model_GroupTabs
{
	
	protected $listClass='groupTabList';
	protected $itemClass='groupTabItem';
	protected $activeItemClass='activeGroupTabItem';
	
	public function getAllGroups()
	{
		$table = new Model_Table_Groups();
		$select = $table->select()
			->where('forGodOnly != ?', 1)
			->order('position');
		$groupRows = $table->fetchAll($select);
		return $groupRows;
	}
	
	static function getGroupsForUser($userID=null)
	{
	    if (!$userID) 
	    {
	       throw new Zend_Exception('FOUT: Geen userID opgegeven');
	    }
	    $tbl = new Model_Table_Groups();
	    $db = $tbl->getAdapter();
	    $query = 'SELECT groups.id, groups.position
	        FROM groups 
	        JOIN user_group ON user_group.group = groups.id
	        WHERE user_group.user = ' . $userID . '
	        ORDER BY position';
	    $results = $db->fetchAll($query);
	    if(!$results or count($results) < 1)
	    {
	        return false;
	    }
	    foreach($results as $result)
	    {
	        $group = Model_Group::getInstance($result['id']);
	        $groups[] = $group;
	    }
	    return $groups;
	}
	
	public function setListClass($class)
	{
		$this->listClass = $class;
	}
	
	public function getListClass()
	{
		return $this->listClass;
	}
	
	public function setItemClass($class)
	{
		$this->itemClass = $class;
	}
	
	public function getItemClass()
	{
		return $this->itemClass;
	}
	
	public function getList($activeTab)
	{
	    
		$groups = $this->getAllGroups();
		$output = "\n";
		$output .= '<ul class="' .  $this->getListClass() . '">' . "\n";

		foreach($groups as $group)
		{
		    if($group->name != 'publiek')
		    {
		    if ($group->id == $activeTab->id)       $active = ' active';
		    else                                    $active = null;
    			$output .= '<li class="groupTabItem' . $active  . '">' . "\n";
    			$output .= '<a href="/page/show/id/' . $group->startPage . '">' . $group->name . '</a>' . "\n";
    			$output .= '</li>' . "\n";
		    }
		}
        $access = new Model_Access();
        if($access->hasRole('administrator') or $access->hasRole('editor'))
        {
			$output .= '<li class="groupTabItem">' . "\n";
			$output .= '<a href="/page/index">site beheer</a>' . "\n";
			$output .= '</li>' . "\n";
        }

		$output .= '</ul>' . "\n\n";
		return $output;
	}
	
	static function renumber()
	{
	    $groups = $this->getAllGroups();
	    $position = 1;
	    foreach ($groups as $group) {
	       $group->position = $position;
	       $group->save();
	       $position++;
	    }
	}
	
    static function getLastGroup()
    {
        $qArray['select'] = 'SELECT *';
        $qArray['from'] = 'FROM groups';
        $qArray['order'] = 'ORDER BY position DESC';
        $qArray['limit'] = 'LIMIT 1';
        $query = implode(' ', $qArray);

	    $tbl = new Model_Table_Groups();
	    $db = $tbl->getAdapter();
	    $lastGroup = $db->fetchRow($query);
	    return $lastGroup;
    }

    static function getFirstGroup()
    {
        $qArray['select'] = 'SELECT *';
        $qArray['from'] = 'FROM groups';
        $qArray['where'] = 'WHERE name != "publiek"';
        $qArray['order'] = 'ORDER BY position ASC';
        $qArray['limit'] = 'LIMIT 1';
        $query = implode(' ', $qArray);

	    $tbl = new Model_Table_Groups();
	    $db = $tbl->getAdapter();
	    $lastGroup = $db->fetchRow($query);
	    return $lastGroup;
    }

    static function getLastPosition()
    {
        $lastGroup = self::getLastGroup();
        return $lastGroup['position'];
    }
    
    static function move($direction,$groupID, $userID)
    {
        $subjectGroup = Model_Group::getInstance($groupID);
        $currentPosition = $subjectGroup->position;

        switch ($direction) {
            case 'up':
                $andWhere = ' AND groups.position > ' . $currentPosition;
                $order = ' ORDER BY groups.position ASC';
                break;
            
                case 'down':
                    $andWhere = ' AND groups.position < ' . $currentPosition;
                    $order = ' ORDER BY groups.position DESC';
                    break;

            default:
                throw new Zend_Exception('FOUT: Geen geldige richting voor deze move opgegeven');
                break;
        }

	    $tbl = new Model_Table_Groups();
	    $db = $tbl->getAdapter();

        // Welke groepen moeten worden meegenomen
        $access = new Model_Access();
        if($access->isGod())
        {
            $where = '';
        }
        else
        {
            $where = ' WHERE user_group.user = ' . $userID;
        }

        // Bouw de query
	    $query = 'SELECT groups.id, groups.position'
	        . ' FROM groups'
	        . ' JOIN user_group ON user_group.group = groups.id' 
	        . $where
	        . $andWhere
	        . $order
	        . ' LIMIT 1';

        // Ruil om met de betreffede burman als die is gevonden
        if($result = $db->fetchRow($query))
        {
            $switchGroup = Model_Group::getInstance($result['id']);
            $newPosition = $switchGroup->position;
            $subjectGroup->position = $newPosition;
            $subjectGroup->save();
            $switchGroup->position = $currentPosition;
            $switchGroup->save();
            return true;
        }
        return false;
    }
}
  