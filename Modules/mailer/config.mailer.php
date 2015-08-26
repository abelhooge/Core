<?php
return array(

		# Sendmail Settings
		'sendmail_enabled'	=> true,

		# SMTP Settings 
		'smtp_enabled' 		=> false,
		'smtp_host'			=> '',
		'smtp_port'			=> 25,
		'smtp_auth'			=> false,
		'smtp_username'		=> '',
		'smtp_password'		=> '',
		/**
		 * 0 = off
		 * 1 = client messages
		 * 2 = client and server messages
		 */
		'smtp_debug_level'	=> 0,

		# Common sender information
		'sender_name'		=> '',
		'sender_mail'		=> '',
	);

?>