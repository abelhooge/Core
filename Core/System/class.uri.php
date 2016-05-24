<?php
/**
 * FuzeWorks.
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
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://fuzeworks.techfuze.net
 * @since Version 0.0.1
 *
 * @version Version 0.0.1
 */

namespace FuzeWorks;

/**
 * URI Class
 *
 * Parses URIs and determines routing
 *
 * @author		EllisLab Dev Team
 * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright   Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 */
class URI {

	/**
	 * List of cached URI segments
	 *
	 * @var	array
	 */
	public static $keyval = array();

	/**
	 * Current URI string
	 *
	 * @var	string
	 */
	public static $uri_string = '';

	/**
	 * List of URI segments
	 *
	 * Starts at 1 instead of 0.
	 *
	 * @var	array
	 */
	public static $segments = array();

	/**
	 * List of routed URI segments
	 *
	 * Starts at 1 instead of 0.
	 *
	 * @var	array
	 */
	public static $rsegments = array();

	/**
	 * Permitted URI chars
	 *
	 * PCRE character group allowed in URI segments
	 *
	 * @var	string
	 */
	protected static $_permitted_uri_chars;

	/**
	 * The configuration of this class
	 *
	 * @var ConfigORM
	 */
	private static $config;

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		self::$config = Config::get('routing');

		// If query strings are enabled, we don't need to parse any segments.
		// However, they don't make sense under CLI.
		if ((PHP_SAPI === 'cli' OR defined('STDIN')) OR self::$config->enable_query_strings !== TRUE)
		{
			self::$_permitted_uri_chars = self::$config->permitted_uri_chars;

			// If it's a CLI request, ignore the configuration
			if ( (PHP_SAPI === 'cli' OR defined('STDIN')) )
			{
				$uri = self::_parse_argv();
			}
			else
			{
				$protocol = self::$config->uri_protocol;
				empty($protocol) && $protocol = 'REQUEST_URI';

				switch ($protocol)
				{
					case 'AUTO': // For BC purposes only
					case 'REQUEST_URI':
						$uri = self::_parse_request_uri();
						break;
					case 'QUERY_STRING':
						$uri = self::_parse_query_string();
						break;
					case 'PATH_INFO':
					default:
						$uri = isset($_SERVER[$protocol])
							? $_SERVER[$protocol]
							: self::_parse_request_uri();
						break;
				}
			}

			self::_set_uri_string($uri);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set URI String
	 *
	 * @param 	string	$str
	 * @return	void
	 */
	protected static function _set_uri_string($str)
	{
		// Filter out control characters and trim slashes
		self::$uri_string = trim(Utf8::remove_invisible_characters($str, FALSE), '/');

		if (self::$uri_string !== '')
		{
			// Remove the URL suffix, if present
			if (($suffix = (string) self::$config->url_suffix) !== '')
			{
				$slen = strlen($suffix);

				if (substr(self::$uri_string, -$slen) === $suffix)
				{
					self::$uri_string = substr(self::$uri_string, 0, -$slen);
				}
			}

			self::$segments[0] = NULL;
			// Populate the segments array
			foreach (explode('/', trim(self::$uri_string, '/')) as $val)
			{
				$val = trim($val);
				// Filter segments for security
				self::filter_uri($val);

				if ($val !== '')
				{
					self::$segments[] = $val;
				}
			}

			unset(self::$segments[0]);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Parse REQUEST_URI
	 *
	 * Will parse REQUEST_URI and automatically detect the URI from it,
	 * while fixing the query string if necessary.
	 *
	 * @return	string
	 */
	protected static function _parse_request_uri()
	{
		if ( ! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']))
		{
			return '';
		}

		// parse_url() returns false if no host is present, but the path or query string
		// contains a colon followed by a number
		$uri = parse_url('http://dummy'.$_SERVER['REQUEST_URI']);
		$query = isset($uri['query']) ? $uri['query'] : '';
		$uri = isset($uri['path']) ? $uri['path'] : '';

		if (isset($_SERVER['SCRIPT_NAME'][0]))
		{
			if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
			{
				$uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
			}
			elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
			{
				$uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
			}
		}

		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0)
		{
			$query = explode('?', $query, 2);
			$uri = $query[0];
			$_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
		}
		else
		{
			$_SERVER['QUERY_STRING'] = $query;
		}

		parse_str($_SERVER['QUERY_STRING'], $_GET);

		if ($uri === '/' OR $uri === '')
		{
			return '/';
		}



		// Do some final cleaning of the URI and return it
		return self::_remove_relative_directory($uri);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse QUERY_STRING
	 *
	 * Will parse QUERY_STRING and automatically detect the URI from it.
	 *
	 * @return	string
	 */
	protected static function _parse_query_string()
	{
		$uri = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');

		if (trim($uri, '/') === '')
		{
			return '';
		}
		elseif (strncmp($uri, '/', 1) === 0)
		{
			$uri = explode('?', $uri, 2);
			$_SERVER['QUERY_STRING'] = isset($uri[1]) ? $uri[1] : '';
			$uri = $uri[0];
		}

		parse_str($_SERVER['QUERY_STRING'], $_GET);

		return self::_remove_relative_directory($uri);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse CLI arguments
	 *
	 * Take each command line argument and assume it is a URI segment.
	 *
	 * @return	string
	 */
	protected static function _parse_argv()
	{
		$args = array_slice($_SERVER['argv'], 1);
		return $args ? implode('/', $args) : '';
	}

	// --------------------------------------------------------------------

	/**
	 * Remove relative directory (../) and multi slashes (///)
	 *
	 * Do some final cleaning of the URI and return it, currently only used in self::_parse_request_uri()
	 *
	 * @param	string	$uri
	 * @return	string
	 */
	protected static function _remove_relative_directory($uri)
	{
		$uris = array();
		$tok = strtok($uri, '/');
		while ($tok !== FALSE)
		{
			if (( ! empty($tok) OR $tok === '0') && $tok !== '..')
			{
				$uris[] = $tok;
			}
			$tok = strtok('/');
		}

		return implode('/', $uris);
	}

	// --------------------------------------------------------------------

	/**
	 * Filter URI
	 *
	 * Filters segments for malicious characters.
	 *
	 * @param	string	$str
	 * @return	void
	 */
	public static function filter_uri(&$str)
	{
		if ( ! empty($str) && ! empty(self::$_permitted_uri_chars) && ! preg_match('/^['.self::$_permitted_uri_chars.']+$/i'.(UTF8_ENABLED ? 'u' : ''), $str))
		{
			throw new UriException('The URI you submitted has disallowed characters.', 1);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch URI Segment
	 *
	 * @see		CI_URI::$segments
	 * @param	int		$n		Index
	 * @param	mixed		$no_result	What to return if the segment index is not found
	 * @return	mixed
	 */
	public static function segment($n, $no_result = NULL)
	{
		return isset(self::$segments[$n]) ? self::$segments[$n] : $no_result;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch URI "routed" Segment
	 *
	 * Returns the re-routed URI segment (assuming routing rules are used)
	 * based on the index provided. If there is no routing, will return
	 * the same result as CI_URI::segment().
	 *
	 * @see		CI_URI::$rsegments
	 * @see		CI_URI::segment()
	 * @param	int		$n		Index
	 * @param	mixed		$no_result	What to return if the segment index is not found
	 * @return	mixed
	 */
	public static function rsegment($n, $no_result = NULL)
	{
		return isset(self::$rsegments[$n]) ? self::$rsegments[$n] : $no_result;
	}

	// --------------------------------------------------------------------

	/**
	 * URI to assoc
	 *
	 * Generates an associative array of URI data starting at the supplied
	 * segment index. For example, if this is your URI:
	 *
	 *	example.com/user/search/name/joe/location/UK/gender/male
	 *
	 * You can use this method to generate an array with this prototype:
	 *
	 *	array (
	 *		name => joe
	 *		location => UK
	 *		gender => male
	 *	 )
	 *
	 * @param	int	$n		Index (default: 3)
	 * @param	array	$default	Default values
	 * @return	array
	 */
	public static function uri_to_assoc($n = 3, $default = array())
	{
		return self::_uri_to_assoc($n, $default, 'segment');
	}

	// --------------------------------------------------------------------

	/**
	 * Routed URI to assoc
	 *
	 * Identical to CI_URI::uri_to_assoc(), only it uses the re-routed
	 * segment array.
	 *
	 * @see		CI_URI::uri_to_assoc()
	 * @param 	int	$n		Index (default: 3)
	 * @param 	array	$default	Default values
	 * @return 	array
	 */
	public static function ruri_to_assoc($n = 3, $default = array())
	{
		return self::_uri_to_assoc($n, $default, 'rsegment');
	}

	// --------------------------------------------------------------------

	/**
	 * Internal URI-to-assoc
	 *
	 * Generates a key/value pair from the URI string or re-routed URI string.
	 *
	 * @used-by	CI_URI::uri_to_assoc()
	 * @used-by	CI_URI::ruri_to_assoc()
	 * @param	int	$n		Index (default: 3)
	 * @param	array	$default	Default values
	 * @param	string	$which		Array name ('segment' or 'rsegment')
	 * @return	array
	 */
	protected static function _uri_to_assoc($n = 3, $default = array(), $which = 'segment')
	{
		if ( ! is_numeric($n))
		{
			return $default;
		}

		if (isset(self::$keyval[$which], self::$keyval[$which][$n]))
		{
			return self::$keyval[$which][$n];
		}

		$total_segments = "total_{$which}s";
		$segment_array = "{$which}_array";

		if (self::$total_segments() < $n)
		{
			return (count($default) === 0)
				? array()
				: array_fill_keys($default, NULL);
		}

		$segments = array_slice(self::$segment_array(), ($n - 1));
		$i = 0;
		$lastval = '';
		$retval = array();
		foreach ($segments as $seg)
		{
			if ($i % 2)
			{
				$retval[$lastval] = $seg;
			}
			else
			{
				$retval[$seg] = NULL;
				$lastval = $seg;
			}

			$i++;
		}

		if (count($default) > 0)
		{
			foreach ($default as $val)
			{
				if ( ! array_key_exists($val, $retval))
				{
					$retval[$val] = NULL;
				}
			}
		}

		// Cache the array for reuse
		isset(self::$keyval[$which]) OR self::$keyval[$which] = array();
		self::$keyval[$which][$n] = $retval;
		return $retval;
	}

	// --------------------------------------------------------------------

	/**
	 * Assoc to URI
	 *
	 * Generates a URI string from an associative array.
	 *
	 * @param	array	$array	Input array of key/value pairs
	 * @return	string	URI string
	 */
	public static function assoc_to_uri($array)
	{
		$temp = array();
		foreach ((array) $array as $key => $val)
		{
			$temp[] = $key;
			$temp[] = $val;
		}

		return implode('/', $temp);
	}

	// --------------------------------------------------------------------

	/**
	 * Slash segment
	 *
	 * Fetches an URI segment with a slash.
	 *
	 * @param	int	$n	Index
	 * @param	string	$where	Where to add the slash ('trailing' or 'leading')
	 * @return	string
	 */
	public static function slash_segment($n, $where = 'trailing')
	{
		return self::_slash_segment($n, $where, 'segment');
	}

	// --------------------------------------------------------------------

	/**
	 * Slash routed segment
	 *
	 * Fetches an URI routed segment with a slash.
	 *
	 * @param	int	$n	Index
	 * @param	string	$where	Where to add the slash ('trailing' or 'leading')
	 * @return	string
	 */
	public static function slash_rsegment($n, $where = 'trailing')
	{
		return self::_slash_segment($n, $where, 'rsegment');
	}

	// --------------------------------------------------------------------

	/**
	 * Internal Slash segment
	 *
	 * Fetches an URI Segment and adds a slash to it.
	 *
	 * @used-by	CI_URI::slash_segment()
	 * @used-by	CI_URI::slash_rsegment()
	 *
	 * @param	int	$n	Index
	 * @param	string	$where	Where to add the slash ('trailing' or 'leading')
	 * @param	string	$which	Array name ('segment' or 'rsegment')
	 * @return	string
	 */
	protected static function _slash_segment($n, $where = 'trailing', $which = 'segment')
	{
		$leading = $trailing = '/';

		if ($where === 'trailing')
		{
			$leading	= '';
		}
		elseif ($where === 'leading')
		{
			$trailing	= '';
		}

		return $leading.self::$which($n).$trailing;
	}

	// --------------------------------------------------------------------

	/**
	 * Segment Array
	 *
	 * @return	array	CI_URI::$segments
	 */
	public static function segment_array()
	{
		return self::$segments;
	}

	// --------------------------------------------------------------------

	/**
	 * Routed Segment Array
	 *
	 * @return	array	CI_URI::$rsegments
	 */
	public static function rsegment_array()
	{
		return self::$rsegments;
	}

	// --------------------------------------------------------------------

	/**
	 * Total number of segments
	 *
	 * @return	int
	 */
	public static function total_segments()
	{
		return count(self::$segments);
	}

	// --------------------------------------------------------------------

	/**
	 * Total number of routed segments
	 *
	 * @return	int
	 */
	public static function total_rsegments()
	{
		return count(self::$rsegments);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch URI string
	 *
	 * @return	string	CI_URI::$uri_string
	 */
	public static function uri_string()
	{
		return self::$uri_string;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Re-routed URI string
	 *
	 * @return	string
	 * @todo Implement router
	 */
	public static function ruri_string()
	{
		return ltrim(load_class('Router', 'core')->directory, '/').implode('/', self::$rsegments);
	}

}
