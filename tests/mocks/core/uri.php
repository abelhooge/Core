<?php

use FuzeWorks\Uri;
use FuzeWorks\Factory;

class Mock_Core_URI extends Uri {

	public function __construct()
	{
		$this->config = Factory::getInstance()->config;

		// set predictable config values
		$this->config->main->index_page = 'index.php';
		$this->config->main->base_url = 'http://example.com/';
		$this->config->main->application_prefix = 'MY_';
		$this->config->routing->enable_query_strings = false;
		$this->config->routing->permitted_uri_chars = 'a-z 0-9~%.:_\-';


		if ($this->config->routing->enable_query_strings !== TRUE OR is_cli())
		{
			$this->_permitted_uri_chars = $this->config->routing->permitted_uri_chars;
		}
	}

	public function _set_permitted_uri_chars($value)
	{
		$this->_permitted_uri_chars = $value;
	}

}