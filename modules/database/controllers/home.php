<?php

class Home_Controller
{
	public function index()
	{
		header('Content-type: text/plain');

		foreach (DB::build()->select('*')->from('users')->execute() as $row)
		{
			print_r($row);
		}


		// Shows how we can generate a raw expression and pass thru to the builder
		$max = DB::expr('MAX(`users.name`) = :val')->value(':val', 5);

		echo DB::build()
			->join(array('bobtable' => 'tbl'), 'tbl.col1', 'tbl2.col2', 'RIGHT')
			->select('*', DB::expr('COUNT(*)'))
			->from(array('users', 'people' => 'p'))
			->where(array('users.id' => 5))
			->where('users.name', '=', 'bob')
			->or_where('users.name', 'LIKE', '%poop%')
			->where($max)
			->order_by(DB::expr('CHAR(`users.name`, 1)'), 'DESC')
			->group_by('users.id', DB::expr('MAX(`id`)'));

		echo "\n\n";

		echo DB::build()
			->set(DB::expr('`tbl.mycol1` = `tbl.mycol2`'))
			->set(array('title' => 'test', 'blah'=>'me'))->where(array('test' => 5))->update('tes');

		echo "\n\n";

		echo DB::build()
			->insert('users')
			->set(array('title' => 5, 'blahblah'=>'test'));


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