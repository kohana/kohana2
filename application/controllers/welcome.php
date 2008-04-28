<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Default Kohana controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Welcome_Controller extends Controller {

	// Disable this controller when Kohana is set to production mode.
	// See http://doc.kohanaphp.com/installation/deployment for more details.
	// const ALLOW_PRODUCTION = FALSE;

	public function __construct()
	{
		parent::__construct();
		new Profiler;
/*
		$pdo = pdomo::registry('default', new PDO(Config::item('pdodb')));
		echo Kohana::debug($pdo);

		$query = $pdo->prepare('SELECT * FROM :table');
		$query->bindValue(':table', 'users');
		$query->setFetchMode(PDO::FETCH_NUM);
		$x = $query->fetch(PDO::FETCH_OBJ);

		echo Kohana::debug($x);

		foreach ($query as $row)
		{
			echo $row[0], ', ', $row[1], '<br />';
		}
*/
	}

	public function _default()
	{
		echo html::email('geertdd@gmail.com');
		echo html::mailto('geert@link.com');
	}

	public function pdo()
	{
		$dbh = new PDO('mysql:host=localhost;dbname=kohana', 'root', 'html123');
/*
		echo Kohana::debug($dbh);

		$query = $dbh->query('SELECT * FROM users LIMIT 3');
		$query = $dbh->query('SELECT COUNT(*) FROM users ORDER BY id DESC LIMIT 3');
		echo Kohana::debug($query);
		echo Kohana::debug($query->queryString);
		// $query = $query->fetchAll(PDO::FETCH_OBJ);
		echo Kohana::debug($query);
		foreach ($query as $row)
		{
			echo Kohana::debug($row);
		}

		$query = $dbh->exec('DELETE FROM users WHERE id = 2');
		echo Kohana::debug($query);

		$query = $dbh->prepare('INSERT INTO users (email, username) VALUES (?, ?)');
		$query->bindParam(1, $email);
		$query->bindParam(2, $username);
		$email = 'joske3@host.com';
		$username = 'josse3';
		$execute = $query->execute(array($email, $username));
		echo Kohana::debug($execute);

		$id = $dbh->lastInsertId();
		echo Kohana::debug($id);
*/
		$query = $dbh->prepare('SELECT ? FROM users WHERE id = ?');
		echo Kohana::debug($query);
		$query->bindValue(1, 'username');
		$query->bindValue(2, 3);
		
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_OBJ);
		echo Kohana::debug($result);
		foreach ($result as $row)
		{
			echo $row->username;
		}
		echo Kohana::debug($query->rowCount());
	}
}