<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type - Channel Entries - Date
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/content_types/importer.content_type.channel_entries/field_types/date.php
 */

class Importer_channel_entries_date
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
	 *	Date only allows a unix timestamp or a human readable form.  Since human readable is what
	 *	is expected by the custom field type, we only have to convert the unix timestamp into human
	 *	readable.  We do NOT allow multiple values.
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
		if ( ! isset($settings['field_id_'.$field_id.'_element']))
		{
			return FALSE;
		}


		if ( $preparsed === FALSE)
		{
			$data = Importer_actions::find_element($settings['field_id_'.$field_id.'_element'], $entry_data, FALSE);
		}
		else
		{
			$data = $entry_data;
		}

		if ( empty($data))
		{
			return FALSE;
		}

		$data = preg_replace('/\040+/', ' ', trim($data));

		if (preg_match("/^[0-9]+$/", $data, $match))
		{
			//EE 2.6+
			if (is_callable(array(ee()->localize, 'human_time')))
			{
				$data = ee()->localize->human_time($data);
			}
			else
			{
				$data = ee()->localize->set_human_time($data);
			}

		}
		// Valid human readable
		else if (ee()->localize->string_to_timestamp($data) === FALSE &&
			! preg_match('/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}\s[0-9]{1,2}:[0-9]{1,2}(?::[0-9]{1,2})?(?:\s[AP]M)?$/i', $data))
		{
			$data = '';
		}

		return $data;
	}
	// END parse_field()
}
// END Importer_channel_entries_field_type_date CLASS
