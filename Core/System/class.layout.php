<?php

namespace FuzeWorks;

/**
 * Layout Class
 * 
 * The Layout class is a wrapper for the Smarty Template engine. Smarty loads .tpl files and variables, and converts them to HTML.
 * See the Smarty documentation for more information
 * This class typically loads files from Application/Views unless specified otherwise. 
 *
 */
class Layout extends Bus {
	
	private $Smarty = array();
	private $title = "";
	public $loaded = false;

	public function __construct(&$core) {
		parent::__construct($core);
	}

	private function load() {
		// Load Smarty
		$smartyDir = "Core/System/Smarty";
		
		if (!defined('SMARTY_DIR')) {
			define('SMARTY_DIR', $smartyDir .  DIRECTORY_SEPARATOR . "libs" .  DIRECTORY_SEPARATOR);
			define('SMARTY_TOP', $smartyDir);
		}

		require_once(SMARTY_TOP . "/libs/Smarty.class.php");

		$this->Smarty['main'] = new \Smarty();
		$this->Smarty['main'] = $this->getSmartyBasicVars($this->Smarty['main']);

		$this->loaded = true;
		$this->mods->events->fireEvent('smartyLoadEvent');
	}

	public function getSmartyBasicVars($Smarty) {
		$Smarty->setCompileDir("Core/Cache/Compile");
		$Smarty->setCacheDir("Core/Cache/");
		$Smarty->assign('siteURL', $this->config->main->SITE_URL);
		$Smarty->assign('serverName', $this->config->main->SERVER_NAME);
		$Smarty->assign('siteDomain', $this->config->main->SITE_DOMAIN);
		return $Smarty;
	}

	public function __get($name) {
		// Chech if Smarty is loaded
		if (!$this->loaded)
			$this->load();

		if (!isset($this->Smarty[$name])) {
			$this->Smarty[$name] = new \Smarty();
		}
		return $this->Smarty[$name];
	}

	public function __call($name, $params) {
		// Chech if Smarty is loaded
		if (!$this->loaded)
			$this->load();

		return call_user_func_array(array($this->Smarty['main'], $name), $params);
	}

	public function getNew() {
		// Chech if Smarty is loaded
		if (!$this->loaded)
			$this->load();

		return new \Smarty();
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function view($view = "default") {
		// Chech if Smarty is loaded
		if (!$this->loaded)
			$this->load();

		$event = $this->events->fireEvent('layoutLoadEvent', $view);
		$directory 			= ($event->directory === null ? "Application/Views" : $event->directory);
		$view 				= ($event->layout === null ? $view : $event->layout);

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
		$this->Smarty['main']->setTemplateDir($directory);

		// Set the title
        $this->Smarty['main']->assign('title', $this->title);
  	
  		// Get the viewdir
        $viewDir = $this->config->main->SITE_URL . "/" . substr($directory . "/", -strlen($directory . "/"));
        $this->layout->assign('viewDir', $viewDir);

        try{
        	
        	// Load the page
            $this->Smarty['main']->display($vw);
            $this->logger->logInfo("VIEW LOAD: '".$vw."'", "Layout", __FILE__, __LINE__);
        }catch (\SmartyException $e){

        	// Throw error on failure
            $this->logger->logError('Could not load view '.$directory.'/'.$vw.' :: ' . $e->getMessage(), 'Layout', __FILE__, __LINE__);
            throw new LayoutException('Could not load view '.$directory.'/'.$vw);
        }
	}

	public function get($view = "default", $directory = "") {
		// Chech if Smarty is loaded
		if (!$this->loaded)
			$this->load();

		// Set the directory
		$directory = ($directory == "" ? "Application/" . '/Views' : $directory);
		$this->Smarty['main']->setTemplateDir($directory);

		// Set the title
        $this->Smarty['main']->assign('title', $this->title);

  		// Get the viewdir
        $viewDir = $this->config->main->SITE_URL . "/" . substr($directory . "/", -strlen($directory . "/"));
        $this->layout->assign('viewDir', $viewDir);
        
        try{
        	
        	// Load the page
            return $this->Smarty['main']->fetch('view.'.$view.'.tpl');
            $this->logger->logInfo("VIEW LOAD: 'view.".$view.'.tpl'."'", "Layout", __FILE__, __LINE__);
        }catch (\SmartyException $e){

        	// Throw error on failure
            $this->logger->logError('Could not load view '.$directory.'/view.'.$view.'.tpl :: ' . $e->getMessage(), 'Layout', __FILE__, __LINE__);
            throw new LayoutException('Could not load view '.$directory.'/view.'.$view.'.tpl');
        }
	}
}