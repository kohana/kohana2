<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana - The Swift PHP Framework
 *
 *  License:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

/**
 * Provides self-generating documentation about Kohana.
 */
class Kodoc_Core {

	protected static $types = array
	(
		'core',
		'config',
		'helpers',
		'libraries',
		'models',
		'views'
	);

	public static function get_types()
	{
		return self::$types;
	}

	public static function get_files()
	{
		// Extension length
		$ext_len = -(strlen(EXT));

		$files = array();
		foreach(self::$types as $type)
		{
			$files[$type] = array();
			foreach(Kohana::list_files($type, TRUE) as $file)
			{
				// Not a source file
				if (substr($file, $ext_len) !== EXT)
					continue;

				// Remove the dirs from the filename
				$file = preg_replace('!^.+'.$type.'/(.+)'.EXT.'$!', '$1', $file);

				if ($type === 'libraries' AND substr($file, 0, 8) === 'drivers/')
				{
					// Remove the drivers directory from the file
					$file = explode('_', substr($file, 8));

					if (count($file) === 1)
					{
						// Driver interface
						$files[$type][current($file)][] = current($file);
					}
					else
					{
						// Driver is class suffix
						$driver = array_pop($file);

						// Library is everything else
						$library = implode('_', $file);

						// Library driver
						$files[$type][$library][] = $driver;
					}
				}
				else
				{
					$files[$type][$file] = NULL;
				}
			}
		}

		return $files;
	}

	public static function remove_docroot($file)
	{
		return preg_replace('!^'.preg_quote(DOCROOT, '!').'!', '', $file);
	}

	// All files to be parsed
	protected $file = array();

	public function __construct($type, $filename)
	{
		// Parse the file
		$this->file = $this->parse($type, $filename);
	}

	/**
	 * Fetch documentation for all files parsed.
	 *
	 * Returns:
	 *  array: file documentation
	 */
	public function get()
	{
		return $this->file;
	}

	/**
	 * Parse a file for Kodoc commands, classes, and methods.
	 *
	 * Parameters:
	 *  string: file type
	 *  string: absolute filename path
	 */
	protected function parse($type, $filename)
	{
		// File definition
		$file = array
		(
			'type'      => $type,
			'comment'   => '',
			'file'      => self::remove_docroot($filename),
			'classes'   => array()
		);

		// Open the file for reading
		$handle = fopen($filename, 'r');

		// For comment handling
		$in_comment = FALSE;
		$end_comment = FALSE;
		$skip_line = FALSE;

		// Add first comment to the file info
		$add_about = TRUE;

		while ($line = fgets($handle))
		{
			switch(substr(trim($line), 0, 2))
			{
				case '/*':
					// Opening of a comment
					$in_comment = TRUE;
					continue;
				case '//':
					// Single line comment
					$skip_line = TRUE;
					continue;
				break;
				case '*/':
					// Ending of a comment
					$end_comment = TRUE;
					continue;
				break;
			}

			if ($skip_line == TRUE)
			{
				// Skip single-line comments
				$skip_line = FALSE;
				continue;
			}
			elseif ($in_comment)
			{
				if ($add_about)
				{
					// Add info to the block
					$file['comment'] .= $line;

					if ($end_comment)
					{
						// Parse the about section
						$file['comment'] = $this->parse_comment($file['comment']);
					}
				}

				if ($end_comment)
				{
					// Reset comment handling
					$in_comment = FALSE;
					$add_about = FALSE;
					$end_comment = FALSE;
				}

				// Keep ignoring comments...
				continue;
			}

			if (strpos($line, 'class') !== FALSE AND preg_match('/(?:class|interface)\s+([a-z0-9_]+).+{$/i', $line, $matches))
			{
				// Include the file if it has not already been included
				class_exists($matches[1], FALSE) or include_once $filename;

				// Add class to file info
				$files['classes'][] = $this->parse_class($matches[1]);
			}
		}

		// Close the file
		fclose($handle);

		return $file;
	}

	protected function parse_comment($block)
	{
		if (trim($block) == '')
			return $block;

		// Explode the lines into an array and trim them
		$block = array_map('trim', explode("\n", $block));

		if (current($block) === '/**')
		{
			// Remove comment opening
			array_shift($block);
		}

		if (end($block) === '*/')
		{
			// Remove comment closing
			array_pop($block);
		}

		// Start comment
		$comment = array();

		while ($line = array_shift($block))
		{
			// Remove comment * and trim
			$line = trim(substr($line, 2));

			if (preg_match('/^(?:class|file|method):\s+([a-z]+)/i', $line))
			{
				// Skip these lines
				continue;
			}

			if (substr($line, 0, 1) === '$' AND substr($line, -1) === '$')
			{
				// Skip SVN property inserts
				continue;
			}

			if (preg_match('/^(?:license):/i', $line))
			{
				if (empty($comment['license']))
				{
					// Create the license block
					$comment['license'] = array();
				}

				// Setup the part
				$part =& $comment['license'];

				// End of part
				$end = '';

				// Do not add the license line
				continue;
			}

			if (isset($part) AND isset($end))
			{
				if ($line === $end)
				{
					// This part is over, clear it
					unset($part, $end);
					continue;
				}

				if ($line === '')
				{
					$line = "\n";
				}

				// Append the line to the current part
				$part[] = $line;
			}
			else
			{
				// Add the line to the comment
				$comment['about'][] = $line;
			}
		}

		foreach($comment as $key => $block)
		{
			// Implode each of the comment blocks
			$comment[$key] = trim(implode("\n", $block));
		}

		return $comment;
	}

	protected function parse_class($class)
	{
		// Use reflection to find information
		$reflection = new ReflectionClass($class);

		// Class definition
		$class = array
		(
			'name'       => $reflection->getName(),
			'comment'    => $this->parse_comment($reflection->getDocComment()),
			'final'      => $reflection->isFinal(),
			'abstract'   => $reflection->isAbstract(),
			'interface'  => $reflection->isInterface(),
			'extends'    => '',
			'implements' => array(),
			'methods'    => array()
		);

		if ($implements = $reflection->getInterfaces())
		{
			foreach($implements as $interface)
			{
				// Get implemented interfaces
				$class['implements'][] = $interface->getName();
			}
		}

		if ($parent = $reflection->getParentClass())
		{
			// Get parent class
			$class['extends'] = $parent->getName();
		}


		if ($methods = $reflection->getMethods())
		{
			foreach($methods as $method)
			{
				// Don't try to document internal methods
				if ($method->isInternal()) continue;

				$class['methods'][] = array
				(
					'name'       => $method->getName(),
					'comment'    => $this->parse_comment($method->getDocComment()),
					'final'      => $method->isFinal(),
					'static'     => $method->isStatic(),
					'abstract'   => $method->isAbstract(),
					'visibility' => $this->visibility($method),
					'parameters' => $this->parameters($method)
				);
			}
		}

		return $class;
	}

	/**
	 * Finds the parameters for a ReflectionMethod.
	 *
	 * Parameters:
	 *  object: ReflectionMethod
	 *
	 * Returns:
	 *  array: parameters
	 */
	protected function parameters(ReflectionMethod $method)
	{
		$params = array();

		if ($parameters = $method->getParameters())
		{
			foreach($parameters as $param)
			{
				// Parameter data
				$data = array
				(
					'name' => $param->getName()
				);

				if ($param->isOptional())
				{
					// Set default value
					$data['default'] = $param->getDefaultValue();
				}

				$params[] = $data;
			}
		}

		return $params;
	}

	/**
	 * Finds the visibility of a ReflectionMethod.
	 *
	 * Parameters:
	 *  object: ReflectionMethod
	 *
	 * Returns:
	 *  string: visibility of method
	 */
	protected function visibility(ReflectionMethod $method)
	{
		$vis = array_flip(Reflection::getModifierNames($method->getModifiers()));

		if (isset($vis['public']))
		{
			return 'public';
		}

		if (isset($vis['protected']))
		{
			return 'protected';
		}

		if (isset($vis['private']))
		{
			return 'private';
		}

		return FALSE;
	}

} // End Kodoc
class Kodoc_xCore {

	/**
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

	/**
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

	/**
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

	/**
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

	/**
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