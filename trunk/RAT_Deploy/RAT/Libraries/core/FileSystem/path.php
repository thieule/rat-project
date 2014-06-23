<?php
/**
 * @package     T.Platform
 * @subpackage  FileSystem
 */

defined('T_PLATFORM') or die;

// Define a boolean constant as true if a Windows based host
define('TPATH_ISWIN', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));

// Define a boolean constant as true if a Mac based host
define('TPATH_ISMAC', (strtoupper(substr(PHP_OS, 0, 3)) === 'MAC'));

if (!defined('DS'))
{
	// Define a string constant shortcut for the DIRECTORY_SEPARATOR define
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('TPATH_ROOT'))
{
	// Define a string constant for the root directory of the file system in native format
	define('TPATH_ROOT', TPath::clean(TPATH_SITE));
}

/**
 * A Path handling class
 *
 * @package     T.Platform
 * @subpackage  FileSystem
 * @since       5/2014
 */
class TPath
{
	/**
	 * Checks if a path's permissions can be changed.
	 *
	 * @param   string  $path  Path to check.
	 *
	 * @return  boolean  True if path can have mode changed.
	 *
	 * @since   5/2014
	 */
	public static function canChmod($path)
	{
		$perms = fileperms($path);
		if ($perms !== false)
		{
			if (@chmod($path, $perms ^ 0001))
			{
				@chmod($path, $perms);
				return true;
			}
		}

		return false;
	}

	/**
	 * Chmods files and directories recursively to given permissions.
	 *
	 * @param   string  $path        Root path to begin changing mode [without trailing slash].
	 * @param   string  $filemode    Octal representation of the value to change file mode to [null = no change].
	 * @param   string  $foldermode  Octal representation of the value to change folder mode to [null = no change].
	 *
	 * @return  boolean  True if successful [one fail means the whole operation failed].
	 *
	 * @since   5/2014
	 */
	public static function setPermissions($path, $filemode = '0644', $foldermode = '0755')
	{
		// Initialise return value
		$ret = true;

		if (is_dir($path))
		{
			$dh = opendir($path);

			while ($file = readdir($dh))
			{
				if ($file != '.' && $file != '..')
				{
					$fullpath = $path . '/' . $file;
					if (is_dir($fullpath))
					{
						if (!TPath::setPermissions($fullpath, $filemode, $foldermode))
						{
							$ret = false;
						}
					}
					else
					{
						if (isset($filemode))
						{
							if (!@ chmod($fullpath, octdec($filemode)))
							{
								$ret = false;
							}
						}
					}
				}
			}
			closedir($dh);
			if (isset($foldermode))
			{
				if (!@ chmod($path, octdec($foldermode)))
				{
					$ret = false;
				}
			}
		}
		else
		{
			if (isset($filemode))
			{
				$ret = @ chmod($path, octdec($filemode));
			}
		}

		return $ret;
	}

	/**
	 * Get the permissions of the file/folder at a give path.
	 *
	 * @param   string  $path  The path of a file/folder.
	 *
	 * @return  string  Filesystem permissions.
	 *
	 * @since   5/2014
	 */
	public static function getPermissions($path)
	{
		$path = TPath::clean($path);
		$mode = @ decoct(@ fileperms($path) & 0777);

		if (strlen($mode) < 3)
		{
			return '---------';
		}

		$parsed_mode = '';
		for ($i = 0; $i < 3; $i++)
		{
			// read
			$parsed_mode .= ($mode{$i} & 04) ? "r" : "-";
			// write
			$parsed_mode .= ($mode{$i} & 02) ? "w" : "-";
			// execute
			$parsed_mode .= ($mode{$i} & 01) ? "x" : "-";
		}

		return $parsed_mode;
	}

	/**
	 * Checks for snooping outside of the file system root.
	 *
	 * @param   string  $path  A file system path to check.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  A cleaned version of the path or exit on error.
	 *
	 * @since   5/2014
	 */
	public static function check($path, $ds = DIRECTORY_SEPARATOR)
	{
		if (strpos($path, '..') !== false)
		{
			TError::raiseError(20, 'TPath::check Use of relative paths not permitted');
			texit();
		}

		$path = TPath::clean($path);
		if ((TPATH_ROOT != '') && strpos($path, TPath::clean(TPATH_ROOT)) !== 0)
		{
			// Don't translate
			TError::raiseError(20, 'TPath::check Snooping out of bounds @ ' . $path);
			texit();
		}

		return $path;
	}

	/**
	 * Function to strip additional / or \ in a path name.
	 *
	 * @param   string  $path  The path to clean.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  The cleaned path.
	 *
	 * @since   5/2014
	 * @throws  UnexpectedValueException
	 */
	public static function clean($path, $ds = DIRECTORY_SEPARATOR)
	{
		if (!is_string($path) && !empty($path))
		{
			throw new UnexpectedValueException('TPath::clean: $path is not a string.');
		}

		$path = trim($path);

		if (empty($path))
		{
			$path = TPATH_ROOT;
		}
		// Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
		// If dealing with a UNC path don't forget to prepend the path with a backslash.
		elseif (($ds == '\\') && ($path[0] == '\\' ) && ( $path[1] == '\\' ))
		{
			$path = "\\" . preg_replace('#[/\\\\]+#', $ds, $path);
		}
		else
		{
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

	/**
	 * Method to determine if script owns the path.
	 *
	 * @param   string  $path  Path to check ownership.
	 *
	 * @return  boolean  True if the php script owns the path passed.
	 *
	 * @since   5/2014
	 */
	public static function isOwner($path)
	{
		tinclude('core.filesystem.file');

		$tmp = md5(TUserHelper::genRandomPassword(16));
		$ssp = ini_get('session.save_path');
		$jtp = TPATH_SITE . '/tmp';

		// Try to find a writable directory
		$dir = is_writable('/tmp') ? '/tmp' : false;
		$dir = (!$dir && is_writable($ssp)) ? $ssp : false;
		$dir = (!$dir && is_writable($jtp)) ? $jtp : false;

		if ($dir)
		{
			$test = $dir . '/' . $tmp;

			// Create the test file
			$blank = '';
			TFile::write($test, $blank, false);

			// Test ownership
			$return = (fileowner($test) == fileowner($path));

			// Delete the test file
			TFile::delete($test);

			return $return;
		}

		return false;
	}

	/**
	 * Searches the directory paths for a given file.
	 *
	 * @param   mixed   $paths  An path string or array of path strings to search in
	 * @param   string  $file   The file name to look for.
	 *
	 * @return  mixed   The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
	 *
	 * @since   5/2014
	 */
	public static function find($paths, $file)
	{
		settype($paths, 'array'); //force to array

		// Start looping through the path set
		foreach ($paths as $path)
		{
			// Get the path to the file
			$fullname = $path . '/' . $file;

			// Is the path based on a stream?
			if (strpos($path, '://') === false)
			{
				// Not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.
				$path = realpath($path); // needed for substr() later
				$fullname = realpath($fullname);
			}

			// The substr() check added to make sure that the realpath()
			// results in a directory registered so that
			// non-registered directories are not accessible via directory
			// traversal attempts.
			if (file_exists($fullname) && substr($fullname, 0, strlen($path)) == $path)
			{
				return $fullname;
			}
		}

		// Could not find the file in the set of paths
		return false;
	}
}
