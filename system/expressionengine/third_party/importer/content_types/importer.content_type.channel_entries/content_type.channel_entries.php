<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type - Channel Entries
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/content_types/importer.content_type.channel_entries/content_type.channel_entries.php
 */

require_once PATH_THIRD.'importer/content_type.importer.php';

class Importer_content_type_channel_entries extends Importer_content_type
{
	public $version						= '1.0.0';

	// Third Party Field Types Supported
	private $third_party_field_types	= array(
		'playa',
		'matrix',
		'date',
		'pt_list',
		'fieldpack_multiselect',
		'tag',
		'assets'
	);

	public $allowed_datatypes			= array('csv', 'json', 'xml');

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
		$this->actions();

		$this->default_settings = array(
			'channel_id'						=> '',

			'title_element'						=> '',
			'default_title'						=> '',

			'url_title_element'					=> '',
			'default_url_title'					=> '',

			'status_element'					=> '',
			'default_status'					=> 'open',

			'default_author'					=> ee()->session->userdata['member_id'],
			'author_element_type'				=> 'screen_name',
			'author_element'					=> '',

			'entry_date_element'				=> '',
			'default_entry_date'				=> '',
			'entry_date_offset'					=> 0,

			'expiration_date_element'			=> '',

			'categories_element'				=> '',
			'default_categories'				=> array(),
			'new_category_group'				=> '',
			'category_delimiter'				=> ',',

			'duplicate_field' 					=> '',
			'duplicate_field_two'				=> '',
			'duplicate_entry_action'			=> 'update_entry',
			'duplicate_entry_category_action'	=> 'delete_old_add_new',
			'duplicate_entry_status_action'		=> 'update_status',

			'notification_emails'				=> '',
			'notification_cc'					=> '',
			'notification_subject'				=> '',
			'notification_message'				=> '',
			'notification_rules'				=> '',
		);

		ee()->lang->loadfile('content');
	}
	// END constructor

	// --------------------------------------------------------------------

	/**
	 *	Settings Form
	 *
	 *	@access		public
	 *	@param		array		// Default Values
	 *	@return		string
	 */
	public function settings_form(array $options, array $settings)
	{
		if ( empty($options) OR ! is_array($options))
		{
			return $this->error_page('invalid_data_received');
		}

		// --------------------------------------------
		//  List of Channels
		// --------------------------------------------

		$this->cached_vars['channels']	= $this->data->get_channels_per_site();
		$this->cached_vars['sites']		= $this->data->get_sites();

		foreach($this->cached_vars['channels'] as $site_id => $channels)
		{
			foreach($channels as $channel_id => $channel_name)
			{
				if ( ! isset(ee()->session->userdata['assigned_channels'][$channel_id]))
				{
					unset($this->cached_vars['channel'][$site_id][$channel_id]);
				}
			}

			if (count($this->cached_vars['channels'][$site_id]) == 0)
			{
				unset($this->cached_vars['channels'][$site_id]);
			}
		}

		$this->cached_vars['importer_channel_id'] = (
			isset($settings['channel_id'])
		) ? $settings['channel_id'] : '';

		// --------------------------------------------
		//  Return the Channel Settings Form
		// --------------------------------------------

		$this->add_crumb(
			lang('importer_channel_entries_settings_form').' - '.$settings['profile_name']
		);

		$this->build_crumbs();

		$this->cached_vars['module_menu_highlight'] = 'module_homepage';

		return $this->view(
			'settings_form.html',
			NULL,
			TRUE,
			PATH_THIRD.'importer/content_types/importer.content_type.channel_entries/views/settings_form.html'
		);

	}

	// --------------------------------------------------------------------

	/**
	 *	Settings Form
	 *
	 *	@access		public
	 *	@param		array		// Default Values
	 *	@return		string
	 */
	public function settings_form_ajax($object)
	{
		if (ee()->input->get_post('channel_id') === FALSE)
		{
			exit('Valid Channel ID is Required');
		}

		if (ee()->input->get_post('profile_id') === FALSE)
		{
			exit('Valid Profile ID is Required');
		}

		$data = $object->settings_form(0, array(), TRUE);

		if ($data === FALSE)
		{
			exit('Unable to Retrieve Profile Data');
		}

		extract($data);

		$settings['channel_id'] = ee()->input->get_post('channel_id');
		$settings['profile_id'] = ee()->input->get_post('profile_id');

		// --------------------------------------------
		//  Take the First Item in the Array and Find Elements + Examples
		// --------------------------------------------

		ee()->load->helper('text');

		foreach($elements as $element => $example)
		{
			if( is_array($example))
			{
				$example = implode(', ', $example);
			}

			if ( is_bool($example))
			{
				$example = ($example === TRUE) ? '(bool) TRUE': '(bool) FALSE';
			}

			settype($example, 'string');

			$elements[$element] = ellipsize($example, 25);
		}

		$this->cached_vars['element_options'] = $elements;

		// --------------------------------------------
		//  Channel Entry Title - Default
		// --------------------------------------------

		$this->default_settings['default_title'] = '';

		// --------------------------------------------
		//  Retrieve the Custom Fields for this Channel
		// --------------------------------------------

		$this->cached_vars['custom_fields'] = $this->data->get_custom_fields_for_channel_id($settings['channel_id']);

		foreach($this->cached_vars['custom_fields'] as $field_id => $field_data)
		{
			$this->default_settings['field_id_'.$field_id.'_element']	= '';
			$this->default_settings['default_field_id_'.$field_id]		= '';

			if ( isset(ee()->lang->language[$field_data['field_type'].'_field_type_subtext']))
			{
				$this->cached_vars['field_type_subtext'][$field_data['field_type']] =
					lang($field_data['field_type'].'_field_type_subtext');
			}

			// --------------------------------------------
			//  Custom Field Type - Third Party
			// --------------------------------------------

			$field_type = strtolower($field_data['field_type']);

			if (($obj = $this->load_field_type($field_type)) === FALSE) continue;

			if (is_callable(array($obj, 'settings_form_row')))
			{
				$this->cached_vars['custom_fields'][$field_id]['field_element_callback'] =
						$obj->settings_form_row(
							$field_id,
							$field_data,
							$this->cached_vars['element_options'],
							array_merge($this->default_settings, $settings)
						);
			}
			else
			{
				// Not necessary.
				unset($obj);
			}
		}

		// --------------------------------------------
		//  A Few Values for the Channel
		// --------------------------------------------

		$query = ee()->db->query("SELECT deft_status, cat_group FROM exp_channels
								  WHERE channel_id = '".ee()->db->escape_str($settings['channel_id'])."'");

		if ($query->num_rows() == 0)
		{
			return FALSE; // Hrm.
		}

		$default_status	= $this->default_settings['default_status'] = $query->row('deft_status');
		$category_group	= $query->row('cat_group');

		// --------------------------------------------
		//  Category Groups for Channel
		// --------------------------------------------

		$this->cached_vars['category_groups'] = array();

		ee()->load->model('category_model');

		foreach( ee()->category_model->get_category_groups(explode('|', $category_group))->result_array() as $row)
		{
			$this->cached_vars['category_groups'][$row['group_id']] = $row['group_name'];
		}

		// --------------------------------------------
		//  Categories. Who wrote it this way? Oh, right, me, years ago. Oy!
		// --------------------------------------------

		$categories = array();

		ee()->load->library('api');
		ee()->api->instantiate('channel_categories');

		ee()->api_channel_categories->category_tree($category_group);

		if (count(ee()->api_channel_categories->categories) > 0)
		{
			foreach (ee()->api_channel_categories->categories as $val)
			{
				$categories[$val[3]][] = $val;
			}
		}

		$this->cached_vars['categories'] = $categories;

		// --------------------------------------------
		//  Statuses
		// --------------------------------------------

		$this->cached_vars['statuses'] = $this->data->get_statuses_for_channel_id($settings['channel_id'], ee()->session->userdata['group_id']);

		// --------------------------------------------
		//  Authors
		// --------------------------------------------

		$this->cached_vars['authors'] = $this->data->get_channel_authors();

		$this->cached_vars['author_field_options'] = array(
			'member_id'		=> lang('importer_member_id'),
			'email'			=> lang('importer_email_address'),
			'username'		=> lang('importer_username'),
			'screen_name'	=> lang('importer_screen_name')
		);

		// --------------------------------------------
		//  Form Values - Based off default settings and $settings array()
		// --------------------------------------------

		foreach(array_merge($this->default_settings, $settings) as $setting => $value)
		{
			if (isset($this->default_settings[$setting]) &&
				is_array($this->default_settings[$setting]))
			{
				// Must be an array, default will already be one, so we check here
				$this->cached_vars['importer_'.$setting] = $this->output((is_array($value)) ?
																		  $value :
																		  explode('|', $value));
			}
			else
			{
				$this->cached_vars['importer_'.$setting] = $this->output($value);
			}
		}

		// --------------------------------------------
		//  Return the Channel Settings Form
		// --------------------------------------------

		exit($this->view('settings_form.html',
			NULL,
			TRUE,
			PATH_THIRD.'importer/content_types/importer.content_type.channel_entries/views/settings_form_channel_entries.html')
		);
	}
	// END settings_form

	// --------------------------------------------------------------------

	/**
	 *	Validate and Save Setting Fields
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
		//  Title - The only REQUIRED field, actually...
		// --------------------------------------------

		if ( empty($_POST['title_element']) && empty($_POST['default_title']))
		{
			return $this->error_page('error_invalid_importer_title_element_selected');
		}

		if (empty($_POST['channel_id']))
		{
			return $this->error_page('error_unable_to_find_channel_in_the_database_this_should_never_happen'); // Hrm.
		}

		$settings['channel_id'] = $_POST['channel_id'];

		// --------------------------------------------
		//  Custom Field - Required Field and Element Validation
		// --------------------------------------------

		foreach($this->data->get_custom_fields_for_channel_id($settings['channel_id']) as $field_id => $field_data)
		{
			// --------------------------------------------
			//  Field is Required.  Either needs a valid element or default value
			// --------------------------------------------

			if ( $field_data['field_required'] == 'y')
			{
				if ((empty($_POST['field_id_'.$field_id.'_element']) OR ! isset($elements[$_POST['field_id_'.$field_id.'_element']]))
					&&
					empty($_POST['default_field_id_'.$field_id])
				   )
				{
					return $this->error_page(str_replace('%field%',
													 $field_data['field_label'],
													 lang('error_importer_custom_field_required')));
				}
			}

			// --------------------------------------------
			//  If the Element Value is set, it must be valid
			// --------------------------------------------

			elseif ( ! empty($_POST['field_id_'.$field_id.'_element']) && ! array_key_exists($_POST['field_id_'.$field_id.'_element'], $elements))
			{
				return $this->error_page(str_replace('%field%',
													 $field_data['field_label'],
													 lang('error_invalid_importer_custom_field_element_selected')));
			}
		}

		// --------------------------------------------
		//  Various Fields - If element selected, must be valid.
		// --------------------------------------------

		$fields = array('title', 'entry_date', 'expiration_date', 'status', 'author', 'categories');

		foreach($fields as $field)
		{
			if ( ! empty($_POST[$field.'_element']) && ! array_key_exists($_POST[$field.'_element'], $elements))
			{
				return $this->error_page(str_replace('%field%',
													 lang('importer_channel_'.$field),
													 lang('error_invalid_importer_custom_field_element_selected')));
			}
		}

		// --------------------------------------------
		//  Entry Date Offset
		// --------------------------------------------

		if ( ! empty($_POST['entry_date_offset']) && ! is_numeric($_POST['entry_date_offset']))
		{
			return $this->error_page('error_invalid_entry_date_offset');
		}

		// --------------------------------------------
		//  A Few Values for the Channel
		// --------------------------------------------

		$query = ee()->db->query("SELECT deft_status, cat_group FROM exp_channels
								  WHERE channel_id = '".ee()->db->escape_str($settings['channel_id'])."'");

		if ($query->num_rows() == 0)
		{
			return $this->error_page('error_unable_to_find_channel_in_the_database_this_should_never_happen'); // Hrm.
		}

		$default_status	= $this->default_settings['default_status'] = $query->row('deft_status');
		$category_group	= $query->row('cat_group');

		// --------------------------------------------
		//  Category Groups for Channel
		// --------------------------------------------

		if ( ! empty($_POST['categories_element']) && ! empty($_POST['new_category_group']))
		{
			$category_groups = array();
			ee()->load->model('category_model');

			foreach( ee()->category_model->get_category_groups(explode('|', $category_group))->result_array() as $row)
			{
				$category_groups[$row['group_id']] = $row['group_name'];
			}

			if ( ! isset($category_groups[$_POST['new_category_group']]))
			{
				return $this->error_page('error_invalid_new_category_group');
			}
		}

		// --------------------------------------------
		//  Categories and Category Groups
		// --------------------------------------------

		if ( ! empty($_POST['default_categories']) && is_array($_POST['default_categories']))
		{
			ee()->load->library('api');
			ee()->api->instantiate('channel_categories');

			ee()->api_channel_categories->category_tree($category_group);

			foreach($_POST['default_categories'] as $value)
			{
				if ( !isset(ee()->api_channel_categories->categories[$value]))
				{
					return $this->error_page('error_invalid_default_categories');
				}
			}
		}

		// --------------------------------------------
		//  Default Status
		// --------------------------------------------

		$statuses = $this->data->get_statuses_for_channel_id($settings['channel_id'], ee()->session->userdata['group_id']);

		if ( ! isset($_POST['default_status']) OR ! isset($statuses[$_POST['default_status']]))
		{
			return $this->error_page('error_invalid_default_status');
		}

		// --------------------------------------------
		//  Default Author
		// --------------------------------------------

		$authors = $this->data->get_channel_authors();

		if ( ! isset($_POST['default_author']) OR ! isset($authors[$_POST['default_author']]))
		{
			return $this->error_page('error_invalid_default_author');
		}

		// --------------------------------------------
		//  Validate Notification Fields - Abstracted in Parent Class
		// --------------------------------------------

		if (($check = $this->validate_notification_fields()) !== TRUE)
		{
			return $check;
		}

		// --------------------------------------------
		//  Default Settings!
		// --------------------------------------------

		foreach($this->data->get_custom_fields_for_channel_id($settings['channel_id']) as $field_id => $field_data)
		{
			$this->default_settings['field_id_'.$field_id.'_element']	= '';
			$this->default_settings['default_field_id_'.$field_id]		= '';

			// --------------------------------------------
			//  Custom Field Type Settings
			// --------------------------------------------

			$field_type = strtolower($field_data['field_type']);

			if (($obj = $this->load_field_type($field_type)) === FALSE) continue;

			if (is_callable(array($obj, 'setting_fields')))
			{
				$this->default_settings = array_merge($this->default_settings, $obj->setting_fields($field_id));
			}

			unset($obj);
		}

		// --------------------------------------------
		//  Insert Settings!
		// --------------------------------------------

		$insert_settings = array();

		foreach($this->default_settings as $field => $default_value)
		{
			if (ee()->input->post($field) === FALSE)
			{
				$insert_settings[$field] = (is_array($default_value)) ?
											implode('|', $default_value) :
											$default_value;
			}
			else
			{
				$insert_settings[$field] = (is_array(ee()->input->post($field))) ?
											implode('|', ee()->input->post($field)) :
											ee()->input->post($field);
			}
		}

		return $insert_settings;
	}
	//  END save_settings();

	// --------------------------------------------------------------------

	/**
	 *	Perform Import
	 *
	 *	@access		public
	 *	@param		array		$settings
	 *	@param		array		$data
	 *	@return		bool|array	Returns either FALSE or an array of debug information
	 */
	public function perform_import($settings, $data)
	{
		// Initialize
		$metadata = $entry_data = array();

		if ( ! is_array($data) OR ! isset($settings['channel_id']))
		{
			$this->set_error(lang('error_incomplete_settings'));
			return FALSE;
		}

		// --------------------------------------------
		//  Validate Channel
		//	- Looked into using the Channel Structure
		//	API but it is too much of a mess
		// --------------------------------------------

		$query = ee()->db
					->select('site_id, url_title_prefix')
					->where('channel_id', $settings['channel_id'])
					->get('channels');

		if ($query->num_rows() == 0)
		{
			$this->set_error(lang('error_channel_not_found'));
			return FALSE; // Hrm.
		}

		$settings['site_id']			= $query->row('site_id');
		$settings['url_title_prefix']	= $query->row('url_title_prefix');

		// --------------------------------------------
		//  Disable Extensions
		// - Extension can cause ALL sorts of headaches
		// when using the EE Channel Entries API.
		// --------------------------------------------

		$allow_extensions = ee()->config->item('allow_extensions');

		ee()->config->config['allow_extensions'] = 'n';

		// -------------------------------------
		//	This is a fix for Extensions->active_hook
		//	returning boolean true even when extensions
		//	are disabled. EE Bug reported.
		//	This should be fixed in EE 2.9.1/2.10.0.
		// -------------------------------------

		$old_ext = ee()->extensions->extensions;

		ee()->extensions->extensions = array();

		// --------------------------------------------
		//  Import the Entries
		// --------------------------------------------

		$channel_start_time = microtime(TRUE);

		$this->statistics	= array(
			'entries_inserted'		=> 0,
			'entries_updated'		=> 0,
			'entries_deleted'		=> 0,
			'total_entries' 		=> 0,
			'site_id'				=> ee()->config->item('site_id'),
			'author_ids'			=> array(),
			'entry_ids'				=> array(),
			'inserted_entry_ids'	=> array(),
			'updated_entry_ids'		=> array(),
			'debugging'				=> array()
		);

		$this->add_memory_stat("Load API - START", $channel_start_time);

		ee()->load->library('api');
		ee()->api->instantiate('channel_entries');
		ee()->api->instantiate('channel_fields');

		$this->add_memory_stat("Load API - END", $channel_start_time);

		// We accumulate so we can do one large insert at the end
		$insert_categories		= array();

		foreach($data as $key => $entry_data)
		{
			$this->add_memory_stat(
				"Entry ({$key}) Start Processing",
				$channel_start_time
			);

			// For memory reasons, all of our data comes in as a serialized array.
			// See $this->actions()->serialize_data_array() for more information
			$entry_data = unserialize($entry_data);

			if (empty($entry_data))
			{
				continue; // No data for this entry is possible.
			}

			// --------------------------------------------
			//  Validate Entry, Organize Data
			// --------------------------------------------

			$return = $this->process_entry($entry_data, $settings);

			// Errors occurred, put into $this->errors array,
			// retrieve with $this->errors()
			if ($return === FALSE)
			{
				// We have to do this because entries that did NOT cause an error will
				// still need to have their categories and tags added.
				$this->do_accumulated_inserts($insert_categories, $settings);

				return FALSE;
			}

			// --------------------------------------------
			//  Set Userdata
			// --------------------------------------------

			ee()->session->userdata = array_merge(
				ee()->session->userdata,
				$return['userdata']
			);

			// -------------------------------------------
			//  Import the Entry
			//	- Options for Duplicates: do_nothing,
			//	update_entry, delete_entry, delete_entry_insert_new
			// --------------------------------------------

			$this->add_memory_stat(
				"Entry ({$key}) API Starts",
				$channel_start_time
			);

			ee()->api_channel_fields->setup_entry_settings(
				$settings['channel_id'], $return['entry_data']
			);


			if ($return['duplicate'] === TRUE)
			{
				// Nothing?  Guess we skip to the next entry.
				if ($settings['duplicate_entry_action'] == 'do_nothing')
				{
					continue;
				}

				if ($settings['duplicate_entry_action'] == 'update_entry')
				{

					// --------------------------------------------
					//  If "Keep Old Add New Category" option is selected,
					//  Find Current Categories to Prevent Duplicates in DB.
					//  Do this before API wipes data.
					// --------------------------------------------

					if ($settings['duplicate_entry_category_action'] == 'keep_old_add_new')
					{
						$existing_categories = array();

						$query = ee()->db->select('cat_id')->where('entry_id', $return['entry_data']['entry_id']);
						$query = $query->get('exp_category_posts');

						foreach($query->result_array() AS $row)
						{
							$existing_categories[] = $row['cat_id'];
						}
					}

					// --------------------------------------------
					//  EE 2.x API Issue
					//	- The API requires that you send all fields of type
					//	'text' and 'blob', otherwise it sets it to an empty
					//	string to prevent MySQL strict errors. Naturally,
					//	this is a complete mistake (UPDATE!) but we have to
					//	work around it.
					//	Solution is to pull those fields out of the DB
					//	and add them to what we send
					// --------------------------------------------

					$fields = $this->data->get_custom_field_ids();

					if ( ! empty($fields))
					{
						ee()->db->select($fields);
						ee()->db->from('exp_channel_data');
						ee()->db->where('entry_id', $return['entry_data']['entry_id']);

						$equery = ee()->db->get();

						$custom_fields = $this->data->get_custom_fields_for_channel_id(
							$return['entry_data']['channel_id']
						);

						foreach($equery->row_array() as $mysql_field => $stored_data)
						{
							if (empty($stored_data))
							{
								continue;
							}

							// New data
							if (isset($return['entry_data'][$mysql_field]))
							{
								continue;
							}

							$field_id = str_replace('field_id_', '', $mysql_field);

							// Custom Field Type Parsing
							if ( isset($custom_fields[$field_id]) &&
								(	$obj = $this->load_field_type(
										$custom_fields[$field_id]['field_type'])
									) !== FALSE &&
								is_callable(array($obj, 'parse_field')))
							{
								// Default Field Formatting
								$entry_data['field_ft_'.$field_id] = $custom_fields[$field_id]['field_fmt'];

								// Let the custom field type do the
								// parsing and return us our data.
								$result = $obj->parse_field(
									$field_id,
									$custom_fields[$field_id],
									$settings,
									$stored_data,
									TRUE,
									$return['entry_data']['entry_id']
								);

								if ($result === FALSE)
								{
									$return['entry_data'][$mysql_field] = $stored_data;
								}
								else
								{
									$return['entry_data']['field_id_'.$field_id] = $result;
								}
							}
							else
							{
								$return['entry_data'][$mysql_field] = $stored_data;
							}
						}
					}

					// --------------------------------------------
					//  Matrix - Delete Existing Data for Updated Fields
					// --------------------------------------------

					if ( ! empty($this->cache['matrix_used']) &&
						is_array($this->cache['matrix_used']))
					{
						ee()->db->where_in('field_id', $this->cache['matrix_used']);
						ee()->db->where('entry_id', $return['entry_data']['entry_id'])
								->delete('matrix_data');
					}

					// --------------------------------------------
					//  Update Entry
					// --------------------------------------------

					if (ee()->api_channel_entries->update_entry(
							$return['entry_data']['entry_id'],
							$return['entry_data'],
							FALSE
						) === FALSE)
					{
						$this->set_error(ee()->api_channel_entries->get_errors());
						return FALSE;
					}

					$this->statistics['updated_entry_ids'][] = $return['entry_data']['entry_id'];

					$this->statistics['entries_updated']++;
				}
				else if ($settings['duplicate_entry_action'] == 'delete_entry')
				{
					// --------------------------------------------
					//  Delete Old Entry
					// --------------------------------------------

					if (ee()->api_channel_entries->delete_entry($return['entry_data']['entry_id']) === FALSE)
					{
						$this->set_error(ee()->api_channel_entries->get_errors());
						return FALSE;
					}

					$this->statistics['entries_deleted']++;
				}
				else if ($settings['duplicate_entry_action'] == 'delete_entry_insert_new')
				{
					// --------------------------------------------
					//  Delete Old Entry First, Then Insert New One
					// --------------------------------------------

					if (ee()->api_channel_entries->delete_entry($return['entry_data']['entry_id']) === FALSE)
					{
						$this->set_error(ee()->api_channel_entries->get_errors());
						return FALSE;
					}

					$this->statistics['entries_deleted']++;

					if (ee()->api_channel_entries->submit_new_entry($settings['channel_id'], $return['entry_data']) === FALSE)
					{
						$this->set_error(ee()->api_channel_entries->get_errors());
						return FALSE;
					}

					$return['entry_data']['entry_id'] = ee()->api_channel_entries->entry_id;
					$this->statistics['inserted_entry_ids'][] = $return['entry_data']['entry_id'];

					$this->statistics['entries_inserted']++;
				}
			}
			else
			{
				if (ee()->api_channel_entries->submit_new_entry(
						$settings['channel_id'],
						$return['entry_data']
					) === FALSE)
				{
					$this->set_error(ee()->api_channel_entries->get_errors());
					return FALSE;
				}
				$return['entry_data']['entry_id'] = ee()->api_channel_entries->entry_id;
				$this->statistics['inserted_entry_ids'][] = $return['entry_data']['entry_id'];

				$this->statistics['entries_inserted']++;
			}

			$this->statistics['total_entries']++;
			$this->statistics['entry_ids'][]	= $return['entry_data']['entry_id'];
			$this->statistics['author_ids'][]	= $return['entry_data']['author_id'];
			$this->statistics['site_id']		= $return['entry_data']['site_id'];

			// --------------------------------------------
			//  Categories
			// --------------------------------------------

			$this->add_memory_stat(
				"Entry ({$key}) Categories/Tags",
				$channel_start_time
			);

			$categories = array();

			if ($return['duplicate'] === TRUE)
			{
				// --------------------------------------------
				//  Find Current Categories to Prevent Duplicates in DB
				// --------------------------------------------

				if ($settings['duplicate_entry_category_action'] == 'keep_old_add_new')
				{
					// Get categories from before the entry update
					$existing_categories = isset($existing_categories) ? $existing_categories : array();

					$categories = array_merge($return['categories'], $existing_categories);
				}
				elseif ($settings['duplicate_entry_category_action'] == 'delete_old_add_new')
				{
					$categories = $return['categories'];
				}
			}
			else
			{
				$categories = $return['categories'];
			}

			foreach(array_unique($categories) as $cat_id)
			{
				$insert_categories[$return['entry_data']['entry_id']][] = $cat_id;
			}
		}
		// End foreach

		$this->add_memory_stat(
			"Entry Loop Finished",
			$channel_start_time
		);

		// --------------------------------------------
		//  Enable Extensions
		// --------------------------------------------

		ee()->config->config['allow_extensions'] = $allow_extensions;
		ee()->extensions->extensions = $old_ext;

		// --------------------------------------------
		//  Insert Categories
		// --------------------------------------------

		$this->do_accumulated_inserts($insert_categories, $settings);

		// --------------------------------------------
		//  Statistics
		// --------------------------------------------

		$this->statistics['author_ids'] = array_unique($this->statistics['author_ids']);

		$this->add_memory_stat(
			"Perform Import Finished",
			$channel_start_time
		);

		// --------------------------------------------
		//  Clear Cache
		// --------------------------------------------

		return $this->statistics;
	}
	// END perform_import()

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
		if (empty($field_type) OR ! in_array($field_type, $this->third_party_field_types))
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Load the Class and Object for Field Type
		// --------------------------------------------

		$field_type_class	 = 'Importer_channel_entries_'.$field_type;
		$field_type_filename = $field_type.'.php';
		$field_type_path	 = $this->addon_path.'content_types/importer.content_type.channel_entries/field_types/'.$field_type_filename;

		// Instantiate and cache object
		if ( ! isset($this->cache['field_type_objects'][$field_type]))
		{
			if (file_exists($field_type_path))
			{
				require_once $field_type_path;
			}

			if (class_exists($field_type_class))
			{
				$this->cache['field_type_objects'][$field_type] = new $field_type_class();
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

	// --------------------------------------------------------------------

	/**
	 *	Perform Import
	 *
	 *	@access		public
	 *	@param		array		$settings
	 *	@param		array		$data - The data being imported
	 *	@return		bool|array	Returns either FALSE or an array of debug information
	 */
	public function process_entry($data, $settings)
	{
		// --------------------------------------------
		//  Default Metadata
		// --------------------------------------------

		$entry_data = array(
			'channel_id'		=> $settings['channel_id'],
			'site_id'			=> $settings['site_id'],
			'ip_address'		=> ee()->input->ip_address(),
			'ping_servers'		=> array()
		);

		// --------------------------------------------
		//  Title Element
		// --------------------------------------------

		// This happens when someone forgets to finish filling out the Settings Form
		if (empty($settings['title_element']) &&
			empty($settings['default_title']))
		{
			return $this->set_error('importer_title_element_not_found');
		}

		if ( ! empty($settings['default_title']))
		{
			$title = $settings['default_title'];

			// The {current_time format=""} variable for our Default Title
			if (strpos($title, '{current_time') !== FALSE &&
				preg_match_all(
					"/\{current_time\s+format=([\"\'])([^\\1]*?)\\1\}/",
					$title,
					$tmatches
				)
			)
			{
				$date_func = (is_callable(array(
						ee()->localize,
						'format_date'
					))
				) ? 'format_date' : 'decode_date';

				for ($j = 0; $j < count($tmatches[0]); $j++)
				{
					$title = str_replace(
						$tmatches[0][$j],
						ee()->localize->$date_func(
							$tmatches[2][$j],
							time()
						),
						$title
					);
				}
			}
		}

		if ( ! empty($settings['title_element']))
		{
			$element_title = Importer_actions::find_element(
				$settings['title_element'],
				$data
			);

			if (! empty($element_title))
			{
				$title = $element_title;
			}
		}

		if (empty($title))
		{
			return $this->set_error('importer_title_element_not_found');
		}

		// Title has a max length of 100, and if it is
		// being used for the duplicate check
		// we need to insure that the length matches
		$entry_data['title'] = substr($title, 0, 100);

		// --------------------------------------------
		//  URL Title
		// --------------------------------------------

		if ( ! empty($settings['url_title_element']))
		{
			$url_title = Importer_actions::find_element(
				$settings['url_title_element'],
				$data
			);
		}

		if ( ! empty($url_title))
		{
			$entry_data['url_title'] = $this->importer_url_title($url_title);
		}
		else
		{
			$entry_data['url_title'] = $this->importer_url_title(
				$settings['url_title_prefix'].$title
			);
		}

		// --------------------------------------------
		//  Author and Userdata
		// --------------------------------------------

		$author = FALSE;

		if ( ! empty($settings['author_element']))
		{
			$author = Importer_actions::find_element(
				$settings['author_element'],
				$data
			);
		}

		$userdata = $this->fetch_member_userdata(
			$author,
			$settings['default_author'],
			$settings['author_element_type']
		);

		if ($userdata == FALSE)
		{
			// Error already set in the fetch_member_userdata() method
			return FALSE;
		}

		$entry_data['author_id'] = $userdata['member_id'];

		// --------------------------------------------
		//  Status Element - Done after Userdata for Status Validation
		// --------------------------------------------

		$entry_data['status'] = (isset($settings['default_status'])) ?
									$settings['default_status'] :
									'closed';

		if ( ! empty($settings['status_element']))
		{
			if (($status = Importer_actions::find_element(
					$settings['status_element'],
					$data
				)) !== FALSE)
			{
				// Lowercase these two because EE will see the
				// ucfirst version as different than all lowercased
				if ($status == 'Open')
				{
					$status = 'open';
				}

				if ($status == 'Closed')
				{
					$status = 'closed';
				}

				$allowed = $this->data->get_statuses_for_channel_id(
					$settings['channel_id'],
					$userdata['group_id']
				);

				// Lower case matching, s'il vous plait
				array_walk(
					$allowed,
					create_function('&$val', '$val = strtolower($val);')
				);

				if ( ! in_array(strtolower($status), $allowed))
				{
					$entry_data['status'] = (isset($settings['default_status'])) ?
												$settings['default_status'] :
												'closed';
				}
				else
				{
					$entry_data['status'] = $status;
				}
			}
		}

		// --------------------------------------------
		//  Entry Date
		// --------------------------------------------

		if ( ! empty($settings['entry_date_element']))
		{
			$temp_entry_date = Importer_actions::find_element(
				$settings['entry_date_element'],
				$data
			);

			if ( $temp_entry_date !== FALSE)
			{
				if (preg_match('/^\-?[0-9]{1,10}$/', $temp_entry_date))
				{
					$entry_date = $temp_entry_date;
				}
				else
				{
					$temp_entry_date = strtotime( (string) $temp_entry_date);

					if ( $temp_entry_date != -1 &&
						$temp_entry_date !== FALSE)
					{
						$entry_date = $temp_entry_date;
					}
				}
			}
		}

		if ( ! isset($entry_date))
		{
			$entry_date = ee()->localize->now;

			if ( isset($settings['entry_date_offset']) &&
				is_numeric($settings['entry_date_offset']))
			{
				$entry_date += $settings['entry_date_offset'];
			}
		}

		// --------------------------------------------
		//  Expiration Date
		// --------------------------------------------

		if ( ! empty($settings['expiration_date_element']))
		{
			$temp_expiration_date = Importer_actions::find_element(
				$settings['expiration_date_element'],
				$data
			);

			if ( $temp_expiration_date !== FALSE &&
				is_string($temp_expiration_date) &&
				$temp_expiration_date !== '')
			{
				if (preg_match('/^[0-9]{1,10}$/', $temp_expiration_date))
				{
					$expiration_date = $temp_expiration_date;
				}
				else
				{
					$temp_expiration_date = strtotime(
						(string) $temp_expiration_date
					);

					if ($temp_expiration_date != -1 &&
						$temp_expiration_date !== FALSE)
					{
						$expiration_date = $temp_expiration_date;
					}
				}
			}
		}

		// --------------------------------------------
		//  Date Fields
		// --------------------------------------------

		$entry_data['entry_date']		= $entry_date;
		$entry_data['edit_date']		= $entry_date; // the API does this for us gmdate("YmdHis", $entry_date);
		//these get overwritten but might as well leave them in
		$entry_data['year']				= gmdate('Y', $entry_date);
		$entry_data['month']			= gmdate('m', $entry_date);
		$entry_data['day']				= gmdate('d', $entry_date);

		if ( isset($expiration_date))
		{
			$entry_data['expiration_date']	= $expiration_date;
		}

		// --------------------------------------------
		//  Retrieve Custom Fields - Cached!
		// --------------------------------------------

		$custom_fields = $this->data->get_custom_fields_for_channel_id(
			$settings['channel_id']
		);

		// --------------------------------------------
		//  Find Custom Field Values, s'il vous plait
		// --------------------------------------------

		foreach($custom_fields as $field_id => $field_data)
		{
			$field_type		= strtolower($field_data['field_type']);
			$field_options	= (array) $field_data['field_list_items'];

			$array_allowed	= (in_array($field_type, $this->third_party_field_types)) ? TRUE : FALSE;

			// --------------------------------------------
			//  Find the File Data or Default
			// --------------------------------------------

			// Custom Field Type Parsing
			if (($obj = $this->load_field_type($field_type)) !== FALSE &&
				is_callable(array($obj, 'parse_field')))
			{
				// Default Field Formatting
				$entry_data['field_ft_'.$field_id] = $field_data['field_fmt'];

				// Let the custom field type do the parsing and return us our data.
				$result = $obj->parse_field($field_id, $field_data, $settings, $data);

				if ($result === FALSE) continue;

				$entry_data['field_id_'.$field_id] = $result;
			}
			// Normal Field Parsing here
			elseif ( ! empty($settings['field_id_'.$field_id.'_element']))
			{
				$temp_field = Importer_actions::find_element(
					$settings['field_id_'.$field_id.'_element'],
					$data,
					$array_allowed
				);

				if ( $temp_field !== NULL)
				{
					// Allow Boolean to equal y/n or yes/no
					if (in_array($field_type, array('select', 'checkbox', 'radio')) &&
						($temp_field === FALSE OR $temp_field === TRUE)
					)
					{
						if ( $temp_field === TRUE)
						{
							foreach(array('y', 'Y', 'yes', 'Yes', 'YES') AS $possible)
							{
								if ( in_array($possible, $field_options))
								{
									$temp_field = $possible;
									break;
								}
							}
						}
						else
						{
							foreach(array('n', 'N', 'no', 'No', 'NO') AS $possible)
							{
								if ( in_array($possible, $field_options))
								{
									$temp_field = $possible;
									break;
								}
							}
						}
					}

					$entry_data['field_id_'.$field_id] = $temp_field;
					$entry_data['field_ft_'.$field_id] = $field_data['field_fmt'];
				}
			}
			elseif ( ! empty($settings['default_field_id_'.$field_id]))
			{
				$entry_data['field_id_'.$field_id] = $settings['default_field_id_'.$field_id];
				$entry_data['field_ft_'.$field_id] = $settings['default_field_id_'.$field_id];
			}
		}

		// --------------------------------------------
		//  Categories - Find Default first and
		//  add imported ones to the list
		// --------------------------------------------

		$categories = array();

		if ( ! empty($settings['default_categories']))
		{
			$categories = explode('|', $settings['default_categories']);
		}

		if ( ! empty($settings['categories_element']))
		{
			if (($temp_field = Importer_actions::find_element(
					$settings['categories_element'],
					$data,
					TRUE
				)) !== FALSE)
			{
				if ( ! is_array($temp_field))
				{
					if ( ! empty($settings['category_delimiter']))
					{
						$temp_field = explode(
							trim($settings['category_delimiter']),
							trim($temp_field)
						);
					}
					else
					{
						$temp_field = (array) $temp_field;
					}
				}

				$cat_data = $this->check_categories($temp_field, $settings);

				if (isset($cat_data['category_ids']))
				{
					$categories = array_merge(
						$categories,
						$cat_data['category_ids']
					);
				}
			}
		}

		// --------------------------------------------
		//  Check for Duplication - duplicate_field/duplicate_field_two
		// --------------------------------------------

		$duplicate = FALSE;

		if ( ! empty($settings['duplicate_field']) &&
			! empty($entry_data[$settings['duplicate_field']]))
		{
			$fields = array('duplicate_field');

			if ( ! empty($settings['duplicate_field_two']) &&
				! empty($entry_data[$settings['duplicate_field_two']]))
			{
				$fields[] = 'duplicate_field_two'; // I exist! Check me too!
			}

			// --------------------------------------------
			//  Build the Query for Checking the Fields in the DB
			// --------------------------------------------

			$sql = "SELECT	ct.entry_id,
							ct.url_title,
							ct.status,
							ct.versioning_enabled
					FROM	exp_channel_titles AS ct,
							exp_channel_data AS cd
					WHERE	ct.entry_id = cd.entry_id
					AND		ct.channel_id = '" .
								ee()->db->escape_str($settings['channel_id'])."'";

			foreach($fields as $field)
			{
				$check = $entry_data[$settings[$field]];

				if ($settings[$field] == 'title')
				{
					$sql .= " AND ct.title = '".ee()->db->escape_str($check)."'";
				}
				elseif (preg_match('/^field_id_[0-9]+$/', $settings[$field]))
				{
					$sql .= " AND cd.`".$settings[$field]."` = '".ee()->db->escape_str($check)."'";
				}
				else
				{
					return FALSE; // Should never happen...right?
				}
			}

			$query = ee()->db->query($sql." LIMIT 1 #Importer Duplicate Test");

			if ($query->num_rows() > 0)
			{
				$duplicate = TRUE;
				$entry_data['entry_id']	 = $query->row('entry_id');
				$entry_data['url_title'] = ( ! empty($url_title)) ? $url_title : $query->row('url_title');

				// EE's API will set to 'y' if value is set to *anything*, 'n' otherwise
				// So, only set it when the DB has it set to 'y'.
				if ($query->row('versioning_enabled') == 'y')
				{
					$entry_data['versioning_enabled'] = 'y';
				}

				// --------------------------------------------
				//  If Duplicate AND do NOT update status, set to current status
				// --------------------------------------------

				if ( ! empty($settings['duplicate_entry_status_action']) && $settings['duplicate_entry_status_action'] == 'do_not_update_status')
				{
					$entry_data['status'] = $query->row('status');
				}
			}
		}

		// --------------------------------------------
		//  Data for the Entry
		// --------------------------------------------

		$return = array('userdata'		=> $userdata,
						'entry_data'	=> $entry_data,
						'categories'	=> $categories,
						'duplicate'		=> $duplicate);

		return $return;
	}
	// END process_entry()

	// --------------------------------------------------------------------

	/**
	 *	Do Accumulated Inserts
	 *
	 *	Does all of the categories in one go instead of doing it piecemeal
	 *
	 *	@access		public
	 *	@param		array
	 *	@param		array
	 *	@param		array
	 *	@return		string
	 */

	protected function do_accumulated_inserts($categories, $settings)
	{
		// --------------------------------------------
		//  Categories - Find Entry IDs
		// --------------------------------------------

		$insert = array();

		if ( ! empty($categories))
		{
			// Delete old categories for entries.
			// If 'keep_old_add_new' was selected, we grabbed the old tags when processing the entry
			ee()->db->where_in('entry_id', array_keys($categories));
			ee()->db->delete('exp_category_posts');

			foreach($categories as $entry_id => $entry_categories)
			{
				foreach(array_unique($entry_categories) as $cat_id)
				{
					$insert[] = array('cat_id' 		=> $cat_id,
									  'entry_id'	=> $entry_id);
				}
			}

			ee()->db->insert_batch('exp_category_posts', $insert);
		}

		return TRUE;
	}
	// END do_accumulated_inserts();


	// --------------------------------------------------------------------

	/**
	 * Fetch Member's Userdata
	 *
	 *	@access	public
	 *	@param	string		$field - The field that we are matching
	 *	@param	array		$settings - Our array of settings so we can find something
	 *	@return	array|bool	$return - FALSE on failure, array of userdata values on success
	 */
	public function fetch_member_userdata($field = '', $default_author, $author_element_type)
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//  No cache, so we do the workings.
		// --------------------------------------------

		$userdata = array();

		// Query DB for member data.
		// Depending on the validation type we'll
		// either use the cookie data or the member ID
		// gathered with the session query.
		$sql = "SELECT	m.screen_name,
						m.member_id,
						m.email,
						m.url,
						m.group_id,
						mg.*
				FROM	exp_members AS m,
						exp_member_groups AS mg
				WHERE	m.group_id = mg.group_id ";

		$asql = '';

		if (empty($field))
		{
			$asql = " AND m.member_id = '".ee()->db->escape_str($default_author)."'";
		}
		else if (in_array(
			$author_element_type,
			array('member_id', 'email', 'username', 'screen_name')
		))
		{
			$asql = " AND m.`".$author_element_type."` = '".ee()->db->escape_str($field)."'";
		}

		$query = ee()->db->query($sql . $asql . " #Fetch Member User Data, Initial");

		if ($query->num_rows() == 0)
		{
			if ($field == '')
			{
				return $this->set_error('importer_unable_to_find_default_author');
			}

			$sql .= " AND m.member_id = '".ee()->db->escape_str($default_author)."'";

			$query = ee()->db->query($sql. " #Fetch Member User Data, No Match");

			if ($query->num_rows() == 0)
			{
				return $this->set_error('importer_unable_to_find_default_author');
			}
		}

		$userdata = $query->row_array();

		return $this->cached[$cache_name][$cache_hash] = $userdata;

		// -------------------------------------------------
		//  Find Assigned Channels - Cached -
		//  NOT REQUIRED! API DOES IT!!
		// -------------------------------------------------*/

		$assigned_channels = $this->data->get_assigned_channels($userdata['group_id']);

		if ( empty($assigned_channels))
		{
			return $this->set_error('importer_author_has_invalid_permissions');
		}

		$userdata['assigned_channels'] = $assigned_channels;

		return $this->cached[$cache_name][$cache_hash] = $userdata;
	}
	// END fetch_member_userdata()

	// --------------------------------------------------------------------

	/**
	 *	Check validity of categories
	 *
	 *	@access	public
	 *	@param	array	$check - Array of categories to check by cat_id or cat_name
	 *	@param	array	$settings
	 *	@return	array	$return = array('category_ids' => $category_ids, 'parent_ids' => $parent_ids);
	 */
	function check_categories($check, $settings)
	{
		if ( ! is_array($check))
		{
			$check = (array) $check;
		}

		array_walk($check, create_function('&$val', '$val = trim($val);'));

		$category_array = $this->data->get_categories_for_channel_id($settings['channel_id']);

		$category_ids	= array();
		$parent_ids		= array();
		$unmatched		= array();
		$matched		= array();
		$start_order 	= 1;

		foreach($category_array as $row)
		{
			if ($settings['new_category_group'] == $row['group_id']) $start_order++;

			if (in_array($row['cat_id'], $check) OR in_array($row['cat_name'], $check))
			{
				$category_ids[]				= $row['cat_id'];
				$parent_ids[$row['cat_id']] = $row['parent_id'];

				$matched[] = $row['cat_id'];
				$matched[] = $row['cat_name'];
			}
		}

		$unmatched = array_diff($check, $matched);

		if ( empty($settings['new_category_group']) OR count($unmatched) == 0)
		{
			return array('category_ids' => $category_ids, 'parent_ids' => $parent_ids);
		}

		// --------------------------------------------
		//  Create New Categories
		// --------------------------------------------

		ee()->load->helper('url');
		$word_separator = ee()->config->item('word_separator');

		foreach($unmatched as $category_name)
		{
			if ( is_numeric($category_name)) continue; // No IDs!

			$category_data = array(
							'group_id'			=> $settings['new_category_group'],
							'cat_name'			=> $category_name,
							'cat_url_title'		=> url_title($category_name, $word_separator, TRUE),
							'cat_description'	=> '',
							'cat_image'			=> '',
							'parent_id'			=> 0,
							'cat_order'			=> $start_order,
							'site_id'			=> $settings['site_id']
			);

			ee()->db->insert('categories', $category_data);

			$cat_id = ee()->db->insert_id();

			$fields['site_id']	= $settings['site_id'];
			$fields['cat_id']	= $cat_id;
			$fields['group_id']	= $settings['new_category_group'];

			ee()->db->insert('category_field_data', $fields);

			$category_ids[] = $cat_id;
			$parent_ids[$cat_id] = 0;

			$start_order++;
		}

		// --------------------------------------------
		//  Clear Out Cache! - PAUL YOU A FOOL!
		// --------------------------------------------

		$cache_name = 'get_categories_for_channel_id';
		$cache_hash = $this->data->_imploder(array($settings['channel_id']));

		unset($this->data->cached[$cache_name][$cache_hash]);

		// --------------------------------------------
		//  Return
		// --------------------------------------------

		return array('category_ids' => $category_ids, 'parent_ids' => $parent_ids);
	}
	// END check_categories

}
//END class Importer_content_type_channel_entries