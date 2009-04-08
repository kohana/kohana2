<?php defined('SYSPATH') OR die('No direct access allowed.');

class Database_Result_Test extends Unit_Test_Case {

	protected $db = NULL;
	protected $db_config = array();

	protected $data = array(
		array('first' => 1, 'second' => 'one', 'third' => 1.5),
		array('first' => 2, 'second' => 'two', 'third' => 2.6),
		array('first' => 3, 'second' => 'three', 'third' => 3.7),
	);

	public function setup()
	{
		if (! $this->db_config = Kohana::config('database.testing'))
			throw new Kohana_Unit_Test_Exception('No testing database found');

		$this->db = new Database($this->db_config);

		$this->teardown();

		$this->db->query('CREATE TABLE '. $this->db->escape_table($this->db_config['table_prefix'].'testtable') .' (
			first integer,
			second varchar(5),
			third numeric(6,2)
		)');

		$this->db->insert('testtable', $this->data[0]);
		$this->db->insert('testtable', $this->data[1]);
		$this->db->insert('testtable', $this->data[2]);
	}

	public function teardown()
	{
		if ($this->db->table_exists('testtable'))
		{
			$this->db->query('DROP TABLE '. $this->db->escape_table($this->db_config['table_prefix'].'testtable'));
		}
	}

	public function array_access_test()
	{
		$result = $this->db->from('testtable')->get();

		$this->assert_object($result)
			->assert_true($result->offsetExists(0))
			->assert_true($result->offsetExists(2))
			->assert_true(isset($result[0]))
			->assert_true(isset($result[2]));

		$result = $this->db->from('testtable')->orderby('first')->get();
		$result->result(FALSE);

		$this->assert_object($result)
			->assert_equal($this->data[0], $result->offsetGet(0))
			->assert_equal($this->data[2], $result->offsetGet(2))
			->assert_equal($this->data[0], $result[0])
			->assert_equal($this->data[2], $result[2]);

		$result = $this->db->from('testtable')->get();

		try
		{
			$result->offsetSet(0, array());
			throw new Kohana_Unit_Test_Exception('offsetSet should not be supported');
		}
		catch (Kohana_Database_Exception $kde) {}

		try
		{
			$result[0] = array();
			throw new Kohana_Unit_Test_Exception('offsetSet should not be supported');
		}
		catch (Kohana_Database_Exception $kde) {}

		try
		{
			$result->offsetUnset(0);
			throw new Kohana_Unit_Test_Exception('offsetUnset should not be supported');
		}
		catch (Kohana_Database_Exception $kde) {}

		try
		{
			unset($result[0]);
			throw new Kohana_Unit_Test_Exception('offsetUnset should not be supported');
		}
		catch (Kohana_Database_Exception $kde) {}
	}

	public function as_array_test()
	{
		$this->result_array_test('as_array');
	}

	public function countable_test()
	{
		$result = $this->db->from('testtable')->get();

		$this->assert_object($result)
			->assert_equal(3, $result->count())
			->assert_equal(3, count($result));
	}

	public function iterator_test()
	{
		$result = $this->db->from('testtable')->orderby('first')->get();
		$result->result(FALSE);

		$this->assert_equal(0, $result->key())
			->assert_equal($this->data[0], $result->current())
			->assert_true($result->valid());

		$result->next();

		$this->assert_equal(1, $result->key())
			->assert_equal($this->data[1], $result->current())
			->assert_true($result->valid());

		$result->next();

		$this->assert_equal(2, $result->key())
			->assert_equal($this->data[2], $result->current())
			->assert_true($result->valid());

		$result->next();

		$this->assert_false($result->valid());

		$result->rewind();

		$this->assert_equal(0, $result->key())
			->assert_equal($this->data[0], $result->current())
			->assert_true($result->valid());
	}

	public function list_fields_test()
	{
		$result = $this->db->from('testtable')->get();

		$this->assert_object($result)
			->assert_equal(array('first','second','third'), $result->list_fields());

		$result = $this->db->select('first')->from('testtable')->get();

		$this->assert_object($result)
			->assert_equal(array('first'), $result->list_fields());
	}

	/**
	 * Used by as_array() above
	 *
	 * @param string $method    Either 'result_array' or 'as_array'
	 */
	public function result_array_test($method = 'result_array')
	{
		$db_result = $this->db->from('testtable')->orderby('first')->get();

		$result = $db_result->$method(FALSE);

		$this->assert_array($result)
			->assert_equal(3, count($result))
			->assert_array($result[0])
			->assert_array($result[1])
			->assert_array($result[2])
			->assert_equal($this->data[0], $result[0])
			->assert_equal($this->data[1], $result[1])
			->assert_equal($this->data[2], $result[2]);

		$result = $db_result->$method(TRUE);

		$this->assert_array($result)
			->assert_equal(3, count($result))
			->assert_object($result[0])
			->assert_object($result[1])
			->assert_object($result[2])
			->assert_equal((object) $this->data[0], $result[0])
			->assert_equal((object) $this->data[1], $result[1])
			->assert_equal((object) $this->data[2], $result[2]);

		$result = $db_result->$method(TRUE, 'Database_Result_Test_Class');

		$this->assert_array($result)
			->assert_equal(3, count($result))
			->assert_object($result[0])
			->assert_object($result[1])
			->assert_object($result[2])
			->assert_true($result[0] instanceof Database_Result_Test_Class)
			->assert_true($result[1] instanceof Database_Result_Test_Class)
			->assert_true($result[2] instanceof Database_Result_Test_Class)
			->assert_equal(new Database_Result_Test_Class($this->data[0]), $result[0])
			->assert_equal(new Database_Result_Test_Class($this->data[1]), $result[1])
			->assert_equal(new Database_Result_Test_Class($this->data[2]), $result[2]);
	}

	public function result_test()
	{
		$result = $this->db->from('testtable')->get();
		$result->result(FALSE);

		$this->assert_object($result)
			->assert_array($result->current())
			->assert_equal($this->data[0], $result->current());

		$result->result(TRUE);

		$this->assert_object($result)
			->assert_object($result->current())
			->assert_equal((object) $this->data[0], $result->current());

		$result->result(TRUE, 'Database_Result_Test_Class');

		$this->assert_object($result)
			->assert_object($result->current())
			->assert_true($result->current() instanceof Database_Result_Test_Class)
			->assert_equal(new Database_Result_Test_Class($this->data[0]), $result->current());
	}

	public function seek_test()
	{
		$result = $this->db->from('testtable')->orderby('first')->get();
		$result->result(FALSE);

		$this->assert_equal($this->data[0], $result->current());

		$result->seek(2);

		$this->assert_equal($this->data[2], $result->current());

		$result->seek(1);

		$this->assert_equal($this->data[1], $result->current());

		$result->seek(2);

		$this->assert_equal($this->data[2], $result->current());

		$result->seek(0);

		$this->assert_equal($this->data[0], $result->current());
	}

	public function sql_test()
	{
		$sql = $this->db->from('testtable')->compile();

		$result = $this->db->from('testtable')->get();

		$this->assert_object($result)
			->assert_equal($sql, $result->sql());
	}

	public function traversable_test()
	{
		$result = $this->db->from('testtable')->orderby('first')->get();
		$result->result(FALSE);

		for ($i = 0; $i < 2; ++$i)
		{
			$id = 0;
			foreach ($result as $key => $value)
			{
				$this->assert_equal($id, $result->key())
					->assert_equal($id, $key)
					->assert_equal($this->data[$id], $result->current())
					->assert_equal($this->data[$id], $value)
					->assert_true($result->valid());

				++$id;
			}

			$this->assert_equal(3, $id)
				->assert_false($result->valid());
		}
	}
}

/**
 * Used to test object fetching
 */
final class Database_Result_Test_Class {

	function __construct($array = array())
	{
		foreach ($array as $key => $value)
		{
			$this->$key = $value;
		}
	}
}
