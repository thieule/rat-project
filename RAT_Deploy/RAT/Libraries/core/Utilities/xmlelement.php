<?php
/**
 * @package     T.Platform
 * @subpackage  Utilities
 */

defined('T_PLATFORM') or die;

/**
 * Wrapper class for php SimpleXMLElement.
 *
 * @package     T.Platform
 * @subpackage  Utilities
 * @since       5/2014
 */
class TXMLElement extends SimpleXMLElement
{
	/**
	 * Get the name of the element.
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function name()
	{
		TLog::add('TXMLElement::name() is deprecated, use SimpleXMLElement::getName() instead.', TLog::WARNING, 'deprecated');
		return (string) $this->getName();
	}

	/**
	 * Legacy method to get the element data.
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function data()
	{
		// Deprecation warning.
		TLog::add('TXMLElement::data() is deprecated.', TLog::WARNING, 'deprecated');

		return (string) $this;
	}

	/**
	 * Legacy method gets an elements attribute by name.
	 *
	 * @param   string  $name  Attribute to get
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 *
	 * @see           SimpleXMLElement::attributes
	 */
	public function getAttribute($name)
	{
		// Deprecation warning.
		TLog::add('TXMLelement::getAttributes() is deprecated.', TLog::WARNING, 'deprecated');

		return (string) $this->attributes()->$name;
	}

	/**
	 * Return a well-formed XML string based on SimpleXML element
	 *
	 * @param   boolean  $compressed  Should we use indentation and newlines ?
	 * @param   integer  $indent      Indention level.
	 * @param   integer  $level       The level within the document which informs the indentation.
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public function asFormattedXML($compressed = false, $indent = "\t", $level = 0)
	{
		TLog::add('TXMLElement::asFormattedXML() is deprecated, use SimpleXMLElement::asXML() instead.', TLog::WARNING, 'deprecated');
		$out = '';

		// Start a new line, indent by the number indicated in $level
		$out .= ($compressed) ? '' : "\n" . str_repeat($indent, $level);

		// Add a <, and add the name of the tag
		$out .= '<' . $this->getName();

		// For each attribute, add attr="value"
		foreach ($this->attributes() as $attr)
		{
			$out .= ' ' . $attr->getName() . '="' . htmlspecialchars((string) $attr, ENT_COMPAT, 'UTF-8') . '"';
		}

		// If there are no children and it contains no data, end it off with a />
		if (!count($this->children()) && !(string) $this)
		{
			$out .= " />";
		}
		else
		{
			// If there are children
			if (count($this->children()))
			{
				// Close off the start tag
				$out .= '>';

				$level++;

				// For each child, call the asFormattedXML function (this will ensure that all children are added recursively)
				foreach ($this->children() as $child)
				{
					$out .= $child->asFormattedXML($compressed, $indent, $level);
				}

				$level--;

				// Add the newline and indentation to go along with the close tag
				$out .= ($compressed) ? '' : "\n" . str_repeat($indent, $level);

			}
			elseif ((string) $this)
			{
				// If there is data, close off the start tag and add the data
				$out .= '>' . htmlspecialchars((string) $this, ENT_COMPAT, 'UTF-8');
			}

			// Add the end tag
			$out .= '</' . $this->getName() . '>';
		}

		return $out;
	}
}
