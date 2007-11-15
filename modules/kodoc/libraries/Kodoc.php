<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Provides self-generating documentation about Kohana.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Kodoc_Core {

	protected $types = array
	(
		'core',
		'config',
		'helpers',
		'libraries',
		'models',
		'views'
	);

	// All files to be parsed
	protected $files = array();

	public function __construct()
	{
		// Find all files and parse them
		foreach($this->types as $type)
		{
			foreach(Kohana::list_files($type, TRUE) as $file)
			{
				$this->files[] = $this->parse($type, $file);
			}
		}
	}

	/*
	 * Fetch documentation for all files parsed.
	 *
	 * Returns:
	 *  Array of files
	 */
	public function get_docs()
	{
		return $this->files;
	}

	/*
	 * Parse a file for Kodoc commands, classes, and methods.
	 *
	 * Parameters:
	 *  type - file type
	 *  file - filename to read
	 */
	protected function parse($type, $file)
	{
		$data = array
		(
			'type'      => $type,
			'about'     => '',
			'package'   => '',
			'file'      => preg_replace('!^'.preg_quote(DOCROOT, '!').'!', '', $file),
			'classes'   => array(),
			'functions' => array()
		);

		// Open the file for reading
		$handle = fopen($file, 'r');

		// Start the line number count
		$l = 0;

		// Loop through each line of the file
		while ($line = fgets($handle))
		{
			$l++;

			// Handle some basics
			switch(substr(trim($line), 0, 2))
			{
				case '<?':
				case '?>':
				case '//':
					// Ignore PHP start and end tags, and non-block comments
					continue;
				case '/*':
					// Open a new comment block
					$block = '';
				break;
				case '* ':
					if (isset($block))
					{
						// Part of this block
						$block .= substr(ltrim($line), 2);
					}
				break;
				case '*':
					if (isset($block))
					{
						// Empty line, add a newline
						$block .= "\n";
					}
				break;
				default:
					if (strpos($line, 'class') !== FALSE AND preg_match('/(class|interface) ([a-zA-Z0-9_]+)/', $line, $matches))
					{
						// Class definition
						$class = array
						(
							'line'       => $l,
							'name'       => $matches[2],
							'about'      => isset($block) ? $block : 'NO INFO! WTF?!',
							'final'      => FALSE,
							'abstract'   => FALSE,
							'interface'  => FALSE,
							'extends'    => '',
							'implements' => array()
						);

						// Clear the current block and class
						unset($current_class, $block);

						if ($matches[1] === 'interface')
						{
							// Interfaces cannot be final, abstract, extend or implement
							$class['interface'] = TRUE;
						}
						else
						{
							if ($this->is_final($line))
							{
								// Class is final
								$class['final'] = TRUE;
							}
							elseif ($this->is_abstract($line))
							{
								// Class is abstract, and therefore cannot be final
								$class['abstract'] = TRUE;
							}

							if (preg_match('/extends ([a-zA-Z0-9_]+)/', $line, $matches))
							{
								// Class extension
								$class['extends'] = $matches[1];
							}

							if (preg_match('/implements ([a-zA-Z0-9_, ]+)/', $line, $matches))
							{
								// Class implements
								$class['implements'] = explode(',', $matches[1]);

								// Remove trailing spaces
								$class['implements'] = array_map('trim', $class['implements']);
							}
						}

						// Add class info to the class list
						$data['classes'][] = $class;

						// Set the current class reference
						$current_class = count($data['classes']) - 1;
						$current_class =& $data['classes'][$current_class];
					}
					elseif (strpos($line, 'function') !== FALSE AND preg_match('/function ([a-z0-9_]+) ?\((.+?)\)/', $line, $matches))
					{
						$method = array
						(
							'line'      => $l,
							'name'      => $matches[1],
							'params'    => array(),
							'about'     => isset($block) ? $block : 'NO INFO? WTF?!',
							'final'     => FALSE,
							'abstract'  => FALSE,
							'static'    => $this->is_static($line),
							'visbility' => $this->visibility($line)
						);

						// Clear the current block
						unset($block);

						if ($this->is_final($line))
						{
							// Method is final
							$method['final'] = TRUE;
						}
						elseif ($this->is_abstract($line))
						{
							// Method is abstract
							$method['abstract'] = TRUE;
						}

						if ( ! empty($matches[2]))
						{
							// Method parameters
							$method['params'] = explode(',', $matches[2]);

							// Remove trailing spaces
							$method['params'] = array_map('trim', $method['params']);
						}

						if (isset($current_class))
						{
							// Add method info the the current class
							$current_class['methods'][] = $method;
						}
						else
						{
							// Add function info the current file
							$data['functions'][] = $method;
						}
					}
				break;
			}
		}

		// Close the file
		fclose($handle);

		if (isset($block))
		{
			// There is no class information, this block is about the file
			$data['about'] = $block;
		}

		return $data;
	}

	/*
	 * Test if a file line is defined as final.
	 *
	 * Parameters:
	 *  line - file line to check
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	protected function is_final($line)
	{
		return (substr(trim($line), 0, 5) === 'final');
	}

	/*
	 * Test if a file line is defined as abstract.
	 *
	 * Parameters:
	 *  line - file line to check
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	protected function is_abstract($line)
	{
		return (substr(trim($line), 0, 8) === 'abstract');
	}

	/*
	 * Test if a file line is defined as static.
	 *
	 * Parameters:
	 *  line - file line to check
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	protected function is_static($line)
	{
		return (strpos(trim($line), 'static function') !== FALSE);
	}

	/*
	 * Finds the visbility of a line.
	 *
	 * Parameters:
	 *  line - file line to check
	 *
	 * Returns:
	 *  public, protected, or private
	 */
	protected function visibility($line)
	{
		$visibility = 'public';

		foreach(explode(' ', trim($line), 4) as $part)
		{
			if (in_array($part, array('public', 'protected', 'private')))
			{
				$visbility = $part;
			}
		}

		return $visibility;
	}

} // End Kodoc
class Kodoc_xCore {

	/*
	 * libraries, helpers, etc
	 */
	protected $files = array
	(
		'core'      => array(),
		'config'    => array(),
		'helpers'   => array(),
		'libraries' => array(),
		'models'    => array(),
		'views'     => array()
	);

	/*
	 * $classes[$name] = array $properties;
	 * $properties = array
	 * (
	 *     'drivers'    => array $drivers
	 *     'properties' => array $properties
	 *     'methods'    => array $methods
	 * )
	 */
	protected $classes = array();

	// Holds the current data until parsed
	protected $current_class;

	// $packages[$name] = array $files;
	protected $packages = array();

	// PHP's visibility types
	protected static $php_visibility = array
	(
		'public',
		'protected',
		'private'
	);

	public function __construct()
	{
		if (isset(self::$php_visibility[0]))
		{
			self::$php_visibility = array_combine(self::$php_visibility, self::$php_visibility);
		}

		foreach($this->files as $type => $files)
		{
			foreach(Kohana::list_files($type) as $filepath)
			{
				// Get the filename with no extension
				$file = pathinfo($filepath, PATHINFO_FILENAME);

				// Skip indexes and drivers
				if ($file === 'index' OR strpos($filepath, 'libraries/drivers') !== FALSE)
					continue;

				// Add the file
				$this->files[$type][$file] = $filepath;

				// Parse the file
				$this->parse_file($filepath);
			}
		}

		Log::add('debug', 'Kodoc Library initialized');
	}

	public function get_docs($format = 'html')
	{
		switch($format)
		{
			default:
				// Generate HTML via a View
				$docs = new View('kodoc_html');

				$docs->set('classes', $this->classes)->render();
			break;
		}

		return $docs;
	}

	protected function parse_file($file)
	{
		$file = fopen($file, 'r');

		$i = 1;
		while ($line = fgets($file))
		{
			if (substr(trim($line), 0, 2) === '/*')
			{
				// Reset vars
				unset($current_doc, $section, $p);

				// Prepare for a new doc section
				$current_doc = array();
				$closing_tag = '*/';

				$current_block = 'description';
				$p = 0;

				// Assign the current doc
				$this->current_doc =& $current_doc;
			}
			elseif (isset($closing_tag))
			{
				if (substr(trim($line), 0, 1) === '*')
				{
					// Remove the leading comment
					$line = substr(ltrim($line), 2);

					if (preg_match('/^([a-z ]+):/i', $line, $matches))
					{
						$current_block = trim($matches[1]);
					}
					elseif (isset($current_doc))
					{
						$line = ltrim($line);

						if (preg_match('/^\-\s+(.+)/', $line, $matches))
						{
							// An unordered list
							$current_doc['html'][$current_block]['ul'][] = $matches[1];
						}
						elseif (preg_match('/^[0-9]+\.\s+(.+)/', $line, $matches))
						{
							// An ordered list
							$current_doc['html'][$current_block]['ol'][] = $matches[1];
						}
						elseif (preg_match('/^([a-zA-Z ]+)\s+\-\s+(.+)/', $line, $matches))
						{
							// Definition list
							$current_doc['html'][$current_block]['dl'][trim($matches[1])] = trim($matches[2]);
						}
						else
						{
							if (trim($line) === '')
							{
								// Start a new paragraph
								$p++;
							}
							else
							{
								// Make sure the current paragraph is set
								if ( ! isset($current_doc['html'][$current_block]['p'][$p]))
								{
									$current_doc['html'][$current_block]['p'][$p] = '';
								}

								// Add to the current paragraph
								$current_doc['html'][$current_block]['p'][$p] .= str_replace("\n", ' ', $line);
							}
						}
					}
				}
				else
				{
					switch(substr(trim($line), 0, 2))
					{
						case '//':
						case '* ': break;
						default:
							$line = trim($line);

							if ($this->is_function($line) OR $this->is_property($line) OR $this->is_class($line))
							{
								$clear = NULL;
								$this->current_doc =& $clear;

								// Restarts searching
								unset($closing_tag, $current_doc);
							}
						break;
					}
				}
			}

			$i++;
		}

		// Close the file
		fclose($file);
	}

	/*
	 * Method:
	 *  Checks if a line is a class, and parses the data out.
	 *
	 * Parameters:
	 *  line - a line from a file
	 *
	 * Returns:
	 *  TRUE or FALSE.
	 */
	protected function is_class($line)
	{
		if (strpos($line, 'class') === FALSE)
		{
			return FALSE;
		}

		$line = explode(' ', trim($line));

		$class = array
		(
			'name'    => '',
			'final'   => FALSE,
			'extends' => FALSE,
			'drivers' => FALSE
		);

		if (current($line) === 'final')
		{
			$class['final'] = (bool) array_shift($line);
		}

		if (current($line) === 'class')
		{
			// Remove "class"
			array_shift($line);

			$name = array_shift($line);
		}

		if (count($line) > 1)
		{
			// Remove "extends"
			array_shift($line);

			$class['extends'] = array_shift($line);
		}

		if (isset($name))
		{
			// Add the class into the docs
			$this->classes[$name] = array_merge($this->current_doc, $class);

			// Set the current class
			$this->current_class =& $this->classes[$name];

			return TRUE;
		}

		return FALSE;
	}

	/*
	 * Method:
	 *  Checks if a line is a property, and parses the data out.
	 *
	 * Parameters:
	 *  line - a line from a file
	 *
	 * Returns:
	 *  TRUE or FALSE.
	 */
	protected function is_property($line)
	{
		static $preg_vis;

		if ($preg_vis === NULL)
		{
			$preg_vis = 'var|'.implode('|', self::$php_visibility);
		}

		if (strpos($line, '$') === FALSE OR ! preg_match('/^(?:'.$preg_vis.')/', $line))
			return FALSE;

		$line = explode(' ', $line);

		$var = array
		(
			'visibility' => FALSE,
			'static'     => FALSE,
			'default'    => NULL
		);

		if (current($line) === 'var')
		{
			// Remove "var"
			array_shift($line);

			$var['visibility'] = 'public';
		}

		if (current($line) === 'static')
		{
			$var['visibility'] = (bool) array_shift($line);
		}

		// If the visibility is not set, this is not a
		if ($var['visibility'] === FALSE)
			return FALSE;

		if (substr(current($line), 0, 1) === '$')
		{
			$name = substr(array_shift($line), 1);
			$name = rtrim($name, ';');
		}

		if (count($line) AND current($line) === '=')
		{
			array_shift($line);

			$var['default'] = implode(' ', $line);
		}

		if (isset($name))
		{
			// Add property to class
			$this->current_class['properties'][$name] = array_merge($this->current_doc, $var);

			return TRUE;
		}

		return FALSE;
	}

	/*
	 * Method:
	 *  Checks if a line is a function, and parses the data out.
	 *
	 * Parameters:
	 *  line - a line from a file
	 *
	 * Returns:
	 *  TRUE or FALSE.
	 */
	protected function is_function($line)
	{
		if (strpos($line, 'function') === FALSE)
		{
			return FALSE;
		}

		$line = explode(' ', trim(strtolower($line)));

		$func = array
		(
			'final'      => FALSE,
			'visibility' => 'public',
			'static'     => FALSE,
		);

		if (current($line) === 'final')
		{
			$func['final'] = TRUE;
		}

		if (isset(self::$php_visibility[current($line)]))
		{
			$func['visibility'] = array_shift($line);
		}

		if (current($line) === 'static')
		{
			$func['static'] = (bool) array_shift($line);
		}

		if (current($line) === 'function')
		{
			// Remove "function"
			array_shift($line);

			// Get name
			$name = array_shift($line);

			// Remove arguments
			if (strpos($name, '(') !== FALSE)
			{
				$name = current(explode('(', $name, 2));
			}

			// Register the method
			$this->current_class['methods'][$name] = array_merge($this->current_doc, $func);

			return TRUE;
		}

		return FALSE;
	}

} // End Kodoc