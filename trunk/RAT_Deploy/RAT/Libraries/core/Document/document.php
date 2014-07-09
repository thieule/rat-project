<?php
/**
 * @package     T.Platform
 * @subpackage  Document
 */

defined('T_PLATFORM') or die;

tinclude('core.environment.response');
tinclude('core.filter.filteroutput');

/**
 * Document class, provides an easy interface to parse and display a document
 *
 * @package     T.Platform
 * @subpackage  Document
 * @since       5/2014
 */
class TDocument extends TObject
{
	/**
	 * Document title
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $title = '';

	/**
	 * Document description
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $description = '';

	/**
	 * Document full URL
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $link = '';

	/**
	 * Document base URL
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $base = '';

	/**
	 * Contains the document language setting
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $language = 'en-gb';

	/**
	 * Contains the document direction setting
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $direction = 'ltr';

	/**
	 * Document generator
	 *
	 * @var    string
	 */
	public $_generator = 'RAT - System Resource Manager';

	/**
	 * Document modified date
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $_mdate = '';

	/**
	 * Tab string
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $_tab = "\11";

	/**
	 * Contains the line end string
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $_lineEnd = "\12";

	/**
	 * Contains the character encoding string
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $_charset = 'utf-8';

	/**
	 * Document mime type
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $_mime = '';

	/**
	 * Document namespace
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $_namespace = '';

	/**
	 * Document profile
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $_profile = '';

	/**
	 * Array of linked scripts
	 *
	 * @var    array
	 * @since  5/2014
	 */
	public $_scripts = array();

	/**
	 * Array of scripts placed in the header
	 *
	 * @var    array
	 * @since  5/2014
	 */
	public $_script = array();

	/**
	 * Array of linked style sheets
	 *
	 * @var    array
	 * @since  5/2014
	 */
	public $_styleSheets = array();

	/**
	 * Array of included style declarations
	 *
	 * @var    array
	 * @since  5/2014
	 */
	public $_style = array();

	/**
	 * Array of meta tags
	 *
	 * @var    array
	 * @since  5/2014
	 */
	public $_metaTags = array();

	/**
	 * The rendering engine
	 *
	 * @var    object
	 * @since  5/2014
	 */
	public $_engine = null;

	/**
	 * The document type
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $_type = null;

	/**
	 * Array of buffered output
	 *
	 * @var    mixed (depends on the renderer)
	 * @since  5/2014
	 */
	public static $_buffer = null;

	/**
	 * @var    array  TDocument instances container.
	 * @since  11.3
	 */
	protected static $instances = array();

	/**
	 * Class constructor.
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since   5/2014
	 */
	public function __construct($options = array())
	{
		parent::__construct();

		if (array_key_exists('lineend', $options))
		{
			$this->setLineEnd($options['lineend']);
		}

		if (array_key_exists('charset', $options))
		{
			$this->setCharset($options['charset']);
		}

		if (array_key_exists('language', $options))
		{
			$this->setLanguage($options['language']);
		}

		if (array_key_exists('direction', $options))
		{
			$this->setDirection($options['direction']);
		}

		if (array_key_exists('tab', $options))
		{
			$this->setTab($options['tab']);
		}

		if (array_key_exists('link', $options))
		{
			$this->setLink($options['link']);
		}

		if (array_key_exists('base', $options))
		{
			$this->setBase($options['base']);
		}
	}

	/**
	 * Returns the global TDocument object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $type        The document type to instantiate
	 * @param   array   $attributes  Array of attributes
	 *
	 * @return  object  The document object.
	 *
	 * @since   5/2014
	 */
	public static function getInstance($type = 'html', $attributes = array())
	{
		$signature = serialize(array($type, $attributes));

		if (empty(self::$instances[$signature]))
		{
			$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
			$path = dirname(__FILE__) . '/' . $type . '/' . $type . '.php';
			$ntype = null;

			// Check if the document type exists
			if (!file_exists($path))
			{
				// Default to the raw format
				$ntype = $type;
				$type = 'raw';
			}

			// Determine the path and class
			$class = 'TDocument' . $type;
			if (!class_exists($class))
			{
				$path = dirname(__FILE__) . '/' . $type . '/' . $type . '.php';
				if (file_exists($path))
				{
					require_once $path;
				}
				else
				{
					TError::raiseError(500, Text::_('JLIB_DOCUMENT_ERROR_UNABLE_LOAD_DOC_CLASS'));
				}
			}

			$instance = new $class($attributes);
			self::$instances[$signature] = &$instance;

			if (!is_null($ntype))
			{
				// Set the type to the Document type originally requested
				$instance->setType($ntype);
			}
		}

		return self::$instances[$signature];
	}

	/**
	 * Set the document type
	 *
	 * @param   string  $type  Type document is to set to
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setType($type)
	{
		$this->_type = $type;

		return $this;
	}

	/**
	 * Returns the document type
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Get the contents of the document buffer
	 *
	 * @return  The contents of the document buffer
	 *
	 * @since   5/2014
	 */
	public function getBuffer()
	{
		return self::$_buffer;
	}

	/**
	 * Set the contents of the document buffer
	 *
	 * @param   string  $content  The content to be set in the buffer.
	 * @param   array   $options  Array of optional elements.
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setBuffer($content, $options = array())
	{
		self::$_buffer = $content;

		return $this;
	}

	/**
	 * Gets a meta tag.
	 *
	 * @param   string   $name       Value of name or http-equiv tag
	 * @param   boolean  $httpEquiv  META type "http-equiv" defaults to null
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getMetaData($name, $httpEquiv = false)
	{
		$result = '';
		$name = strtolower($name);
		if ($name == 'generator')
		{
			$result = $this->getGenerator();
		}
		elseif ($name == 'description')
		{
			$result = $this->getDescription();
		}
		else
		{
			if ($httpEquiv == true)
			{
				$result = @$this->_metaTags['http-equiv'][$name];
			}
			else
			{
				$result = @$this->_metaTags['standard'][$name];
			}
		}

		return $result;
	}

	/**
	 * Sets or alters a meta tag.
	 *
	 * @param   string   $name        Value of name or http-equiv tag
	 * @param   string   $content     Value of the content tag
	 * @param   boolean  $http_equiv  META type "http-equiv" defaults to null
	 * @param   boolean  $sync        Should http-equiv="content-type" by synced with HTTP-header?
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setMetaData($name, $content, $http_equiv = false, $sync = true)
	{
		$name = strtolower($name);

		if ($name == 'generator')
		{
			$this->setGenerator($content);
		}
		elseif ($name == 'description')
		{
			$this->setDescription($content);
		}
		else
		{
			if ($http_equiv == true)
			{
				$this->_metaTags['http-equiv'][$name] = $content;

				// Syncing with HTTP-header
				if ($sync && strtolower($name) == 'content-type')
				{
					$this->setMimeEncoding($content, false);
				}
			}
			else
			{
				$this->_metaTags['standard'][$name] = $content;
			}
		}

		return $this;
	}

	/**
	 * Adds a linked script to the page
	 *
	 * @param   string   $url    URL to the linked script
	 * @param   string   $type   Type of script. Defaults to 'text/javascript'
	 * @param   boolean  $defer  Adds the defer attribute.
	 * @param   boolean  $async  Adds the async attribute.
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function addScript($url, $type = "text/javascript", $defer = false, $async = false)
	{
		$this->_scripts[$url]['mime'] = $type;
		$this->_scripts[$url]['defer'] = $defer;
		$this->_scripts[$url]['async'] = $async;

		return $this;
	}

	/**
	 * Adds a script to the page
	 *
	 * @param   string  $content  Script
	 * @param   string  $type     Scripting mime (defaults to 'text/javascript')
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function addScriptDeclaration($content, $type = 'text/javascript')
	{
		if (!isset($this->_script[strtolower($type)]))
		{
			$this->_script[strtolower($type)] = $content;
		}
		else
		{
			$this->_script[strtolower($type)] .= chr(13) . $content;
		}

		return $this;
	}

	/**
	 * Adds a linked stylesheet to the page
	 *
	 * @param   string  $url      URL to the linked style sheet
	 * @param   string  $type     Mime encoding type
	 * @param   string  $media    Media type that this stylesheet applies to
	 * @param   array   $attribs  Array of attributes
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function addStyleSheet($url, $type = 'text/css', $media = null, $attribs = array())
	{
		$this->_styleSheets[$url]['mime'] = $type;
		$this->_styleSheets[$url]['media'] = $media;
		$this->_styleSheets[$url]['attribs'] = $attribs;

		return $this;
	}

	/**
	 * Adds a stylesheet declaration to the page
	 *
	 * @param   string  $content  Style declarations
	 * @param   string  $type     Type of stylesheet (defaults to 'text/css')
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function addStyleDeclaration($content, $type = 'text/css')
	{
		if (!isset($this->_style[strtolower($type)]))
		{
			$this->_style[strtolower($type)] = $content;
		}
		else
		{
			$this->_style[strtolower($type)] .= chr(13) . $content;
		}

		return $this;
	}

	/**
	 * Sets the document charset
	 *
	 * @param   string  $type  Charset encoding string
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setCharset($type = 'utf-8')
	{
		$this->_charset = $type;

		return $this;
	}

	/**
	 * Returns the document charset encoding.
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getCharset()
	{
		return $this->_charset;
	}

	/**
	 * Sets the global document language declaration. Default is English (en-gb).
	 *
	 * @param   string  $lang  The language to be set
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setLanguage($lang = "en-gb")
	{
		$this->language = strtolower($lang);

		return $this;
	}

	/**
	 * Returns the document language.
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Sets the global document direction declaration. Default is left-to-right (ltr).
	 *
	 * @param   string  $dir  The language direction to be set
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setDirection($dir = "ltr")
	{
		$this->direction = strtolower($dir);

		return $this;
	}

	/**
	 * Returns the document direction declaration.
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getDirection()
	{
		return $this->direction;
	}

	/**
	 * Sets the title of the document
	 *
	 * @param   string  $title  The title to be set
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * Return the title of the document.
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Sets the base URI of the document
	 *
	 * @param   string  $base  The base URI to be set
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setBase($base)
	{
		$this->base = $base;

		return $this;
	}

	/**
	 * Return the base URI of the document.
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getBase()
	{
		return $this->base;
	}

	/**
	 * Sets the description of the document
	 *
	 * @param   string  $description  The description to set
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Return the title of the page.
	 *
	 * @return  string
	 *
	 * @since    5/2014
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Sets the document link
	 *
	 * @param   string  $url  A url
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setLink($url)
	{
		$this->link = $url;

		return $this;
	}

	/**
	 * Returns the document base url
	 *
	 * @return string
	 *
	 * @since   5/2014
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * Sets the document generator
	 *
	 * @param   string  $generator  The generator to be set
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setGenerator($generator)
	{
		$this->_generator = $generator;

		return $this;
	}

	/**
	 * Returns the document generator
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getGenerator()
	{
		return $this->_generator;
	}

	/**
	 * Sets the document modified date
	 *
	 * @param   string  $date  The date to be set
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setModifiedDate($date)
	{
		$this->_mdate = $date;

		return $this;
	}

	/**
	 * Returns the document modified date
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getModifiedDate()
	{
		return $this->_mdate;
	}

	/**
	 * Sets the document MIME encoding that is sent to the browser.
	 *
	 * This usually will be text/html because most browsers cannot yet
	 * accept the proper mime settings for XHTML: application/xhtml+xml
	 * and to a lesser extent application/xml and text/xml. See the W3C note
	 * ({@link http://www.w3.org/TR/xhtml-media-types/
	 * http://www.w3.org/TR/xhtml-media-types/}) for more details.
	 *
	 * @param   string   $type  The document type to be sent
	 * @param   boolean  $sync  Should the type be synced with HTML?
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 *
	 * @link    http://www.w3.org/TR/xhtml-media-types
	 */
	public function setMimeEncoding($type = 'text/html', $sync = true)
	{
		$this->_mime = strtolower($type);

		// Syncing with meta-data
		if ($sync)
		{
			$this->setMetaData('content-type', $type, true, false);
		}

		return $this;
	}

	/**
	 * Return the document MIME encoding that is sent to the browser.
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function getMimeEncoding()
	{
		return $this->_mime;
	}

	/**
	 * Sets the line end style to Windows, Mac, Unix or a custom string.
	 *
	 * @param   string  $style  "win", "mac", "unix" or custom string.
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setLineEnd($style)
	{
		switch ($style)
		{
			case 'win':
				$this->_lineEnd = "\15\12";
				break;
			case 'unix':
				$this->_lineEnd = "\12";
				break;
			case 'mac':
				$this->_lineEnd = "\15";
				break;
			default:
				$this->_lineEnd = $style;
		}

		return $this;
	}

	/**
	 * Returns the lineEnd
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function _getLineEnd()
	{
		return $this->_lineEnd;
	}

	/**
	 * Sets the string used to indent HTML
	 *
	 * @param   string  $string  String used to indent ("\11", "\t", '  ', etc.).
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function setTab($string)
	{
		$this->_tab = $string;

		return $this;
	}

	/**
	 * Returns a string containing the unit for indenting HTML
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function _getTab()
	{
		return $this->_tab;
	}

	/**
	 * Load a renderer
	 *
	 * @param   string  $type  The renderer type
	 *
	 * @return  TDocumentRenderer  Object or null if class does not exist
	 *
	 * @since   5/2014
	 */
	public function loadRenderer($type)
	{
		$class = 'TDocumentRenderer' . $type;

		if (!class_exists($class))
		{
			$path = dirname(__FILE__) . '/' . $this->_type . '/renderer/' . $type . '.php';

			if (file_exists($path))
			{
				require_once $path;
			}
			else
			{
				TError::raiseError(500, Text::_('Unable to load renderer class'));
			}
		}

		if (!class_exists($class))
		{
			return null;
		}

		$instance = new $class($this);

		return $instance;
	}

	/**
	 * Parses the document and prepares the buffers
	 *
	 * @param   array  $params  The array of parameters
	 *
	 * @return  TDocument instance of $this to allow chaining
	 *
	 * @since   5/2014
	 */
	public function parse($params = array())
	{
		return $this;
	}

	/**
	 * Outputs the document
	 *
	 * @param   boolean  $cache   If true, cache the output
	 * @param   array    $params  Associative array of attributes
	 *
	 * @return  The rendered data
	 *
	 * @since   5/2014
	 */
	public function render($contents,$template='index')
	{
		if ($mdate = $this->getModifiedDate())
		{
			TResponse::setHeader('Last-Modified', $mdate /* gmdate('D, d M Y H:i:s', time() + 900) . ' GMT' */);
		}

		TResponse::setHeader('Content-Type', $this->_mime . ($this->_charset ? '; charset=' . $this->_charset : ''));
                $path = TPATH_TEMPLATES.DS.$template.'.php';
                if(!file_exists($path)) texit('Not exit template file');
                $session = T::getSession();
                ob_start();
                if(count($contents))
                    $cn['data'] = $contents;
                
                $cn['token'] = $session->getToken();
                require_once $path;
                $content = ob_get_contents();
                
                ob_end_clean();
                
                return $content;
	}
}
