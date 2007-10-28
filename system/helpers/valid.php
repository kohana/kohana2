<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: valid
 *  Validation helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class valid {

	/*
	 * Method: email
	 *  Validate email, commonly used characters only
	 *
	 * Parameters:
	 *  email - email address
	 *
	 * Returns:
	 *  TRUE if email is valid, FALSE if not.
	 */
	public static function email($email)
	{
		return (bool) preg_match('/^(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}$/iD', $email);
	}
	
	/*
	 * Method: email_rfc
	 *  Validate email, RFC compliant version
	 *  Note: This function is LESS strict than valid_email. Choose carefully.
	 *  
	 *  Originally by Cal Henderson, modified to fit Kohana syntax standards:
	 *  - http://www.iamcal.com/publish/articles/php/parsing_email/
	 *  - http://www.w3.org/Protocols/rfc822/
	 *
	 * Parameters:
	 *  email - email address
	 *
	 * Returns:
	 *  TRUE if email is valid, FALSE if not.
	 */
	public static function email_rfc($email)
	{
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom  = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$pair  = '\\x5c[\\x00-\\x7f]';

		$domain_literal = "\\x5b($dtext|$pair)*\\x5d";
		$quoted_string  = "\\x22($qtext|$pair)*\\x22";
		$sub_domain     = "($atom|$domain_literal)";
		$word           = "($atom|$quoted_string)";
		$domain         = "$sub_domain(\\x2e$sub_domain)*";
		$local_part     = "$word(\\x2e$word)*";
		$addr_spec      = "$local_part\\x40$domain";

		return (bool) preg_match('/^'.$addr_spec.'$/', $email);
	}
	
	/*
	 * Method: url
	 *  Validate URL
	 *
	 * Parameters:
	 *  url    - URL
	 *  scheme - protocol
	 *
	 * Returns:
	 *  TRUE if URL is valid, FALSE if not.
	 */
	public static function url($url, $scheme = 'http')
	{
		if (is_array($scheme) AND ! empty($scheme))
		{
			$scheme = current($scheme);
		}

		// Scheme is always lowercase
		$scheme = strtolower($scheme);

		// Use parse_url to validate the URL
		$url_check = @parse_url($url);

		if (empty($url_check) OR empty($url_check['scheme']) OR empty($url_check['host']) OR $url_check['scheme'] !== $scheme)
			return FALSE;

		return TRUE;
	}

	/*
	 * Method: ip
	 *  Validate IP
	 *
	 * Parameters:
	 *  ip - IP address
	 *
	 * Returns:
	 *  TRUE if IP address is valid, FALSE if not.
	 */
	public static function ip($ip)
	{
		return (bool) Kohana::instance()->input->valid_ip($ip);
	}

} // End validate