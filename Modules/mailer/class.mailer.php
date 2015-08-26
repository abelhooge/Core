<?php

namespace Module\Mailer;
use \FuzeWorks\Module;
use \PHPMailer;

/**
 * Main class for the Mailer module
 * This class is a simple wrapper for PHPMailer. It has a simple prepared config file and can create instances based on these config files.
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