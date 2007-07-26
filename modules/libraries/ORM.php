<?php

class Core_ORM {
	
	public function __construct($foo = 'none')
	{
		print get_class($this)." loaded: $foo<br/>\n";
	}
	
}

?>