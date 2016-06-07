<?php
/**
 * FuzeWorks.
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
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://fuzeworks.techfuze.net
 * @since Version 0.0.1
 *
 * @version Version 0.0.1
 */

namespace FuzeWorks;

use Application\Init;

/**
 * Class Router.
 *
 * This class handles the framework's routing. The router determines which system should be loaded and called.
 * The overall structure of the routing is as follows:
 *
 * The routes-array will hold a list of RegEx-strings. When the route-method is called, the framework will try
 * to match the current path against all the RegEx's. When a RegEx matches, the linked callable will be called.
 *
 * Every module can register routes and add their own callables. By default, two callables are used:
 * The defaultCallable and the moduleCallable.
 *
 * The defaultCallable is a traditional MVC controller loader. Loading an URL using a default route works as follows:
 *
 *      Let's say the visitor requests /A/B/C
 *
 *      A would be the 'controller' (default: standard)
 *      B would be the function to be called in the 'controller' (default: index)
 *      C would be the first parameter
 *
 *      All controllers are to be placed in the /Application/controller-directory.
 *
 * This is the default behaviour by adding routes to the config.routes.php. It is also possible to load Modules using routes.
 * To load a Module using a route, add the route to the moduleInfo.php in a routes array.
 * When this route is matched, a moduleCallable gets loaded which loads the module and loads either a controller file, or a routing function.
 *
 * But because of this RegEx-table, modules can easily listen on completely different paths. You can, for example, make
 * a module that only triggers when /admin/<page>/<component>/.. is accessed. Or even complexer structure are
 * available, e.g: /webshop/product-<id>/view/<detailID>.
 *
 * BE AWARE:
 *
 *      Callables are NO controllers!! By default, the 'defaultCallable' will load the correct controller from
 *      the default controller directory. When you make custom routes, the callable will need to call your own
 *      controllers. This means that the one callable you provide with your RegEx will be called for EVERYTHING
 *      the RegEx matches. The names groups 'controller' and 'function' will be passed as first two arguments,
 *      if no names groups are available; you will need to extract them yourself from the path.
 *
 * After the core has been loaded, the URI class will generate the URI which is currently being used.
 * That method will then call the route-method, which will call the right controller and it's method.
 *
 * @see Router::route
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * 
 * @todo Implement Query Strings
 * @todo Add Documentation
 * @todo Implement Unit tests
 */
class Router
{
    /**
     * @var array Routes
     */
    protected $routes = array();

    /**
     * @var null|mixed The callable
     */
    protected $callable = null;

    /**
     * @var null|array The extracted matches from the regex
     */
    protected $matches = null;

    /**
     * Translate URI dashes
     *
     * Determines whether dashes in controller & method segments
     * should be automatically replaced by underscores.
     *
     * @var bool
     */
    protected $translate_uri_dashes = false;

    protected $config;

    protected $uri;

    protected $logger;

    protected $events;

    protected $output;

    /**
     * The constructor adds the default route to the routing table.
     */
    public function __construct()
    {
        // Load related classes
        $factory = Factory::getInstance();
        $this->config = $factory->config;
        $this->uri = $factory->uri;
        $this->logger = $factory->logger;
        $this->events = $factory->events;
        $this->output = $factory->output;

        // Start parsing the routing
        $this->parseRouting();
    }

    protected function parseRouting()
    {
        // Get routing routes
        $routes = $this->config->routes;
        $routing = $this->config->routing;

        // If no query strings are used, we will add all routes in the config.routes.php file. 
        // We modify these routes to be an array of a regex string and a callable
        $http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

        foreach ($routes as $route => $value) 
        {
            // Check if the route format is using HTTP verbs
            if (is_array($value))
            {
                $value = array_change_key_case($value, CASE_LOWER);
                if (isset($value[$http_verb]))
                {
                    $value = $value['http_verb'];
                }
                else
                {
                    continue;
                }
            }

            // Convert wildcards to Regex
            $route = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $route);

            $this->addRoute($route, $value, false);
        }
    }

    /**
     * Returns an array with all the routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Returns the currently loaded or selected callable.
     *
     * @return null|callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Returns all the matches with the RegEx route.
     *
     * @return null|array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    public function addRoute($route, $callable, $prepend = true)
    {
        if ($prepend) {
            $this->routes = array($route => $callable) + $this->routes;
        } else {
            $this->routes[$route] = $callable;
        }

        $this->logger->log('Route added at '.($prepend ? 'top' : 'bottom').': "'.$route.'"');
    }

    /**
     * Removes a route from the array based on the given route.
     *
     * @param $route string The route to remove
     */
    public function removeRoute($route)
    {
        unset($this->routes[$route]);

        $this->logger->log('Route removed: '.$route);
    }

    /**
     * Extracts the routing path from the URL using the routing table.
     *
     * Determines what callable should be loaded and what data matches the route regex.
     *
     * @param bool $performLoading Immediate process the route after it has been determined
     */
    public function route($performLoading = true)
    {
        // Turn the segment array into a URI string
        $uri = implode('/', $this->uri->segments);

        // Fire the event to notify our modules
        $event = Events::fireEvent('routerRouteEvent', $this->routes, $performLoading, $uri);

        // The event has been cancelled
        if ($event->isCancelled()) 
        {
            return;
        }

        // Assign everything to the object to make it accessible, but let modules check it first
        $routes = $event->routes;
        $performLoading = $event->performLoading;

        // If a cached page should be loaded, do so and stop loading a routed page
        if ($performLoading === true && $event->cacheOverride === false && $this->output->_display_cache() === true)
        {
            return true;
        }

        // Check the custom routes
        foreach ($routes as $route => $value) 
        {
            // Match the path against the routes
            if (preg_match('#^'.$route.'$#', $event->path, $matches))
            {
                $this->logger->log('Route matched: '.$route);
                // Save the matches
                $this->matches = $matches;

                // Are we using callbacks or another method?
                if ( is_array($value))
                {
                    // Maybe there is a real callable which should be called in the future
                    if ( isset($value['callable']) )
                    {
                        $this->callable = $value['callable'];
                    }

                    // If the callable is satisfied, break away
                    if (!$performLoading || !$this->loadCallable($matches, $route))
                    {
                        return;
                    }

                    // Otherwise try other routes
                    continue;
                }
                elseif ( ! is_string($value) && is_callable($value))
                {
                    // Prepare the callable
                    array_shift($matches);

                    // Retrieve the path that should be loaded
                    $value = call_user_func_array($value, $matches);
                }
                elseif (strpos($value, '$') !== FALSE && strpos($route, '(') !== FALSE)
                {
                    $value = preg_replace('#^'.$route.'$#', $value, $event->path);
                }

                // Now run the defaultRouter for when something is not a callable
                $this->routeDefault(explode('/', $value), $route);
                return;
            }
        }

        // If we got this far it means we didn't encounter a
        // matching route so we'll set the site default route
        $this->matches = array();
        if ($performLoading === true)
        {
            $this->routeDefault(array_values($this->uri->segments), '.*$');
        }
    }

    /**
     * @todo Implement validateRequest
     */
    protected function validateRequest($segments)
    {
        $c = count($segments);
    }

    protected function routeDefault($segments = array(), $route)
    {
        // If we don't have any segments left - try the default controller;
        // WARNING: Directories get shifted out of the segments array!
        if (empty($segments))
        {
            $segments[0] = $this->config->routing->default_controller;
        }

        if ($this->translate_uri_dashes === true)
        {
            $segments[0] = str_replace('-', '_', $segments[0]);
            if (isset($segments[1]))
            {
                $segments[1] = str_replace('-', '_', $segments[1]);
            }
        }

        // Prepare the values for loading
        $controller = $segments[0];
        $function = (isset($segments[1]) ? $segments[1] : $this->config->routing->default_function);

        // And prepare the Router URI
        array_unshift($segments, null);
        unset($segments[0]);
        $this->uri->rsegments = $segments;

        // Now create a matches array
        $matches = array(
                'controller' => $controller,
                'function' => $function,
                'parameters' => array_slice($this->uri->rsegments, 2)
            );

        // And finally load the callable
        $this->callable = array('\FuzeWorks\Router', 'defaultCallable');
        $this->loadCallable($matches, $route);
    }

    /**
     * Load the callable to which the route matched.
     *
     * First it checks if it is possible to call the callable. If not, the default callable gets selected and a controller, function and parameters get selected.
     *
     * Then the arguments get prepared and finally the callable is called.
     *
     * @param array   Preg matches with the routing path
     * @param string  The route that matched
     *
     * @return bool Whether or not the callable was satisfied
     */
    public function loadCallable($matches = array(), $route)
    {
        $this->logger->newLevel('Loading callable');

        // Fire the event to notify our modules
        $event = Events::fireEvent('routerLoadCallableEvent', $this->callable, $matches, $route);

        // The event has been cancelled
        if ($event->isCancelled()) {
            return false;
        }

        // Prepare the arguments and add the route
        $args = $event->matches;
        $args['route'] = $event->route;

        if (!is_callable($event->callable)) {
            if (isset($this->callable['controller'])) {
                // Reset the arguments and fetch from custom callable
                $args = array();
                $args['controller'] = isset($this->callable['controller']) ? $this->callable['controller'] : (isset($matches['controller']) ? $matches['controller'] : null);
                $args['function'] = isset($this->callable['function'])   ? $this->callable['function']   : (isset($matches['function']) ? $matches['function'] : null);
                $args['parameters'] = isset($this->callable['parameters']) ? $this->callable['parameters'] : (isset($matches['parameters']) ? explode('/', $matches['parameters']) : null);

                $this->callable = array('\FuzeWorks\Router', 'defaultCallable');
            } else {
                $this->logger->log('The given callable is not callable!', E_ERROR);
                $this->logger->http_error(500);
                $this->logger->stopLevel();

                return true;
            }
        } else {
            $this->callable = $event->callable;
        }

        // And log the input to the logger
        $this->logger->newLevel('Calling callable');
        foreach ($args as $key => $value) {
            $this->logger->log($key.': '.var_export($value, true).'');
        }
        $this->logger->stopLevel();

        $skip = call_user_func_array($this->callable, array($args)) === false;

        if ($skip) {
            $this->logger->log('Callable not satisfied, skipping to next callable');
        }

        $this->logger->stopLevel();

        return $skip;
    }

    /**
     * The default callable.
     *
     * This callable will do the 'old skool' routing. It will load the controllers from the controller-directory
     * in the application-directory.
     */
    public function defaultCallable($arguments = array())
    {
        $this->logger->log('Default callable called!');

        $controller = $arguments['controller'];
        $function = $arguments['function'];
        $parameters = empty($arguments['parameters']) ? null : $arguments['parameters'];

        // Construct file paths and classes
        $class = '\Application\Controller\\'.ucfirst($controller);
        $directory = 'Application/Controller/';
        $file = $directory . 'controller.'.$controller.'.php';

        $event = Events::fireEvent('routerLoadControllerEvent', 
            $file, 
            $directory, 
            $class, 
            $controller, 
            $function, 
            $parameters
        );

        // Cancel if requested to do so
        if ($event->isCancelled()) {
            return;
        }

        // Check if the file exists
        if (file_exists($event->file)) {
            if (!class_exists($event->className)) {
                $this->logger->log('Loading controller '.$event->className.' from file: '.$event->file);
                include $event->file;
            }

            // Get the path the controller should know about
            $path = implode('/', $this->uri->rsegments);

            // And create the controller
            $this->callable = new $event->className($path);

            // If the controller does not want a function to be loaded, provide a halt parameter.
            if (isset($this->callable->halt)) {
                return;
            }

            // Check if method exists or if there is a caller function
            if (method_exists($this->callable, $event->function) || method_exists($this->callable, '__call')) {
                // Execute the function on the controller
                echo $this->callable->{$event->function}($event->parameters);
            } else {
                // Function could not be found
                $this->logger->log('Could not find function '.$event->function.' on controller '.$event->className);
                $this->logger->http_error(404);
            }
        } else {
            // Controller could not be found
            $this->logger->log('Could not find controller '.$event->className);
            $this->logger->http_error(404);
        }
    }
}
