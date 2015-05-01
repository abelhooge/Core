<?php

namespace Controller;
use \FuzeWorks\Controller;

class Standard extends Controller {
	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function index($path = null) {
		$this->layout->view('maintenance');
	}

	public function not_found($path = null) {
		$this->logger->http_error(404);
		$this->layout->assign('page', $_SERVER['REQUEST_URI']);
		$this->layout->assign('mail', $this->config->main->administrator_mail);
		$this->layout->view('404');
	}
}


?>