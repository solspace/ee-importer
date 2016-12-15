<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type - Channel Entries - Playa
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/content_types/importer.content_type.channel_entries/field_types/playa.php
 */

class Importer_channel_entries_playa
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
	 *	Return: Array ( [selections] => Array ( [0] => 500 [1] => 504 [2] => 506 ) )
	 *
	 *	There are two ways we expect this data.
	 *	 - A list of entry_ids, either in an array OR a list separated by comma, bar, or white space
	 *	 - The way the data is stored in exp_channel_data with [###] Entry Title - entry_url_title
	 *
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
			$data = Importer_actions::find_element($settings['field_id_'.$field_id.'_element'], $entry_data, TRUE);
        }
        else
        {
        	$data = $entry_data;
        }

		if ( $data === NULL )
		{
			return FALSE;
		}

		if (is_string($data))
		{
			$data = preg_split("/[\r\n,\|]+/", $data, -1, PREG_SPLIT_NO_EMPTY);
		}

		if ( ! is_array($data))
		{
			return array();
		}

		$return['selections'] = array();

		foreach($data as $value)
		{
			if (is_numeric($value))
			{
				$return['selections'][] = $value;
			}
			// [###] Entry Title - entry_url_title
			elseif(preg_match('/\[(\!)?(\d+)\]/', $value, $match))
			{
				$return['selections'][] = $match[2];
			}
		}

		return $return;
	}
	// END parse_field()
}
// END Importer_channel_entries_field_type_playa CLASS
