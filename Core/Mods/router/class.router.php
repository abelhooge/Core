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
		// Retrieve the path and convert it to a proper format
		$path = (!empty($this->getPath()) ? explode('/', preg_replace('#/+#','/',$this->getPath())) : array());
		$path_size = count($path);

		// If trailing slash was given or the last element was empty: remove it
		if(end($path) == ''){
			array_pop($path);
		}

		// Perform a routing check
		// Prepare CONTROLLER, FUNCTION and PARAMS variables
		$CONTROLLER = "";
		$FUNCTION = "";
		$PARAMS = array();

		// First check if anything is given
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
		$this->controllerName   = ($event->controller === null || empty($event->controller) ? $this->config->main->default_controller : $event->controller);
		$this->function		    = ($event->function === null || empty($event->function) ? $this->config->main->default_function : $event->function);
		$this->parameters 	    = $event->parameters;
		$this->directory 		= ($event->directory === null || empty($event->directory) ? FUZEPATH . "/Application/Controller/" : $event->directory);

        // Load the controller
        $this->loadController();
	}

	/**
	 * Load a controller
	 * @access public
	 * @param String controller name
	 * @param String function name
	 * @param Array Parameters
	 */
	public function loadController() {
		$file = $this->directory . "controller.".strtolower($this->controllerName).".php";
		$this->logger->log("Loading controller from file: '".$file."'");

		if (file_exists($file)) {
			if (!class_exists(ucfirst($this->controllerName)))
				require_once($file);

			$this->controllerClass = ucfirst($this->controllerName);
			$this->controller = new $this->controllerClass($this->core);

			if (method_exists($this->controller, $this->function) || method_exists($this->controller, '__call')) {
				$this->controller->{$this->function}($this->parameters);
			} elseif (method_exists($this->controller, 'not_found')) {
				// Trying last resort
				$this->logger->log("Function was not found, trying Controllers not_found function");
				$this->controller->not_found($this->parameters);
			} else {
				$this->logger->logError("Could not load not_found function. Aborting");
				// totally not found
			}
		} else {
			$this->logger->logError("Could not find class. Reverting to default controller not_found");
			$file = $this->directory . "controller.".strtolower($this->config->main->default_controller).".php";
			if (file_exists($file))
				require_once($file);
			$this->controllerClass = ucfirst($this->config->main->default_controller);
			$this->controller = new $this->controllerClass($this->core);
			$this->controller->not_found($this->parameters);
		}
	}
}

?>