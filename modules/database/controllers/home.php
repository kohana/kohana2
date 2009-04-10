<?php

class Home_Controller
{
	public function index()
	{
		header('Content-type: text/plain');

		// Shows how we can generate a raw expression and pass thru to the builder
		$max = DB::expr('MAX({users.name}) = :val')->set(':val', 5);

		echo DB::build()
			->join(array('bobtable' => 'tbl'), 'test')
			->select('*', DB::expr('COUNT(*)'))
			->from(array('users', 'people' => 'p'))
			->where(array('users.id' => 5))
			->where('users.name', '=', 'bob')
			->where($max)
			->order_by(DB::expr('CHAR({users.name}, 1)'), 'DESC')
			->group_by('users.id', DB::expr('MAX({id})'));


		/*echo DB::build()->select(array('t.id' => 'man.man', 'blah.*', '*', DB::exp('MAX(id1)')))->from(array('users', 'blah', 'crazy' => 'man'))
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
		*/
	}
}