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

namespace Module\Mailer;
use \FuzeWorks\Module;
use \PHPMailer;

/**
 * Main class for the Mailer module
 *
 * This class is a simple wrapper for PHPMailer. It has a simple prepared config file and can create instances based on these config files.
 * @package     net.techfuze.fuzeworks.mailer
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
 class Main extends Module {

 	/**
 	 * Array of all the active PHPMailer instances
 	 * @access private
 	 * @var Array of \PHPMailer
 	 */
 	private $mailers = array();

 	/**
 	 * First function to get called. Initiates all module variables
 	 * @access public
 	 */
	public function onLoad() {
		$this->cfg = $this->config->loadConfigFile('mailer', $this->getModulePath());
	}

	/**
	 * Return one of the instances of PHPMailer.
	 * If not found, it creates a new instance and returns that
	 * @access public
	 * @param String instance name
	 * @return \PHPMailer instance
	 */
	public function __get($name) {
		if (!isset($this->mailers[$name])) {
			$this->mailers[$name] = new PHPMailer();

			// Set settings
			$cfg = $this->cfg;

			// First check what is enabled
			if ($cfg->sendmail_enabled && !$cfg->smtp_enabled) {
				$this->mailers[$name]->isSendmail();
			} elseif (!$cfg->sendmail_enabled && $cfg->smtp_enabled) {
				$this->mailers[$name]->isSMTP();
				$this->mailers[$name]->SMTPDebug = $cfg->smtp_debug_level;
				$this->mailers[$name]->Debugoutput = 'html';
				$this->mailers[$name]->Host = $cfg->smtp_host;
				$this->mailers[$name]->Port = $cfg->smtp_port;

				// Authentication
				if ($cfg->smtp_auth) {
					$this->mailers[$name]->SMTPAuth = true;
					$this->mailers[$name]->Username = $cfg->smtp_username;
					$this->mailers[$name]->Password = $cfg->smtp_password;
				}

				// Set the sender correctly
				if ($cfg->sender_name != '' && $cfg->sender_mail != '') {
					$this->mailers[$name]->setFrom($cfg->sender_mail, $cfg->sender_name);
				} elseif ($cfg->sender_name == '' && $cfg->sender_mail != '') {
					$mail->From = $cfg->sender_mail;
				}

			}
		}

		return $this->mailers[$name];
	}
 }

 ?>