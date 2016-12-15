<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type - Channel Entries - Assets
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/content_types/importer.content_type.channel_entries/field_types/assets.php
 */

class Importer_channel_entries_assets
{
	// --------------------------------------------------------------------

	/**
	 *	Constructor
	 *
	 *	@access		public
	 *	@return		string
	 */
	public function __construct()
	{
		$this->EE = get_instance();
	}
	// END constructor

	// --------------------------------------------------------------------

	/**
	 *	Parses the Field and Returns in Correct Format to send to Channel Entries API
	 *
	 *	@access		public
	 *	@param		integer
	 *	@param		array			// $field_data
	 *	@param		array			// $settings
	 *	@param		string|array	// The array of data for this entry
	 *	@param		boolean			// Whether the $entry_data is preparsed for us
	 *	@return		string
	 */
	public function parse_field($field_id, $field_data, $settings, $entry_data, $preparsed = FALSE, $entry_id = FALSE)
	{
	    // We are currently only preventing overwriting on UPDATE of an entry
	    if ( $preparsed === FALSE) return FALSE;

	    if ( ee()->db->table_exists('exp_assets_selections') === TRUE)
	    {
	        $query = ee()->db->get_where('exp_assets_selections', array('entry_id' => $entry_id));
	    }
	    else
	    {
	        $query = ee()->db->get_where('exp_assets_entries', array('entry_id' => $entry_id));
	    }

	    if ( $query->num_rows() == 0) return '';

	    $return = array();

	    foreach($query->result_array() AS $row)
	    {
	        $return[] = (isset($row['file_id'])) ? $row['file_id'] : $row['asset_id'];
	    }

	    return $return;
	}
	// END parse_field()
}
// END Importer_channel_entries_field_type_assets CLASS
