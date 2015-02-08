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

		// Explode on each slash
		$path_array = explode('/', $this->getPath());

		// If trailing slash was given or the last element was empty: remove it
		if(end($path_array) == ''){
		
			array_pop($path_array);
		}

		// Default values
		$controller	= null;
		$function	= null;
		$parameters	= array();

        // Extract controller from the path
		if(count($path_array) >= 1){

			// Controller is always the first
            $controller = $path_array[0];

            // Extract function or controller from the path
			if(count($path_array) >= 2){

				// Function is always second
                $function = $path_array[1];

                // Extract parameters or controller from the path
				if(count($path_array) >= 3){

					// Parameters come last
					$parameters = array_slice($path_array, 2);
				}
			}
		}

        // Fire the event to notify our modules
        $event = $this->events->fireEvent('routerRouteEvent', $controller, $function, $parameters);

        // The event has been cancelled
        if($event->isCancelled()){
            return;
        }

        // Assign everything to the object to make it accessible, but let modules check it first
		$this->route		    = $path_array;
		$this->controllerName   = $event->controller === null ? $this->config->main->default_controller : $event->controller;
		$this->function		    = $event->function === null ? $this->config->main->default_function : $event->function;
		$this->parameters 	    = $event->parameters;

        // Load the controller
        $dir = (isset($event->directory) ? $event->directory : null);
        $this->loadController($dir);
	}

	/**
	 * Load the controller
	 *
	 * @param null $directory custom controllers directory
	 */
	public function loadController($directory = null){
        // Select default directory if not given
        if($directory === null){
            $directory = FUZEPATH . '/Application/Controller';
        }

		// Fetch function
		$function   = $this->function;

        // Are there any namespaces given in the controller name?
		if(strpos($this->controllerName, '\\') === false){

			// No, add them
			$className 	= ucfirst($this->controllerName);
			$file		= $directory.'/controller.'.strtolower($this->controllerName).'.php';
		}else{

			// Yes, but that means that the name isn't suitable for determining the file name
			$className = $this->controllerName;

			// Retrieve the actual name of the controller so we can load the correct file
			$file = $directory.'/'.end(explode('\\', $this->controllerName)).'.php';
		}

        $this->logger->log('Loading controller from file: '.$file);

        // Check if the file exists
        if(file_exists($file)){
            require $file;

            $this->controller = new $className($this->core);

            // Check if method exists or if there is a caller function
            if(method_exists($this->controller, $function) || method_exists($this->controller, '__call')){

                // Execute the function on the controller
                $this->controller->{$function}();
            }else{

                // Function could not be found
                $this->logger->log('Could not find function '.$function.' on controller '.$className);
                $this->logger->http_logger(404);
            }
        }else{

            // Controller could not be found
            $this->logger->log('Could not find controller '.$className);
            $this->logger->http_error(404);
        }
    }
}

?>