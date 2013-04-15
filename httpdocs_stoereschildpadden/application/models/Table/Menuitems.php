<?php

class Model_Table_Menuitems extends Zend_Db_Table_Abstract
{
	protected $_name = "menuitems";
    protected $_rowClass = 'Model_Menuitem';
    
    public function getAll()
    {
        $select = $this->select()
                    ->order('slug');
        return $this->fetchAll();
    }

	static function getDefaultItem()
	{
		$tbl = new self;
		$select = $tbl->select()
			->where('parentid = ?', 0)
			->order('sequencenumber')
			->limit(1);
		$item = $tbl->fetchRow($select);
		return $item;
	}

}

