<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type - Channel Entries - Tag
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/content_types/importer.content_type.channel_entries/field_types/tag.php
 */

class Importer_channel_entries_tag extends Addon_builder_importer
{
	protected $solspace_tag;

	public $delimiters = array(
		'[array]'		=> 'array',
		'newline'		=> "\n",
		'comma'			=> ',',
		'pipe'			=> '|',
		'colon'			=> ':',
		'semicolon' 	=> ';',
		'space'			=> ' ',
		'tab'			=> "\t"
	);

	// --------------------------------------------------------------------

	/**
	 *	Constructor
	 *
	 *	@access		public
	 *	@return		string
	 */
	public function __construct()
	{
		parent::__construct('importer');
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
	 *	@return		string
	 */
	public function parse_field($field_id, $field_data, $settings, $entry_data, $preparsed = FALSE)
	{
		if ( ! isset($settings['field_id_'.$field_id.'_element']))
		{
			return FALSE;
		}

		if ($preparsed === FALSE)
		{
			$data = Importer_actions::find_element(
				$settings['field_id_'.$field_id.'_element'],
				$entry_data,
				TRUE
			);
		}
		else
		{
			$data = $entry_data;
		}

		if (empty($data))
		{
			//fall back on default if possible
			if (empty($settings['default_field_id_'.$field_id]))
			{
				return FALSE;
			}
			else
			{
				$data = $settings['default_field_id_'.$field_id];
			}
		}

		$delimiter =
				(isset($settings['field_id_'.$field_id.'_delimiter'])) ?
				$settings['field_id_'.$field_id.'_delimiter'] :
				'comma';

		if ( is_array($data) || $delimiter == '[array]')
		{
			// Needs to be specified as an array AND an array to happen
			if ( ! is_array($data) OR $delimiter != '[array]')
			{
				return FALSE;
			}
		}
		elseif (is_string($data))
		{
			$this->solspace_tag()->separator_override = $delimiter;
			$this->solspace_tag()->str = $data;

			$data = $this->solspace_tag()->str_arr();
		}

		if ( ! is_array($data))
		{
			return '';
		}

		array_walk($data, create_function('&$val', '$val = trim($val);'));
		$data = array_filter($data);

		return implode("\n", $data);
	}
	// END parse_field()


	// --------------------------------------------------------------------

	/**
	 *	Settings Form Row
	 *
	 *	Creates a row for this field in the Settings Form, complete with label, fields, and default
	 *
	 *	@access		public
	 *	@param		integer			// $field_id
	 *	@param		array			// Element Options
	 *	@param		array			// Settings, including value for field
	 *	@return		string
	 */
	public function settings_form_row($field_id, $field_data, $element_options, $settings)
	{
		$this->view_vars['field_id']		= $field_id;
		$this->view_vars['field_data']		= $field_data;
		$this->view_vars['element_options']	= $element_options;
		$this->view_vars['selected']		= $settings;

		$this->view_vars['delimiter_options'] = array_keys($this->delimiters);
		$this->view_vars['field_id_'.$field_id.'_delimiter'] =
			(isset($settings['field_id_'.$field_id.'_delimiter'])) ?
			$settings['field_id_'.$field_id.'_delimiter'] :
			'comma';

		// --------------------------------------------
		//  Create View File
		// --------------------------------------------

		return $this->view('field_types/display_form_tag.html', $this->view_vars, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 *	Setting Fields
	 *
	 *	The fields that are required by this field type - Used for saving
	 *
	 *	@access		public
	 *	@param		integer		$field_id
	 *	@return		array
	 */
	public function setting_fields($field_id)
	{
		// element and default handled automatically by channel_entries' save_settings()

		$settings['field_id_'.$field_id.'_delimiter'] =
			(isset($_POST['field_id_'.$field_id.'_delimiter'])) ?
			$_POST['field_id_'.$field_id.'_delimiter'] :
			'comma';

		return $settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Solspace Tag Instantiation.
	 *
	 * @access	private
	 * @return	object	tag object
	 */

	private function solspace_tag()
	{
		if ( ! is_object($this->solspace_tag))
		{
			require_once PATH_THIRD . 'tag/mod.tag.php';

			$this->solspace_tag = new Tag();
		}

		return $this->solspace_tag;
	}

}
// END Importer_channel_entries_matrix CLASS
