<?php
/**
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

	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function onLoad() {
		// Load module configuration
		$this->cfg = $this->config->loadConfigFile('sections', $this->getModulePath());

		// Register Events
		$this->events->addListener(array($this, 'eventRegisterBuild'), 'eventRegisterBuildEvent', EventPriority::NORMAL);
		$this->events->addListener(array($this, 'routerEvent'), 'routerRouteEvent', EventPriority::NORMAL);
	}

	public function eventRegisterBuild($event) {
		$event->addEvent('sections', 'routerRouteEvent');
		return $event;
	}

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
			if ($section->module_section) {
				$this->core->loadMod($section->module_name);
				$event->directory = $this->mods->{$section->module_name}->getModulePath() . "/Controller/";
			} else {
				// Now for regular sections
				$event->directory = $section->controller_path;
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
		}

        if($controller !== null)$event->controller  = $controller;
        if($function   !== null)$event->function    = $function;
        if(count($parameters) !== 0)$event->parameters  = $parameters;

		return $event;
	}

	public function __get($name){
		// Something given?
        if(empty($name)) {

			// Currently in a section?
            if($this->currentSection !== null){

				// Return that one then
                return $this->cfg[$this->currentSection];
            }

			// Emptiness...
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