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
use \FuzeWorks\Event;

/**
 * An Event that gets fired when an email is about to be sent to the user upon registration
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionRegisterMailEvent extends Event {

	/**
	 * User Data Table of the user to send the mail to
	 * @var Array UDT
	 */
	public $udt;

	/**
	 * HTML of the final mail
	 * @var HTML
	 */
	public $html;

	/**
	 * Identifier for the verification of a user
	 * @var String Code
	 */
	public $verifyCode;

	/**
	 * URL to send the verification to
	 * @var String URL
	 */
	public $verifyURL;

	/**
	 * Wether to use custom HTML or not
	 * @var Boolean true of using custom HTML
	 */
	public $customHtml = false;

	/**
	 * Wether to use custom HTML or not. Can be set here
	 * @access public
	 * @param True if using custom HTML or false if not
	 * @return true on success
	 * @throws ServiceException on Fatal error
	 */
	public function useCustomHtml($bool) {
		if (is_bool($bool)) {
			$this->customHtml = $bool;
			return true;
		}
		throw new ServiceException("Could not set customHtml. Input is not a Boolean", 1);
	}

	/**
	 * Intializes the event and it's variables
	 * @access public
	 * @param Array UDT of user to send mail to
	 * @param String VerificationCode
	 * @param String VerificationURL
	 */
	public function init($udt, $verifyCode, $verifyURL) {
		$this->udt = $udt;
		$this->verifyCode = $verifyCode;
		$this->verifyURL = $verifyURL;
	}
}

/**
 * Event that is fired when a user tries to log in.
 * Event should be cancelled if you want to deny a user to log in.
 * If you want to use your own authenticator service, please provide a User Data Table so the authenticator can be successfully identified.
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionLoginEvent extends Event {

	/**
	 * The username of the user.
	 * Available if the user has been successfully verified by the sessions module
	 * @var String Username
	 */
	public $username;

	/**
	 * Identifier used when logging in. Usefull for if another module wants to log users in
	 * @var String Identifier
	 */
	public $identifier;

	/**
	 * The password used to identify a user
	 * @var String password
	 */
	public $password;

	/**
	 * The email of the user.
	 * Available if the user has been successfully verified by the sessions module
	 * @var String Email
	 */
	public $email;

	/**
	 * User id of the user
	 * @var Int User Id
	 */
	public $user_id;

	/**
	 * Wether the user has been successfully verified.
	 * Set to true (AND PROVIDE A UDT) to log a custom user in using another mechanism.
	 * @var boolean true if verified.
	 */
	public $verified = false;

	/**
	 * Wether the user should be logged in for a very long time.
	 * @var boolean true if remember for long time.
	 */
	public $remember_me = false;

	/**
	 * User Data Table for when the user is verified by the sessions module.
	 * @var Array User Data Table
	 */
	public $udt;

	/**
	 * Initializes the event.
	 * @param  String $identifier  Can be an email or username
	 * @param  String $password    Password of the user
	 * @param  boolean $remember_me Wether the user should be logged in for a long time
	 * @param  String $username  Username of a user (optional)
	 * @param  String $email  Email of a user (optional)
	 * @param  Array $udt  User Data Table of a user (optional)
	 */
	public function init($identifier, $password, $remember_me, $user_id, $username = null, $email = null, $udt = null) {
		$this->identifier = $identifier;
		$this->password = $password;
		$this->remember_me = $remember_me;
		$this->user_id = $user_id;
		$this->username = $username;
		$this->email = $email;
		$this->udt = $udt;
	}

	/**
	 * Set the verification of this user
	 * @param boolean $verified True if verified.
	 */
	public function setVerified($verified = false) {
		$this->verified = $verified;
	}

	/**
	 * Sets the UserId of a user
	 * @param Int $user_id Id of the user
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}

	/**
	 * Sets the username when a user is successfully verified
	 * @param String $username Username of the user
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * Sets the email when a user is successfully verified
	 * @param String $email Email of the user
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * Sets the User Data Table when a user is successfully verified
	 * @param Array $udt User Data Table
	 */
	public function setUdt($udt) {
		$this->udt = $udt;
	}
}

/**
 * Event gets fired when a user tries to log out
 * Cancel the event to deny a logout.
 * The Event only provides information. Changing variables will not log someone else out.
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionLogoutEvent extends Event {

	/**
	 * User ID of a user
	 * @var Int User ID
	 */
	public $user_id;

	/**
	 * Username of a user
	 * @var String username
	 */
	public $username;

	/**
	 * Email of a user
	 * @var String Email
	 */
	public $email;

	/**
	 * Initializes the event
	 * @param  String $user_id  User ID of a user
	 * @param  String $username Username of a user
	 * @param  String $email    Email of a user
	 */
	public function init($user_id, $username, $email) {
		$this->user_id = $user_id;
		$this->username = $username;
		$this->email = $email;
	}
}

/**
 * Event that gets fired when a new user get's created.
 * Cancel the event to deny user creation.
 * The Event only provides information. Changing variables will not register with new data.
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionUserCreateEvent extends Event {

	/**
	 * Username of a user
	 * @var String Username
	 */
	public $username;

	/**
	 * Email of a user
	 * @var String Email
	 */
	public $email;

	/**
	 * User ID of a user
	 * @var Int UserId
	 */
	public $user_id;

	/**
	 * Initializes the event
	 * @param  Int    $user_id  User ID of a user
	 * @param  String $username Username of a user
	 * @param  String $email    Email of a user
	 */
	public function init($user_id, $username, $email) {
		$this->user_id = $user_id;
		$this->username = $username;
		$this->email = $email;
	}
}

/**
 * Event that gets fired when a user is modified.
 * Cancel the event to deny the change.
 * Change variables to change what gets changed.
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionUserModifyEvent extends Event {

	/**
	 * User ID of a user
	 * @var Int User ID
	 */
	public $user_id;

	/**
	 * The key that get's changed
	 * @var String name of key
	 */
	public $key;

	/**
	 * The original value that is getting changed.
	 * @var Mixed Value
	 */
	public $from;

	/**
	 * The value it is getting changed into
	 * @var Mixed value
	 */
	public $value;

	/**
	 * Initializes the Event
	 * @param  Int    $user_id User ID of a user
	 * @param  String $key     The key that get's changed
	 * @param  Mixed  $value   The original value that is getting changed.
	 * @param  Mixed  $from    The value it is getting changed into
	 */
	public function init($user_id, $key, $value, $from) {
		$this->user_id = $user_id;
		$this->key = $key;
		$this->value = $value;
		$this->from = $from;
	}
}

/**
 * Event that gets fired when a user is getting removed.
 * Cancel the event to deny the user removal.
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionUserRemoveEvent extends Event {

	/**
	 * User ID of the user
	 * @var Int User ID
	 */
	public $user_id;

	/**
	 * Username of the user
	 * @var String username
	 */
	public $username;

	/**
	 * Email of the user
	 * @var String email
	 */
	public $email;

	/**
	 * Initializes the event
	 * @param  Int    $user_id  User ID of the user
	 * @param  String $username Username of the user
	 * @param  String $email    Email of the user
	 */
	public function init($user_id, $username, $email) {
		$this->user_id = $user_id;
		$this->username = $username;
		$this->email = $email;
	}
}

/**
 * Event that gets fired when a password is changed.
 * Cancel the event to deny password change
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionChangePasswordEvent extends Event {

	/**
	 * User ID of the user
	 * @var Int UserId
	 */
	public $user_id;

	/**
	 * Username of the user
	 * @var String Username
	 */
	public $username;

	/**
	 * Old Password of the user. (the one that it is being changed from).
	 * It will be null when the oldPassword is not required
	 * @var String|null Password
	 */
	public $oldPassword;

	/**
	 * New Password of the user. (the one that it is being changed to)
	 * @var String password
	 */
	public $newPassword;

	/**
	 * Initializes the Event
	 * @param  Int         $user_id     User ID of the user
	 * @param  String      $username    Username of the user
	 * @param  String|null $oldPassword Old Password of the user. (the one that it is being changed from)
	 * @param  String      $newPassword New Password of the user. (the one that it is being changed to)
	 */
	public function init($user_id, $username, $oldPassword = null, $newPassword) {
		$this->user_id = $user_id;
		$this->username = $username;
		$this->oldPassword = $oldPassword;
		$this->newPassword = $newPassword;
	}
}

/**
 * Event that gets fired when a user is suspended
 * Cancel event to deny user suspension
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionUserSuspendEvent extends Event {

 	/**
	 * User ID of the user
	 * @var Int User ID
	 */
	public $user_id;

	/**
	 * Username of the user
	 * @var String username
	 */
	public $username;

	/**
	 * Email of the user
	 * @var String email
	 */
	public $email;

	/**
	 * Initializes the event
	 * @param  Int    $user_id  User ID of the user
	 * @param  String $username Username of the user
	 * @param  String $email    Email of the user
	 */
	public function init($user_id, $username, $email) {
		$this->user_id = $user_id;
		$this->username = $username;
		$this->email = $email;
	}
}

/**
 * Event that gets fired when a user is unsuspended
 * Cancel event to deny user unsuspension
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionUserUnsuspendEvent extends Event {

 	/**
	 * User ID of the user
	 * @var Int User ID
	 */
	public $user_id;

	/**
	 * Username of the user
	 * @var String username
	 */
	public $username;

	/**
	 * Email of the user
	 * @var String email
	 */
	public $email;

	/**
	 * Initializes the event
	 * @param  Int    $user_id  User ID of the user
	 * @param  String $username Username of the user
	 * @param  String $email    Email of the user
	 */
	public function init($user_id, $username, $email) {
		$this->user_id = $user_id;
		$this->username = $username;
		$this->email = $email;
	}
}

/**
 * Event that gets fired when a user is registering.
 * Gets called before the SessionUserCreateEvent, so the userdata can still be changed.
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionRegisterEvent extends Event {

	/**
	 * Username of the user
	 * @var String username
	 */
	public $username;

	/**
	 * Email of the user
	 * @var String email
	 */
	public $email;

	/**
	 * The password of the user
	 * @var String password
	 */
	public $password;

	/**
	 * Initializes the Event
	 * @param  String $username Username of the user
	 * @param  String $email    Email of the user
	 * @param  String $password The password of the user
	 */
	public function init($username, $email, $password) {
		$this->username = $username;
		$this->email = $email;
		$this->password = $password;
	}
}

/**
 * Event that gets fired when a session gets started.
 * Cancel the event so that the user gets a guest session
 * Change the UDT to change the session information
 * @package net.techfuze.fuzeworks.sessions
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SessionStartEvent extends Event {

 	/**
	 * User ID of the user
	 * @var Int User ID
	 */
	public $user_id;

	/**
	 * Username of the user
	 * @var String username
	 */
	public $username;

	/**
	 * Email of the user
	 * @var String email
	 */
	public $email;

	/**
	 * User Data Table of the user
	 * @var Array UDT
	 */
	public $udt;

	/**
	 * Wether the session is from a guest.
	 * @var boolean true if a guest session
	 */
	public $guest_session = true;

	/**
	 * Initializes the event
	 * @param  Int    $user_id  User ID of the user
	 * @param  String $username Username of the user
	 * @param  String $email    Email of the user
	 * @param  Array  $udt      User Data Table of the user
	 * @param boolean $guest_session Wether the session is from a guest.
	 */
	public function init($user_id, $username, $email, $udt = null, $guest_session = true) {
		$this->user_id = $user_id;
		$this->username = $username;
		$this->email = $email;
		$this->udt = $udt;
		$this->guest_session = $guest_session;
	}

	/**
	 * Set wether the session is a guest session
	 * @param boolean $bool Wether the session is from a guest
	 */
	public function setGuestSession($bool) {
		$this->guest_session = $bool;
	}

	/**
	 * Set a new User Data Table.
	 * @param Array $udt UDT
	 */
	public function setUdt($udt) {
		$this->udt = $udt;
	}
}



