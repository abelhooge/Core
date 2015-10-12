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

/**
 * Models Class
 *
 * Simple loader class for MVC Models. Typically loads models from Application/Models unless otherwise specified.
 * If a model is not found, it will load a DatabaseModel type which will analyze the database and can directly be used.
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Models {

    /**
     * Array of all the loaded models
     * @var array
     */
    private static $models_array = array();

    /**
     * Array of all the existing model types (classes)
     * @var array
     */
    private static $model_types = array();

    /**
     * Load a model.
     * @param  String $name      Name of the model
     * @param  String $directory Optional directory of the model
     * @return Object            The Model object.
     */
    public static function loadModel($name, $directory = null){
        // Model load event
        $event = Events::fireEvent('modelLoadEvent', $name, $directory);
        $directory          = ($event->directory === null ? "Application/Models" : $event->directory);
        $name               = ($event->model === null ? $name : $event->model);

        $file = $directory.'/model.'.$name.'.php';
        if (isset(self::$model_types[$name])) {
            Logger::log('Loading Model: '.get_class(self::$model_types[$name]), get_class(self::$model_types[$name]));
            self::$models_array[$name] = self::$model_types[$name];
        } elseif (file_exists($file)){
            require_once($file);
            $model = "\Application\Model\\" . ucfirst($name);
            Logger::log('Loading Model: '.$model, $model);
            return self::$models_array[$name] = new $model();
        } else{
            throw new ModelException("The requested model: \''.$name.'\' could not be found", 1);
        }
    }

    /**
     * Retrieve a model
     * @param  String $name Name of the model
     * @return Object       The Model object
     */
    public static function get($name){
        // Get the name
        $name = strtolower($name);

        // Check if it already exists
    	if (isset(self::$models_array[$name])) {
            // Return if it does
    		return self::$models_array[$name];
    	} else {
            // If not, load and return afterwards
    		return self::loadModel($name);
    	}
    }
}

?>