<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Interface: Session_Driver
 *  Session API Driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
interface Session_Driver {

	/*
	 * Method: open
	 *  Opens a session.
	 *
	 * Parameters:
	 *  path - save path
	 *  name - session name
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function open($path, $name);

	/*
	 * Method: close
	 *  Closes a session.
	 */
	public function close();

	/*
	 * Method: read
	 *  Reads a session.
	 *
	 * Parameters:
	 *  id - session id
	 *
	 * Returns:
	 *  Session data
	 */
	public function read($id);

	/*
	 * Method: write
	 *  Writes a session.
	 *
	 * Parameters:
	 *  id   - session id
	 *  data - session data
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function write($id, $data);

	/*
	 * Method: destroy
	 *  Destroys a session.
	 *
	 * Parameters:
	 *  id - session id
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function destroy($id);

	/*
	 * Method: regenerate
	 *  Regenerates the session id.
	 *
	 * Returns:
	 *  The new session id
	 */
	public function regenerate();

	/*
	 * Method: gc
	 *  Garbage collection, called by close()
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function gc();

} // End Session Driver Interface