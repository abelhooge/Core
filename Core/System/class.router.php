<?php

namespace FuzeWorks;

class Router extends Bus{

    /**
     * @var null|string The provided path
     */
    private $path       = null;

    /**
     * @var array Routes
     */
    private $routes     = array();

	/**
	 * @var null|mixed The callable
	 */
	private  $callable  = null;

    /**
	 * @var null|string The controller object
	 */
	private  $controller= null;

	/**
	 * @var null|string The extracted controller's function name
	 */
	private  $function  = null;

	/**
	 * @var array The extracted parameters
	 */
	private $parameters = array();

    /**
     * The constructor adds the default route to the routing table
     *
     * @param Core $core Reference to the core object
     */
    public function __construct(&$core){
        parent::__construct($core);
    }

    /**
     * Add the default routes to the routing table
     */
    public function init() {
        foreach($this->config->routes as $route => $callable){

            if(is_int($route)) {

                $route    = $callable;
                $callable = array($this, 'defaultCallable');
            }

            $this->addRoute($route, $callable, false);
        }
    }

    /**
     * Returns the current routing path
     *
     * @return bool|string
     */
    public function getPath(){

        return $this->path;
    }

    /**
     * Returns an array with all the routes
     * @return array
     */
    public function getRoutes(){

        return $this->routes;
    }

    /**
     * Returns the currently loaded callable
     * @return null|callable
     */
    public function getCallable(){

        return $this->callable;
    }

    /**
     * Returns the active controller
     *
     * @return null|Controller The controller object
     */
    public function getController() {

        return $this->controller;
    }

    /**
     * Returns the name of the function
     *
     * @return null|string The name of the function
     */
    public function getFunction() {

        return $this->function;
    }

	/**
	 * Returns the routing parameters
	 *
	 * @return array
	 */
	public function getParameters(){

		return $this->parameters;
    }

	/**
	 * Returns the routing parameter at given index
	 *
	 * @param int $index
	 * @return array
	 */
	public function getParameter($index = 0){

		$parameters = $this->parameters;
        $index      = ($index >= 0 ? $index : count($parameters)+$index);

        if(isset($parameters[$index]))
            return $parameters[$index];

        return null;
	}

	/**
	 * Set the current routing path
	 *
	 * @param string $path The routing path (e.g. a/b/c/d/e)
	 * @return bool|string
	 */
	public function setPath($path){
        // Fire the event to notify our modules
        $event = $this->events->fireEvent('routerSetPathEvent', $path);

        // The event has been cancelled
        if($event->isCancelled()){

            return false;
        }

		// Remove double slashes
		$event->path = preg_replace('@[/]+@', '/', $event->path);

		// Remove first slash
		if(substr($event->path, 0, 1) == '/')
			$event->path = substr($event->path, 1);

        // Remove trailing slash
        if(substr($event->path, -1, 1) == '/')
            $event->path = substr($event->path, 0, strlen($event->path)-1);

		return $this->path = $event->path;
	}

    /**
     * Add a route
     *
     * The path will be checked before custom routes before the default route(/controller/function/param1/param2/etc)
     * When the given RegEx matches the current routing-path, the callable will be called.
     * 
     * The callable will be called with three arguments:
     * 
     *      Callable($controller, $function, $parameters)
     * 
     * These three variables will be extracted from the named groups of your RegEx. When one or more named groups are
     * not matched, they will be set to NULL. The default RegEx is:
     *
     *      /^(?P<controller>.*?)(|\/(?P<function>.*?)(|\/(?P<parameters>.*?)))$/
     *
     *           ^ Named group 1        ^ Named group 2     ^ Named group 3
     *
     *      Named group 1 is named 'controller' and thus will become $controller
     *      Named group 2 is named 'function' and thus will become $function
     *      Named group 3 is named 'parameters' and thus will become $parameters
     *
     * You can also add aliases with the following:
     *
     *      '/^this-is-an-alias$/' => array(
     *          'controller' => 'home',
     *          'function'   => 'index',
     *          'parameters' => array()
     *      ),
     *
     *      This will link '/this-is-an-alias/ to /home/index. It is also possible to use the three named capture groups
     *      for the function, parameters or controllers. Like this:
     *
     *      '/^alias(|\-(?P<function>.*?))$/' => array(
     *          'controller' => 'home'
     *      ),
     *
     *      This will mask '/alias' to '/home' and '/alias-test' to 'home/test'.
     *
     * You do not *have* to use named groups, but when you don't the arguments will be left NULL; and you will need to
     * extract the information from the routing-path yourself.
     *
     * @param string $route This is a RegEx of the route, Every capture group will be a parameter
     * @param callable $callable The callable to execute
     * @param bool $prepend Whether or not to insert at the beginning of the routing table
     */
    public function addRoute($route, $callable, $prepend = true){

        if($prepend)
            $this->routes = array($route => $callable) + $this->routes;
        else
            $this->routes[$route] = $callable;

        $this->logger->log('Route added at '.($prepend ? 'top' : 'bottom').': "'.$route.'"');
    }

    /**
     * Removes a route from the array based on the given route
     *
     * @param $route string The route to remove
     */
    public function removeRoute($route){

        unset($this->routes[$route]);

        $this->logger->log('Route removed: '.$route);
    }

    /**
	 * Extracts the routing path to controller, function and parameters
	 *
	 * @param boolean $loadCallable Immediate load the callable after routing
	 */
	public function route($loadCallable = true)
    {
        // Default values
        $callable = null;
        $args     = array();

        $controller = null;
        $function   = null;
        $parameters = null;

        // Fire the event to notify our modules
        $event = $this->events->fireEvent('routerRouteEvent', $this->routes, $loadCallable);

        // The event has been cancelled
        if($event->isCancelled()){

            return;
        }

        // Assign everything to the object to make it accessible, but let modules check it first
        $this->routes = $event->routes;
        $loadCallable = $event->loadCallable;

        //Check the custom routes
        foreach ($this->routes as $r => $c) {

            //A custom route is found
            if(preg_match($r, $this->path, $matches)) {

                $controller = !empty($matches['controller']) ? $matches['controller']               : null;
                $function   = !empty($matches['function'])   ? $matches['function']                 : null;
                $parameters = !empty($matches['parameters']) ? explode('/', $matches['parameters']) : null;

                $this->logger->log('Route matched: '.$r);

                $callable = $c;
                break;
            }
        }

        $this->callable   = $callable;
        $this->controller = $controller;
        $this->function   = $function;
        $this->parameters = $parameters;

        // Check if we found a callable anyway
        if($this->callable === null){

            $this->logger->logWarning('No routes found for given path: "'.$this->path.'"', 'Router');
            $this->logger->http_error(404);
            return;
        }

        if($loadCallable)
            $this->loadCallable();
	}

	/**
	 * Load and execute the callable
	 */
	public function loadCallable(){

        $this->logger->newLevel('Loading callable');

        // Fire the event to notify our modules
        $event = $this->events->fireEvent('routerLoadCallableEvent', $this->callable, $this->controller, $this->function, $this->parameters);

        // The event has been cancelled
        if($event->isCancelled()){

            return;
        }

        // Assign everything to the object to make it accessible, but let modules check it first
        $this->callable   = $event->callable;
        $this->controller = $event->controller;
        $this->function   = $event->function;
        $this->parameters = $event->parameters;

        if(!is_callable($this->callable))
        if(isset($this->callable['controller'])) {

            $this->controller = isset($this->callable['controller']) ? $this->callable['controller'] : $this->controller;
            $this->function   = isset($this->callable['function'])   ? $this->callable['function']   : $this->function;
            $this->parameters = isset($this->callable['parameters']) ? $this->callable['parameters'] : $this->parameters;

            $this->callable = array($this, 'defaultCallable');
        }else{

            $this->logger->log('The given callable is not callable!', E_ERROR);
            $this->error->http_error(500);
            $this->logger->stopLevel();
            return;
        }

        $args = array(
            $this->controller,
            $this->function,
            $this->parameters,
        );

        $this->logger->newLevel('Calling Callable');
        $this->logger->log('Controller: '.  ($args[0] === null ? 'null' : $args[0]));
        $this->logger->log('Function: '.    ($args[1] === null ? 'null' : $args[1]));
        $this->logger->log('Parameters: '.  (empty($args[2])   ? '[]'   : implode(', ',$args[2])));
        $this->logger->stopLevel();

        call_user_func_array($this->callable, $args);

        $this->logger->stopLevel();
    }

    /**
     * The default callable
     *
     * This callable will do the 'old skool' routing. It will load the controllers from the controller-directory
     * in the application-directory.
     */
    public function defaultCallable(){

        $this->logger->log('Default callable called!');

        $this->controller = $this->controller === null ? $this->config->main->default_controller : $this->controller;
        $this->function   = $this->function   === null ? $this->config->main->default_function   : $this->function;

        // Construct file paths and classes
        $class  = '\Controller\\'.ucfirst($this->controller);
        $file   = 'Application/Controller/controller.'.$this->controller.'.php';

        $this->logger->log('Loading controller '.$class.' from file: '.$file);

        // Check if the file exists
        if(file_exists($file)){

            if(!class_exists($class))
                require $file;

            $this->callable = new $class($this->core);

            // Check if method exists or if there is a caller function
            if(method_exists($this->callable, $this->function) || method_exists($this->callable, '__call')){

                // Execute the function on the controller
                $this->callable->{$this->function}($this->parameters);
            }else{

                // Function could not be found
                $this->logger->log('Could not find function '.$this->function.' on controller '.$class);
                $this->logger->http_error(404);
            }
        }else{

            // Controller could not be found
            $this->logger->log('Could not find controller '.$class);
            $this->logger->http_error(404);
        }
    }
}
?>