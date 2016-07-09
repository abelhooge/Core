<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Cross Site Request Forgery
	|--------------------------------------------------------------------------
	| Enables a CSRF cookie token to be set. When set to TRUE, token will be
	| checked on a submitted form. If you are accepting user data, it is strongly
	| recommended CSRF protection be enabled.
	|
	| 'csrf_token_name' = The token name
	| 'csrf_cookie_name' = The cookie name
	| 'csrf_expire' = The number in seconds the token should expire.
	| 'csrf_regenerate' = Regenerate token on every submission
	| 'csrf_exclude_uris' = Array of URIs which ignore CSRF checks
	*/
	'csrf_protection' => true,
	'csrf_token_name' => 'csrf_test_name',
	'csrf_cookie_name' => 'csrf_cookie_name',
	'csrf_expire' => 7200,
	'csrf_regenerate' => TRUE,
	'csrf_exclude_uris' => array(),

	/*
	|--------------------------------------------------------------------------
	| Standardize newlines
	|--------------------------------------------------------------------------
	|
	| Determines whether to standardize newline characters in input data,
	| meaning to replace \r\n, \r, \n occurrences with the PHP_EOL value.
	|
	| This is particularly useful for portability between UNIX-based OSes,
	| (usually \n) and Windows (\r\n).
	|
	*/
	'standardize_newlines' => FALSE,

	/*
	|--------------------------------------------------------------------------
	| Global XSS Filtering
	|--------------------------------------------------------------------------
	|
	| Determines whether the XSS filter is always active when GET, POST or
	| COOKIE data is encountered
	|
	| WARNING: This feature is DEPRECATED and currently available only
	|          for backwards compatibility purposes!
	|
	*/
	'global_xss_filtering' => FALSE,

	/*
	|--------------------------------------------------------------------------
	| Reverse Proxy IPs
	|--------------------------------------------------------------------------
	|
	| If your server is behind a reverse proxy, you must whitelist the proxy
	| IP addresses from which CodeIgniter should trust headers such as
	| HTTP_X_FORWARDED_FOR and HTTP_CLIENT_IP in order to properly identify
	| the visitor's IP address.
	|
	| You can use both an array or a comma-separated list of proxy addresses,
	| as well as specifying whole subnets. Here are a few examples:
	|
	| Comma-separated:	'10.0.1.200,192.168.5.0/24'
	| Array:		array('10.0.1.200', '192.168.5.0/24')
	*/
	'proxy_ips' => ''

);
