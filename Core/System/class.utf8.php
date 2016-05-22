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
 * Utf8 Class
 *
 * Provides support for UTF-8 environments
 *
 * @author      EllisLab Dev Team
 * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright   Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 */
class Utf8 {

    /**
     * Class constructor
     *
     * Determines if UTF-8 support is to be enabled.
     *
     * @return  void
     */
    public function __construct()
    {
        $charset = strtoupper(Config::get('main')->charset);
        ini_set('default_charset', $charset);

        /*
         * Configure mbstring and/or iconv if they are enabled
         * and set MB_ENABLED and ICONV_ENABLED constants, so
         * that we don't repeatedly do extension_loaded() or
         * function_exists() calls.
         */
        if (extension_loaded('mbstring'))
        {
            define('MB_ENABLED', TRUE);
            // mbstring.internal_encoding is deprecated starting with PHP 5.6
            // and it's usage triggers E_DEPRECATED messages.
            if (! Core::isPHP('5.6'))
            {
                @ini_set('mbstring.internal_encoding', $charset);                
            }
            else
            {
                mb_internal_encoding($charset);            
            }
            // This is required for mb_convert_encoding() to strip invalid characters.
            // That's utilized by CI_Utf8, but it's also done for consistency with iconv.
            mb_substitute_character('none');
        }
        else
        {
            define('MB_ENABLED', FALSE);
        }

        // There's an ICONV_IMPL constant, but the PHP manual says that using
        // iconv's predefined constants is "strongly discouraged".
        if (extension_loaded('iconv'))
        {
            define('ICONV_ENABLED', TRUE);
            // iconv.internal_encoding is deprecated starting with PHP 5.6
            // and it's usage triggers E_DEPRECATED messages.
            if ( ! Core::isPHP(5.6) )
            {
                @ini_set('iconv.internal_encoding', $charset);
            }
            else
            {
                ini_set('default_encoding', $charset);
            }
        }
        else
        {
            define('ICONV_ENABLED', FALSE);
        }

        if (Core::isPHP('5.6'))
        {
            ini_set('php.internal_encoding', $charset);
        }

        if (
            defined('PREG_BAD_UTF8_ERROR')              // PCRE must support UTF-8
            && (ICONV_ENABLED === TRUE OR MB_ENABLED === TRUE)  // iconv or mbstring must be installed
            && strtoupper($charset) === 'UTF-8'   // Application charset must be UTF-8
            )
        {
            define('UTF8_ENABLED', TRUE);
            Logger::log('UTF-8 Support Enabled');
        }
        else
        {
            define('UTF8_ENABLED', FALSE);
            Logger::log('UTF-8 Support Disabled');
        }
    }

    // --------------------------------------------------------------------

    /**
     * Clean UTF-8 strings
     *
     * Ensures strings contain only valid UTF-8 characters.
     *
     * @param   string  $str    String to clean
     * @return  string
     */
    public static function clean_string($str)
    {
        if (self::is_ascii($str) === FALSE)
        {
            if (MB_ENABLED)
            {
                $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            }
            elseif (ICONV_ENABLED)
            {
                $str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
            }
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Remove ASCII control characters
     *
     * Removes all ASCII control characters except horizontal tabs,
     * line feeds, and carriage returns, as all others can cause
     * problems in XML.
     *
     * @param   string  $str    String to clean
     * @return  string
     */
    public static function safe_ascii_for_xml($str)
    {
        Helpers::load('common');
        return remove_invisible_characters($str, FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Convert to UTF-8
     *
     * Attempts to convert a string to UTF-8.
     *
     * @param   string  $str        Input string
     * @param   string  $encoding   Input encoding
     * @return  string  $str encoded in UTF-8 or FALSE on failure
     */
    public static function convert_to_utf8($str, $encoding)
    {
        if (MB_ENABLED)
        {
            return mb_convert_encoding($str, 'UTF-8', $encoding);
        }
        elseif (ICONV_ENABLED)
        {
            return @iconv($encoding, 'UTF-8', $str);
        }

        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Is ASCII?
     *
     * Tests if a string is standard 7-bit ASCII or not.
     *
     * @param   string  $str    String to check
     * @return  bool
     */
    public static function is_ascii($str)
    {
        return (preg_match('/[^\x00-\x7F]/S', $str) === 0);
    }

    /**
     * Remove Invisible Characters
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param   string
     * @param   bool
     * @return  string
     */
    public static function remove_invisible_characters($str, $url_encoded = TRUE)
    {
        $non_displayables = array();

        // every control character except newline (dec 10),
        // carriage return (dec 13) and horizontal tab (dec 09)
        if ($url_encoded)
        {
            $non_displayables[] = '/%0[0-8bcef]/';  // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/';   // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';   // 00-08, 11, 12, 14-31, 127

        do
        {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        }
        while ($count);

        return $str;
    }

}
