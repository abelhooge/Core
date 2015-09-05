<?php

namespace Controller;
use \FuzeWorks\Controller;
use \FuzeWorks\Layout;

class Standard extends Controller {

	public function index($path = null) {
		Layout::view('home');
	}
}


?>