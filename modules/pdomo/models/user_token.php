<?php defined('SYSPATH') or die('No direct script access.');
/**
 * PDO User_Token_Model, a replacement for the default Auth User_Token_Model (ORM).
 *
 * $Id$
 *
 * @package    pdomo
 * @author     Woody Gilk
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class User_Token_Model extends PDO_Model {

	protected $table = 'user_tokens';

	protected $types = array
	(
		'id' => 'integer',
		'user_id' => 'integer',
		'user_agent' => 'string',
		'token' => 'string',
		'created' => 'integer',
		'expires' => 'integer',
	);

	/* PDO_Model Methods */

	protected function __on_construct()
	{
		if (mt_rand(1, 100) === 1)
		{
			// Garbage collection
			$this->delete_expired();
		}
	}

	protected function __on_find()
	{
		if ($this->loaded AND $this->expires < time())
		{
			// The object is expired, delete it
			$this->delete();
		}
	}

	public function save()
	{
		if (empty($this->data['id']))
		{
			// Set the created time
			$this->created = time();

			// Set the token value
			$this->token = $this->create_token();

			// Set the user agent
			$this->user_agent = sha1(Kohana::$user_agent);
		}

		return parent::save();
	}

	/* Custom Methods */

	public function delete_expired()
	{
		// Delete all expired rows
		$this->db->exec('DELETE FROM '.$this->table.' WHERE expires < '.time());
	}

	protected function create_token()
	{
		// SQL to find a token
		$sql = 'SELECT id FROM '.$this->table.' WHERE token = %s LIMIT 1 OFFSET 0';

		while (TRUE)
		{
			// Create a random token
			$token = text::random('distinct', 32);

			// Make the query
			$query = $this->db->query(sprintf($sql, $this->__quote_value($this->__quote_value($token))));

			if ($query->rowCount() === 0)
			{
				// A unique token has been found
				return $token;
			}
		}
	}

} // End User Token