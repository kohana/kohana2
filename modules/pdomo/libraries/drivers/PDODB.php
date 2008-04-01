<?php defined('SYSPATH') or die('No direct script access.');

abstract class PDODB_Driver {

	protected static $instance;

	abstract public static function instance();

	final public function __construct() { }

	abstract public function limit($limit, $offset = NULL);

	abstract public function quote_identifier($str);

} // End PDODB_Driver