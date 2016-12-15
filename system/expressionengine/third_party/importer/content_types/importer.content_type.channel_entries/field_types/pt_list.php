<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type - Channel Entries - P&T List
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource 	importer/content_types/importer.content_type.channel_entries/field_types//pt_list.php
 */

class Importer_channel_pt_list
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
	public function parse_field($field_id, $field_data, $settings, $entry_data, $preparsed = FALSE)
	{
		if ( ! isset($settings['field_id_'.$field_id.'_element'])) return FALSE;

		if ($preparsed === FALSE)
		{
			$data = Importer_actions::find_element($settings['field_id_'.$field_id.'_element'], $entry_data, FALSE);
        }
        else
        {
        	$data = $entry_data;
        }

		return explode(PHP_EOL, $data);
	}
	// END parse_field()
}
// END Importer_channel_field_type_pt_list CLASS
