<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type - Channel Entries - Matrix
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/content_types/importer.content_type.channel_entries/field_types/matrix.php
 */

class Importer_channel_entries_matrix extends Addon_builder_importer
{
	private $third_party_cell_types = array('date', 'playa');

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

		if ( ! isset($this->cache['matrix_used']) OR ! is_array($this->cache['matrix_used']))
		{
			$this->cache['matrix_used'] = array();
		}
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
		$cols = $this->_get_field_cols($field_id);

		if ( ! $cols)
		{
			return FALSE;
		}

		// Matrix does not do preparsed data
		if ($preparsed === TRUE) return FALSE;

		$rows	= array();
		$order 	= array();

		// --------------------------------------------
        //  Cycle Through the Columns for this Field.  Find Data.  Parse.  Create Rows.
        // --------------------------------------------

		foreach($cols as &$col)
		{
			$element_id = 'field_id_'.$field_id.'_col_id_'.$col['col_id'];

			if ( ! isset($settings[$element_id])) continue;
			$element = Importer_actions::find_element($settings[$element_id], $entry_data, TRUE);

			if ($element === NULL) continue;

			$i = 1;

			// --------------------------------------------
			//  We will either get an array of items or a single one
			// --------------------------------------------

			if ( is_array($element) && isset($element[0]))
			{
				foreach($element as $col_data)
				{
					// Custom field type, so we need to load it and let it parse the data
					if (($obj = $this->load_field_type($col['col_type'])) !== FALSE && is_callable(array($obj, 'parse_field')))
					{
						$col_data = $obj->parse_field($field_id, $field_data, $settings, $col_data, TRUE);
					}

					// Create our rows and columns
					$order['row_new_'.$i] = 'row_new_'.$i;
					$rows['row_new_'.$i]['col_id_'.$col['col_id']] = $col_data;
					$i++;
				}
			}
			else
			{
				// Custom field type, so we need to load it and let it parse the data
				if (($obj = $this->load_field_type($col['col_type'])) !== FALSE && is_callable(array($obj, 'parse_field')))
				{
					$element = $obj->parse_field($field_id, $field_data, $settings, $element, TRUE);
				}

				// Create a single row for these columns
				$order['row_new_1'] = 'row_new_1';
				$rows['row_new_1']['col_id_'.$col['col_id']] = $element;
			}
		}

		// Seems that no data was found in the import at all...
		if (count($rows) == 0) return FALSE;

		$rows['row_order'] = array_values($order);

		// --------------------------------------------
        //  Matrix Field Set
        //	- Used by Channel Content Type to know what fields to clean out on update of entry
        // --------------------------------------------

		$this->cache['matrix_used'][] = $field_id;

		return $rows;
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
        $this->view_vars['selected']		= array();

		// --------------------------------------------
        //  First, We Find the Cols data for Matrix
        // --------------------------------------------

		$cols = $this->_get_field_cols($field_id);

		if ( ! $cols)
		{
			$this->view_vars['error'] = lang('Matrix field not configured in Custom Field settings.');
			return $this->view('field_types/display_form_matrix.html', $this->view_vars, TRUE);
		}

		// --------------------------------------------
        //  Then We Create Form Elements for Each One
        // --------------------------------------------

        $this->view_vars['matrix_columns'] = array();

		foreach ($cols as $col)
		{
			$col_field_id = 'field_id_'.$field_id.'_col_id_'.$col['col_id'];

			if ($col['col_label'] == '')
			{
				$col['col_label'] = '-no label-';
			}

			foreach($col as $key => $val)
			{
				$this->view_vars['matrix_columns'][$col_field_id][str_replace('col_','', $key)] = $val;
				$this->view_vars['selected'][$col_field_id] = (isset($settings[$col_field_id])) ? $settings[$col_field_id] : '';
			}
		}

		// --------------------------------------------
        //  Create View File
        // --------------------------------------------

		return $this->view('field_types/display_form_matrix.html', $this->view_vars, TRUE);
	}
	// END settings_form_row()


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
		// --------------------------------------------
        //  First, We Find the Cols data for Matrix
        // --------------------------------------------

		$cols = $this->_get_field_cols($field_id);

		if ( ! $cols)
		{
			return array();
		}

		// --------------------------------------------
        //  Then We Create Form Elements for Each One
        // --------------------------------------------

        $matrix_columns = array();

		foreach ($cols as $col)
		{
			$matrix_columns['field_id_'.$field_id.'_col_id_'.$col['col_id']] = '';
		}

		return $matrix_columns;
	}
	// END setting_fields()

	// --------------------------------------------------------------------

	/**
	 *	Save Settings Form Row
	 *
	 *	@access		public
	 *	@param		integer			// $field_id
	 *	@param		array			// Element Options
	 *	@param		array			// Settings, including value for field
	 *	@return		string
	 */
	public function save_settings_form_row($field_id, $field_data, $element_options, $settings)
	{
		// --------------------------------------------
        //  First, We Find the Cols data for Matrix
        // --------------------------------------------

		$cols = $this->_get_field_cols($field_id);

		if ( ! $cols) return lang('Matrix field not configured in Custom Field settings.');

		// --------------------------------------------
        //  Then We Create Form Elements for Each One
        // --------------------------------------------

        $this->cached_vars['matrix_columns'] = '';

		foreach ($cols as $col)
		{
			$col_field_id = $field_id.'_col_id_'.$col['col_id'];

			if ($col['col_label'] == '')
			{
				$col['col_label'] = '-no label-';
			}

			foreach($col as $key => $val)
			{
				$this->cached_vars['matrix_columns'][$col_field_id][str_replace('col_','', $key)] = $val;
			}
		}

		// --------------------------------------------
        //  Create View File
        // --------------------------------------------

        $this->view_vars['field_id']		= $field_id;
        $this->view_vars['field_data']		= $field_data;
        $this->view_vars['element_options']	= $element_options;

		return $this->view('field_types/display_form_matrix.html', $this->view_vars, TRUE);
	}
	// END settings_form_row()

	// --------------------------------------------------------------------

	/**
	 *	Process the Row (Fields/Default) for this Custom Field
	 *
	 *	@access		public
	 *	@return		string
	 */
	public function process_settings_form_row()
	{
		return 'DOOM!';
	}
	// END process_settings_form_row()

	// --------------------------------------------------------------------

	/**
	 * Get Field Cols
	 */
	private function _get_field_cols($field_id)
	{
		if (! isset($this->cache['field_cols'][$field_id]))
		{
			$query = ee()->db->select('col_id, col_type, col_label, col_name, col_instructions, col_width, col_required, col_search')
			                       ->where('field_id', $field_id)
			                       ->order_by('col_order')
			                       ->get('matrix_cols');

			$this->cache['field_cols'][$field_id] = ($query->num_rows() == 0) ? array() : $query->result_array();;
		}

		return $this->cache['field_cols'][$field_id];
	}
	// END _get_field_cols()

	// --------------------------------------------------------------------

	/**
	 *	Instantiate Custom Field Type File
	 *
	 *	@access		public
	 *	@param		string	$field_type
	 *	@return		string
	 */
	private function load_field_type($field_type = '')
	{
		if (empty($field_type) OR ! in_array($field_type, $this->third_party_cell_types))
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Load the Class and Object for Field Type
		// --------------------------------------------

		$field_type_class	 = 'Importer_channel_entries_'.$field_type;
		$field_type_filename = $field_type.'.php';
		$field_type_path	 = PATH_THIRD.'importer/content_types/importer.content_type.channel/field_types/'.$field_type_filename;

		// Instantiate and cache object
		if ( ! isset($this->cache['field_type_objects'][$field_type]))
		{
			if (file_exists($field_type_path))
			{
				require_once $field_type_path;
			}

			if (class_exists($field_type_class))
			{
				$this->cache['field_type_objects'][$field_type] = new $field_type_class;
			}
		}

		// Insure that we actually have what we need.
		if ( ! isset($this->cache['field_type_objects'][$field_type]) OR
			 ! is_object($this->cache['field_type_objects'][$field_type]))
		{
			return FALSE;
		}

		return $this->cache['field_type_objects'][$field_type];
	}
	// END load_field_type

}
// END Importer_channel_entries_matrix CLASS
