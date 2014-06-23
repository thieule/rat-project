<?php
/**
 * @package     T.Platform
 * @subpackage  FileSystem
 */

defined('T_PLATFORM') or die;

tinclude('core.filesystem.stream');

/**
 * Bzip2 format adapter for the JArchive class
 *
 * @package     T.Platform
 * @subpackage  FileSystem
 * @since       5/2014
 */
class TArchiveBzip2 extends TObject
{
	/**
	 * Bzip2 file data buffer
	 *
	 * @var    string
	 * @since  5/2014
	 */
	private $_data = null;

	/**
	 * Constructor tries to load the bz2 extension if not loaded
	 *
	 * @since   5/2014
	 */
	public function __construct()
	{
		self::loadExtension();
	}

	/**
	 * Extract a Bzip2 compressed file to a given path
	 *
	 * @param   string  $archive      Path to Bzip2 archive to extract
	 * @param   string  $destination  Path to extract archive to
	 * @param   array   $options      Extraction options [unused]
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   5/2014
	 */
	public function extract($archive, $destination, $options = array ())
	{
		// Initialise variables.
		$this->_data = null;

		if (!extension_loaded('bz2'))
		{
			$this->set('error.message', Text::_('JLIB_FILESYSTEM_BZIP_NOT_SUPPORTED'));

			return TError::raiseWarning(100, $this->get('error.message'));
		}

		if (!isset($options['use_streams']) || $options['use_streams'] == false)
		{
			// Old style: read the whole file and then parse it
			if (!$this->_data = TFile::read($archive))
			{
				$this->set('error.message', 'Unable to read archive');
				return TError::raiseWarning(100, $this->get('error.message'));
			}

			$buffer = bzdecompress($this->_data);
			unset($this->_data);
			if (empty($buffer))
			{
				$this->set('error.message', 'Unable to decompress data');
				return TError::raiseWarning(100, $this->get('error.message'));
			}

			if (TFile::write($destination, $buffer) === false)
			{
				$this->set('error.message', 'Unable to write archive');
				return TError::raiseWarning(100, $this->get('error.message'));
			}

		}
		else
		{
			// New style! streams!
			$input = T::getStream();
			$input->set('processingmethod', 'bz'); // use bzip

			if (!$input->open($archive))
			{
				$this->set('error.message', Text::_('JLIB_FILESYSTEM_BZIP_UNABLE_TO_READ'));

				return TError::raiseWarning(100, $this->get('error.message'));
			}

			$output = T::getStream();

			if (!$output->open($destination, 'w'))
			{
				$this->set('error.message', Text::_('JLIB_FILESYSTEM_BZIP_UNABLE_TO_WRITE'));
				$input->close(); // close the previous file

				return TError::raiseWarning(100, $this->get('error.message'));
			}

			do
			{
				$this->_data = $input->read($input->get('chunksize', 8196));
				if ($this->_data)
				{
					if (!$output->write($this->_data))
					{
						$this->set('error.message', Text::_('JLIB_FILESYSTEM_BZIP_UNABLE_TO_WRITE_FILE'));

						return TError::raiseWarning(100, $this->get('error.message'));
					}
				}
			}
			while ($this->_data);

			$output->close();
			$input->close();
		}

		return true;
	}

	/**
	 * Tests whether this adapter can unpack files on this computer.
	 *
	 * @return  boolean  True if supported
	 *
	 * @since   11.3
	 */
	public static function isSupported()
	{
		self::loadExtension();

		return extension_loaded('bz2');
	}

	/**
	 * Load the bzip2 extension
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	private static function loadExtension()
	{
		// Is bz2 extension loaded?  If not try to load it
		if (!extension_loaded('bz2'))
		{
			if (TPATH_ISWIN)
			{
				@ dl('php_bz2.dll');
			}
			else
			{
				@ dl('bz2.so');
			}
		}
	}
}
