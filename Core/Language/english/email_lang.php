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

return array (

	'email_must_be_array' => 'The email validation method must be passed an array.',
	'email_invalid_address' => 'Invalid email address: %s',
	'email_attachment_missing' => 'Unable to locate the following email attachment: %s',
	'email_attachment_unreadable' => 'Unable to open this attachment: %s',
	'email_no_from' => 'Cannot send mail with no "From" header.',
	'email_no_recipients' => 'You must include recipients: To, Cc, or Bcc',
	'email_send_failure_phpmail' => 'Unable to send email using PHP mail(). Your server might not be configured to send mail using this method.',
	'email_send_failure_sendmail' => 'Unable to send email using PHP Sendmail. Your server might not be configured to send mail using this method.',
	'email_send_failure_smtp' => 'Unable to send email using PHP SMTP. Your server might not be configured to send mail using this method.',
	'email_sent' => 'Your message has been successfully sent using the following protocol: %s',
	'email_no_socket' => 'Unable to open a socket to Sendmail. Please check settings.',
	'email_no_hostname' => 'You did not specify a SMTP hostname.',
	'email_smtp_error' => 'The following SMTP error was encountered: %s',
	'email_no_smtp_unpw' => 'Error: You must assign a SMTP username and password.',
	'email_failed_smtp_login' => 'Failed to send AUTH LOGIN command. Error: %s',
	'email_smtp_auth_un' => 'Failed to authenticate username. Error: %s',
	'email_smtp_auth_pw' => 'Failed to authenticate password. Error: %s',
	'email_smtp_data_failure' => 'Unable to send data: %s',
	'email_exit_status' => 'Exit status code: %s'

);
