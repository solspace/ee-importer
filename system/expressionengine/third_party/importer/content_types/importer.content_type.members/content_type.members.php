<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type - Members
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/content_types/importer.content_type.members/content_type.members.php
 */

require_once PATH_THIRD.'importer/content_type.importer.php';

class Importer_content_type_members extends Importer_content_type
{
	public $version						= '1.0.0';

	// Third Party Field Types Supported
	private $third_party_field_types	= array('playa', 'matrix', 'date');

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

		$this->default_settings = array(
			'member_id_element'				=> 'member_id',
			'member_id_default'				=> 'auto',

			'group_id_element'				=> 'group_id',
			'group_id_default'				=> '5',

			'email_element'					=> 'email',
			'email_default'					=> '',

			'username_element'				=> 'username',
			'username_default'				=> 'none',

			'screen_name_element'			=> 'screen_name',
			'screen_name_default'			=> 'none',

			'password_element'				=> 'password',
			'password_default'				=> 'auto',

			'unique_field' 					=> '',
			'unique_field_element'			=> '',

			'unique_field_two'				=> '',
			'unique_field_two_element'		=> '',

			'duplicate_member_action'		=> 'update_member',

			'notification_emails'			=> '',
			'notification_cc'				=> '',
			'notification_subject'			=> '',
			'notification_message'			=> '',
			'notification_rules'			=> '',
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

		$vars['sites']		= $this->data->get_sites();

		if (ee()->input->get_post('profile_id') === FALSE)
		{
			exit('Valid Profile ID is Required');
		}

		$settings['profile_id'] = ee()->input->get_post('profile_id');

		// --------------------------------------------
		//  Take the First Item in the Array and Find Elements + Examples
		// --------------------------------------------

		ee()->lang->loadfile('member_import');
		ee()->lang->loadfile('myaccount');
		ee()->lang->loadfile('member');
		ee()->load->helper('text');

		foreach($options as $element => $example)
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

		$vars['element_options'] = $elements;

		// --------------------------------------------
		//  Possible Member Groups
		// --------------------------------------------

		$vars['member_groups'] = array();

		$groups_query = ee()->db->query("SELECT group_id, group_title FROM exp_member_groups
										WHERE group_id NOT IN (2,3,4)
										AND site_id = '".ee()->db->escape_str(ee()->config->item('site_id'))."'
										ORDER BY group_title");

		foreach($groups_query->result_array() as $row)
		{
			$vars['member_groups'][$row['group_id']] = $row['group_title'];
		}

		// --------------------------------------------
		//  Required Fields
		// --------------------------------------------

		$vars['required_fields']  = array(
			'group_id'		=> array(),
			'username'		=> array(),
			'screen_name'	=> array(),
			'email'			=> array(),
			'unique_id'		=> array(),
			'password'		=> array(),
		);

		//$disabled_fields = array();
		$disabled_fields = array('password', 'unique_id');

		// --------------------------------------------
		//  Retrieve the EE Fields
		// --------------------------------------------

		$vars['all_fields']	   = array();

		$query = ee()->db->query("SHOW COLUMNS FROM exp_members");

		foreach($query->result_array() as $row)
		{
			$field_name = $row['Field'];

			$row = array(
				'field_id'			=> $field_name,
				'field_name'		=> $field_name,
				'field_type'		=> 'text',
				'field_subtext'		=> '',
				'label'				=> (
					! empty(ee()->lang->language['importer_'.$field_name])
				) ? lang('importer_'.$field_name) : lang($field_name),
				'options'			=> array(),
				'field_required'	=> 'n',
				'disabled'			=> in_array($field_name, $disabled_fields),
				'field_label'		=> (
					! empty(ee()->lang->language['importer_'.$field_name])
				) ? lang('importer_'.$field_name) : lang($field_name));

			extract($row);

			$field_type = strtolower($field_type);

			if ( ! isset($this->default_settings[$field_id.'_element']))
			{
				$this->default_settings[$field_id.'_element']	= '';
			}

			if ( ! isset($this->default_settings[$field_id.'_default']))
			{
				$this->default_settings[$field_id.'_default'] = '';
			}

			if (in_array($field_name, $disabled_fields))
			{
				$settings[$field_id.'_default'] = 'auto';
				$settings[$field_id.'_element'] = '';
			}

			// --------------------------------------------
			//  Subtext for Field
			// --------------------------------------------

			if ( isset(ee()->lang->language[$field_type.'_field_type_subtext']))
			{
				$row['field_subtext'] = lang($field_data['field_type'].'_field_type_subtext');
			}

			if ( isset(ee()->lang->language[$field_name.'_field_subtext']))
			{
				$row['field_subtext'] = lang($field_name.'_field_subtext');
			}

			if ( in_array($field_name, array_keys($vars['required_fields'])))
			{
				$vars['required_fields'][$field_id] = $row;
			}

			$vars['all_fields'][$field_id] = $row;
		}

		// --------------------------------------------
		//  Required Field Default Options
		// --------------------------------------------

		$vars['required_fields']['group_id']['options'] = $vars['member_groups'];

		$vars['required_fields']['username']['options']['method:none']				= lang('none');
		$vars['required_fields']['username']['options']['method:use_email']			= lang('use_email_address');
		$vars['required_fields']['username']['options']['method:auto']				= lang('create_via_alpha');

		$vars['required_fields']['screen_name']['options']['method:none']			= lang('none');
		$vars['required_fields']['screen_name']['options']['method:use_email']		= lang('use_email_address');
		$vars['required_fields']['screen_name']['options']['method:use_username']	= lang('use_username');
		$vars['required_fields']['screen_name']['options']['method:auto']			= lang('create_via_alpha');

		$vars['required_fields']['unique_id']['options']['method:auto']				= lang('automatically_create');
		$vars['required_fields']['password']['options']['method:auto']				= lang('automatically_create');

		// --------------------------------------------
		//  Retrieve the Member Custom Fields
		// --------------------------------------------

		$vars['custom_fields'] = array();

		$query = ee()->db
					->select(
						'*,
						m_field_name		AS field_name,
						m_field_label		AS field_label,
						m_field_id			AS field_id,
						m_field_type		AS field_type,
						m_field_required	AS field_required,
						m_field_list_items	AS field_list_items'
					)
					->order_by('m_field_order')
					->get('exp_member_fields');


		foreach($query->result_array() as $row)
		{
			extract($row);

			// Give it a prefix to indicate member field
			$field_id = 'm_field_id_'.$field_id;
			$field_type = strtolower($field_type);

			$row['field_label'] = lang('custom_field').' - '.$row['field_label'];

			// --------------------------------------------
			//  Subtext for Field
			// --------------------------------------------

			if ( isset(ee()->lang->language[$field_type.'_field_type_subtext']))
			{
				$row['field_subtext'] = lang($field_data['field_type'].'_field_type_subtext');
			}

			if ( isset(ee()->lang->language[$field_name.'_field_subtext']))
			{
				$row['field_subtext'] = lang($field_name.'_field_subtext');
			}

			// --------------------------------------------
			//  List Items
			// --------------------------------------------

			if ($field_type == 'select')
			{
				$row['field_list_options'] = array();

				foreach (explode("\n", trim($row['field_list_items'])) as $v)
				{
					$v = trim($v);
					$row['field_list_options'][$v] = $v;
				}
			}

			// --------------------------------------------
			//  Set
			// --------------------------------------------

			$vars['custom_fields'][$field_id] = $row;

			$this->default_settings[$field_id.'_element']	= '';
			$this->default_settings[$field_id.'_default']	= '';
		}

		$vars['all_fields']	 = array_merge($vars['all_fields'], $vars['custom_fields']);
		$vars['unique_fields']  = array_merge($vars['required_fields'], $vars['custom_fields']);

		function field_compare($a, $b)
		{
			return strcmp($a["field_label"], $b["field_label"]);
		}

		uasort($vars['all_fields'], 'field_compare');

		// --------------------------------------------
		//  Member ID is a Unique Field but not a REQUIRED Field
		//  - This is a clever way to make it first on the list.  Well, semi-clever.
		//  - To be honest, I think it is a bit tedious.  OH well... -Paul
		// --------------------------------------------

		$arr = array_reverse($vars['unique_fields'], TRUE);
		$arr['member_id'] = array('field_label' => lang('member_id'));
		$vars['unique_fields'] = array_reverse($arr, TRUE);

		// --------------------------------------------
		//  Remove These
		//  - Nothing for images (maybe in the future we can import images, not today)
		//  - All stats and dates
		// --------------------------------------------

		foreach($this->ignore_fields() as $name)
		{
			unset($vars['all_fields'][$name]);
		}

		// --------------------------------------------
		//  Form Values - Based off default settings and $settings array()
		// --------------------------------------------

		foreach(array_merge($this->default_settings, $settings) as $setting => $value)
		{
			if (isset($this->default_settings[$setting]) && is_array($this->default_settings[$setting]))
			{
				// Must be an array, default will already be one, so we check here
				$vars['selected_'.$setting] = $this->output((is_array($value)) ?
																		  $value :
																		  explode('|', $value));
			}
			else
			{
				$vars['selected_'.$setting] = $this->output($value);
			}
		}

		// --------------------------------------------
		//  Return the Channel Settings Form
		// --------------------------------------------

		$this->add_crumb(lang('importer_member_settings_form').' - '.$settings['profile_name']);
		$this->build_crumbs();
		$vars['module_menu_highlight'] = 'module_homepage';

		//jQuery will soon only load at the end of the page after EE 2.6
		ee()->cp->add_to_foot(
			'<script src="' . $this->importer_theme_url .
				'js/content_types/members.js"></script>'
		);

		return $this->view(
			'settings_form.html',
			$vars,
			TRUE,
			PATH_THIRD.'importer/content_types/importer.content_type.members/views/settings_form.html'
		);
	}

	// --------------------------------------------------------------------

	/**
	 *	Validate and Save Setting Fields
	 *
	 *	@access		public
	 *  @param	  integer
	 *	@param		array
	 *	@param		array
	 *	@return		bool|string  - Returns either TRUE or an error message
	 */

	public function save_settings($profile_id, $elements, $settings)
	{
		// --------------------------------------------
		//  Check for Required Fields
		// --------------------------------------------

		if ( empty($_POST['unique_field_element']) OR empty($_POST['unique_field']))
		{
			return $this->error_page('A Unique Field Element with valid EE Field Are Required.');
		}

		$group_id_default = ee()->input->post('group_id_default');

		if ( empty($_POST['group_id_element']) && ! ctype_digit($group_id_default))
		{
			return $this->error_page('Must choose a valid Member Group element or valid default option.');
		}

		if ( empty($_POST['username_element']) && ee()->input->post('username_default') == 'none')
		{
			return $this->error_page('Must choose a valid Username element or valid default option.');
		}

		if ( empty($_POST['screen_name_element']) && ee()->input->post('screen_name_default') == 'none')
		{
			return $this->error_page('Must choose a valid Screen Name element or valid default option.');
		}

		if ( empty($_POST['email_element']))
		{
			return $this->error_page('Must choose a valid Email element.');
		}

		// --------------------------------------------
		//  Forced Security!
		// --------------------------------------------

		$_POST['unique_id_default'] = 'auto';
		$_POST['unique_id_element'] = '';
		$_POST['password_default']  = 'auto';
		$_POST['password_element']  = '';

		// --------------------------------------------
		//  Custom Fields
		// --------------------------------------------

		$query = ee()->db->query("SHOW COLUMNS FROM exp_members");

		foreach($query->result_array() as $row)
		{
			$field_name = $row['Field'];

			if ( in_array($field_name, $this->ignore_fields())) continue;

			// Keep our default settings
			if ( ! empty($this->default_settings[$field_name.'_element'])) continue;

			// Create defaults for fields (POST data is handled below)
			$this->default_settings[$field_name.'_element']	= '';
			$this->default_settings[$field_name.'_default']	= '';
		}

		// --------------------------------------------
		//  Retrieve the Member Custom Fields
		// --------------------------------------------

		$vars['custom_fields'] = array();

		$query = ee()->db->query("SELECT *,
								  m_field_name AS field_name,
								  m_field_label AS field_label,
								  m_field_id AS field_id,
								  m_field_type AS field_type,
								  m_field_required AS field_required,
								  m_field_list_items AS field_list_items
								  FROM exp_member_fields
								  ORDER BY m_field_order");

		foreach($query->result_array() as $row)
		{
			extract($row);

			$field_name = 'm_field_id_'.$row['field_id'];

			if ( in_array($field_name, $this->ignore_fields())) continue;

			// Create defaults for fields (POST data is handled below)
			$this->default_settings[$field_name.'_element']	= '';
			$this->default_settings[$field_name.'_default']	= '';

			// Do a little validation for list_items?
			// One wonders why a SuperAdmin would do an import and then hack the form...
			// Definitely should validate during the Importing though.
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
	 *	Ignore Fields
	 *
	 *	@access		private
	 *	@return		array
	 */
	 private function ignore_fields()
	 {
		// --------------------------------------------
		//  Remove These
		//  - Nothing for images (maybe in the future we can import images, not today)
		//  - All stats and dates
		// --------------------------------------------

		$list[] = 'member_id';
		$list[] = 'authcode';
		$list[] = 'salt';
		$list[] = 'crypt_key';

		$list[] = 'avatar_filename';
		$list[] = 'avatar_width';
		$list[] = 'avatar_height';
		$list[] = 'photo_filename';
		$list[] = 'photo_width';
		$list[] = 'photo_height';
		$list[] = 'sig_img_filename';
		$list[] = 'sig_img_width';
		$list[] = 'sig_img_height';

		$list[] = 'total_comments';
		$list[] = 'total_forum_topics';
		$list[] = 'total_forum_posts';
		$list[] = 'last_view_bulletins';
		$list[] = 'last_bulletin_date';
		$list[] = 'last_entry_date';
		$list[] = 'last_comment_date';
		$list[] = 'last_forum_post_date';
		$list[] = 'last_email_date';
		$list[] = 'total_entries';
		$list[] = 'last_activity';
		$list[] = 'last_visit';

		// And these just seem uninteresting for an import
		// If someone complains, it is easy enough to comment items out

		$list[] = 'tracker';
		$list[] = 'pmember_id';
		$list[] = 'rte_enabled';
		$list[] = 'rte_toolset_id';
		$list[] = 'private_messages';
		$list[] = 'ignore_list';
		$list[] = 'smart_notifications';
		$list[] = 'show_sidebar';
		$list[] = 'quick_tabs';
		$list[] = 'localization_is_site_default';
		$list[] = 'notify_of_pm';
		$list[] = 'notify_by_default';
		$list[] = 'display_signatures';
		$list[] = 'display_avatars';
		$list[] = 'parse_smileys';
		$list[] = 'msn_im'; // Seriously, does anyone use this?

		return $list;
	 }

	// --------------------------------------------------------------------

	/**
	 *	Perform Import
	 *
	 *	@access		public
	 *	@param		array		$settings
	 *	@param		array		$data
	 *	@return		bool|array	Returns either FALSE or an array of debug information
	 */
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
		//  Initial Data Checks
		// --------------------------------------------

		if ( ! is_array($data))
		{
			// Seriously, why are you even here?
			return FALSE;
		}

		$this->settings = $settings;

		// --------------------------------------------
		//  Import the Members!  Yayyyyyy!  ::Kermit flailing::
		// --------------------------------------------

		$import_start_time = microtime(TRUE);

		$this->statistics	= array(
			'members_inserted'			=> 0,
			'members_updated'			=> 0,
			'total_members_processed' 	=> 0,
			'member_ids'				=> array(),
			'debugging'					=> array()
		);

		$this->statistics['debugging'][] = "Importing - START".$this->debug_time_memory($import_start_time);

		// --------------------------------------------
		//  Fetch List of Member Fields
		// --------------------------------------------

		$member_fields		= array();
		$required_fields	= array(
								'group_id'    => 'group_id', 
								'username'    => 'username',
								'screen_name' => 'screen_name',
								'email'       => 'email', 
								'unique_id'   => 'unique_id', 
								'password'    => 'password'
								);

		$disabled_fields	= array('password', 'unique_id');

		$query = ee()->db->query("SHOW COLUMNS FROM exp_members");

		foreach($query->result_array() as $row)
		{
			$field_name = $row['Field'];

			if ( in_array($field_name, $this->ignore_fields())) continue;

			$member_fields[$field_name] = $field_name;
		}

		// --------------------------------------------
		//  Retrieve the Member Custom Fields
		//  - Required and Validate Fields
		// --------------------------------------------

		$validate_fields	= array();

		$query = ee()->db->query("SELECT
									  m_field_name AS field_name,
									  m_field_id AS field_id,
									  m_field_type AS field_type,
									  m_field_required AS field_required,
									  m_field_list_items AS field_list_items
								  FROM exp_member_fields
								  ORDER BY m_field_order");

		foreach($query->result_array() as $row)
		{
			$field_name = 'm_field_id_'.$row['field_id'];

			if ( in_array($field_name, $this->ignore_fields())) continue;

			$member_fields[$field_name] = $field_name;

			if ($row['field_type'] == 'select')
			{
				$validate_fields[$row['field_name']] = array();

				foreach (explode("\n", trim($row['field_list_items'])) as $v)
				{
					$v = trim($v);
					$validate_fields[$row['field_name']][$v] = $v;
				}
			}

			if ($row['field_required'] == 'y')
			{
				$required_fields['m_field_id_'.$row['field_id']] = $row['field_name'];
			}
		}

		// --------------------------------------------
		//  Validation and Error Messages
		// --------------------------------------------

		ee()->lang->loadfile('member_import');
		ee()->load->library('validate');
		ee()->load->helper('security');

		// --------------------------------------------
		//  Cycle Through Array
		// --------------------------------------------

		foreach($data as $key => $entry_data)
		{
			// For memory reasons, all of our data comes in as a serialized array.
			// See $this->actions()->serialize_data_array() for more information
			$entry_data = unserialize($entry_data);

			if (empty($entry_data) OR ! is_array($entry_data)) continue; // No data for this entry is possible.

			$this->statistics['debugging'][] = "Member #{$key} - Start Processing".$this->debug_time_memory($import_start_time);

			// --------------------------------------------
			//  Default Variables
			// --------------------------------------------

			$new			= TRUE;
			$member_id	  = NULL;
			$member_data	= array();
			$custom_data	= array();

			ee()->validate->cur_username		= '';
			ee()->validate->cur_screen_name	= '';
			ee()->validate->cur_email			= '';

			// --------------------------------------------
			//  Check Unique Fields
			//  - i.e., Let's find a member match, if one exists
			// --------------------------------------------

			// This should never happen, but even I play with the DB data sometimes.
			if ( ! isset($settings['unique_field']) OR ! isset($settings['unique_field_element'])) continue;

			if ( empty($entry_data[$settings['unique_field_element']]) OR ! in_array($settings['unique_field'], $member_fields))
			{
				$this->statistics['debugging'][] = " -- No Unique Field Match ".$this->debug_time_memory($import_start_time);
			}

			// Unique Field Two Set - But Invalid Member Field OR No Data for It
			if ( ! empty($settings['unique_field_two'])
				&&
				(
					empty($entry_data[$settings['unique_field_element']])
					OR
					! in_array($settings['unique_field_two'], $member_fields)
				)
			)
			{
				$this->statistics['debugging'][] = " -- No Unique Field Two Match ".$this->debug_time_memory($import_start_time);
			}

			$sql = "SELECT m.member_id, username, screen_name, email FROM exp_members AS m, exp_member_data AS md ";
			$sql .= "WHERE `".$settings['unique_field']."` = '".ee()->db->escape_str($entry_data[$settings['unique_field_element']])."' ";

			if ( ! empty($settings['unique_field_two']))
			{
				$sql .= "AND `".$settings['unique_field_two']."` = '".ee()->db->escape_str($entry_data[$settings['unique_field_two_element']])."' ";
			}

			$sql .= "LIMIT 1";

			$query = ee()->db->query($sql);

			if ($query->num_rows() > 0)
			{
				$new = FALSE;
				$member_id = $query->row('member_id');

				ee()->validate->cur_username		= $query->row('username');
				ee()->validate->cur_screen_name	= $query->row('screen_name');
				ee()->validate->cur_email			= $query->row('email');

				$this->statistics['debugging'][] = " -- Unique Fields Matched Member ID #{$member_id} ".$this->debug_time_memory($import_start_time);
			}
			else
			{
				$this->statistics['debugging'][] = " -- Unique Fields NOT Matched, New Member ".$this->debug_time_memory($import_start_time);
			}

			// --------------------------------------------
			//  Go Through Member Fields
			// --------------------------------------------

			$loop	   = 0;
			$max_loops  = count($member_fields);

			foreach($member_fields as $field_name => $database_name)
			{
				$loop++;

				if ($loop > $max_loops) break;

				// Disabled fields are created automatically for *NEW* Members
				// Disabled fields are ignored for *EXISTING* Members
				if (in_array($field_name, $disabled_fields))
				{
					if ($new === TRUE)
					{
						if(stristr($database_name, 'm_field_id_'))
						{
							$custom_data[$database_name] = $this->auto($field_name);
						}
						else
						{
							$member_data[$database_name] = $this->auto($field_name);
						}
					}

					continue;
				}

				// --------------------------------------------
				//  Method Calling for Default Values May Require Multiple Loops
				//  This prevents us from doing extra work if field data already set.
				// --------------------------------------------

				if(stristr($database_name, 'm_field_id_'))
				{
					if ( ! empty($custom_data[$database_name])) continue;
				}
				else
				{
					if ( ! empty($member_data[$database_name])) continue;
				}

				// --------------------------------------------
				//  Set Empty String of Default
				// --------------------------------------------

				$value = FALSE;

				// --------------------------------------------
				//  Element Exists and Has Data
				// --------------------------------------------

				// Got an element specified?  Got data for that element?
				if ( ! empty($settings[$field_name.'_element']) && ! empty($entry_data[$settings[$field_name.'_element']]))
				{
					$value = trim($entry_data[$settings[$field_name.'_element']]);
				}

				// --------------------------------------------
				//  Default Value is Set?
				//  - Can either be a callable class method
				//  - Or, can be a simple string
				//  - Only for New Members
				// --------------------------------------------

				elseif ( $new === TRUE  && ! empty($settings[$field_name.'_default']) && is_string($settings[$field_name.'_default']))
				{
					// Default value is an object method
					if (substr($settings[$field_name.'_default'], 0, strlen('method:')) == 'method')
					{
						$method = substr($settings[$field_name.'_default'], strlen('method:'));

						if (is_callable(array($this, $method)))
						{
							$value = $this->{$method}($field_name, $entry_data, array_merge($member_data, $custom_data));

							// This means it is unable to be set yet. Likely because it is based
							// off another field that is still empty.  In this case, what we do
							// is remove it and append it to back to this array and let it cycle through again.
							// We allow this to happen a maximum of X times.
							if ($value === FALSE)
							{
								unset($member_fields[$field_name]);
								$member_fields[$field_name] = $database_name;
								$max_loops++;

								$this->statistics['debugging'][] = " -- Extra Loop Added for '{$field_name}' ".$this->debug_time_memory($import_start_time);

								continue;
							}
						}
					}
					else
					{
						$value = $settings[$field_name.'_default'];
					}
				}

				// --------------------------------------------
				//  If Custom Field with Select, Validation Value
				// --------------------------------------------

				if ( ! empty($value) && isset($validate_fields[$field_name]) && is_array($validate_fields[$field_name]))
				{
					if ( ! in_array($value, $validate_fields[$field_name]))
					{
						$this->statistics['debugging'][] = " -- Invalid Value in Data for '{$field_name}' ".$this->debug_time_memory($import_start_time);
						continue;
					}
				}

				// --------------------------------------------
				//  Set Value in Our INSERT/UPDATE arrays
				// --------------------------------------------

				if ($value !== FALSE)
				{
					if(stristr($database_name, 'm_field_id_'))
					{
						$custom_data[$database_name] = $value;
					}
					else
					{
						$member_data[$database_name] = $value;
					}
				}
			}

			// --------------------------------------------
			//  Check for Required Fields Required for New Members!
			// --------------------------------------------

			if ( $new === TRUE)
			{
				$missing_required = FALSE;

				foreach($required_fields AS $required_field_id => $required_field)
				{
					if ( empty($custom_data[$required_field_id]) && empty($member_data[$required_field_id]))
					{
						$missing_required =
							($missing_required === FALSE) ?
							array($required_field) :
							array_merge($missing_required, array($required_field));
					}
				}

				if ( $missing_required !== FALSE)
				{
					$this->statistics['debugging'][] = " -- Missing Required Fields '".implode("', '",$missing_required)."' ".$this->debug_time_memory($import_start_time);
					continue;
				}
			}

			// --------------------------------------------
			//  Validation!
			// --------------------------------------------

			ee()->validate->member_id			= $member_id;
			ee()->validate->val_type			= ($new === TRUE) ? 'new' : 'update';
			ee()->validate->fetch_lang			= TRUE;
			ee()->validate->require_cpw			= FALSE;
			ee()->validate->enable_log			= FALSE;

			if ( isset($member_data['username']))
			{
				ee()->validate->username = $member_data['username'];

				ee()->validate->validate_username();

				if ( ! empty(ee()->validate->errors))
				{
					foreach(ee()->validate->errors as $key => $val)
					{
						ee()->validate->errors[$key] = $val." (Username: '".$member_data['username']."' - ".lang('within_user_record')." '".$member_data['username']."')";
					}
					$this->errors[] = ee()->validate->errors;
					unset(ee()->validate->errors);
				}
			}

			if ( isset($member_data['screen_name']))
			{
				ee()->validate->screen_name = $member_data['screen_name'];

				ee()->validate->validate_screen_name();

				if ( ! empty(ee()->validate->errors))
				{
					foreach(ee()->validate->errors as $key => $val)
					{
						ee()->validate->errors[$key] = $val." (Screen Name: '".$member_data['screen_name']."' - ".lang('within_user_record')." '".$member_data['screen_name']."')";
					}

					$this->errors[] = ee()->validate->errors;
					unset(ee()->validate->errors);
				}
			}

			if ( isset($member_data['email']))
			{
				ee()->validate->email = $member_data['email'];

				if ( ! empty(ee()->validate->errors))
				{
					foreach(ee()->validate->errors as $key => $val)
					{
						ee()->validate->errors[$key] = $val." (Email: '".$member_data['email']."' - ".lang('within_user_record')." '".$member_data['email']."')";
					}
					$this->errors[] = ee()->validate->errors;
					unset(ee()->validate->errors);
				}
			}

			// --------------------------------------------
			//  Few More Requirements
			// --------------------------------------------

			if ($new === TRUE)
			{
				$member_data['language']			= (
					empty($member_data['language'])
				) ? 'english' : $member_data['language'];

				$member_data['timezone']			= (
					empty($member_data['timezone'])
				) ? ee()->config->item('server_timezone') :
					$member_data['timezone'];

				$member_data['time_format']			= (
					empty($member_data['time_format'])
				) ? 'us' : $member_data['time_format'];

				if (version_compare($this->ee_version, '2.6.0', '<'))
				{
					$member_data['daylight_savings']	= (
						empty($member_data['daylight_savings'])
					) ? 'y' : $member_data['daylight_savings'];
				}

				$member_data['ip_address']			= (
					empty($member_data['ip_address'])
				) ? '0.0.0.0' : $member_data['ip_address'];

				$member_data['join_date']			= (
					empty($member_data['join_date'])
				) ? ee()->localize->now : $member_data['join_date'];
			}

			// --------------------------------------------
			//  Insert
			// --------------------------------------------

			if ( $new === TRUE)
			{
				// -------------------------------------
				//	importing password?
				// -------------------------------------

				if (FALSE && isset($entry_data['password']))
				{
					ee()->load->library('auth');
					$pass_data = ee()->auth->hash_password(stripslashes($entry_data['password']));
					$member_data['password']	= $pass_data['password'];
					$member_data['salt']		= $pass_data['salt'];
				}

				// -------------------------------------
				//	final insert for new members
				// -------------------------------------

				ee()->db->insert('exp_members', $member_data);

				$member_id = ee()->db->insert_id();

				$custom_data['member_id'] = $member_id;

				ee()->db->insert('exp_member_data', $custom_data);
				ee()->db->insert('exp_member_homepage', array('member_id' => $member_id));

				$this->statistics['member_ids'][] = $member_id;

				$this->statistics['members_inserted']++;
			}
			else
			{
				ee()->db->update('exp_members', $member_data, array('member_id' => $member_id));

				if ( ! empty($custom_data))
				{
					ee()->db->update('exp_member_data', $custom_data, array('member_id' => $member_id));
				}

				$this->statistics['member_ids'][] = $member_id;

				$this->statistics['members_updated']++;
			}

			// --------------------------------------------
			//  Update Entries
			// --------------------------------------------

			$this->statistics['total_members_processed']++;
		}

		//  Update Statistics
		ee()->stats->update_member_stats();

		$this->statistics['debugging'][] = "Member Loop Finished".$this->debug_time_memory($import_start_time);
		$this->statistics['debugging'][] = "Perform Import Finished".$this->debug_time_memory($import_start_time);

		return $this->statistics;
	}
	// END perform_import()


	// --------------------------------------------------------------------

	/**
	 *	Auto Generated Field Value
	 *
	 *	@access		protected
	 *	@param		string  $field_name
	 *	@return		string
	 */

	protected function auto($field_name)
	{
		ee()->load->helper('security');

		if ($field_name == 'password')
		{
			return sha1(mt_rand());
		}

		if ($field_name == 'unique_id')
		{
			return random_string('encrypt');
		}

		// By default we just return a random string.
		return random_string();
	}

	// --------------------------------------------------------------------

	/**
	 *	Auto Generated Empty Value
	 *
	 *	@access		protected
	 *	@param		string  $field_name
	 *	@return		string
	 */

	protected function none($field_name)
	{
		return '';
	}

	// --------------------------------------------------------------------

	/**
	 *	Us Username
	 *
	 *	@access		protected
	 *	@param		string  $field_name
	 *	@return		string
	 */

	protected function use_username($current_data)
	{
		if ( ! empty($current_data['username'])) return $current_data['username'];

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 *	Use Email
	 *
	 *	@access		protected
	 *	@param		string  $field_name
	 *	@return		string
	 */

	protected function use_email($current_data)
	{
		if ( ! empty($current_data['email'])) return $current_data['email'];

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 *	Do Accumulated Inserts
	 *
	 *	Does all of the categories and Solspace tags in one go instead of doing it piecemeal
	 *
	 *	@access		public
	 *	@param		array
	 *	@param		array
	 *	@param		array
	 *	@return		string
	 */

	protected function do_accumulated_inserts($categories, $solspace_tags, $settings)
	{}
	// END do_accumulated_inserts();

}
// END CLASS Importer_content_type_members