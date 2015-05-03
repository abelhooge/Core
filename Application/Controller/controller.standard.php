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
}


?>