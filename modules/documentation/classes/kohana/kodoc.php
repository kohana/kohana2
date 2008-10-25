<?php
/**
 * Provides self-generating documentation about classes.
 *
 * $Id$
 *
 * @package    Documentation
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Kodoc_Core {

	/**
	 * Scan all class files and find all classes within.
	 *
	 * @return  array
	 */
	public static function list_classes()
	{
		Benchmark::start('list_classes');

		static $classes;

		// Cache class list
		if ($classes === NULL)
		{
			$classes = array();

			$cache = new Cache(array('lifetime' => 180));

			if ( ! $classes = $cache->get('kohana_documentation_classes'))
			{
				// Fetch all files in classes dir
				$files = Kohana::list_files('classes', TRUE);
				foreach ($files as $file)
				{
					// Parse classes in file
					$kodoc = new Kohana_Kodoc($file);
					$file_classes = $kodoc->get_classes();

					foreach ($file_classes as $class)
					{
						$classes[$class] = $file;
					}
				}

				$cache->set('kohana_documentation_classes', $classes);
			}
		}

		uksort($classes, 'strnatcasecmp');

		Benchmark::stop('list_classes');

		return $classes;
	}

	/**
	 * Get list of all class methods.
	 *
	 * @param   string  class name
	 * @return  array
	 */
	public static function class_methods($class)
	{
		$classes = self::list_classes();
		$path = $classes[$class];

		$kodoc = new Kohana_Kodoc($path);

		$docs = $kodoc->get($class);

		return $docs['methods'];

		// Get methods
		$methods = array();
		foreach ($docs['methods'] as $method)
		{
			$type = $method['static'] ? 'static' : 'public';
			$methods[$type][] = $method['name'];
		}

		return $methods;
	}

	/**
	 * Remove docroot from a file path.
	 *
	 * @param   string  file path
	 * @return  string
	 */
	public static function remove_docroot($path)
	{
		return preg_replace('!^'.preg_quote(DOCROOT, '!').'!', '', $path);
	}

	/**
	 * Convert list of types to human readable string.
	 *
	 * @param   string|array  pipe delimited string or array of types
	 * @return  string
	 */
	public static function humanize_type($types)
	{
		$types = is_array($types) ? $types : explode('|', $types);

		$output = array();
		while ($t = array_shift($types))
		{
			$output[] = trim($t);
		}

		return implode(' or ', $output);
	}

	/**
	 * Convert value to human readable string.
	 *
	 * @param   mixed   value to convert
	 * @return  string
	 */
	public static function humanize_value($value)
	{
		if ($value === NULL)
		{
			return 'NULL';
		}
		elseif (is_bool($value))
		{
			return $value ? 'TRUE' : 'FALSE';
		}
		elseif (is_string($value))
		{
			return 'string '.$value;
		}
		elseif (is_numeric($value))
		{
			return (is_int($value) ? 'int' : 'float').' '.$value;
		}
		elseif (is_array($value))
		{
			return 'array';
		}
		elseif (is_object($value))
		{
			return 'object '.get_class($value);
		}
	}

	protected $file;
	protected $classes = array();
	protected $extendable = array();

	public function __construct($filename)
	{
		// Kohana::find_file always returns forward slashes so convert to back slashes for Windows
		if (KOHANA_IS_WIN)
			$filename = str_replace('/', '\\', $filename);

		$this->file = $filename;

		// Parse the file
		$classes = $this->parse_file($filename);
		$this->classes = $classes['classes'];
		$this->extendable = $classes['extendable'];
	}

	/**
	 * Get list of classes contained within file.
	 *
	 * @return  array
	 */
	public function get_classes()
	{
		return $this->classes;
	}

	/**
	 * Get documentation for class.
	 *
	 * @return  array  file documentation
	 */
	public function get($class)
	{
		if (in_array($class, $this->classes) OR in_array($class, $this->extendable))
		{
			return $this->parse_class($class);
		}

		return FALSE;
	}

	/**
	 * Parse a file for classes contained within.
	 *
	 * @param   string  file to parse
	 * @return  array
	 */
	protected function parse_file($filename)
	{
		// Read the entire file into an array
		$data = file($filename);

		$classes = array('classes' => array(), 'extendable' => array());

		foreach ($data as $line)
		{
			// Find class declarations
			if (strpos($line, 'class') !== FALSE AND preg_match('#^\s*(?:final|abstract)*\s*(?:class|interface)\s+([a-z0-9_]+).+$#i', $line, $matches))
			{
				$class = $matches[1];

				// Remove suffix if exists
				if (substr($class, -5) == '_Core')
				{
					$classes['extendable'][] = $class;
					$class = substr($class, 0, -5);
				}

				$classes['classes'][] = $class;
			}
		}

		return $classes;
	}

	/**
	 * Get raw documentation for a class.
	 *
	 * @param   string  class to parse
	 * @return  array
	 */
	protected function parse_class($class)
	{
		$file = $this->file;

		// Class is a transparent extension
		$is_extension = FALSE;

		// Class that is just an extension of core version
		// This needs to be done so reflection can read the comments
		$is_core = FALSE;

		// This is basically a copy of Kohana::auto_load with
		// some changes we need to choose the right class to parse
		if ( ! class_exists($class, FALSE))
		{
			require $file;

			// Class name to use when extending
			$extend_class = NULL;

			if (substr($class, -5) == '_Core')
			{
				$extend_class = substr($class, 0, -5);
			}
			else if (class_exists($class.'_Core', FALSE))
			{
				$filename = str_replace('_', '/', strtolower($class));
				if ($path = Kohana::find_file('extensions', $filename, FALSE))
				{
					// Load class extension
					require $path;

					if (KOHANA_IS_WIN)
						$path = str_replace('/', '\\', $path);

					$file = self::remove_docroot($path);
					$is_extension = TRUE;
				}
				else
				{
					$extend_class = $class;
					$class = $class.'_Core';
					$is_core = TRUE;
				}
			}

			if ( ! $is_extension AND $extend_class !== NULL)
			{
				// Class extension to be evaluated
				$extension = 'class '.$extend_class.' extends '.$extend_class.'_Core { }';

				// Start class analysis
				$core = new ReflectionClass($class);

				if ($core->isAbstract())
				{
					// Make the extension abstract
					$extension = 'abstract '.$extension;
				}

				// Transparent class extensions are handled using eval. This is
				// a disgusting hack, but it gets the job done.
				eval($extension);
			}
		}
		else
		{
			// Class is already loaded, we need to figure out if it's an extension
			if (substr($class, -5) != '_Core' AND class_exists($class.'_Core', FALSE))
			{
				$filename = str_replace('_', '/', strtolower($class));
				if ($path = Kohana::find_file('extensions', $filename, FALSE))
				{
					$is_extension = TRUE;
				}
				else
				{
					$class = $class.'_Core';
					$is_core = TRUE;
				}
			}
		}

		// Use reflection to find information
		$reflection = new ReflectionClass($class);

		$comments = $this->parse_comment($reflection->getDocComment());

		// Class definition
		$class = array
		(
			'file'       => self::remove_docroot($file),
			'name'       => $is_core ? substr($reflection->getName(), 0, -5) : $reflection->getName(),
			'comment'    => arr::remove('comment', $comments),
			'tags'       => $comments,
			'final'      => $reflection->isFinal(),
			'abstract'   => $reflection->isAbstract(),
			'interface'  => $reflection->isInterface(),
			'extends'    => '',
			'extension'  => $is_extension,
			'implements' => array(),
			'constants'  => array(),
			'properties' => array(),
			'methods'    => array()
		);

		// Get parent class
		if ($parent = $reflection->getParentClass())
		{
			$class['extends'] = $parent->getName();
		}

		// Get implemented interfaces
		if ($implements = $reflection->getInterfaces())
		{
			foreach ($implements as $interface)
			{
				$class['implements'][] = $interface->getName();
			}
		}

		// Get constants
		if ($constants = $reflection->getConstants())
		{
			$class['constants'] = $constants;
		}

		// Get properties
		if ($properties = $reflection->getProperties())
		{
			foreach ($properties as $property)
			{
				// Get property visibility
				$visibility = 'public';
				if ($property->isProtected()) $visibility = 'protected';
				else if ($property->isPrivate()) $visibility = 'private';

				$comments = $this->parse_comment($property->getDocComment());

				// Remove known tags from comment's tags
				$var = arr::remove('var', $comments);
				if (count($var) === 1) $var = $var[0];

				$class['properties'][] = array
				(
					'name'       => $property->getName(),
					'comment'    => empty($var['comment']) ? arr::remove('comment', $comments) : $var['comment'],
					'static'     => $property->isStatic(),
					'visibility' => $visibility,
					'type'       => $var['type']
				);
			}

			// Sort properties
			uasort($class['properties'], array($this, 'sort_properties'));
		}

		// Get methods
		if ($methods = $reflection->getMethods())
		{
			foreach ($methods as $method)
			{
				// Don't try to document internal methods
				if ($method->isInternal()) continue;

				$name = $method->getName();

				// Fetch example if it exists
				$example = 'kohana_docs/api/examples/'.strtolower($class['name'].'/'.$name);
				if (Kohana::find_file('views', $example))
				{
					$example = View::factory($example)->render();
				}
				else
				{
					$example = NULL;
				}

				// Get method visibility
				$visibility = 'public';
				if ($method->isProtected()) $visibility = 'protected';
				else if ($method->isPrivate()) $visibility = 'private';

				$comments = $this->parse_comment($method->getDocComment());

				// Remove known tags from comment's tags
				$params = arr::remove('param', $comments);
				$return = arr::remove('return', $comments);
				$throws = arr::remove('throws', $comments);

				$return = $return[0];
				$parameters = $this->parameters($method);

				foreach ($parameters as $i => $parameter)
				{
					if (isset($params[$i]))
					{
						$parameters[$i] = array_merge($parameters[$i], $params[$i]);
					}
				}

				$class['methods'][] = array
				(
					'name'       => $name,
					'comment'    => arr::remove('comment', $comments),
					'tags'       => $comments,
					'class'      => $class['name'],
					'final'      => $method->isFinal(),
					'static'     => $method->isStatic(),
					'abstract'   => $method->isAbstract(),
					'visibility' => $visibility,
					'parameters' => $parameters,
					'return'     => $return,
					'throws'     => $throws,
					'example'    => $example
				);
			}

			// Sort methods
			uasort($class['methods'], array($this, 'sort_methods'));
		}

		return $class;
	}

	/**
	 * Parse comment block for comment and tags
	 *
	 * @param   string  comment block to parse
	 * @return  array
	 */
	protected function parse_comment($block)
	{
		// Start comment
		$comments = array('comment' => '');

		// Comment is empty
		if (($block = trim($block)) == '')
			return $comments;

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

		while ($line = array_shift($block))
		{
			// Remove * from the line
			$line = trim(substr($line, 2));

			if (substr($line, 0, 1) === '$' AND substr($line, -1) === '$')
			{
				// Skip SVN property inserts
				continue;
			}

			if (substr($line, 0, 1) === '@')
			{
				// Parse tags
				if (preg_match('/^@(.+?)\s+(.+)$/', $line, $matches))
				{
					switch ($matches[1])
					{
						// Parse known tags for type and comment
						case 'var':
						case 'param':
						case 'throws':
						case 'return':

							if (strpos($matches[2], ' ') !== FALSE)
							{
								// Extract the type
								list ($type, $comment) = explode(' ', $matches[2], 2);
								$comment = trim($comment);

								// Strip out variable names from comment
								if (substr($comment, 0, 1) == '$')
								{
									if (($pos = strpos($comment, ' ')) !== FALSE)
									{
										$comment = substr($comment, $pos);
									}
									else
									{
										// No comment after the variable name
										$comment = '';
									}
								}
							}
							else
							{
								$type = $matches[2];
								$comment = '';
							}

							$comments[$matches[1]][] = array(
								'type'    => $type,
								'comment' => $comment
							);

							break;
						default:
							$comments[$matches[1]][] = $matches[2];
					}
				}
			}
			else
			{
				$comments['comment'][] = $line;
			}
		}

		$comments['comment'] = implode(' ', $comments['comment']);

		return $comments;
	}

	/**
	 * Finds the parameters for a ReflectionMethod.
	 *
	 * @param   ReflectionMethod
	 * @return  array
	 */
	protected function parameters(ReflectionMethod $method)
	{
		$params = array();

		if ($parameters = $method->getParameters())
		{
			foreach ($parameters as $param)
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
	 * Sort properties, by visibility, then name
	 *
	 * @param   array    first property
	 * @param   array    second property
	 * @return  integer  1 to swap, -1 to not swap
	 */
	protected function sort_properties($a, $b)
	{
		// Both are same, sort by visibility
		$a_sort = $b_sort = 1;

		if ($a['visibility'] == 'protected') $a_sort = 2;
		else if ($a['visibility'] == 'private') $a_sort = 3;

		if ($b['visibility'] == 'protected') $b_sort = 2;
		else if ($b['visibility'] == 'private') $b_sort = 3;

		if ($a_sort > $b_sort)
			return 1;
		else if ($a_sort == $b_sort)
		{
			// Both are same, sort by name
			$a_sort = $a['name'];
			$b_sort = $b['name'];
			if ($a_sort > $b_sort)
			{
				return 1;
			}
		}
	}

	/**
	 * Sort methods, by static, then visibility, then name
	 *
	 * @param   array    first method
	 * @param   array    second method
	 * @return  integer  1 to swap, -1 to not swap
	 */
	protected function sort_methods($a, $b)
	{
		if ($a['static'] AND ! $b['static'])
		{
			// Don't swap
			return -1;
		}
		else if ( ! $a['static'] AND $b['static'])
		{
			// Swap
			return 1;
		}
		else
		{
			// Both are same, sort by visibility
			$a_sort = $b_sort = 1;

			if ($a['visibility'] == 'protected') $a_sort = 2;
			else if ($a['visibility'] == 'private') $a_sort = 3;

			if ($b['visibility'] == 'protected') $b_sort = 2;
			else if ($b['visibility'] == 'private') $b_sort = 3;

			if ($a_sort > $b_sort)
				return 1;
			else if ($a_sort == $b_sort)
			{
				// Both are same, sort by name
				$a_sort = $a['name'];
				$b_sort = $b['name'];
				if ($a_sort > $b_sort)
				{
					return 1;
				}
			}
		}
	}

} // End Kodoc
