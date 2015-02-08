<?php

class Layout extends Bus {
	
	private $Smarty = array();
	private $title = "";

	public function __construct(&$core) {
		parent::__construct($core);

		// Load Smarty
		$smartyDir = FUZEPATH . "/Core/System/Smarty";
		
		if (!defined('SMARTY_DIR')) {
			define('SMARTY_DIR', $smartyDir .  DIRECTORY_SEPARATOR . "libs" .  DIRECTORY_SEPARATOR);
			define('SMARTY_TOP', $smartyDir);
		}

		require_once(SMARTY_TOP . "/libs/Smarty.class.php");

		$this->Smarty['main'] = new \Smarty();
		$this->Smarty['main'] = $this->getSmartyBasicVars($this->Smarty['main']);
	}

	public function getSmartyBasicVars($Smarty) {
		$Smarty->setCompileDir(FUZEPATH . "/Core/Cache/Compile");
		$Smarty->setCacheDir(FUZEPATH . "/Core/Cache/");
		$Smarty->assign('siteURL', $this->config->main->SITE_URL);
		$Smarty->assign('serverName', $this->config->main->SERVER_NAME);
		$Smarty->assign('siteDomain', $this->config->main->SITE_DOMAIN);
		return $Smarty;
	}

	public function __get($name) {
		if (!isset($this->Smarty[$name])) {
			$this->Smarty[$name] = new \Smarty();
		}
		return $this->Smarty[$name];
	}

	public function __call($name, $params) {
		return call_user_func_array(array($this->Smarty['main'], $name), $params);
	}

	public function getNew() {
		return new \Smarty();
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function view($view = "default", $dir = "") {
		// Set the file name and location
		$vw = explode('/', $view);
		if (count($vw) == 1) {
			$vw = 'view.'.$vw[0].'.tpl';
		} else {
			// Get last file
			$file = end($vw);

			// Reset to start
			reset($vw);

			// Remove last value
			array_pop($vw);

			$vw[] = 'view.'.$file.'.tpl';

			// And create the final value
			$vw = implode('/', $vw);
		}

		// Set the directory
		$dir = ($dir == "" ? FUZEPATH . "/Application/" . '/Views' : $dir);
		$this->Smarty['main']->setTemplateDir($dir);

		// Set the title
        $this->Smarty['main']->assign('title', $this->title);
  	
  		// Get the viewdir
        $one = FUZEPATH;
        $two = $dir . "/";
        $count_one = strlen($one);
        $count_two = strlen($two);
        $length_three = $count_two - $count_one;
        $three = $this->config->main->SITE_URL . "/" . substr($two, -$length_three);
        $this->layout->assign('viewDir', $three);

        try{
        	
        	// Load the page
            $this->Smarty['main']->display($vw);
            $this->logger->logInfo("VIEW LOAD: '".$vw."'", "FuzeWorks->Layout", __FILE__, __LINE__);
        }catch (\SmartyException $e){

        	// Throw error on failure
            $this->logger->logError('Could not load view '.$dir.'/'.$vw.' :: ' . $e->getMessage(), 'FuzeWorks->Layout', __FILE__, __LINE__);
            throw new Exception\Layout('Could not load view '.$dir.'/'.$vw);
        }
	}

	public function get($view = "default", $dir = "") {
		// Set the directory
		$dir = ($dir == "" ? FUZEPATH . "/Application/" . '/Views' : $dir);
		$this->Smarty['main']->setTemplateDir($dir);

		// Set the title
        $this->Smarty['main']->assign('title', $this->title);

  		// Get the viewdir
        $one = FUZEPATH;
        $two = $dir . "/";
        $count_one = strlen($one);
        $count_two = strlen($two);
        $length_three = $count_two - $count_one;
        $three = $this->config->main->SITE_URL . "/" . substr($two, -$length_three);
        $this->layout->assign('viewdir', $three);
        try{
        	
        	// Load the page
            return $this->Smarty['main']->fetch('view.'.$view.'.tpl');
            $this->logger->logInfo("VIEW LOAD: 'view.".$view.'.tpl'."'", "FuzeWorks->Layout", __FILE__, __LINE__);
        }catch (\SmartyException $e){

        	// Throw error on failure
            $this->logger->logError('Could not load view '.$dir.'/view.'.$view.'.tpl :: ' . $e->getMessage(), 'FuzeWorks->Layout', __FILE__, __LINE__);
            throw new Exception\Layout('Could not load view '.$dir.'/view.'.$view.'.tpl');
        }
	}
}