<?php
/**
 * FuzeWorks
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
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 * @version     Version 0.0.1
 */

namespace Module\Users;

/**
 * Cookie object class.
 *
 * When a cookie is set in the users class, this object gets returned which can apply the cookie
 * @package  net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Cookie {

    /**
     * The name of the cookie. Usually the value in the Users config file
     * @var String
     */
	public $cookie_name;

    /**
     * The value of the cookie. This will most likely be a sessionkey
     * @var Mixed
     */
	public $cookie_value = '';

    /**
     * Cookie time to live. Time until the cookie runs out
     * @var integer
     */
	public $cookie_ttl = 0;

    /**
     * The path where this cookie is active. Usually site-wide, but not always
     * @var string
     */
	public $cookie_path = '';

    /**
     * The domain where this cookie is active. Usually the domain of the website define in config->main
     * @var string
     */
	public $cookie_domain = '';

    /**
     * Whether the cookie is only active on HTTPS
     * @var boolean
     */
	public $cookie_secure = false;

    /**
     * Whether the cookie is only active on HTTP
     * @var boolean
     */
	public $cookie_http_only = false;

    /**
     * Create the cookie class and all its variables
     * @param String  $name     The name of the cookie. Usually the value in the Users config file
     * @param string  $value    The value of the cookie. This will most likely be a sessionkey
     * @param integer $expire   Cookie time to live. Time until the cookie runs out
     * @param string  $path     The path where this cookie is active. Usually site-wide, but not always
     * @param string  $domain   The domain where this cookie is active. Usually the domain of the website define in config->main
     * @param boolean $secure   Whether the cookie is only active on HTTPS
     * @param boolean $httponly Whether the cookie is only active on HTTP
     */
    public function __construct($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false) {
        $this->cookie_name = $name;
        $this->cookie_value = $value;
        $this->cookie_ttl = $expire;
        $this->cookie_path = $path;
        $this->cookie_domain = $domain;
        $this->cookie_secure = $secure;
        $this->cookie_http_only = $httponly;
    }

    /**
     * Gets the The name of the cookie. Usually the value in the Users config file.
     *
     * @return String
     */
    public function getCookieName()
    {
        return $this->cookie_name;
    }

    /**
     * Sets the The name of the cookie. Usually the value in the Users config file.
     *
     * @param String $cookie_name the cookie name
     *
     * @return self
     */
    public function setCookieName(String $cookie_name)
    {
        $this->cookie_name = $cookie_name;

        return $this;
    }

    /**
     * Gets the The value of the cookie. This will most likely be a sessionkey.
     *
     * @return Mixed
     */
    public function getCookieValue()
    {
        return $this->cookie_value;
    }

    /**
     * Sets the The value of the cookie. This will most likely be a sessionkey.
     *
     * @param Mixed $cookie_value the cookie value
     *
     * @return self
     */
    public function setCookieValue(Mixed $cookie_value)
    {
        $this->cookie_value = $cookie_value;

        return $this;
    }

    /**
     * Gets the Cookie time to live. Time until the cookie runs out.
     *
     * @return integer
     */
    public function getCookieTtl()
    {
        return $this->cookie_ttl;
    }

    /**
     * Sets the Cookie time to live. Time until the cookie runs out.
     *
     * @param integer $cookie_ttl the cookie ttl
     *
     * @return self
     */
    public function setCookieTtl($cookie_ttl)
    {
        $this->cookie_ttl = $cookie_ttl;

        return $this;
    }

    /**
     * Gets the The path where this cookie is active. Usually site-wide, but not always.
     *
     * @return string
     */
    public function getCookiePath()
    {
        return $this->cookie_path;
    }

    /**
     * Sets the The path where this cookie is active. Usually site-wide, but not always.
     *
     * @param string $cookie_path the cookie path
     *
     * @return self
     */
    public function setCookiePath($cookie_path)
    {
        $this->cookie_path = $cookie_path;

        return $this;
    }

    /**
     * Gets the The domain where this cookie is active. Usually the domain of the website define in config->main.
     *
     * @return string
     */
    public function getCookieDomain()
    {
        return $this->cookie_domain;
    }

    /**
     * Sets the The domain where this cookie is active. Usually the domain of the website define in config->main.
     *
     * @param string $cookie_domain the cookie domain
     *
     * @return self
     */
    public function setCookieDomain($cookie_domain)
    {
        $this->cookie_domain = $cookie_domain;

        return $this;
    }

    /**
     * Gets the Whether the cookie is only active on HTTPS.
     *
     * @return boolean
     */
    public function getCookieSecure()
    {
        return $this->cookie_secure;
    }

    /**
     * Sets the Whether the cookie is only active on HTTPS.
     *
     * @param boolean $cookie_secure the cookie secure
     *
     * @return self
     */
    public function setCookieSecure($cookie_secure)
    {
        $this->cookie_secure = $cookie_secure;

        return $this;
    }

    /**
     * Gets the Whether the cookie is only active on HTTP.
     *
     * @return boolean
     */
    public function getCookieHttpOnly()
    {
        return $this->cookie_http_only;
    }

    /**
     * Sets the Whether the cookie is only active on HTTP.
     *
     * @param boolean $cookie_http_only the cookie http only
     *
     * @return self
     */
    public function setCookieHttpOnly($cookie_http_only)
    {
        $this->cookie_http_only = $cookie_http_only;

        return $this;
    }

    /**
     * Send the cookie to the user
     * @return boolean Whether the cookie has been successfully placed
     */
    public function place() {
        return setcookie($this->cookie_name,
            $this->cookie_value,
            $this->cookie_ttl,
            $this->cookie_path,
            $this->cookie_domain,
            $this->cookie_secure,
            $this->cookie_http_only
        );
    }
}
?>