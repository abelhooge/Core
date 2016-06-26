<?php

return array(
  'base_url' => '',
  'index_page' => 'index.php',
  'server_name' => '',

  'administrator_mail' => '',

  'default_controller' => 'standard',
  'default_function' => 'index',
  'application_prefix' => 'MY_',

  'charset' => 'UTF-8',
  'language' => 'english',

  /*
  |--------------------------------------------------------------------------
  | Cookie Related Variables
  |--------------------------------------------------------------------------
  |
  | 'cookie_prefix'   = Set a cookie name prefix if you need to avoid collisions
  | 'cookie_domain'   = Set to .your-domain.com for site-wide cookies
  | 'cookie_path'     = Typically will be a forward slash
  | 'cookie_secure'   = Cookie will only be set if a secure HTTPS connection exists.
  | 'cookie_httponly' = Cookie will only be accessible via HTTP(S) (no javascript)
  |
  | Note: These settings (with the exception of 'cookie_prefix' and
  |       'cookie_httponly') will also affect sessions.
  |
  */
  'cookie_prefix' => '',
  'cookie_domain' => '',
  'cookie_path' => '/',
  'cookie_secure' => FALSE,
  'cookie_httponly' => FALSE,

  /*
  |--------------------------------------------------------------------------
  | Output Compression
  |--------------------------------------------------------------------------
  |
  | Enables Gzip output compression for faster page loads.  When enabled,
  | the output class will test whether your server supports Gzip.
  | Even if it does, however, not all browsers support compression
  | so enable only if you are reasonably sure your visitors can handle it.
  |
  | Only used if zlib.output_compression is turned off in your php.ini.
  | Please do not use it together with httpd-level output compression.
  |
  | VERY IMPORTANT:  If you are getting a blank page when compression is enabled it
  | means you are prematurely outputting something to your browser. It could
  | even be a line of whitespace at the end of one of your scripts.  For
  | compression to work, nothing can be sent before the output buffer is called
  | by the output class.  Do not 'echo' any values with compression enabled.
  |
  */
  'compress_output' => FALSE,
);
