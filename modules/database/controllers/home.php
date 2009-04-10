<?php

class Home_Controller
{
	public function index()
	{
		header('Content-type: text/plain');

		echo DB::build()->select(array('t.id' => 'man.man', 'DISTINCT blah.*', '*', DB::exp('MAX(id1)')))->from(array('users', 'blah', 'crazy' => 'man'))
			->open()
				->where(DB::exp('MAX(`id1`) > 5'))
				->where(array('id' => array(5,6)))
				->where(array('id' => 7))
			->close()
			->where('table.id', 'IS NULL')
			->or_where(array('id' => 6))
			->or_open()
				->where(array('id' => 7))
			->close()
			->having(array('test' => 5))
			->open()
				->having(array('blah'=>6))
			->close()
			->order_by(NULL, 'RAND()');

	}
}