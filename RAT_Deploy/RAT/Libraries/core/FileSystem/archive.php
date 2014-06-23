<?php
/**
 * @package     T.Platform
 * @subpackage  FileSystem
 */

defined('T_PLATFORM') or die;

/**
 * An Archive handling class
 *
 * @package     T.Platform
 * @subpackage  FileSystem
 * @since       5/2014
 */
class TArchive
{
	/**
	 * Extract an archive file to a directory.
	 *
	 * @param   string  $archivename  The name of the archive file
	 * @param   string  $extractdir   Directory to unpack into
	 *
	 * @return  boolean  True for success
	 *
	 * @since   5/2014
	 */
	public static function extract($archivename, $extractdir)
	{
		tinclude('core.filesystem.file');
		tinclude('core.filesystem.folder');

		$untar = false;
		$result = false;
		$ext = TFile::getExt(strtolower($archivename));

		// Check if a tar is embedded...gzip/bzip2 can just be plain files!
		if (TFile::getExt(TFile::stripExt(strtolower($archivename))) == 'tar')
		{
			$untar = true;
		}

		switch ($ext)
		{
			case 'zip':
				$adapter = TArchive::getAdapter('zip');

				if ($adapter)
				{
					$result = $adapter->extract($archivename, $extractdir);
				}
				break;

			case 'tar':
				$adapter = TArchive::getAdapter('tar');

				if ($adapter)
				{
					$result = $adapter->extract($archivename, $extractdir);
				}
				break;

			case 'tgz':
				// This format is a tarball gzip'd
				$untar = true;

			case 'gz':
			case 'gzip':
				// This may just be an individual file (e.g. sql script)
				$adapter = TArchive::getAdapter('gzip');

				if ($adapter)
				{
					$config = T::getConfig();
					$tmpfname = $config->get('tmp_path') . '/' . uniqid('gzip');
					$gzresult = $adapter->extract($archivename, $tmpfname);

					if ($gzresult instanceof Exception)
					{
						@unlink($tmpfname);

						return false;
					}

					if ($untar)
					{
						// Try to untar the file
						$tadapter = TArchive::getAdapter('tar');

						if ($tadapter)
						{
							$result = $tadapter->extract($tmpfname, $extractdir);
						}
					}
					else
					{
						$path = TPath::clean($extractdir);
						TFolder::create($path);
						$result = TFile::copy($tmpfname, $path . '/' . TFile::stripExt(TFile::getName(strtolower($archivename))), null, 1);
					}

					@unlink($tmpfname);
				}
				break;

			case 'tbz2':
				// This format is a tarball bzip2'd
				$untar = true;

			case 'bz2':
			case 'bzip2':
				// This may just be an individual file (e.g. sql script)
				$adapter = TArchive::getAdapter('bzip2');

				if ($adapter)
				{
					$config = T::getConfig();
					$tmpfname = $config->get('tmp_path') . '/' . uniqid('bzip2');
					$bzresult = $adapter->extract($archivename, $tmpfname);

					if ($bzresult instanceof Exception)
					{
						@unlink($tmpfname);
						return false;
					}

					if ($untar)
					{
						// Try to untar the file
						$tadapter = TArchive::getAdapter('tar');

						if ($tadapter)
						{
							$result = $tadapter->extract($tmpfname, $extractdir);
						}
					}
					else
					{
						$path = TPath::clean($extractdir);
						TFolder::create($path);
						$result = TFile::copy($tmpfname, $path . '/' . TFile::stripExt(TFile::getName(strtolower($archivename))), null, 1);
					}

					@unlink($tmpfname);
				}
				break;

			default:
				TError::raiseWarning(10, TText::_('JLIB_FILESYSTEM_UNKNOWNARCHIVETYPE'));
				return false;
				break;
		}

		if (!$result || $result instanceof Exception)
		{
			return false;
		}

		return true;
	}

	/**
	 * Get a file compression adapter.
	 *
	 * @param   string  $type  The type of adapter (bzip2|gzip|tar|zip).
	 *
	 * @return  object   TObject
	 *
	 * @since   5/2014
	 */
	public static function getAdapter($type)
	{
		static $adapters;

		if (!isset($adapters))
		{
			$adapters = array();
		}

		if (!isset($adapters[$type]))
		{
			// Try to load the adapter object
			$class = 'TArchive' . ucfirst($type);

			if (!class_exists($class))
			{
				$path = dirname(__FILE__) . '/archive/' . strtolower($type) . '.php';
				if (file_exists($path))
				{
					require_once $path;
				}
				else
				{
					TError::raiseError(500, TText::_('JLIB_FILESYSTEM_UNABLE_TO_LOAD_ARCHIVE'));
				}
			}

			$adapters[$type] = new $class;
		}

		return $adapters[$type];
	}
}
