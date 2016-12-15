<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing DataType - XML
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/datatypes/importer.datatype.xml/datatype.xml.php
 */

require_once PATH_THIRD.'importer/datatype.importer.php';

class Importer_datatype_xml extends Importer_datatype
{
	public $version		= '1.0.0';

	public $default_settings = array('parse_xml_element' => '', 'xml_parsing_strictness' => 'strict');
	public $settings		 = array();

	// --------------------------------------------------------------------

	/**
	 *	Constructor
	 *
	 *	@access		public
	 *	@return		string
	 */
	public function __construct()
	{
		parent::__construct();
	}
	// END constructor

	// --------------------------------------------------------------------

	/**
	 *	Returns Any Additional Profile Source Fields Required for this Data Type
	 *
	 *	@access		public
	 *	@param		array	$settings - Current settings
	 *	@return		string
	 */

	public function profile_source_fields_form(array $settings = array())
	{
		$fields = array(	'primary_xml_element' 		=> '',
							'xml_parsing_strictness'	=> '');

		foreach($fields as $field => $default)
		{
			$this->cached_vars['importer_'.$field] = ( ! isset($settings[$field])) ? $default : $settings[$field];
		}

		return $this->view('profile_source_fields.html', NULL, TRUE, PATH_THIRD.'importer/datatypes/importer.datatype.xml/views/profile_source_fields.html');
	}
	// END profile_source_fields_form()


	// --------------------------------------------------------------------

	/**
	 *	Returns Any Additional Profile Source Fields Required for this Data Type
	 *
	 *	@access		public
	 *	@return		array
	 */

	public function profile_source_fields()
	{
		return array('primary_xml_element', 'xml_parsing_strictness');
	}
	// END profile_source_fields()

	// --------------------------------------------------------------------

	/**
	 *	Validate Incoming Source Fields
	 *
	 *	@access		public
	 *	@return		bool|
	 */

	public function validate_profile_source_fields()
	{
		$settings = array(	'primary_xml_element' 		=> '',
							'xml_parsing_strictness'	=> '');

		if ( ee()->input->post('primary_xml_element') === FALSE OR ee()->input->post('primary_xml_element') == '')
		{
			return lang('invalid_primary_xml_element_submitted');
		}

		 if ( ee()->input->post('xml_parsing_strictness') === FALSE OR
			 ! in_array(ee()->input->post('xml_parsing_strictness'), array('strict', 'loose')))
		{
			return lang('invalid_xml_parsing_strictness_submitted');
		}

		return TRUE;
	}
	// END profile_source_fields()

	// --------------------------------------------------------------------

	/**
	 *	Parse Data
	 *
	 *	@access		public
	 *	@param		string		$string - Data to be parsed
	 *	@param		integer		$offset - Offset this number of items from the beginning
	 *	@param		integer		$limit - How many items shall we process?
	 *	@return		array
	 */
	public function parse_data($string = '', $settings = array(), $offset = 0, $limit = 500)
	{
		if ( ! is_string($string) OR empty($settings))
		{
			return FALSE;
		}

		if (strpos($string, '<') === FALSE)
		{
			return FALSE;
		}

		// --------------------------------------------
		//  XML Strict - SimpleXML
		// --------------------------------------------

		libxml_use_internal_errors(true);

		if ($settings['xml_parsing_strictness'] == 'strict' && function_exists('simplexml_load_string'))
		{
			$xml_object = simplexml_load_string($string, NULL, LIBXML_NOCDATA);

			if ( ! is_object($xml_object)) return FALSE;

			$namespaces = (array) $xml_object->getDocNamespaces();

			// ->xpath allows to do a direct search for the element we want.  Very handy.
			$xml_array = $xml_object->xpath('//'.$settings['primary_xml_element']);

			if ( empty($xml_array)) return FALSE;

			// The JSON nonsense here takes the object-array that we get back from SimpleXML
			// and converts it entirely into arrays, which is what all of our content types expect.
			// https://bugs.php.net/bug.php?id=38604 (explain why we are using a for() loop)

			$new_array = array();

			for($i = 0, $s = count($xml_array); $i < $s; $i++)
			{
				// --------------------------------------------
				//  Namespaced Vars!
				// --------------------------------------------

				foreach($namespaces as $namespace => $schema)
				{
					$namespaced_children = (array) $xml_array[$i]->children($schema);

					if ( ! empty($namespaced_children))
					{
						foreach($namespaced_children as $ckey => $cvalue)
						{
							//below causes error 'It is not yet possible to assign complex types to properties'
							//$xml_array[$i]->{$namespace.':'.$ckey} = $cvalue;

							//solution by Joann
							if (is_array($cvalue))
							{
								$cvalue = implode(" ", $cvalue);
							}

							$xml_array[$i]->addChild($ckey, $cvalue, $namespace);

							// Does not work, boo hoo -PB
							//$xml_array[$i]->addChild($namespace.':'.$ckey, $cvalue);
						}
					}
				}

				// --------------------------------------------
				//  Check Subelements for Attributes
				//  - The JSON trick below that converts the SimpleXMLElement objects into an
				//  array seems to ignore the @attributes, so we have to add them as children ourself.
				// --------------------------------------------

				foreach($xml_array[$i] AS $key => $value)
				{
					if ( is_object($value))
					{
						$attrs = (array) $value->attributes();

						if ( ! empty($attrs['@attributes']))
						{
							foreach($attrs['@attributes'] as $akey => $avalue)
							{
								$xml_array[$i]->addChild($key.'@'.$akey, $avalue);
							}
						}

						// Give the kids some attributes too, but no recursion beyond this point.
						foreach($value->children() as $ckey => $child)
						{
							if ( is_object($child))
							{
								$attrs = (array) $child->attributes();

								if ( ! empty($attrs['@attributes']))
								{
									foreach($attrs['@attributes'] as $akey => $avalue)
									{
										$value->addChild($ckey.'@'.$akey, $avalue);
									}
								}
							}
						}
					}
				}

				// --------------------------------------------
				//  This converts the XML into a convenient array
				// --------------------------------------------

				$new_array[$i] = serialize(json_decode(json_encode($xml_array[$i]), 1));
			}

			return $new_array;
		}

		// --------------------------------------------
		//  XML Strict - EE's XMLParser Library
		// --------------------------------------------

		if ($settings['xml_parsing_strictness'] == 'strict')
		{
			ee()->load->library('xmlparser');
			$parsed = ee()->xmlparser->parse_xml( $string );

			if ($parsed === FALSE) return FALSE;

			$xml_array = $this->find_xml_tag_array($settings['primary_xml_element'],
												   $this->xmlparser_element_array($parsed));

			if ( ! is_array($xml_array)) return FALSE;

			return $this->serialize_data_array($xml_array);
		}

		// --------------------------------------------
		//  XML Loose
		//	- Mitchell's custom parser built for the previous version
		// --------------------------------------------

		$parsed = $this->parse_loose_xml( $string, $settings['primary_xml_element'], 2);

		if ( empty($parsed[$settings['primary_xml_element']]))
		{
			return FALSE;
		}

		return $this->serialize_data_array($parsed[$settings['primary_xml_element']]);
	}
	// END parse_data()

	// --------------------------------------------------------------------

	/**
	 *	SimpleXML Element Array
	 *
	 *	Loops through a SimpleXML object converting to arrays and strings.  Replaced this function
	 *	with the faster: json_decode(json_encode($xml_array), 1);
	 *
	 *	@access		public
	 *	@param		array	$parsed	 - Parsed data array
	 *	@return		string
	 */

	function simplexml_element_array($parsed)
	{
		if ( $parsed->children() == FALSE)
		{
			return (string) $parsed;
		}

		$return = array();

		foreach ( $parsed->children() as $child )
		{
			$name = $child->getName();

			if (count($parsed->$name) == 1)
			{
				$return[$name] = $this->simplexml_element_array($child);
			}
			else
			{
				$return[$name][] = $this->simplexml_element_array($child);
			}
		}

		return $return;
	}
	// END simplexml_element_array()

	// --------------------------------------------------------------------

	/**
	 *	XMLParser Element Array
	 *
	 *	Loops through a XMLParser object converting to arrays and strings
	 *
	 *	@access		public
	 *	@param		array	$parsed	 - Parsed data array
	 *	@return		string
	 */

	function xmlparser_element_array($parsed, $first = TRUE)
	{
		if ( empty($parsed->children))
		{
			$return = $parsed->value;
		}
		else
		{
			$return = array();
			foreach($parsed->children as $child)
			{
				// Because of how the EE does its XML Object structre, we will have an
				// array of children, not organized by name or anything. So, we need to suss
				// out the names and creates arrays ourself.

				if ( isset($return[$child->tag]))
				{
					if ( ! is_array($return[$child->tag]) OR ! isset($return[$child->tag][0]))
					{
						$return[$child->tag] = array(0 => $return[$child->tag]);
					}

					$return[$child->tag][] = $this->xmlparser_element_array($child, FALSE);
				}
				else
				{
					$return[$child->tag] = $this->xmlparser_element_array($child, FALSE);

					// --------------------------------------------
					//  Check Children for Their Attributes
					// --------------------------------------------

					if ( ! empty($child->attributes))
					{
						foreach($child->attributes as $akey => $avalue)
						{
							$return[$child->tag.'@'.$akey] = $avalue;
						}
					}
				}
			}
		}

		if ( $first === FALSE) return $return;

		// --------------------------------------------
		//  First Element Could Have Attributes
		// --------------------------------------------

		$return = array($parsed->tag => $return);

		if ( ! empty($parsed->attributes))
		{
			foreach($parsed->attributes as $akey => $avalue)
			{
				$return[$parsed->tag.'@'.$akey] = $avalue;
			}
		}

		return $return;
	}
	// END xmlparser_element_array()

	// --------------------------------------------------------------------

	/**
	 *	Parse Loose XML
	 *
	 *	@access		public
	 *	@param		string	$content - The content to be parsed
	 *	@return		array
	 */
	public function parse_loose_xml( $data, $element, $levels = 2, $sub = FALSE )
	{
		// --------------------------------------------
		//  Multiple Levels of Elements
		// --------------------------------------------

		if ( stristr($element, '/'))
		{
			$return = array();
			$x		= explode('/', $element);

			foreach($x as $part)
			{
				$result = $this->parse_loose_xml($data, $part);

				if ( empty($result)) break;

				$return = $data = array_shift($result);

				unset($result);
			}

			return array($element => $return);
		}

		/*
			The result will take the form of the below
			[primary_tag] => Array
				(
					[0]	=> Array
						(
							[some_tag]	=> some value
							[some_tag]	=> some value
							[some_tag]	=> some value

						)
					[1]	=> Array
						(
							[some_tag]	=> some value
							[some_tag]	=> some value
							[some_tag]	=> some value
						)
				)
		*/

		$xml_array	= array();

		// --------------------------------------------
		//  Find all of the element - Ex: item
		// --------------------------------------------

		if ( preg_match_all( "/<".$element."(\s.+?)*>(.*?)<\/".$element.">/s", (string) $data, $match ) )
		{
			$records	= $match[2];

			foreach ( $records as $key => $val )
			{
				$attrs = array();

				// --------------------------------------------
				//  Got an Element with Tags Inside
				// --------------------------------------------

				if ( $levels > 0 && preg_match( "/<\/(.*?)>/s", $val))
				{
					$total = 0;

					// Searches backwards from the end of the string.  That way we can effectively
					// find all of the children of this parent, but not parse their grandchildren
					// EX: <parent><child><grandchild></grandchild></child></parent>

					// The consquence of this is that we have to do two array_reverse()'s later on
					// to get the correct order of children within a parent.

					// My original regex tended to be too greedy in some cases, so I switched to
					// preg_match_all() and an array_pop();

					// -Paul "Who Made this Mess?!" Burdick

					while(preg_match_all("/<\/(.*?)>/", $val, $subelements, PREG_SET_ORDER) && $total < 40)
					{
						$subelement = array_pop($subelements);
						$return[$subelement[1]] = $this->parse_loose_xml( $val, $subelement[1], $levels - 1, TRUE);

						// --------------------------------------------
						//  Invalid Element == FALSE
						// --------------------------------------------

						if ($return[$subelement[1]] === FALSE)
						{
							// Unset element
							unset($return[$subelement[1]]);

							// Remove closing tag, find next closing tag, and remove everything after it.
							$val = preg_replace("/(<\/(.*?)>).*?$/s", '\\1', str_replace($subelement[0], '', $val));
						}
						else
						{
							preg_match( "/<".preg_quote($subelement[1], '/')."(\s.+?)?>(.*?)<\/".$subelement[1].">/s", $val, $pieces);

							// Attributes have been found!
							if (trim($pieces[1]) != '')
							{
								$attrs = ee()->functions->assign_parameters($pieces[1]);

								foreach($attrs AS $akey => $avalue)
								{
									$return[$subelement[1].'@'.$akey] = $avalue;
								}
							}

							$val = preg_replace( "/<".$subelement[1]."(\s.+?)*>(.*?)<\/".$subelement[1].">/s", '', $val);
						}

						$total++;
					}

					// Reverse the array of children so that we have them in the proper order
					// as we parsed them backwards so we did not catch grandchildren in the mix.
					if ($sub === TRUE)
					{
						$return = array_reverse($return);
					}
				}
				else
				{
					// --------------------------------------------
					//  Remove CDATA Tags or convert entites back
					// --------------------------------------------

					if ( strpos( $val, '<![CDATA[' ) !== FALSE )
					{
						$val = str_replace( array( '<![CDATA[', ']]>' ), '', $val );
					}
					elseif ( strpos( $val, '&lt;' ) !== FALSE )
					{
						if (function_exists('htmlspecialchars_decode'))
						{
							$val = htmlspecialchars_decode($val);
						}
						else
						{
							$val = strtr($val, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
						}
					}

					if (count($records) == 1 && $sub === TRUE)
					{
						return $val;
					}

					// --------------------------------------------
					//  For elements below the parent, we include a key.
					// --------------------------------------------

					if ($sub === FALSE)
					{
						$return = $val;
					}
					else
					{
						$return[$key] = $val;
					}
				}

				// --------------------------------------------
				//  For the first PRIMARY element, we include the element name
				// --------------------------------------------

				if ($sub === FALSE)
				{
					$xml_array[$element][$key] = (is_array($return)) ? array_reverse($return) : $return;
				}
				else
				{
					$xml_array = $return;
				}

			}
		}
		else
		{
			return FALSE;
		}

		// Array Reverse required as we parse backwards to find out XML Elements
		return $xml_array;
	}
	// END parse_loose_xml()

	// --------------------------------------------------------------------

	/**
	 *	Find XML Element
	 *
	 *	Finds an XML Element, first occurrence detected, and returns it.
	 *
	 *	@access		public
	 *	@param		string	$element - That for which we hunt
	 *	@param		array	$parsed - Array of parsed XML elements
	 *	@return		array|bool
	 */

	function find_xml_tag_array( $element, $parsed )
	{
		if ( empty( $parsed )) return FALSE;

		if (isset($parsed[$element])) return $parsed[$element];

		foreach ( $parsed as $key => $val )
		{
			if ( is_array( $val ) )
			{
				$return = $this->find_xml_tag_array( $element, $val);

				if ($return !== FALSE)
				{
					return $return;
				}
			}
		}

		return FALSE;
	}
	// END find_xml_tag_array()

}
// END CLASS Importer_datatype_xml