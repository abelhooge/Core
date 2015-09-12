<?php
/**
 * FuzeWorks
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2015   TechFuze
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 * @version     Version 0.0.1
 */

namespace FuzeWorks;
use \Application\Init;

/**
 * Class Router
 *
 * This class handles the framework's routing. The router determines which controller should be called.
 * The overall structure of the routing is as follows:
 *
 * The routes-array will hold a list of RegEx-strings. When the route-method is called, the framework will try
 * to match the current path against all the RegEx's. When a RegEx matches, the linked callable will be called.
 *
 * The default route works as follows:
 *
 *      Let's say the visitor requests /A/B/C
 *
 *      A would be the 'controller' (default: home)
 *      B would be the function to be called in the 'controller' (default: index)
 *      C would be the first parameter
 *
 *      All controllers are to be placed in the /Application/controller-directory.
 *
 * But because of this RegEx-table, modules can easily listen on completely different paths. You can, for example, make
 * a module that only triggers when /admin/<controller>/<function>/.. is accessed. Or even complexer structure are
 * available, e.g: /webshop/product-<controller>/view/<function>.
 *
 * BE AWARE:
 *
 *      Callables are NO controllers!! By default, the 'defaultCallable' will load the correct controller from
 *      the default controller directory. When you make custom routes, the callable will need to call your own
 *      controllers. This means that the one callable you provide with your RegEx will be called for EVERYTHING
 *      the RegEx matches. The names groups 'controller' and 'function' will be passed as first two arguments,
 *      if no names groups are available; you will need to extract them yourself from the path.
 *
 * After the core has been loaded, the method setPath will be called with the request URI (e.g. obtained via .htaccess).
 * That method will then call the route-method, which will call the right controller and it's method.
 *
 * @see Router::setPath
 * @see Router::route
 *
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Router{

    /**
     * @var null|string The provided path
     */
    private static $path        = null;

    /**
     * @var array Routes
     */
    private static $routes      = array();

    /**
     * @var null|mixed The callable
     */
    private static $callable    = null;

    /**
     * @var null|string The extracted controller's name
     */
    private static $controller  = null;

    /**
     * @var null|string The extracted controller's function name
     */
    private static $function    = null;

    /**
     * @var array The extracted parameters
     */
    private static $parameters  = array();

    /**
     * The constructor adds the default route to the routing table
     */
    public static function init(){

        foreach(Config::get('routes') as $route => $callable){

            if(is_int($route)) {

                $route    = $callable;
                $callable = array('\FuzeWorks\Router', 'defaultCallable');
            }

            self::addRoute($route, $callable, false);
        }
    }

    /**
     * Returns the current routing path
     *
     * @return bool|string
     */
    public static function getPath(){

        return self::$path;
    }

    /**
     * Returns an array with all the routes
     * @return array
     */
    public static function getRoutes(){

        return self::$routes;
    }

    /**
     * Returns the currently loaded callable
     * @return null|callable
     */
    public static function getCallable(){

        return self::$callable;
    }

    /**
     * Returns the active controller's name
     *
     * @return null|string
     */
    public static function getController() {

        return self::$controller;
    }

    /**
     * Returns the name of the active controller's function
     *
     * @return null|string The name of the function
     */
    public static function getFunction() {

        return self::$function;
    }

    /**
     * Returns the routing parameters
     *
     * @return array
     */
    public static function getParameters(){

        return self::$parameters;
    }

    /**
     * Returns the routing parameter at given index
     *
     * @param int $index
     * @return array
     */
    public static function getParameter($index = 0){

        $parameters = self::$parameters;
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
    public static function setPath($path){

        // Fire the event to notify our modules
        $event = Events::fireEvent('routerSetPathEvent', $path);

        // The event has been cancelled
        if($event->isCancelled()){

            return false;
        }

        // Remove double slashes
        $path = preg_replace('@[/]+@', '/', $path);

        // Remove first slash
        if(substr($path, 0, 1) == '/')
            $path = substr($path, 1);

        // Remove trailing slash
        if(substr($path, -1, 1) == '/')
            $path = substr($path, 0, strlen($path)-1);

        return self::$path = $path;
    }

    /**
     * Set the controller name
     *
     * Until we decide what to do with name giving of controllers/functions,
     * this function stays here.
     *
     * @param string $controller the name of the controller
     */
    public static function setController($controller){

        self::$controller = $controller;
    }

    /**
     * Set the function name
     *
     * Until we decide what to do with name giving of controllers/functions,
     * this function stays here.
     *
     * @param string $function the name of the controller
     */
    public static function setFunction($function){

        self::$function = $function;
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
    public static function addRoute($route, $callable, $prepend = true){

        if($prepend)
            self::$routes = array($route => $callable) + self::$routes;
        else
            self::$routes[$route] = $callable;

        Logger::log('Route added at '.($prepend ? 'top' : 'bottom').': "'.$route.'"');
    }

    /**
     * Removes a route from the array based on the given route
     *
     * @param $route string The route to remove
     */
    public static function removeRoute($route){

        unset(self::$routes[$route]);

        Logger::log('Route removed: '.$route);
    }

    /**
     * Extracts the routing path to controller, function and parameters
     *
     * @TODO: $loadCallable might not be needed anymore
     * @param boolean $loadCallable Immediate load the callable when it's route matches
     */
    public static function route($loadCallable = true)
    {
        // Fire the event to notify our modules
        $event = Events::fireEvent('routerRouteEvent', self::$routes, $loadCallable);

        // The event has been cancelled
        if($event->isCancelled()){

            return;
        }

        // Assign everything to the object to make it accessible, but let modules check it first
        //self::$routes = $event->routes;
        //$loadCallable = $event->loadCallable;

        //Check the custom routes
        foreach (self::$routes as $r => $c) {

            //A custom route is found
            if(preg_match($r, self::$path, $matches)) {

                self::$controller = !empty($matches['controller']) ? $matches['controller']               : null;
                self::$function   = !empty($matches['function'])   ? $matches['function']                 : null;
                self::$parameters = !empty($matches['parameters']) ? explode('/', $matches['parameters']) : null;

                Logger::log('Route matched: '.$r);

                self::$callable   = $c;
                if(!$loadCallable || !self::loadCallable())
                    break;
            }
        }

        // Check if we found a callable anyway
        if(self::$callable === null){

            Logger::log('No routes found for given path: "'.self::$path.'"', E_WARNING);
            Logger::http_error(404);
            return;
        }
    }

    /**
     * Load and execute the callable
     *
     * @return boolean Whether or not the callable was satisfied
     */
    public static function loadCallable(){

        Logger::newLevel('Loading callable');

        // Fire the event to notify our modules
        $event = Events::fireEvent('routerLoadCallableEvent', self::$callable, self::$controller, self::$function, self::$parameters);

        // The event has been cancelled
        if($event->isCancelled())
            return false;

        if(!is_callable(self::$callable))
        if(isset(self::$callable['controller'])) {

            self::$controller = isset(self::$callable['controller']) ? self::$callable['controller'] : self::$controller;
            self::$function   = isset(self::$callable['function'])   ? self::$callable['function']   : self::$function;
            self::$parameters = isset(self::$callable['parameters']) ? self::$callable['parameters'] : self::$parameters;

            self::$callable = array('\FuzeWorks\Router', 'defaultCallable');
        }else{

            Logger::log('The given callable is not callable!', E_ERROR);
            Logger::http_error(500);
            Logger::stopLevel();
            return true;
        }

        $args = array(
            self::$controller,
            self::$function,
            self::$parameters,
        );

        Logger::newLevel('Calling callable');
        Logger::log('Controller: '.  ($args[0] === null ? 'null' : $args[0]));
        Logger::log('Function: '.    ($args[1] === null ? 'null' : $args[1]));
        Logger::log('Parameters: '.  (empty($args[2])   ? '[]'   : implode(', ',$args[2])));
        Logger::stopLevel();

        $skip = call_user_func_array(self::$callable, $args) === false;

        if($skip)
            Logger::log('Callable not satisfied, skipping to next callable');

        Logger::stopLevel();
        return $skip;
    }

    /**
     * The default callable
     *
     * This callable will do the 'old skool' routing. It will load the controllers from the controller-directory
     * in the application-directory.
     */
    public static function defaultCallable(){

        Logger::log('Default callable called!');

        self::$controller = self::$controller === null ? Config::get('main')->default_controller : self::$controller;
        self::$function   = self::$function   === null ? Config::get('main')->default_function   : self::$function;

        // Construct file paths and classes
        $class  = '\Controller\\'.ucfirst(self::$controller);
        $file   = 'Application/Controller/controller.'.self::$controller.'.php';

        Logger::log('Loading controller '.$class.' from file: '.$file);

        // Check if the file exists
        if(file_exists($file)){

            if(!class_exists($class))
                require $file;

            // Get the path the controller should know about
            $path = substr(self::getPath(), ($pos = strpos(self::getPath(), '/')) !== false ? $pos + 1 : 0);

            // And create the controller
            self::$callable = new $class($path);

            // If the controller does not want a function to be loaded, provide a halt parameter.
            if (isset(self::$callable->halt)) {
                return;
            }

            // Check if method exists or if there is a caller function
            if(method_exists(self::$callable, self::$function) || method_exists(self::$callable, '__call')){

                // Execute the function on the controller
                echo self::$callable->{self::$function}(self::$parameters);
            }else{

                // Function could not be found
                Logger::log('Could not find function '.self::$function.' on controller '.$class);
                Logger::http_error(404);
            }
        }else{

            // Controller could not be found
            Logger::log('Could not find controller '.$class);
            Logger::http_error(404);
        }
    }
}
?>