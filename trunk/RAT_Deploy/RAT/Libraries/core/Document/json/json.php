<?php
/**
 * @package     T.Platform
 * @subpackage  Document
 */

defined('T_PLATFORM') or die;

/**
 * TDocumentJSON class, provides an easy interface to parse and display JSON output
 *
 * @package     T.Platform
 * @subpackage  Document
 * @see         http://www.json.org/
 * @since       5/2014
 */
class TDocumentJSON extends TDocument
{
	/**
	 * Document name
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $_name = 'T';

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since  5/2014
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Set mime type
		$this->_mime = 'application/json';

		// Set document type
		$this->_type = 'json';
	}

	/**
	 * Render the document.
	 *
	 * @param   array  $contents   
	 * @param   string    $template  Associative array of attributes
	 *
	 * @return  The rendered data
	 *
	 * @since  5/2014
	 */
	public function render($contents,$template='index')
	{
		TResponse::allowCache(false);
		TResponse::setHeader('Content-disposition', 'attachment; filename="' . $this->getName() . '.json"', true);

		return parent::render($contents,$template);

	}

	/**
	 * Returns the document name
	 *
	 * @return  string
	 *
	 * @since  5/2014
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Sets the document name
	 *
	 * @param   string  $name  Document name
	 *
	 * @return  TDocumentJSON instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setName($name = 'T')
	{
		$this->_name = $name;

		return $this;
	}
}
