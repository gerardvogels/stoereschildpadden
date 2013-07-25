<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initSession()
	{

		Zend_Session::start();

        // ===========================================================================
        // = login tijdens de ontwikkeling moet op false worden gezet voor productie =
        // ===========================================================================
		if (false)
		{
			if (isset($_POST['codip_temp_pwd']) and $_POST['codip_temp_pwd'] == 'dsiatveSS') 
			{
			    $_SESSION['codip_temp_pwd'] = 'dsiatveDR';
			}
		
	        if(!isset($_SESSION['codip_temp_pwd']) or $_SESSION['codip_temp_pwd'] != 'dsiatveDR')
	        {
	            echo '
	                <!DOCTYPE html>

	                <html lang="nl">
	                <head>
	                    <title>Ontwikkelsite CODIP</title>
	                </head>
	                <body>
	                    <h1>Ontwikkelsite CODIP / Dappere Dino\'s</h1>
	                    <p>
	                        Deze site is in ontwikkeling. Hij zal straks op deze plaats verdwijnen en is nu alleen toegankelijk voor ontwikkelaars.
	                        Bent u een van deze ontwikkelaars, vul dan het u bekende wachtwoord in.
	                    </p>
	                    <form method="POST">
	                        <input type="password" name="codip_temp_pwd" id="password"></input>
	                        <input type="submit"></input>
	                    </form>
	                </body>
	                </html>
	                    ' . "\n";
	            exit;
	        }
		}
	}
	
	protected function _initConfig()
	{
		// ===============================================
		// = definieeer de project specifieke parameters =
		// ===============================================
		
		date_default_timezone_set ('Europe/Amsterdam');
		
		Zend_Registry::set('headTitle','CODIP');
	}

	protected function _initAutoLoad()
	{
		$autoloader = Zend_Loader_AutoLoader::getInstance();
		$autoloader->registerNamespace('Codip_');
		$autoloader->registerNamespace('Vit_');
		$autoloader->registerNamespace('Yaml_');
		return $autoloader;
	}

	protected function _initMagicQuotes()
	{
		if (get_magic_quotes_gpc()) {
			function stripslashes_deep($value)
			{
				$value = is_array($value) ?
							array_map('stripslashes_deep', $value) :
							stripslashes($value);

				return $value;
			}

			$_POST    = array_map('stripslashes_deep', $_POST);
			$_GET     = array_map('stripslashes_deep', $_GET);
			$_COOKIE  = array_map('stripslashes_deep', $_COOKIE);
			$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
		}
	}
	
	protected function _initResourceLoader()
	{
	    $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
	        'namespace' => '',
	        'basePath'  => APPLICATION_PATH,
	    ));
	    $resourceLoader->addResourceType('model', 'models/', 'Model');
	    $resourceLoader->addResourceType('form', 'forms/', 'Form');
	    $resourceLoader->addResourceType('plugin', 'plugins/', 'Plugin');
	    $resourceLoader->addResourceType('validator', 'validators/', 'Validator');

	    return $resourceLoader;
	}
	
	protected function _initDb()
	{
	    $resource = $this->getPluginResource('db');
	    $db = $resource->getDbAdapter();
	    Zend_Db_Table_Abstract::setDefaultAdapter($db);
	    return $db;
	}
	
	protected function _initController()
	{	
		// Routing
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		$routes = array();
		
		$routes['default'] = new Zend_Controller_Router_Route_Module(
			array(),
			$front->getDispatcher(),
			$front->getRequest()
		);
		
		// $routes['page'] = new Zend_Controller_Router_Route(
		// 	'p/:slug',
		// 	array(
		// 		'action' => 'view',
		// 		'controller' => 'page'
		// 	)
		// );

		foreach($routes as $key => $curRoute) {
			if ($key != 'default') {
				$router->addRoute($key,$curRoute);
			}
		}
		
		// Plugins
		// $front->registerPlugin(new Plugin_Auth());
		// $front->registerPlugin(new Plugin_Multilanguage());
		
	}
	
	protected function _initView()
	{
		$view = new Zend_View();
		$view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
		
        $view->jQuery()->enable();
        $view->jQuery()->setVersion('1.4');
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		
        $view->headTitle(Zend_Registry::get('headTitle'));
        $view->headTitle()->setSeparator(' - ');
		
		$viewRenderer->setView($view);
		return Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
		
	}
	



}

