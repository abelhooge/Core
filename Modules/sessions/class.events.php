<?php

namespace Module\Sessions;
use \FuzeWorks\Event;

/**
 * An Event that gets fired when an email is about to be sent to the user upon registration
 */
class RegisterMailEvent extends Event {

	/**
	 * UDT of the user to send the mail to
	 * @access public
	 * @var Array UDT
	 */
	public $udt;

	/**
	 * HTML of the final mail
	 * @access public
	 * @var HTML
	 */ 
	public $html;

	/**
	 * Identifier for the verification of a user
	 * @access public
	 * @var String Code
	 */
	public $verifyCode;

	/**
	 * URL to send the verification to
	 * @access public
	 * @var String URL
	 */
	public $verifyURL;

	/**
	 * Wether to use custom HTML or not
	 * @access public
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
	}

}