<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mail connection DSN, used with the email helper.
 *
 * PHP mail function - native://mail
 * Server sendmail   - sendmail:///path/to/sendmail
 * External SMTP     - smtp://user:password@host:port
 */
$config['dsn'] = 'native://mail';
