<?php

class Router extends Bus {

	public $controller = null;
	public $controllerName = null;
	public $function = null;
	private $route = array();
	private $parameters = array();
	private $path;

	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function onLoad() {}

	public function setPath($path) {
		if (substr($path, -1, 1) == '/')
			$path = substr($path, 0, strlen($path)-1);

		return $this->path = $path;
	}

	public function getPath() {
		return $this->path;
	}

	public function getRoute($index = null) {
		if ($index === null)
			return $this->route;
		return $this->route[$index];
	}

	public function getParameters() {
		return $this->parameters;
	}

	public function getParameter($index = 0) {
		$parameters = $this->getParameters();
		return ($index >= 0 ? $parameters[$index] : $parameters[count($parameters)+$index]);
	}

	/**
	 * Extracts the routing path to controller, function and parameters
	 *
	 * Path structure: /controller/function/par1/par2...
	 */
	public function route(){
		$path = (!empty($this->getPath()) ? explode('/', preg_replace('#/+#','/',$this->getPath())) : array());
		$path_size = count($path);

		// If trailing slash was given or the last element was empty: remove it
		if(end($path) == ''){
			array_pop($path);
		}

		$CONTROLLER = "";
		$FUNCTION = "";
		$PARAMS = array();
		if ($path_size >= 1) {
			$CONTROLLER = $path[0];
			if ($path_size >= 2) {
				$FUNCTION = $path[1];
				if ($path_size >= 3) {
					$PARAMS = array_slice($path, 2);
				}
			}
		} else {
			// Default controller, default function, no arguments
			$CONTROLLER = 'standard';
		}

        // Fire the event to notify our modules
        $event = $this->events->fireEvent('routerRouteEvent', $CONTROLLER, $FUNCTION, $PARAMS);

        // The event has been cancelled
        if($event->isCancelled()){
            return;
        }

        // Assign everything to the object to make it accessible, but let modules check it first
		$this->route		    = $path;
		$this->controllerName   = $event->controller === null ? $this->config->main->default_controller : $event->controller;
		$this->function		    = $event->function === null ? $this->config->main->default_function : $event->function;
		$this->parameters 	    = $event->parameters;

        // Load the controller
        $dir = (isset($event->directory) ? $event->directory : null);
        $this->loadController($CONTROLLER, $FUNCTION, $PARAMS, $dir);
	}

	/**
	 * Load a controller
	 * @access public
	 * @param String controller name
	 * @param String function name
	 * @param Array Parameters
	 */
	public function loadController($controller, $function = '', $params = array(), $directory = null) {
		$path = ($function != '' ? array($controller, $function) : array($controller));
		$path = (!empty($params) ? array_merge($path, $params) : $path );

		// The directory where to find the controller files
		$dir = (isset($directory) ? $directory : FUZEPATH . "/Application/Controller/" );

		// Select the proper functions and parameters
		$FUNCTION = ($function != '' ? $function : "index");
		$PARAMS = (!empty($params) ? $params : array());

		$CONTROLLER_NAME = ucfirst($controller);
		$CONTROLLER_FILE = $dir . 'controller.'.strtolower($controller).".php";
		
		if (file_exists($CONTROLLER_FILE)) {
			require_once($CONTROLLER_FILE);
			array_shift($path);
		} else {
			$CONTROLLER_NAME = 'Standard';
			$CONTROLLER_FILE = $dir . 'controller.standard.php';
			$FUNCTION = 'not_found';
			if (file_exists($CONTROLLER_FILE)) {
				require_once($CONTROLLER_FILE);
			} else {
				$this->mods->logger->logError("Failed to load standard controller. Controller loading can not continue!", "Web");
				$this->mods->logger->enable();
				return false;
			}
		}

		$this->mods->logger->log("Loading controller <b>'".$CONTROLLER_NAME."'</b>", "Web");
		$CLASS = new $CONTROLLER_NAME($this->core);
		if (method_exists($CLASS, $FUNCTION) || method_exists($CLASS, '__call')) {
			array_shift($path);
			call_user_func(array($CLASS, $FUNCTION), $path);
		} elseif (method_exists($CLASS, 'not_found')) {
			$this->mods->logger->logInfo("Function from URL not found. Trying not_found function", $CONTROLLER_NAME, __FILE__, __LINE__);
			call_user_func(array($CLASS, 'not_found'), $path);
		} elseif (method_exists($CLASS, 'index')) {
			$this->mods->logger->logInfo("Function from URL not found. Last try. Trying index function", $CONTROLLER_NAME, __FILE__, __LINE__);
			call_user_func(array($CLASS, 'index'), $path);
		} else {
			$this->mods->logger->logError("Function from URL not found. No index function present. Ignoring request", $CONTROLLER_NAME, __FILE__, __LINE__);
			$this->mods->logger->enable();
			return false;
		}
	}
}

?>