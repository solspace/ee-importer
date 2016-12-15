<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing DataType - JSON
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/datatypes/importer.datatype.json/datatype.json.php
 */

require_once PATH_THIRD.'importer/datatype.importer.php';

class Importer_datatype_json extends Importer_datatype
{
	public $version		= '1.0.0';

	public $default_settings = array('parse_json_element' => '');
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
		$fields = array('primary_json_element' 		=> '');

		foreach($fields as $field => $default)
		{
			$this->cached_vars['importer_'.$field] = ( ! isset($settings[$field])) ? $default : $settings[$field];
		}

		return $this->view('profile_source_fields.html', NULL, TRUE, PATH_THIRD.'importer/datatypes/importer.datatype.json/views/profile_source_fields.html');
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
		return array('primary_json_element');
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
		$json_array = $this->json_decode($string, TRUE);

		if ( ! is_array($json_array)) return FALSE;

		// --------------------------------------------
        //  Find Items?
        // --------------------------------------------

        if ( ! empty($settings['primary_json_element']))
        {
        	$json_array = $this->find_json_element_array($settings['primary_json_element'],
											   			$json_array);

			//var_dump($json_array);exit;

			if ( ! is_array($json_array)) return FALSE;
		}

		return $this->serialize_data_array($json_array);
	}
	// END parse_data()

	// --------------------------------------------------------------------

	/**
	 *	Find JSON Element
	 *
	 *	Finds an JSON Element, first occurrence detected, and returns it.
	 *
	 *	@access		public
	 *	@param		string	$element - That for which we hunt
	 *	@param		array	$parsed - Array of parsed XML elements
	 *	@return		array|bool
	 */

	function find_json_element_array( $element, $parsed )
	{
		if ( empty( $parsed )) return FALSE;

		if (isset($parsed[$element])) return $parsed[$element];

		foreach ( $parsed as $key => $val )
		{
			if ( is_array( $val ) )
			{
				$return = $this->find_json_element_array( $element, $val);

				if ($return !== FALSE)
				{
					return $return;
				}
			}
		}

		return FALSE;
	}
	// END find_json_element_array()

}
// END CLASS Importer_datatype_json