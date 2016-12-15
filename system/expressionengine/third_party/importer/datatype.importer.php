<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Datatype
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.6
 * @filesource	importer/datatype.importer.php
 */

require_once PATH_THIRD.'importer/addon_builder/module_builder.php';

class Importer_datatype extends Module_builder_importer
{
	public $label		= '';
	public $name		= '';
	public $version		= '0.0.0';

	public $default_settings = array();
	public $settings		 = array();

	public $first_party	  = TRUE;

	public $data_sources  = array(); // Allowed data sources. If empty, uses default File, Upload, S/FTP
	public $batch_enabled = FALSE;   // Does this datatype have its own data source that can do batches? Requires a count method to return number of results.

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

		$this->name = str_replace('Importer_datatype_', '', get_class($this));

		$this->first_party = (is_dir(PATH_THIRD.'importer/datatypes/importer.datatype.'.$this->name.'/'));

		if ($this->first_party === TRUE)
		{
			ee()->lang->load('datatype.'.$this->name, ee()->lang->user_lang, FALSE, TRUE, PATH_THIRD.'importer/datatypes/importer.datatype.'.$this->name.'/');
		}
		else
		{
			ee()->lang->load('datatype.'.$this->name, ee()->lang->user_lang, FALSE, TRUE, PATH_THIRD.'importer.datatype.'.$this->name.'/');
		}

		$this->label = lang('datatype_'.$this->name.'_label');
	}
	// END constructor

	// --------------------------------------------------------------------

	/**
	 *	Returns Form for Additional Source Fields
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function profile_source_fields_form()
	{
		return '';
	}
	// END profile_source_fields_form()

	// --------------------------------------------------------------------

	/**
	 *	Validation of Data Source Fields
	 *
	 *	@access		public
	 *	@return		bool|string
	 */

	function validate_profile_source_fields()
	{
		return TRUE;
	}
	// validate_profile_source_fields

	// --------------------------------------------------------------------

	/**
	 *	Returns Any Additional Profile Source Fields Required for this Data Type
	 *
	 *	@access		public
	 *	@return		array
	 */

	public function profile_source_fields()
	{
		return array();
	}
	// END profile_source_fields()


	// --------------------------------------------------------------------

	/**
	 *	Settings Form
	 *
	 *	@access		public
	 *	@param		array		// Default Values
	 *	@return		string
	 */
	public function settings_form(array $values = array())
	{
		return '';
	}
	// END settings_form

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
	public function parse_data($string = '', $offset = 0, $limit = 500)
	{

	}
	// END parse_data


	// --------------------------------------------------------------------

	/**
	 *	Serialize Data Array
	 *
	 *	Takes the array of data and serializes each row to conserve memory.  It has become apparent
	 *	that PHP uses an ungodly large amount of memory to store even the simplest associative array.
	 *	Something about opcodes and memory storage.  However, serializing this data greatly reduces
	 *	this overhead.  So, that is how we are now storing our rows of data. Constant vigilance!
	 *
	 *	@access		public
	 *	@param		array
	 *	@return		string
	 */

	public static function serialize_data_array(&$data)
	{
	    if ( ! is_array($data)) return FALSE;

		foreach($data as &$item)
		{
			$item = serialize($item);
		}

		return $data;
	}
	// END serialize_data_array()

}
// END CLASS Importer_datatype