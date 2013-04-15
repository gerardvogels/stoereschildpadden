<?php
class Model_AdminTabs
{
	
	protected $listClass='adminTabList';
	protected $itemClass='adminTabItem';
	protected $activeItemClass='activeAdminTabItem';
	
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
	
	public function getList()
	{
		$output = '';
		$active = '';

        $access = new Model_Access();
        $output .= '<ul class="' .  $this->getListClass() . '">' . "\n";

        $adminTabs = array();
        // if($access->hasRole('administrator') or $access->isGod())
        if(false and ($access->hasRole('administrator') or $access->isGod()))
        {
            $adminTabs = array(
                'gebruikers' => '/user/index',
                'groepen'     => '/group/index'
            );
        }
        
        
        $editTabs =  array();
        if(true or $access->hasRole('editor') or $access->isGod())
        {
            $editTabs = array(
                'menu' => '/menu/index',
                'pagina\'s' => '/page/index'
            );
        }
        
        $tabs = array_merge($adminTabs,$editTabs);
        
        $output = '';
        foreach ($tabs as $tekst => $link) {
            $output .= '    <li class="groupTabItem' . $active  . '">' . "\n";
            $output .= '        <a href="' . $link . '">' . $tekst . '</a>' . "\n";
            $output .= '    </li>' . "\n";
        }
        
		$output .= '<li class="groupTabItem' . $active  . '">' . "\n";
		$output .= '<a href="http://' . $_SERVER['SERVER_NAME'] . '">naar de site</a>' . "\n";
		$output .= '</li>' . "\n";


        $user = $access->getUser();
        if(isset($user->id) and $user->id != '')
        {
            $action = 'logoff';
            $itemText = 'uitloggen';
        }
        else
        {
            $action = 'login';
            $itemText = 'inloggen';
        }
		$output .= '<li class="groupTabItem' . $active  . '">' . "\n";
		$output .= '<a href="http://' . $_SERVER['SERVER_NAME'] . '/user/' . $action . '">' . $itemText . '</a>' . "\n";
		$output .= '</li>' . "\n";
       
        $output .= '</ul>' . "\n";
		return $output;
         
	}
	
}
  