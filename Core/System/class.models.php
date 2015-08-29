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
class Models extends Bus{

    private $models_array = array();
    private $model_types = array();
    private $models_loaded = false;

    public function __construct(&$core){
        parent::__construct($core);
    }

    public function loadModel($name, $directory = null){
        // Model load event
        $event = $this->events->fireEvent('modelLoadEvent', $name, $directory);
        $directory          = ($event->directory === null ? "Application/Models" : $event->directory);
        $name               = ($event->model === null ? $name : $event->model);

        $file = $directory.'/model.'.$name.'.php';
        if (isset($this->model_types[$name])) {
            $this->logger->logInfo('Loading Model: '.get_class($this->model_types[$name]), get_class($this->model_types[$name]));
            $this->models_array[$name] = $this->model_types[$name];
        } elseif (file_exists($file)){
            require_once($file);
            $model = "\Model\\" . ucfirst($name);
            $this->logger->logInfo('Loading Model: '.$model, $model);
            $this->models_array[$name] = new $model($this->core);
        } else{
        	$this->logger->logWarning('The requested model: \''.$name.'\' could not be found. Loading empty model', 'Models');
            require_once("Core/System/Models/model.interpret.php");
            $this->logger->logInfo('Loading Model: interprated databasemodel', 'Models');
            $model = new Interpret($this->core);
            $model->table($name);
            $this->models_array[$name] = $model;
        }
    }

    public function __get($name){
    	if (isset($this->models_array[strtolower($name)])) {
    		return $this->models_array[strtolower($name)];
    	} else {
    		$this->loadModel(strtolower($name));
    		return $this->models_array[strtolower($name)];
    	}
    }
}

?>