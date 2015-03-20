<?php
/**
 * Sections module, see usage documentation
 * @author TechFuze
 */
class Sections extends Module {

	/** 
	 * The config holder for this module. Holds an StdObject 
	 * @access public
	 * @var StdObject Module Config
	 */
	public $cfg;

	/**
	 * The current section that we are using right now!
	 * @access private
	 * @var String name of section or null
	 */
	private $currentSection = null;

	/**
	 * Loads the module and registers the events
	 * @access public
	 */
	public function onLoad() {
		// Load module configuration
		$this->cfg = $this->config->loadConfigFile('sections', $this->getModulePath());

		// Register Events
		$this->events->addListener(array($this, 'eventRegisterBuild'), 'eventRegisterBuildEvent', EventPriority::NORMAL);
		$this->events->addListener(array($this, 'routerEvent'), 'routerRouteEvent', EventPriority::NORMAL);
		$this->events->addListener(array($this, 'layoutLoadEvent'), 'layoutLoadEvent', EventPriority::NORMAL);
		$this->events->addListener(array($this, 'modelLoadevent'), 'modelLoadEvent', EventPriority::NORMAL);
	}

	/**
	 * Registers this module in the eventRegister for routerRouteEvent
	 * @access public
	 * @param eventRegisterBuildEvent Event
 	 * @return eventRegisterBuildEvent Event
	 */
	public function eventRegisterBuild($event) {
		$event->addEvent('sections', 'routerRouteEvent');
		$event->addEvent('sections', 'layoutLoadEvent');
		$event->addEvent('sections', 'modelLoadEvent');
		return $event;
	}

	/**
	 * Redirects layouts to the new section
	 * @access public
	 * @param layoutLoadEvent Event
	 * @return layoutLoadEvent Event
	 */
	public function layoutLoadEvent($event) {
		$layout_name = $event->layout;
		if ($this->currentSection !== null) {
			$section = $this->getSection($this->currentSection);
			$event->directory = $section['view_path'];
		}
		return $event;
	}

	/**
	 * Redirects models to the new section
	 * @access public
	 * @param layoutLoadEvent Event
	 * @return layoutLoadEvent Event
	 */
	public function modelLoadEvent($event) {
		$model_name = $event->model;
		if ($this->currentSection !== null) {
			$section = $this->getSection($this->currentSection);
			$event->directory = $section['model_path'];
		}
		return $event;
	}

	/** 
	 * Add a section to the config file
	 * @access public
	 * @param String section_name, name of the section
	 * @param Boolean module_section wether this is a module_section
	 * @param String module_name to use when this is a module_section
	 * @param String Controller_path, where to find the controllers for this section
	 * @param String Model_path, where to find the models for this section
	 * @param String View_path, where to find the views for this section
	 */
	public function addSection($name, $module_section = false, $module_name = null, $controller_path = null, $model_path = null, $view_path = null) {
		if ($module_section) {
			$m = $this->core->loadMod($module_name);
			$m_dir = $m->getModulePath();
			$data = array(
			    'name' => $name,
			    'module_section' => $module_section,
			    'module_name' => $module_name,
			    'controller_path' => $m_dir .  '/Controller/',
			    'model_path' => $m_dir . '/Models/',
			    'view_path' => $m_dir . '/Views/',			
				);
		} else {
			$data = array(
			    'name' => $name,
			    'module_section' => $module_section,
			    'module_name' => $module_name,
			    'controller_path' => FUZEPATH .  $controller_path,
			    'model_path' => FUZEPATH . $model_path,
			    'view_path' => FUZEPATH . $view_path,			
				);			
		}

		$this->config->set('sections', $name, $data, $this->getModulePath());
	}

	/**
	 * Removes a section from the config file
	 * @access public 
	 * @param String section_name, name of the section to remove
	 */
	public function removeSection($name) {
		$this->config->set('sections', $name, null, $this->getModulePath());
	}

	/**
	 * Get's called on routerRouteEvent. Redirects when a section is found
	 * @access public 
	 * @param routerRouteEvent Event
	 * @return routerRouteEvent Event
	 */
	public function routerEvent($event) {
		$name = $event->controller;
		$controller = null;
		$function = null;
		$parameters = array();
		$section = $this->{$name};

		if ($section !== null) {
			// Section found
			$this->logger->log("Section found with name: '".$name."'", 'Sections');
			$this->currentSection = $name;

			// Logic here, first for module sections
			if ($section['module_section']) {
				$mod = $this->core->loadMod($section['module_name']);
				$event->directory = $mod->getModulePath() . "/Controller/";
			} else {
				// Now for regular sections
				$event->directory = $section['controller_path'];
			}

			// Move the path so it matches the new regime
			if (count($event->parameters) > 0) {
				$function = $event->parameters[0];
				$parameters = array_slice($event->parameters, 1);
			} else {
				$function = $this->config->main->default_function;
			}

			// And finally set the controller, if no parameters are set, load the default function
			$controller = (!empty($event->function) ? $event->function : $this->config->main->default_controller );
		} else {
			$this->logger->log("No section was found with name: '".$name."'", 'Sections');
		}

        if($controller !== null)$event->controller  = $controller;
        if($function   !== null)$event->function    = $function;
        if(count($parameters) !== 0)$event->parameters  = $parameters;

		return $event;
	}

	/**
	 * Load a section file
	 * @access public
	 * @param String section name
	 * @return Array Section
	 */
	public function getSection($name) {
		if (isset($this->cfg->$name)) {
			return $this->cfg->$name;
		}
	}

	/**
	 * Retrieves section information from the config file
	 * @access public
	 * @param String section_name, name of the section
	 * @return Array section_information or null
	 */
	public function __get($name){
		// Something given?
        if(empty($name)) {
			// Currently in a section?
            if($this->currentSection !== null){
				// Return that one then
                return $this->cfg[$this->currentSection];
            }
            return null;
        }

		// Return element $name of the config file
		if (isset($this->cfg->$name)) {
			$section = $this->cfg->$name;
		} else {
			$section = null;
		}
        
        return $section;
	}

}

?>