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
 * User Data Table
 *
 * Contains all the information about a user available in the database.
 *
 * This includes permissions, key-data storage, emails and general information.
 *
 * This includes session information of the current session if applicable. This does not contain a history of sessions.
 *
 * This does also NOT include password and verification code because of security.
 *
 * This class can read the permissions of the user, this class can however NOT change the permissions.
 *
 * For changing permissions, invoke removePermission of addPermission on the \Module\Users\Users class.
 *
 * @package  net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Udt {

    /**
     * The user ID of the user
     * @var int UserID
     */
    public $user_id;

    /**
     * The username of the user
     * @var String username
     */
    public $username;

    /**
     * The primary email address of the user
     * @var String email adress
     */
    public $primaryEmail;

    /**
     * All the emails of this user
     * @var array of emails
     */
    public $emails = array();

    /**
     * All the permissions that this user has
     * @var array of permissions
     */
    public $permissions = array();

    /**
     * Basic key-value data storage of this user
     * @var array key-value
     */
    public $data = array();

    /**
     * Information about the current session, if applicable
     * @var null|array Null if not active, array if about the current session
     */
    public $session = null;

    /**
     * The sessionKey of the current session
     * @var null|string Null if not active, string if about the current session
     */
    public $sessionKey = null;

    /**
     * Create the UDT object
     * @param int         $id           The user ID of the user
     * @param String      $username     The username of the user
     * @param String      $primaryEmail The primary email address of the user
     * @param Array       $emails       All the emails of this user
     * @param Array       $permissions  All the permissions that this user has
     * @param array       $data         Basic key-value data storage of this user
     * @param null|string $session      Information about the current session, if applicable
     * @param null|array  $sessionKey   The sessionKey of the current session, if applicable
     */
    public function __construct($id, $username, $primaryEmail, $emails, $permissions, $data = array(), $session = null, $sessionKey = null) {
        $this->user_id = $id;
        $this->username = $username;
        $this->primaryEmail = $primaryEmail;
        $this->emails = $emails;
        $this->permissions = $permissions;
        $this->data = $data;
        $this->session = $session;
        $this->sessionKey = $sessionKey;
    }

    /**
     * Whether this user has access to a certain permissiontag
     * @param  String  $permissionTag The tag to look for
     * @return boolean                true if permission is present
     */
    public function hasPermission($permissionTag) {
        return (isset($this->permissions[$permissionTag]) || isset($this->permissions['ADMIN']));
    }

    /**
     * Return the UDT as an array
     * @return Array of UDT
     */
    public function toArray() {
        return array(
                'user_id' => $this->user_id,
                'username' => $this->username,
                'email' => $this->primaryEmail,
                'emails' => $this->emails,
                'permissions' => $this->permissions,
                'data' => $this->data,
                'session' => (is_null($this->session) ? false : $this->session),
                'sessionKey' => (is_null($this->sessionKey) ? false : $this->sessionKey)
            );
    }

}
?>