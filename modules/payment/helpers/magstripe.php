<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Parses a Track One, Format B magstripe swipe result string.
 *
 * $Id$
 * @package    Payment
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

class magstripe_Core
{
	// Class Constants
	const CARD_TYPE_MC      = 'mastercard';
	const CARD_TYPE_VS      = 'visa';
	const CARD_TYPE_AX      = 'american express';
	const CARD_TYPE_DC      = 'diners club';
	const CARD_TYPE_DS      = 'discover';
	const CARD_TYPE_JC      = 'jcb';
	const CARD_TYPE_MA      = 'maestro';
	const CARD_TYPE_UNKNOWN = 'unknown'; 
	
	// Results array
	protected $magstripe_parsed = array
    ('card_type'             => '',
     'card_number'           => '',
     'card_holder_lastname'  => '',
     'card_holder_firstname' => '',
     'card_expiration'       => ''
    );
	
	/**
	 * Parse a magstripe card string. It only extracts basic
	 * information - more routines will have to be added to
	 * extract CVV/CVK codes, service codes, etc...
	 * 
	 * @param	varchar	$magstripeLine
	 * @return	mixed
	 */
	public function parse($magstripe_string)
	{
		// Explode the string
		$magstripe_pieces  = explode('^', $magstripe_string);
		
		// Check that we are parsing Format B
		if (substr($magstripe_pieces[0], 1, 1)!=='B')
			return False;
		
		// Get the card type and number
		$card_number       = substr($magstripe_pieces[0], 2);
		
		$this->magstripe_parsed['card_type']    = $this->credit_card($card_number);
		$this->magstripe_parsed['card_number']  = $card_number;
		
		// Get card holder first/last
		$cardholder        = explode('/', $magstripe_pieces[1]);
		
		$this->magstripe_parsed['card_holder_lastname']  = $cardholder[0];
		$this->magstripe_parsed['card_holder_firstname'] = $cardholder[1];
		
		// Get the card expiry date
		$this->magstripe_parsed['card_expiration']       = substr($magstripe_pieces[2], 2, 2).substr($magstripe_pieces[2], 0, 2);
		
		// Return an array of the data
		return $this->magstripe_parsed;
	}
	
	/**
	 * Get the card type by the prefix of the number.
	 * 
	 * @param	mixed $ccnumber
	 * @return	varchar
	 */
	public function credit_card($cc_number)
	{
		// Simple, check the prefixes
		switch (TRUE)
		{
			// Visa
			case $cc_number[0]=='4'                                     :
				return magstripe_Core::CARD_TYPE_VS;
			break;
			
			// MasterCard
			case preg_match('/^5[1-5]/', $cc_number)                    :
				return magstripe_Core::CARD_TYPE_MC;
			break;
			
			// American Express
			case preg_match('/^3[47]/', $cc_number)                     :
				return magstripe_Core::CARD_TYPE_AX;
			break;
			
			// Discover
			case preg_match('/^6(?:5|011)/', $cc_number)                :
				return magstripe_Core::CARD_TYPE_DS;
			break;
			
			// Diners Club
			case preg_match('/^36|55|30[0-5]/', $cc_number)             :
				return magstripe_Core::CARD_TYPE_DC;
			break;
			
			// JCB
			case preg_match('/^3|1800|2131/', $cc_number)               :
				return magstripe_Core::CARD_TYPE_JC;
			break;
			
			// Maestro
			case preg_match('/^50(?:20|38)|6(?:304|759)/', $cc_number) :
				return magstripe_Core::CARD_TYPE_MA;
			break;
			
			// Unknown
			default :
				return magstripe_Core::CARD_TYPE_UNKNOWN;
			break;
		}
	}
}