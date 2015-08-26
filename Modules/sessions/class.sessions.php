<?php

namespace Module\Sessions;
use \FuzeWorks\Module;
use \FuzeWorks\EventPriority;

/**
 * Sessions and User Management.
 * Manages users and login requests from FuzeWorks. 
 */
class Main extends Module {

	/**
	 * UDT of the current session, send to the user
	 * @access public
	 * @var Array UDT's
	 */
	public $udt;

	/**
	 * Database object for quick interaction
	 * @access private
	 * @var \Module\Database\Main Object Reference
	 */
	private $db;

	public function onLoad() {
		require_once($this->getmodulePath() . "/class.events.php");
		$this->setModuleConfig($this->config->loadConfigFile('sessions', $this->getModulePath()));
		$this->db = &$this->mods->database;
	}

	/**
	 * Validate a session using a sessionKey
	 * Looks up wether a session exists and returns sessionData
	 * @access public
	 * @param String SessionKey (optional)
	 * @return SessionData Array
	 */
	public function start($sessionKey = null) {
		// Fetch the sessionKey, if it exists
		$sessionKey = (isset($_COOKIE[$this->cfg->cookie_name]) ? $_COOKIE[$this->cfg->cookie_name] : (isset($_REQUEST['sessionKey']) ? $_REQUEST['sessionKey'] : $sessionKey ));

		// If a sessionKey is given, check it
		if (!is_null($sessionKey)) {
			$data = $this->sessionIsValid($sessionKey, true);
			if ($data !== false) {
				$udt = $this->convertUserData($data);
				if (!$this->sessionBlocked($udt)) {
					return $this->sendUserSession($udt);
				}
			}
		}

		return $this->sendGuestSession();
	}

	/**
	 * Send a Guest Session to the user
	 * @access private
	 * @return Guest UDT
	 */
	private function sendGuestSession() {
		if (isset($_COOKIE[$this->cfg->cookie_name])) {
			setcookie($this->cfg->cookie_name, '', time()-3600, '/', $this->config->main->SITE_DOMAIN);
		}
		$udt = array(
				'user_id' => 0,
				'user_username' => 'Guest',
				'username' => 'Guest',
				'user_email' => 'Guest@'.$this->config->main->SITE_DOMAIN,
				'email' => 'Guest@'.$this->config->main->SITE_DOMAIN,
				'permissions' => array('GUEST' => 'GUEST', 'LOGIN' => 'LOGIN'),
				'session_hash' => '0'
			);
		$this->udt = $udt;
		$this->logSessionData();
		return $udt;
	}

	/**
	 * Send a user session to the user
	 * Also sets the module UDT to the input
	 * @access private
	 * @param Array UDT
	 * @return UDT
	 */
	private function sendUserSession($udt) {
		$udt['username'] = $udt['user_username'];
		$udt['email'] = $udt['user_email'];
		$this->udt = $udt;
		$this->logSessionData();
		return $udt;
	}

	/**
	 * Log the session data to the FuzeWorks logger
	 * @access private
	 */
	private function logSessionData() {
		$this->logger->newLevel("Activating Session");
		$this->logger->logInfo("<br />SessionKey: " . $this->session_hash . "<br />Username: " . $this->user_username . "<br/>Email: " . $this->user_email . "<br/>Permissions: " . implode('-', $this->permissions));
		$this->logger->stopLevel();		
	}

	/**
	 * Convert the userdata from the database to UDT
	 * @access private
	 * @param Array SessionData
	 * @return Array Userdata
	 * @todo change this
	 */
	private function convertUserData($userData) {
		$udt = array();
		for ($i=0; $i < count($userData); $i++) { 
			foreach ($userData[$i] as $key => $value) {
				if (strpos($key, 'user_') === 0 || strpos($key, 'session_') === 0) {
					$udt[$key] = $value;
				}
			}
			$udt['permissions'][$userData[$i]['tag_name']] = $userData[$i]['tag_name'];
		}
		return $udt;
	}

	/**
	 * Check wether the UDT has a permission tag that blocks the account
	 * @access private
	 * @param Array UDT
	 * @return Boolean true if blocked, false if not
	 */
	private function sessionBlocked($udt) {
		if (isset($udt['permissions']['UNVERIFIED']) || isset($udt['permissions']['BLOCKED'])) {
			return true;
		}
		return false;
	}

	/**
	 * Check wether a sessionKey is valid and active
	 * @access private
	 * @param String SessionKey
	 * @param Boolean fetchData (wether to return the sessionData)
	 * @return Boolean False if invalid, true if valid and no fetchData, array if valid and fetchData
	 */
	private function sessionIsValid($sessionKey, $fetchData = false) {
		$prefix = $this->db->getPrefix();
		$query = "
			SELECT *
			FROM ".$prefix."session_permissions AS permissions 

			LEFT JOIN ".$prefix."session_users AS users 
				ON permissions.permission_user_id=users.user_id 

			LEFT JOIN ".$prefix."session_tags AS tags
				ON permissions.permission_tag_id=tags.tag_id 

			LEFT JOIN ".$prefix."session_sessions AS sessions 
				ON permissions.permission_user_id=sessions.session_user_id

			WHERE sessions.session_hash = ?
			AND sessions.session_active = 1
		";
		$stmnt = $this->db->prepare($query);
		$stmnt->execute(array($sessionKey));
		$result = $stmnt->fetchAll(\PDO::FETCH_ASSOC);

		if (count($result) > 0) {
			if (is_null($result[0]['session_hash'])) {
				return false;
			}
			if ($fetchData) {
				return $result;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Logs a user into the system. Checks the password and generates a session hash
	 * @access public
	 * @param String identifier (email or username)
	 * @param String password
	 * @param Boolean Wether to remember the session (true for 100 years, false for incremental)
	 * @param Boolean propagate, wether the login should be propagated to the database (default true)
	 * @todo Add ADMITTED Tag to check wether a user is allowed to log in
	 * @return Array SessionData
	 */
	public function login($identifier, $password, $remember_me = false, $propagate = true) {
		// Get the user with this identifier
		$prefix = $this->db->getPrefix();
		$query = "
			SELECT *
			FROM ".$prefix."session_users AS users 
			WHERE users.user_email = :identifier OR users.user_username = :identifier";
		$stmnt = $this->db->prepare($query);
		$stmnt->execute(array('identifier' => $identifier));
		$result = $stmnt->fetch(\PDO::FETCH_ASSOC);

		// Prepare the return variable
		$sessionData = array();
		if (count($result) > 0) {
			$sessionData = $result;
			// If user found, check password
			if (password_verify($password, $result['user_password'])) {
				// If correct, prepare sessionData
				// Create Session
				$one = uniqid();
				$two = sha1(uniqid() . $identifier); 
				$three = uniqid();
				$hash = sha1($one . $two . $three);

				// Add SessionInfo to the SessionData Array
				$info['type'] = 'fuzeworks.sessions';
				$info['agent'] = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "");
				$info['IP'] = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "UNKNOWN");

				$sessionData['info'] = json_encode($info);
				$sessionData['ip'] = $info['IP'];
				$sessionData['session_start'] = date('Y-m-d H:i:s',strtotime("now"));
				$sessionData['hash'] = $hash;
				$sessionData['valid_time'] = ($remember_me ? time()+(10*365*24*3600) : time()+3600*720);

				// Add the success value
				$sessionData['valid'] = true;

				if ($propagate) {
					$this->propagate($sessionData);
				}
			} else {
				// Password incorrect
				$sessionData['valid'] = false;
				$sessionData['reason'] = "PASSWORD_INCORRECT";
				$sessionData['reason_explained'] = "Username and/or password is incorrect";
			}
		} else {
			// User not found
			$sessionData['valid'] = false;
			$sessionData['reason'] = "USER_NOT_FOUND";
			$sessionData['reason_explained'] = "Username and/or password is incorrect";
		}

		return $sessionData;
	}

	/**
	 * Propagate a login to the database and set the cookie. Don't forget to redirect to apply the cookie
	 * @access public
	 * @param Array SessionData
	 * @return Boolean true on success, false on failure
	 */
	public function propagate($sessionData) {
		$prefix = $this->db->getPrefix();
		// The variables to insert
		$insert_array = array(
			'hash' => $sessionData['hash'], 
			'user_id' => $sessionData['user_id'], 
			'info' => $sessionData['info'], 
			'ip' => $sessionData['ip'], 
			'session_start' => $sessionData['session_start']);

		$query = "
			INSERT INTO ".$prefix."session_sessions 
				(session_hash,session_user_id,session_info,session_ip,session_start)
				VALUES (:hash, :user_id, :info, :ip, :session_start)
		";
		$stmnt = $this->db->prepare($query);
		$stmnt->execute($insert_array);
		if ($stmnt->rowCount() == 1) {
			// Set the cookie
			setcookie($this->cfg->cookie_name, $sessionData['hash'], $sessionData['valid_time'], '/', $this->config->main->SITE_DOMAIN);
			return true;
		} else {
			throw new SessionException("Could not log user in. Database error", 1);
			return false;
		}
	}

	/**
	 * Sign a user out of the system
	 * 
	 * @access public
	 * @param String SessionKey (optional)
	 * @param Boolean Propagate the logout to the database (default true)
	 * @return Boolean true on success
	 * @throws SessionException on fatal error
	 */
	public function logout($sessionKey = null, $propagate = true) {
		// Fetch the sessionKey, if it exists
		$sessionKey = (isset($_COOKIE[$this->cfg->cookie_name]) ? $_COOKIE[$this->cfg->cookie_name] : (isset($_REQUEST['sessionKey']) ? $_REQUEST['sessionKey'] : $sessionKey ));

		// If a sessionKey is given, check it
		if (!is_null($sessionKey)) {
			// Fetch the session data
			$data = $this->sessionIsValid($sessionKey, true);
			if ($data !== false) {
				// If valid, remove the current session
				$this->udt = null;
				if ($propagate) {
					// If set to propagete, edit the entry in the database
					$prefix = $this->db->getPrefix();
					$query = "UPDATE ".$prefix."session_sessions SET session_active = 0 WHERE session_hash = ?";
					$stmnt = $this->db->prepare($query);
					$stmnt->execute(array($sessionKey));

					// And after that remove the cookie
					if ($stmnt->rowCount() == 1) {
						// Set the cookie
						setcookie($this->cfg->cookie_name, $sessionKey, date('U') - 3600, '/', $this->config->main->SITE_DOMAIN);
						return true;
					}
					
					throw new SessionException("Could not log user out. Database error", 1);
				}
			}
		}
		
		throw new SessionException("Could not log user out. SessionKey not found", 1);
	}

	/**
	 * Register a new User. Features input handling
	 * @access public 
	 * @param String Username. Username of the new user
	 * @param String email. Email of the new user
	 * @param String password. Password of the new user
	 * @return Boolean true on success
	 * @throws SessionException on fatal error
	 */
	public function register($username, $email, $password) {
		// First match if the username and email are valid

		$errors = [];
		// Email
		if (!preg_match('/^[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/',$email)) { 
			// Invalid
			$errors[] = 'Invalid Email';
		}

		// Username
		if (!preg_match('/^(?=.{1,15}$)[a-zA-Z][a-zA-Z0-9]*(?: [a-zA-Z0-9]+)*$/', $username)) {
			// Invalid
			$errors[] = "Invalid Username";
		}

		// And finally parse the error if there are errors present
		if (!empty($errors)) {
			throw new SessionException("Could not register user. " . implode(' .', $errors), 1);
		}

		// And perform the handling
		try {
			return $this->createUser($username, $email, $password, true);
		} catch (SessionException $e) {
			throw new SessionException("Could not register user. '" . $e->getMessage() . "'", 1, $e);
		}
	}

	/**
	 * Create a new User
	 * @access public
	 * @param String username
	 * @param String email
	 * @param String password
	 * @param Boolean Send Email of registration (default false)
	 * @throws SessionException on fatal error
	 * @return Boolean true on success
	 */
	public function createUser($username, $email, $password, $send_email = false) {
		// Prepare the variables
		$password = password_hash($password, PASSWORD_DEFAULT);

		// Check for the existence of an account
		$qry = "SELECT * FROM hi_session_users WHERE user_username = :username OR user_email = :email";
		$stmnt = $this->mods->database->prepare($qry);
		$stmnt->execute(['username' => $username, 'email' => $email]);
		$data = $stmnt->fetch(\PDO::FETCH_ASSOC);
		if (empty($data)) {
			// And put the data into the database
			$prefix = $this->db->getPrefix();
			$qry1 = "INSERT INTO ".$prefix."session_users (user_username,user_password,user_email,verify_code) VALUES (:username,:password,:email,:verify_code)";
			$qry2 = "INSERT INTO ".$prefix."session_permissions (permission_tag_id,permission_user_id) VALUES (:tag_id,:user_id)";

			$this->mods->database->beginTransaction();
			$stmnt1 = $this->mods->database->prepare($qry1);
			$stmnt2 = $this->mods->database->prepare($qry2);

			$stmnt1->execute(['username' => $username, 'password' => $password, 'email' => $email, 'verify_code' => substr(sha1(uniqid()), 0, 15)]);
			$id = $this->mods->database->lastInsertId();
			$stmnt2->execute(['tag_id' => 1, 'user_id' => $id]);

			$this->mods->database->commit();

			// After that send a registration mail
			if ($send_email) {
				$this->registerMail($id);
			}

			return true;
		} else {
			throw new SessionException("Could not create user. Username or email already exists", 1);
			return false;
		}
	}

	/**
	 * Sends a mail to a user when a registration happens
	 * @access private
	 * @param Int UserID
	 * @param Boolean Verify. Wether the user needs to verify using the email (default false)
	 * @throws SessionException on fatal error
	 * @todo Apply new Template Engine
	 */
	public function registerMail($userId, $verify = false) {
		$udt = $this->getUsersByIds(intval($userId));
		if (empty($udt)) {
			throw new SessionException("Could not send mail. User not found", 1);
		}
		$udt = $udt[0];

		// Load the mailer module
		$mailer = $this->core->loadMod('techfuze/mailer')->mailer;
		$mailer->setFrom('no-reply@'.$this->config->main->SITE_DOMAIN, 'Auth Service');

		// First prepare the layout manager
		// $this->layout->setEngine('PHP');
			
		// Assign all variables
		$verifyCode = $udt['user_verify_code'];
		$verifyURL = '';

		$event = $this->events->fireEvent(new RegisterMailEvent(), $udt, $verifyCode, $verifyURL);
		if ($event->isCancelled()) {
			$this->logger->log("Sending of Registration Mail has been cancelled");
			return false;
		}

		// Retrieve some variables
		$udt = $event->udt;

		// Assign new variables
		$this->layout->assign('serverName', $this->config->main->SERVER_NAME);
		$this->layout->assign('logo', $this->config->main->SITE_LOGO_URL);

		// More if there is a need to verify
		if ($verify) {
			$this->layout->assign('verifyURL', $event->verifyURL);
			$this->layout->assign('verifyCode', $event->verifyCode);
		}

		// Check if a custom HTML should be used
		if ($event->customHtml) {
			$html = $event->html;
		} else {
			// Or retrieve it from a layout file
			$html = $this->layout->get('email_layout', $this->getModulePath() . "/Views/" );
		}

		// And finally send it
		$mailer->addAddress($udt['email']);
		$mailer->isHTML(true);
		$mailer->Body = $html;
		$mailer->Subject = $this->config->main->SERVER_NAME . " | Registration";
		$mailer->send();
		if (!empty($mailer->ErrorInfo)) {
			// Throw Exception if something goes wrong
			throw new SessionException("Could not send mailer. PHPMailer Error", 1);
		}
	}

	/**
	 * Modifies a user entry
	 * @access public
	 * @param Int UserID to edit
	 * @param String column to edit
	 * @param Mixed value to apply
	 * @return true on success
	 * @throws \Exception|SessionException on fatal error
	 */
	public function modifyUser($userId, $key, $value) {
		$udt = $this->getUsersByIds($userId)[0];
		// And fetch tag information
		$prefix = $this->db->getPrefix();
		$stmnt = $this->mods->database->prepare("UPDATE ".$prefix."session_users SET $key = ?");
		$stmnt->execute([$value]);
		if ($stmnt->rowCount() == 1) {
			return true;
		}

		throw new SessionException("Could not modify user. Database error", 1);
	}

	/**
	 * Changes the password of a user
	 * @access public
	 * @param Int UserID to edit
	 * @param String|null Old password of the user or nothing if trying to change as admin 
	 * @param String New password of the user
	 * @return true on success
	 * @throws \Exception|SessionException on fatal error
	 */
	public function changePassword($userId, $oldPassword = null, $newPassword) {
		$udt = $this->getUsersByIds($userId)[0];
		// First check if the oldPassword is correct
		if (is_null($oldPassword) || password_verify($oldPassword, $udt['user_password'])) {
			// Then apply the new password
			$hash = password_hash($newPassword, PASSWORD_DEFAULT);
			$this->modifyUser($userId, 'user_password', $hash);
			return true;
		} 

		throw new SessionException("Could not change password. Old password did not match", 1);
	}

	/**
	 * Suspends a user from using the system
	 * @access public
	 * @param Int UserID
	 * @return true on success
	 * @throws SessionException on fatal error
	 */
	public function suspendUser($userId) {
		return $this->removePermission('ADMITTED', $userId, false);
	}

	/**
	 * Allows a user to log in again
	 * @access public
	 * @param Int UserID
	 * @return true on success
	 * @throws SessionException on fatal error
	 */
	public function unsuspendUser($userId) {
		return $this->addPermission('ADMITTED', $userId, false);
	}

	/**
	 * Removes a user from the system
	 * @access public
	 * @param Int UserID
	 * @return true on success
	 * @throws SessionException on fatal error
	 */
	public function removeUser($userId) {
		// First get all relevant data
		$udt = $this->getUsersByIds($userId)[0];
		$userId = $udt['user_id'];

		// Remove the active permission, effectively removing the user
		return $this->removePermission('ACTIVE', $userId, false, true);
	}

	/**
	 * Verify a user with a code to we can unsuspend them
	 * @access public
	 * @param String VerifyCode
	 * @return true on success
	 * @throws SessionException on fatal error
	 */
	public function verifyUser($verifyCode) {
		// And fetch tag information
		$prefix = $this->db->getPrefix();
		$stmnt = $this->mods->database->prepare("SELECT * FROM ".$prefix."session_users WHERE user_verify_code = ?");
		$stmnt->execute([$verifyCode]);
		$data = $stmnt->fetchAll(\PDO::FETCH_ASSOC);
		if (count($data == 1)) {
			$id = $data[0]['user_id'];
			return $this->unsuspendUser($id);
		} else {
			throw new SessionException("Could not verify user. Code invalid", 1);
		}
	}

	/**
	 * Checks wether a user has permission to a certain action
	 * If a userID is provided, a specific user is checked. Otherwise the current session is used
	 * @access public
	 * @param String PermissionTag
	 * @param Int UserID (optional)
	 * @param Boolean Wether to ignore admin permission (default false)
	 * @throws SessionException on fatal error
	 * @return Boolean true on permission. False on no permission
	 */
	public function hasPermission($permissionTag, $userId = null, $ignoreAdmin = false) {
		// First retrieve the UDT
		if (is_null($userId)) {
			if (!is_null($this->udt)) {
				$udt = $this->udt;
			} else {
				throw new SessionException("Could not check for permission. No user logged in and no user provided", 1);
			}
		} else {
			$udt = $this->getUsersByIds($userId);
			if (!empty($udt)) {
				$udt = $udt[0];
			} else {
				throw new SessionException("Could not check for permission. User not found", 1);
			}
		}

		// Then read it and check for permission
		$tag = strtoupper($permissionTag);

		// What type to return if ignoring admin
		if ($ignoreAdmin) {
			if (isset($udt['permissions'][$tag])) {
				return true;
			}
		} else {
			if (isset($udt['permissions'][$tag]) || isset($udt['permissions']['ADMIN'])) {
				return true;
			}	
		}

		return false;
	}

	/**
	 * Removes a permission from a user
	 * If a userID is provided, a specific user is checked. Otherwise the current session is used
	 * @access public
	 * @param String PermissionTag
	 * @param Int UserID (optional)
	 * @param Boolean Wether to remove the tag if it is not used anymore
	 * @param Boolean wether to ignore the ACTIVE tag. Seriously, do not touch this
	 * @throws SessionException on fatal error
	 * @return Boolean true on success
	 */
	public function removePermission($permissionTag, $userId = null, $removeTag = false, $ignoreActive = false) {
		// Check if the active tag is given
		if (!$ignoreActive && strtoupper($permissionTag) == 'ACTIVE') {
			throw new SessionException("Could not remove permission. ACTIVE tag removal is prohibited", 1);
		}

		// First retrieve the UDT
		if (!$this->hasPermission($permissionTag, $userId, true)) {
			throw new SessionException("Could not remove permission. User does not have permission", 1);
		}

		// Fetch the user ID
		if (is_null($userId)) {
			$user_id = $this->udt['user_id'];
		} else {
			$udt = $this->getUsersByIds($userId);
			if (!empty($udt)) {
				$udt = $udt[0];
				$user_id = $udt['user_id'];
			}
		}

		// And fetch tag information
		$prefix = $this->db->getPrefix();
		$stmnt = $this->mods->database->prepare("SELECT * FROM ".$prefix."session_tags WHERE tag_name = ?");
		$stmnt->execute([strtoupper($permissionTag)]);
		$tag = $stmnt->fetch(\PDO::FETCH_ASSOC);
		if (!empty($tag)) {
			$tag_id = intval($tag['tag_id']);
		}

		// And now remove the reference in the database
		$stmnt = $this->mods->database->prepare("DELETE FROM ".$prefix."session_permissions WHERE permission_tag_id = :tag_id AND permission_user_id = :user_id");
		$stmnt->execute(['tag_id' => $tag_id, 'user_id' => $user_id]);
		if ($stmnt->rowCount() == 1) {
			// Check if the tag is still used
			if ($removeTag) {
				$stmnt = $this->mods->database->prepare("SELECT * FROM ".$prefix."session_permissions WHERE permission_tag_id = ?");
				$stmnt->execute([strtoupper($permissionTag)]);
				if (count($stmnt->fetchAll(\PDO::FETCH_ASSOC)) == 0) {
					// Remove the tag
					$stmnt = $this->mods->database->prepare("DELETE FROM ".$prefix."session_tags WHERE tag_name = ?");
					$stmnt->execute([strtoupper($permissionTag)]);
					if ($stmnt->rowCount() == 0) {
						// Something went wrong
						throw new SessionException("Could not remove permission tag. Database error", 1);
					}
				}
			}

			return true;
		}
		
		throw new SessionException("Could not remove permission. Database error", 1);
	}

	/**
	 * Adds a permission to a user
	 * If a userID is provided, a specific user is checked. Otherwise the current session is used
	 * @access public
	 * @param String PermissionTag
	 * @param Int UserID (optional)
	 * @throws SessionException on fatal error
	 * @return Boolean true on success
	 */
	public function addPermission($permissionTag, $userId = null) {
		// First retrieve the UDT
		if ($this->hasPermission($permissionTag, $userId, true)) {
			throw new SessionException("Could not add permission. User does already have permission", 1);
		}

		// Fetch the user ID
		if (is_null($userId)) {
			$user_id = $this->udt['user_id'];
		} else {
			$udt = $this->getUsersByIds($userId);
			if (!empty($udt)) {
				$udt = $udt[0];
				$user_id = $udt['user_id'];
			}
		}

		// Check if the tag already exists
		$prefix = $this->db->getPrefix();
		$stmnt = $this->mods->database->prepare("SELECT * FROM ".$prefix."session_tags WHERE tag_name = ?");
		$stmnt->execute([strtoupper($permissionTag)]);
		$d = $stmnt->fetchAll(\PDO::FETCH_ASSOC);

		if (count($d) == 0) {
			// Create tag
			$stmnt = $this->mods->database->prepare("INSERT INTO ".$prefix."session_tags (tag_name) VALUES (:tag_name)");
			$stmnt->execute(['tag_name' => strtoupper($permissionTag)]);
			$id = $stmnt->lastInsertId();
		} elseif (count($d) == 1) {
			// Get ID
			$data = $d[0];
			$id = $data['tag_id'];
		}

		// Add the permission
		$stmnt = $this->mods->database->prepare("INSERT INTO ".$prefix."session_permissions (permission_tag_id,permission_user_id) VALUES (:permission_tag_id,:permission_user_id)");
		$stmnt->execute(['permission_tag_id' => $id, 'permission_user_id' => $user_id]);

		if ($stmnt->rowCount() == 1) {
			return true;
		} 

		throw new SessionException("Could not add permission. Database Error", 1);
	}

	/**
	 * Get users by Usernames
	 * @access public
	 * @param Array of usernames
	 * @return Array of UDT's
	 * @todo Support for 1 parameter (STRING)
	 */
	public function getUsersByName($usernames = array()) {
		$prefix = $this->db->getPrefix();
		$query = "
			SELECT *
			FROM ".$prefix."session_permissions AS permissions 

			LEFT JOIN ".$prefix."session_users AS users 
				ON permissions.permission_user_id=users.user_id 

			LEFT JOIN ".$prefix."session_tags AS tags
				ON permissions.permission_tag_id=tags.tag_id 

			WHERE users.user_username = ?
		";
		$stmnt = $this->mods->database->prepare($query);
		$users = array();
		for ($i=0; $i < count($usernames); $i++) { 
			$username = $usernames[$i];
			$stmnt->execute(array($username));
			$user_data = $stmnt->fetchAll(\PDO::FETCH_ASSOC);
			if (!empty($user_data)) {
				$users[] = $this->handleUserSelectData($user_data);
			}
		}
		return $users;
	}

	/**
	 * Get users by Identifier
	 * @access public
	 * @param Array of user ids OR Int of single ID
	 * @return Array of UDT's
	 */
	public function getUsersByIds($ids = array()) {
		if (is_int($ids)) {
			$ids = array($ids);
		}

		$prefix = $this->db->getPrefix();
		$query = "
			SELECT *
			FROM ".$prefix."session_permissions AS permissions 

			LEFT JOIN ".$prefix."session_users AS users 
				ON permissions.permission_user_id=users.user_id 

			LEFT JOIN ".$prefix."session_tags AS tags
				ON permissions.permission_tag_id=tags.tag_id 

			WHERE users.user_id = ?
		";
		$stmnt = $this->mods->database->prepare($query);
		$users = array();
		for ($i=0; $i < count($ids); $i++) { 
			$id = $ids[$i];
			$stmnt->execute(array($id));
			$user_data = $stmnt->fetchAll(\PDO::FETCH_ASSOC);
			if (!empty($user_data)) {
				$users[] = $this->handleUserSelectData($user_data);
			}
		}			

		return $users;
	}

	/**
	 * Get users by Email address
	 * @access public
	 * @param Array of emails
	 * @return Array of UDT's
	 * @todo Support for 1 parameter (STRING)
	 */
	public function getUsersByEmails($emails = array()) {
		$prefix = $this->db->getPrefix();
		$query = "
			SELECT *
			FROM ".$prefix."session_permissions AS permissions 

			LEFT JOIN ".$prefix."session_users AS users 
				ON permissions.permission_user_id=users.user_id 

			LEFT JOIN ".$prefix."session_tags AS tags
				ON permissions.permission_tag_id=tags.tag_id 

			WHERE users.user_email = ?
		";
		$stmnt = $this->mods->database->prepare($query);
		$users = array();
		for ($i=0; $i < count($emails); $i++) { 
			$email = $emails[$i];
			$stmnt->execute(array($email));
			$user_data = $stmnt->fetchAll(\PDO::FETCH_ASSOC);
			if (!empty($user_data)) {
				$users[] = $this->handleUserSelectData($user_data);
			}
		}
		return $users;
	}

	/**
	 * Get all users who have a specific permission tag
	 * @access public
	 * @param String Permission Tag
	 * @return Array of UDT's
	 * @todo Support for 1 parameter (STRING)
	 */
	public function getUsersByPermissions($permissionTags = array()) {
		$prefix = $this->db->getPrefix();
		$query = "
			SELECT *
			FROM ".$prefix."session_permissions AS permissions 

			LEFT JOIN ".$prefix."session_users AS users 
				ON permissions.permission_user_id=users.user_id 

			LEFT JOIN ".$prefix."session_tags AS tags
				ON permissions.permission_tag_id=tags.tag_id 

			WHERE tags.tag_name = ?
		";
		$stmnt = $this->mods->database->prepare($query);
		$users = array();
		for ($i=0; $i < count($permissionTags); $i++) { 
			$tag = $permissionTags[$i];
			$stmnt->execute(array($tag));

			$user_data = $stmnt->fetchAll(\PDO::FETCH_ASSOC);
			if (!empty($user_data)) {
				$users[] = $this->getUsersByIds(array($user_data[0]['user_id']))[0];
			}
		}
		return $users;
	}

	/**
	 * Internal function used by the getUsersby* functions
	 * @access private
	 * @param Array PDO Data result
	 * @return UDT
	 */
	private function handleUserSelectData($user_data) {
		$user = array();
		$basic = $user_data[0];
		foreach ($basic as $key => $value) {
			if (strpos($key, 'user_') === 0) {
				$user[$key] = $value;
			}
		}
		$user['permissions'] = array();

		for ($j=0; $j < count($user_data); $j++) { 
			$user['permissions'][ $user_data[$j]['tag_name'] ] = $user_data[$j]['tag_name'];
		}
		$user['username'] = $user['user_username'];
		$user['email'] = $user['user_email'];
		return $user;
	}

	/**
	 * Fetch data from the current UDT
	 * @access public
	 * @param Mixed Key
	 * @return Mixed Value
	 */
	public function __get($key) {
		return $this->udt[$key];
	}



}

/**
 * Exception class for the Sessions Module
 */
class SessionException extends \Exception {}

?>