<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type - Playa
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/content_types/importer.content_type.playa/content_type.playa.php
 */

require_once PATH_THIRD.'importer/content_type.importer.php';

class Importer_content_type_playa extends Importer_content_type
{
	public  $version		    = '1.0.0';

	public $allowed_datatypes   = array('relationship_fields', 'playa_fields');

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
	 *	Settings Form
	 *
	 *	@access		public
	 *  @param      array       // First row of data
	 *	@param		array		// Stored settings for profile_id
	 *	@return		string
	 */
	public function settings_form(array $options, array $settings)
	{
		// --------------------------------------------
		//  Typical Validation and Set Up
		// --------------------------------------------

		if ( empty($options) OR ! is_array($options))
		{
			return $this->error_page('invalid_data_received');
		}

		if ($settings['datatype'] == 'playa_fields')
		{
			if ( empty($settings['playa_fields']) OR empty($settings['playa_fields_channel']))
			{
				return $this->error_page('no_valid_playa_fields_selected');
			}

			$fields                 = preg_split("/[\r\n,\|]+/", $settings['playa_fields'], -1, PREG_SPLIT_NO_EMPTY);
			$importing_channel_id   = $settings['playa_fields_channel'];

			if ( empty($fields)) return $this->error_page('no_valid_playa_field_selected');
		}
		elseif($settings['datatype'] == 'relationship_fields')
		{
			if ( empty($settings['relationship_fields']) OR empty($settings['relationship_fields_channel']))
			{
				return $this->error_page('no_valid_relationship_fields_selected');
			}

			$fields                 = preg_split("/[\r\n,\|]+/", $settings['relationship_fields'], -1, PREG_SPLIT_NO_EMPTY);
			$importing_channel_id   = $settings['relationship_fields_channel'];

			if ( empty($fields)) return $this->error_page('no_valid_relationship_fields_selected');
		}

		// --------------------------------------------
		//  First.  Check for Playa Installed
		// --------------------------------------------

		if ( ! ee()->db->table_exists('exp_playa_relationships') )
		{
			return $this->error_page('playa_40_is_required_for_importing');
		}

		// --------------------------------------------
		//  Second. Insure they have at least one Playa field type.
		// --------------------------------------------

		$query = ee()->db->query("SELECT field_id, field_label, field_settings, c.channel_id
								  FROM exp_channel_fields AS cf, exp_channels AS c, exp_field_groups AS fg
								  WHERE c.field_group = fg.group_id
								  AND fg.group_id = cf.group_id
								  AND cf.field_type = 'playa'
								  AND c.channel_id = '".ee()->db->escape_str($importing_channel_id)."'
								  ORDER BY field_label");

		if ($query->num_rows() == 0)
		{
			return $this->error_page('playa_channel_custom_fields_required');
		}

		foreach($query->result_array() AS $row)
		{
			$vars['playa_fields']['field_id_'.$row['field_id']] = $row['field_label'];
		}

		// --------------------------------------------
		//  Looking Good.  Let's Grab the Relationship Fields
		// --------------------------------------------

		$xsql = array();

		foreach($fields as $field_id)
		{
			$xsql[] = "(cf.field_id = '".ee()->db->escape_str(str_replace('field_id_', '', $field_id))."')";
		}

		if (count($xsql) == 0)
		{
			return $this->error_page('playa_selected_custom_fields_required');
		}

		$query = ee()->db->query("SELECT field_id, field_label, field_settings, c.channel_id
								  FROM exp_channel_fields AS cf, exp_channels AS c, exp_field_groups AS fg
								  WHERE c.field_group = fg.group_id
								  AND fg.group_id = cf.group_id
								  AND c.channel_id = '".ee()->db->escape_str($importing_channel_id)."'
								  AND (".implode(" OR ", $xsql).")
								  ORDER BY field_label");

		if ($query->num_rows() == 0)
		{
			return $this->error_page('playa_selected_custom_fields_required');
		}

		// --------------------------------------------
		//  Possible Options
		// --------------------------------------------

		$vars['channels']	= $this->data->get_channels_per_site();

		foreach($query->result_array() AS $row)
		{
			$vars['importing_fields'][$row['channel_id']]['field_id_'.$row['field_id']] = $row['field_label'];
		}

		foreach($this->data->get_channels_per_site() as $site_id => $channels)
		{
			foreach($channels as $channel_id => $channel_title)
			{
				$vars['channel_titles'][$channel_id] = $channel_title;
			}
		}

		// --------------------------------------------
		//  Default Sorted Options
		// --------------------------------------------

		$vars['selected_importing_fields_channel']       = $importing_channel_id;
		$vars['selected_importing_fields_import_order']  = array();

		foreach($vars['importing_fields'] AS $channel_id => $fields)
		{
			foreach($fields as $field_id => $field_label)
			{
				$vars['selected_importing_fields_import_order'][$field_id] = $field_label;
			}
		}

		// --------------------------------------------
		//  Selected Sorted Options
		// --------------------------------------------

		if ( ! empty($settings['importing_fields_import_order']))
		{
			$new     = array();
			$current = explode('|', $settings['importing_fields_import_order']);

			foreach($current AS $value)
			{
				if ( isset($vars['selected_importing_fields_import_order'][$value]))
				{
					$new[$value] = $vars['selected_importing_fields_import_order'][$value];
					unset($vars['selected_importing_fields_import_order'][$value]);
				}
			}

			// Take the current, tack on any new items not there.
			$vars['selected_importing_fields_import_order'] =
				array_merge($new, $vars['selected_importing_fields_import_order']);
		}

		// --------------------------------------------
		//  Current Playa Field
		// --------------------------------------------

		$vars['selected_playa_field']  = ( isset($settings['playa_field'])) ?
											$settings['playa_field'] : '';

		// --------------------------------------------
		//  Return the Settings Form
		// --------------------------------------------

		ee()->cp->add_js_script(array( 'ui' => 'sortable'));

		$this->add_crumb(lang('importer_playa_settings_form').' - '.$settings['profile_name']);
		$this->build_crumbs();
		$this->cached_vars['module_menu_highlight'] = 'module_homepage';

		//jQuery will soon only load at the end of the page after EE 2.6
		ee()->cp->add_to_foot(
			'<script src="' . $this->importer_theme_url .
				'js/content_types/playa.js"></script>'
		);

		return $this->view(
			'settings_form.html',
			$vars,
			TRUE,
			PATH_THIRD . 'importer/content_types/importer.content_type.playa/views/settings_form.html'
		);
	}

	// --------------------------------------------------------------------

	/**
	 *	Validate Setting Fields
	 *
	 *	@access		public
	 *  @param      integer
	 *	@param		array
	 *	@param		array
	 *	@return		bool|string  - Returns either TRUE or an error message
	 */

	public function save_settings($profile_id, $elements, $settings)
	{
		// --------------------------------------------
		//  Get List of EE 1.x Galleries
		// --------------------------------------------

		if ( $settings['datatype'] == 'playa_fields')
		{
			if ( empty($_POST['importing_fields_import_order']) OR ! is_array($_POST['importing_fields_import_order']))
			{
				return $this->error_page('no_valid_playa_fields_selected');
			}
		}
		elseif ($settings['datatype'] == 'relationship_fields')
		{
			if ( empty($_POST['importing_fields_import_order']) OR ! is_array($_POST['importing_fields_import_order']))
			{
				return $this->error_page('no_valid_relationship_fields_selected');
			}
		}

		if ( empty($_POST['playa_field']))
		{
			return $this->error_page('no_valid_playa_field_selected');
		}

		return array('importing_fields_import_order' => implode('|', $_POST['importing_fields_import_order']),
					 'playa_field'                      => $_POST['playa_field']);
	}

	// --------------------------------------------------------------------

	/**
	 *	Perform Import
	 *
	 *	@access		public
	 *	@param		array		$settings
	 *	@param		array		$data
	 *	@return		bool|array	Returns either FALSE (failure) or an array of statistics information (success)
	 */
	public function perform_import($settings, $data)
	{
		// --------------------------------------------
		//  Initial States!
		// --------------------------------------------

		$import_start_time = microtime(TRUE);

		$this->statistics	=
					  array('entries_modified'		    => 0,
							'entry_ids'				    => array(),
							'debugging'				    => array());

		$this->statistics['debugging'][] = "Importing - START".$this->debug_time_memory($import_start_time);

		// --------------------------------------------
		//  Initial Data Check
		// --------------------------------------------

		if ( ! is_array($data)) return FALSE;

		if ( empty($settings['playa_field']) OR empty($settings['importing_fields_import_order']))
		{
			$this->statistics['debugging'][] = " -- Missing Required Settings".$this->debug_time_memory($import_start_time);
		}

		$playa_field_id   = str_replace('field_id_', '', $settings['playa_field']);
		$importing_fields = explode('|', $settings['importing_fields_import_order']);

		// --------------------------------------------
		//  Cycle Through Array
		// --------------------------------------------

		foreach($data as $key => $entry_data)
		{
			// --------------------------------------------
			//  Unserialize Data and Validate
			// --------------------------------------------

			$entry_data = unserialize($entry_data);

			if (empty($entry_data) OR ! is_array($entry_data)) continue; // No data for this entry is possible.

			$this->statistics['debugging'][] = "Entry ID #{$entry_data['entry_id']} - Start Processing".$this->debug_time_memory($import_start_time);

			// --------------------------------------------
			//  Find Existing Selections and Order
			// --------------------------------------------

			$query = ee()->db->query("SELECT rel_order, child_entry_id FROM exp_playa_relationships
									  WHERE parent_entry_id = '".ee()->db->escape_str($entry_data['entry_id'])."'
									  AND parent_field_id = '".ee()->db->escape_str($playa_field_id)."'
									  ORDER BY rel_order ASC");

			$selections = array();

			// Current selections, if any, go first
			foreach($query->result_array() AS $key => $row)
			{
				$selections[$row['rel_order']] = $row['child_entry_id'];
			}

			sort($selections);

			$this->statistics['debugging'][] = " -- ".count($selections)." Existing EE Relationships Found".$this->debug_time_memory($import_start_time);

			// --------------------------------------------
			//  Find Relationships in exp_relationships
			// --------------------------------------------

			if ($settings['datatype'] == 'relationship_fields')
			{
				$rel_ids = array();

				foreach($importing_fields AS $field_id)
				{
					if ( ! empty($entry_data[$field_id]) && is_numeric($entry_data[$field_id]))
					{
						$rel_ids[$field_id] = $entry_data[$field_id];
					}
				}

				if ( empty($rel_ids))
				{
					$this->statistics['debugging'][] = " -- No Relationships to Import".$this->debug_time_memory($import_start_time);
					continue;
				}

				$query = ee()->db->query("SELECT rel_id, rel_child_id FROM exp_relationships
										  WHERE rel_id IN ('".implode("','", ee()->db->escape_str($rel_ids))."')
										  AND rel_parent_id = '".ee()->db->escape_str($entry_data['entry_id'])."'");

				if ($query->num_rows() == 0)
				{
					$this->statistics['debugging'][] = " -- No Relationships to Import".$this->debug_time_memory($import_start_time);
					continue;
				}

				// --------------------------------------------
				//  Cycle Through in Correct Order and Put in $selections
				// --------------------------------------------

				$newbies = 0;
				$existies = 0;

				foreach($rel_ids AS $field_id => $rel_id)
				{
					// So search results for rel_id match and set entry_id
					foreach($query->result_array() AS $row)
					{
						if ($row['rel_id'] ==  $rel_id)
						{
							if ( in_array($row['rel_child_id'], $selections))
							{
							   $existies++;
							}
							else
							{
								$selections[] = $row['rel_child_id'];
								$newbies++;
							}

							break;
						}
					}
				}

				// --------------------------------------------
				//  Debugging Information
				// --------------------------------------------

				if ($newbies == 0)
				{
					$this->statistics['debugging'][] = " -- No Playa Relations to Import".$this->debug_time_memory($import_start_time);
					continue;
				}

				$this->statistics['debugging'][] = " -- ".$newbies." Playa Relation(s) Inserted".$this->debug_time_memory($import_start_time);

				if ($existies > 0)
				{
					$this->statistics['debugging'][] = " -- ".$existies." Existing Playa Relation(s) Not Inserted".$this->debug_time_memory($import_start_time);
				}
			}

			// --------------------------------------------
			//  Fold in Playa Relationship
			// --------------------------------------------

			if ($settings['datatype'] == 'playa_fields')
			{
				$query = ee()->db->query("SELECT child_entry_id, parent_field_id FROM exp_playa_relationships
										  WHERE parent_field_id IN ('".implode("','", ee()->db->escape_str(str_replace('field_id_', '', $importing_fields)))."')
										  AND parent_entry_id = '".ee()->db->escape_str($entry_data['entry_id'])."'
										  ORDER BY parent_field_id, rel_order ASC");

				$newbies = 0;

				// Go through our fields in the correct order
				foreach($importing_fields AS $field_id)
				{
					// And look at the results and find a match
					foreach($query->result_array() AS $row)
					{
						// Must match field_id
						if ($row['parent_field_id'] == str_replace('field_id_', '', $field_id))
						{
							// Must not be currently selected
							if ( ! in_array($row['child_entry_id'], $selections))
							{
								// Append to current selections
								$selections[] = $row['child_entry_id'];
								$newbies++;
							}
						}
					}
				}

				if ($newbies == 0)
				{
					$this->statistics['debugging'][] = " -- No Playa Fields to Import".$this->debug_time_memory($import_start_time);
					continue;
				}

				$this->statistics['debugging'][] = " -- ".$newbies." Playa Relation(s) Inserted".$this->debug_time_memory($import_start_time);
			}

			// --------------------------------------------
			//  Begin Importing of Relationship Fields into Playa for Entry
			// --------------------------------------------

			$data = array(
				'parent_entry_id' => $entry_data['entry_id'],
				'parent_field_id' => $playa_field_id
			);

			$this->save_relationships($selections, $data);

			// --------------------------------------------
			//  Save Keywords
			// --------------------------------------------

			$keywords = $this->get_related_keywords($selections);

			ee()->db->update('exp_channel_data',
							 array('field_id_'.$playa_field_id => $this->get_related_keywords($selections)),
							 array('entry_id' => $entry_data['entry_id']));

			// And...scene!
			$this->statistics['entries_modified']++;
		}

		$this->statistics['debugging'][] = "Entry Loop Finished".$this->debug_time_memory($import_start_time);
		$this->statistics['debugging'][] = "Import Finished".$this->debug_time_memory($import_start_time);

		return $this->statistics;
	}
	// END perform_import()


	// --------------------------------------------------------------------

	/**
	 *	Save Relationships
	 *
	 *	@access		private
	 *	@param		array
	 *  @param      array
	 *	@return		null
	 */
	private function save_relationships($selections, $data)
	{
		// -------------------------------------------
		//  'playa_save' hook
		//   - Update the $data array before the deletion and insert
		//
			if (ee()->extensions->active_hook('playa_save_rels'))
			{
				$data = ee()->extensions->call('playa_save_rels', $this, $selections, $data);
			}
		//
		// -------------------------------------------

		// Delete existing relationships
		ee()->db->where($data)->delete('playa_relationships');

		if ( ! empty($selections) && is_array($selections))
		{
			foreach ($selections as $rel_order => $child_entry_id)
			{
				$batch_rel_data[] = array_merge($data, array(
					'child_entry_id' => $child_entry_id,
					'rel_order'      => $rel_order
				));
			}

			ee()->db->insert_batch('playa_relationships', $batch_rel_data);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the relationship keywords in the format "[EntryId] [UrlTitle] EntryTitle"
	 * which will get saved into exp_channel_data, exp_matrix_data, or exp_global_variables
	 *
	 * @param array $entry_ids The selected entry IDs
	 * @return string
	 */
	private function get_related_keywords($entry_ids)
	{
		$keywords = '';

		if ($entry_ids)
		{
			$entries = ee()->db->select('entry_id, url_title, title')
									->where_in('entry_id', $entry_ids)
									->get('channel_titles')
									->result();

			foreach ($entries as $entry)
			{
				$keywords .= ($keywords ? "\n" : '') . "[{$entry->entry_id}] [{$entry->url_title}] ".str_replace('\'', '', $entry->title);
			}
		}

		return $keywords;
	}


}
// END CLASS Importer_datatype
