<?php
/**
 * @package     T.Platform
 * @subpackage  FileSystem
 */

defined('T_PLATFORM') or die;

/**
 * Gzip format adapter for the TArchive class
 *
 * This class is inspired from and draws heavily in code and concept from the Compress package of
 * The Horde Project <http://www.horde.org>
 *
 * @contributor  Michael Slusarz <slusarz@horde.org>
 * @contributor  Michael Cochrane <mike@graftonhall.co.nz>
 *
 * @package     T.Platform
 * @subpackage  FileSystem
 * @since       5/2014
 */
class TArchiveGzip extends TObject
{
	/**
	 * Gzip file flags.
	 *
	 * @var    array
	 * @since  5/2014
	 */
	private $_flags = array('FTEXT' => 0x01, 'FHCRC' => 0x02, 'FEXTRA' => 0x04, 'FNAME' => 0x08, 'FCOMMENT' => 0x10);

	/**
	 * Gzip file data buffer
	 *
	 * @var    string
	 * @since  5/2014
	 */
	private $_data = null;

	/**
	 * Extract a Gzip compressed file to a given path
	 *
	 * @param   string  $archive      Path to ZIP archive to extract
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

		if (!extension_loaded('zlib'))
		{
			$this->set('error.message', Text::_('JLIB_FILESYSTEM_GZIP_NOT_SUPPORTED'));

			return TError::raiseWarning(100, $this->get('error.message'));
		}

		if (!isset($options['use_streams']) || $options['use_streams'] == false)
		{
			if (!$this->_data = TFile::read($archive))
			{
				$this->set('error.message', 'Unable to read archive');
				return TError::raiseWarning(100, $this->get('error.message'));
			}

			$position = $this->_getFilePosition();
			$buffer = gzinflate(substr($this->_data, $position, strlen($this->_data) - $position));
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
			$input->set('processingmethod', 'gz'); // use gz

			if (!$input->open($archive))
			{
				$this->set('error.message', Text::_('JLIB_FILESYSTEM_GZIP_UNABLE_TO_READ'));

				return TError::raiseWarning(100, $this->get('error.message'));
			}

			$output = T::getStream();

			if (!$output->open($destination, 'w'))
			{
				$this->set('error.message', Text::_('JLIB_FILESYSTEM_GZIP_UNABLE_TO_WRITE'));
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
						$this->set('error.message', Text::_('JLIB_FILESYSTEM_GZIP_UNABLE_TO_WRITE_FILE'));

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
	 * @since   5/2014
	 */
	public static function isSupported()
	{
		return extension_loaded('zlib');
	}

	/**
	 * Get file data offset for archive
	 *
	 * @return  integer  Data position marker for archive
	 *
	 * @since   5/2014
	 */
	public function _getFilePosition()
	{
		// gzipped file... unpack it first
		$position = 0;
		$info = @ unpack('CCM/CFLG/VTime/CXFL/COS', substr($this->_data, $position + 2));

		if (!$info)
		{
			$this->set('error.message', Text::_('JLIB_FILESYSTEM_GZIP_UNABLE_TO_DECOMPRESS'));
			return false;
		}

		$position += 10;

		if ($info['FLG'] & $this->_flags['FEXTRA'])
		{
			$XLEN = unpack('vLength', substr($this->_data, $position + 0, 2));
			$XLEN = $XLEN['Length'];
			$position += $XLEN + 2;
		}

		if ($info['FLG'] & $this->_flags['FNAME'])
		{
			$filenamePos = strpos($this->_data, "\x0", $position);
			$position = $filenamePos + 1;
		}

		if ($info['FLG'] & $this->_flags['FCOMMENT'])
		{
			$commentPos = strpos($this->_data, "\x0", $position);
			$position = $commentPos + 1;
		}

		if ($info['FLG'] & $this->_flags['FHCRC'])
		{
			$hcrc = unpack('vCRC', substr($this->_data, $position + 0, 2));
			$hcrc = $hcrc['CRC'];
			$position += 2;
		}

		return $position;
	}
}
