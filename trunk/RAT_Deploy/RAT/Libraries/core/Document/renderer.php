<?php
/**
 * @package     T.Platform
 * @subpackage  Document
 */

defined('T_PLATFORM') or die;

/**
 * Abstract class for a renderer
 *
 * @package     T.Platform
 * @subpackage  Document
 * @since       5/2014
 */
class TDocumentRenderer extends TObject
{
	/**
	 * Reference to the TDocument object that instantiated the renderer
	 *
	 * @var    TDocument
	 * @since  5/2014
	 */
	protected	$_doc = null;

	/**
	 * Renderer mime type
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $_mime = "text/html";

	/**
	 * Class constructor
	 *
	 * @param   TDocument  &$doc  A reference to the TDocument object that instantiated the renderer
	 *
	 * @since   5/2014
	 */
	public function __construct(&$doc)
	{
		$this->_doc = &$doc;
	}

	/**
	 * Renders a script and returns the results as a string
	 *
	 * @param   string  $name     The name of the element to render
	 * @param   array   $params   Array of values
	 * @param   string  $content  Override the output of the renderer
	 *
	 * @return  string  The output of the script
	 *
	 * @since   5/2014
	 */
	public function render($name, $params = null, $content = null)
	{
	}

	/**
	 * Return the content type of the renderer
	 *
	 * @return  string  The contentType
	 *
	 * @since   5/2014
	 */
	public function getContentType()
	{
		return $this->_mime;
	}
}
