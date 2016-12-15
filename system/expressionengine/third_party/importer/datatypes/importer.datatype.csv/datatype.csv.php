<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing DataType - CSV
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/datatypes/importer.datatype.csv/datatype.csv.php
 */

require_once PATH_THIRD.'importer/datatype.importer.php';

class Importer_datatype_csv extends Importer_datatype
{
	public $version		= '1.0.0';

	public $default_settings = array();
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
		$fields = array(	'csv_delimiter' 			=> ';', // semicolon, tab, comma
							'csv_encloser'				=> '"',
							'first_record_column_names' => 'y');

		foreach($fields as $field => $default)
		{
			$this->cached_vars['importer_'.$field] = ( ! isset($settings[$field])) ? $default : $settings[$field];
		}

		return $this->view('profile_source_fields.html', NULL, TRUE, PATH_THIRD.'importer/datatypes/importer.datatype.csv/views/profile_source_fields.html');

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
		return array('csv_delimiter', 'csv_encloser', 'first_record_column_names');
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
		$fields = array(	'csv_delimiter' 			=> 'comma', // semicolon, tab, comma
							'csv_encloser'				=> '"',
							'first_record_column_names' => 'y');

        foreach($fields as $name => $default)
        {
			if ( ee()->input->post($name) === FALSE OR ee()->input->post($name) == '')
			{
				return lang('invalid_csv_field_submitted');
			}
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

		require_once PATH_THIRD.'importer/datatypes/importer.datatype.csv/libraries/Csv_reader.php';

		$config = array('first_record_column_names' => ($settings['first_record_column_names'] == 'n') ? FALSE : TRUE,
						'delimiter'					=> $settings['csv_delimiter'],
						'encloser'					=> $settings['csv_encloser']);

		$CR = new Csv_reader($config);

		$CR->data = $string;

		if (($return = $CR->parse()) === FALSE)
		{
			return FALSE;
		}

		return $this->serialize_data_array($return);
	}
	// END parse_data

}
// END CLASS Importer_datatype_csv