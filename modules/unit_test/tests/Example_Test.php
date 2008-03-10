<?php defined('SYSPATH') or die('No direct script access.');

class Example_Test extends Unit_Test_Case {

	public function true_false_test()
	{
		$var = TRUE;
		$this->assert_true($var);
		$this->assert_true_strict($var);
		$this->assert_false( ! $var);
		$this->assert_false_strict( ! $var);
	}

	public function equal_identical_test()
	{
		$var = '5';
		$this->assert_equal($var, 5);
		$this->assert_not_equal($var, 6);
		$this->assert_identical($var, '5');
		$this->assert_not_identical($var, 5);
	}

	public function type_test()
	{
		$this->assert_boolean(TRUE);
		$this->assert_not_boolean('TRUE');
		$this->assert_integer(123);
		$this->assert_not_integer('123');
		$this->assert_float(1.23);
		$this->assert_not_float(123);
		$this->assert_array(array(1, 2, 3));
		$this->assert_not_array('array()');
		$this->assert_object(new stdClass);
		$this->assert_not_object('X');
		$this->assert_null(NULL);
		$this->assert_not_null(0);
		$this->assert_empty('0');
		$this->assert_not_empty('1');
	}

	public function pattern_test()
	{
		$var = "Kohana\n";
		$this->assert_pattern($var, '/^Kohana$/');
		$this->assert_not_pattern($var, '/^Kohana$/D');
	}

	public function debug_example_test()
	{
		foreach (array(1, 5, 6, 12, 65, 128, 9562) as $var)
		{
			// By supplying $var in the debug parameter,
			// we can on which number this test fails.
			$this->assert_true($var < 100, $var);
		}
	}

}
