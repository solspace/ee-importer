<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Control Panel
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.6
 * @filesource	importer/mcp.importer.php
 */

require_once 'addon_builder/module_builder.php';

class Importer_mcp extends Module_builder_importer
{
	private $row_limit		= 50;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct( $switch = TRUE )
	{
		parent::__construct('importer');

		if ((bool) $switch === FALSE) return; // Install or Uninstall Request

		// --------------------------------------------
		//  Module Menu Items
		// --------------------------------------------

		$menu	= array(
			'module_homepage'		=> array(
				'link'  => $this->base,
				'title' => lang('importer_homepage')
			),
			'module_import_log'		=> array(
				'link'  => $this->base.'&method=import_log',
				'title' => lang('import_log')
			),
			'module_documentation'		=> array(
				'link'  => IMPORTER_DOCS_URL,
				'new_window' => TRUE,
				'title' => lang('online_documentation')
			),
		);

		$this->cached_vars['lang_module_version'] 	= lang('importer_module_version');
		$this->cached_vars['module_version'] 		= IMPORTER_VERSION;
		$this->cached_vars['module_menu_highlight'] = 'module_homepage';
		$this->cached_vars['module_menu'] 			= $menu;
		$this->cached_vars['importer_theme_path']	= $this->sc->addon_theme_url;
		$this->cached_vars['connection_test_url']	= $this->get_action_url(
			'ajax_connnection_test'
		);

		// --------------------------------------------
		//  Batch Clean Up
		// --------------------------------------------

		srand(time());

		if ((rand() % 100) < 10)
		{
			ee()->db->delete(
				'exp_importer_batches',
				array('batch_date <' => (time() - 7 * 24 * 60 * 60))
			);

			ee()->db->delete(
				'exp_importer_batch_data',
				array('batch_date <' => (time() - 7 * 24 * 60 * 60))
			);
		}

		// -------------------------------------
		//	special crap for our beta versions
		// -------------------------------------

		if(
			(
				$this->version_compare(
					$this->database_version(),
					'<',
					IMPORTER_VERSION
				)
				AND
				//EE checks like this instead of version_compare
				! (IMPORTER_VERSION > $this->database_version())
			)
			OR
			! $this->extensions_enabled()
		)
		{
			// For EE 2.x, we need to redirect the request to Update Routine
			$_GET['method'] = 'importer_module_update';

			$updated = TRUE;
		}

		// -------------------------------------
		//	default package JS ans CSS
		// -------------------------------------

		ee()->cp->load_package_css('importer_cp');
		ee()->cp->load_package_js('importer_cp');

		ee()->cp->add_js_script(array(
			'ui'		=> array('dialog'),
			'plugin'	=> array('overlay')
		));

	}
	// END __construct()


	// --------------------------------------------------------------------

	/**
	 *	The Main CP Index Page
	 *
	 *	@access		public
	 *	@param		string		$message - That little message display thingy
	 *	@return		string
	 */

	public function index($message = '')
	{
		//--------------------------------------------
		//	Message
		//--------------------------------------------

		if ($message == '' AND ee()->input->get_post('msg') !== FALSE)
		{
			$message = lang(ee()->input->get_post('msg'));
		}
		elseif ($message == '')
		{
			if ( ($basepath = $this->actions()->check_cache_directory()) === FALSE)
			{
				$message = lang('unable_to_create_importer_directory');
			}
		}

		// --------------------------------------------
		//  List of Import Data Types
		// --------------------------------------------

		$this->cached_vars['datatypes'] = array();

		foreach($this->actions()->list_datatypes() as $name => $path)
		{
			$this->cached_vars['datatypes'][$name] = $this->actions()->load_datatype($name)->label;
		}

		// --------------------------------------------
		//  List of Import Content Types
		// --------------------------------------------

		$this->cached_vars['content_types'] = array();

		foreach($this->actions()->list_content_types() as $name => $path)
		{
			$this->cached_vars['content_types'][$name] = $this->actions()->load_content_type($name)->label;
		}

		// --------------------------------------------
		//  Current Import Profiles
		// --------------------------------------------

		$this->cached_vars['importer_profiles'] = $this->data->importer_profiles();

		foreach($this->cached_vars['importer_profiles'] as &$data)
		{
			$data['datatype'] = (isset($this->cached_vars['datatypes'][$data['datatype']])) ?
								$this->cached_vars['datatypes'][$data['datatype']] :
								lang('invalid_datatype');

			$data['content_type'] = (isset($this->cached_vars['content_types'][$data['content_type']])) ?
								$this->cached_vars['content_types'][$data['content_type']] :
								lang('invalid_content_type');
		}

		// --------------------------------------------
		//  Cron URL
		// --------------------------------------------

		$this->cached_vars['cron_url'] = ee()->functions->fetch_site_index(0, 0).
										 QUERY_MARKER.
										 'ACT='.
										 ee()->functions->insert_action_ids(ee()->functions->fetch_action_id('Importer', 'cron_import'));

		//----------------------------------------
		//	 Build page
		//----------------------------------------

		$this->add_crumb(lang('importer_module_name'));

		$this->cached_vars['message'] 	= $message;

		$this->cached_vars['current_page'] = $this->view('home.html', NULL, TRUE);

		$this->add_right_link(lang('clear_batches'), $this->base.'&method=clear_batches');

		ee()->cp->load_package_js('importer_home');

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	// END index()


	// --------------------------------------------------------------------

	/**
	 *	New Profile Page
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function start_profile()
	{
		$datatypes = $this->actions()->list_datatypes();

		if ( empty($_POST['datatype']) OR ! isset($datatypes[$_POST['datatype']]))
		{
			ee()->functions->redirect($this->base);
		}

		$content_types = $this->actions()->list_content_types();

		if ( empty($_POST['content_type']) OR ! isset($content_types[$_POST['content_type']]))
		{
			ee()->functions->redirect($this->base);
		}

		$this->cached_vars['profile_datatype']		= $_POST['datatype'];
		$this->cached_vars['profile_content_type']	= $_POST['content_type'];
		$this->cached_vars['profile_name']			= '';
		$this->cached_vars['profile_instructions']	= '';
		$this->cached_vars['importer_profile_edit']	= FALSE;
		$this->cached_vars['importer_profile_id']	= '';

		//----------------------------------------
		//	 Build page
		//----------------------------------------

		$this->add_crumb(lang('importer_profile'));

		$this->cached_vars['current_page'] = $this->view('profile_name_instructions.html', NULL, TRUE);

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	// END start_profile()


	// --------------------------------------------------------------------

	/**
	 *	Edit Profile
	 *
	 *	Loads up a profile and starts with the name/instructions page.
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function edit_profile_name()
	{
		// --------------------------------------------
		//  Default Data!
		// --------------------------------------------

		// One cannot change a data type for a profile
		$this->cached_vars['profile_datatype']		= '';
		// One cannot change a data type for a profile
		$this->cached_vars['profile_content_type']	= '';
		$this->cached_vars['profile_name']			= '';
		$this->cached_vars['profile_instructions']	= '';
		$this->cached_vars['importer_profile_edit']	= TRUE;
		$this->cached_vars['importer_profile_id']	= '';

		// --------------------------------------------
		//  Data from DB?
		// --------------------------------------------

		if (ee()->input->get_post('profile_id') !== FALSE &&
			ctype_digit(ee()->input->get_post('profile_id'))
		)
		{
			$profile_data = $this->data->get_profile_data(
				ee()->input->get_post('profile_id')
			);

			if (empty($profile_data))
			{
				return FALSE;
			}

			$this->cached_vars['importer_profile_id'] = ee()->input->get_post('profile_id');

			foreach($profile_data as $key => $value)
			{
				$this->cached_vars['profile_'.$key] = $value;
			}
		}
		else
		{
			return $this->start_profile();
		}

		//----------------------------------------
		//	 Build page
		//----------------------------------------

		$this->add_crumb(lang('importer_edit_profile'));

		$this->cached_vars['current_page'] = $this->view(
			'profile_name_instructions.html',
			NULL,
			TRUE
		);

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	// END edit_profile()


	// --------------------------------------------------------------------

	/**
	 *	Save Name/Instructions
	 *
	 *	Takes the Name/Description Form and saves its data.
	 *	On Edit, they can simply change this.
	 *	On a new importer profile, they must continue onto the next step.
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function save_profile_name()
	{
		$continue = TRUE;

		// --------------------------------------------
		//  Continue?  Nein!
		// --------------------------------------------

		$settings = array();

		if ( ! empty($_POST['profile_id']) &&
			is_numeric($_POST['profile_id']) &&
			isset($_POST['submit_finish']))
		{
			$query = ee()->db
						->select('COUNT(*) AS count')
						->where('profile_id', $_POST['profile_id'])
						->get('importer_profiles');

			if ($query->row('count') > 0)
			{
				$continue = FALSE;
			}
		}

		// --------------------------------------------
		//  Data Check
		// --------------------------------------------

		$data['name']			= (ee()->input->post('name') !== FALSE) ?
									ee()->input->post('name') : '';
		$data['instructions']	= (ee()->input->post('instructions') !== FALSE) ?
									ee()->input->post('instructions') : '';

		if (empty($data['name']))
		{
			return $this->error_page('invalid_importer_profile_name');
		}

		ee()->db->select('COUNT(*) AS count');

		if ( ! empty($_POST['profile_id']) && is_numeric($_POST['profile_id']))
		{
			ee()->db->where('profile_id !=', $_POST['profile_id']);
		}

		$query = ee()->db->where('name', $data['name'])
								->get('importer_profiles');

		if ($query->row('count') > 0)
		{
			return $this->error_page('error_duplicate_profile_name');
		}


		// --------------------------------------------
		//  Save
		// --------------------------------------------

		if ( ! empty($_POST['profile_id']) && is_numeric($_POST['profile_id']))
		{
			$profile_id = $_POST['profile_id'];

			ee()->db->where('profile_id', $_POST['profile_id']);
			ee()->db->update('exp_importer_profiles', $data);
		}
		else
		{
			// --------------------------------------------
			//  Data Type Only Settable by New Profiles
			// --------------------------------------------

			$datatypes = $this->actions()->list_datatypes();

			if (ee()->input->get_post('datatype') === FALSE OR
				ee()->input->get_post('datatype') == '' OR
				! isset($datatypes[ee()->input->get_post('datatype')]))
			{
				return $this->error_page('invalid_importer_profile_datatype');
			}

			// --------------------------------------------
			//  Content Type Only Settable by New Profiles
			// --------------------------------------------

			$content_types = $this->actions()->list_content_types();

			if (ee()->input->get_post('content_type') === FALSE OR
				ee()->input->get_post('content_type') == '' OR
				! isset($content_types[ee()->input->get_post('content_type')]))
			{
				return $this->error_page('invalid_importer_profile_content_type');
			}

			$data['datatype']		= ee()->input->get_post('datatype');
			$data['content_type']	= ee()->input->get_post('content_type');
			$data['site_id']		= ee()->config->item('site_id');
			$data['hash']			= ee()->functions->random('alnum', 32);

			ee()->db->insert('exp_importer_profiles', $data);

			$profile_id = ee()->db->insert_id();
		}

		// --------------------------------------------
		//  AJAX Response
		// --------------------------------------------

		if ($this->is_ajax_request())
		{
			$this->send_ajax_response(array(
				'success' => TRUE,
				'heading' => lang('importer_profile_updated'),
				'message' => lang('success_importer_profile_name_updated'),
				'content' => lang('success_importer_profile_name_updated')
			));
		}


		// --------------------------------------------
		//  Not Continuing?  Wish Them Well and Send Back to Homepage
		// --------------------------------------------

		if ($continue == FALSE)
		{
			return ee()->functions->redirect(
				$this->base . '&msg=success_importer_profile_name_updated'
			);
		}

		// --------------------------------------------
		//  Continuing? Send them to the next page...
		// --------------------------------------------

		ee()->functions->redirect(
			$this->base.'&method=edit_profile_source&profile_id='.$profile_id
		);
	}
	// END save_profile_name()


	// --------------------------------------------------------------------

	/**
	 *	Edit the Source of an Importer Profile
	 *
	 *	This is *where* this Importer Profile will
	 *	get its data AND *what* type it is.
	 *	Example:  Where = Remote URL, What = XML
	 *	The Where is built in options of local file,
	 *	remote URL, FTP, SFTP, and manual file upload
	 *	The What is the Data Types, which will be
	 *	stored in the ./datatypes/ folder.
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function edit_profile_source()
	{
		// --------------------------------------------
		//  Check for Profile ID
		// --------------------------------------------

		if ( ee()->input->get_post('profile_id') === FALSE OR
			! is_numeric(ee()->input->get_post('profile_id')))
		{
			return $this->error_page('invalid_importer_profile_id');
		}

		// --------------------------------------------
		//  Retrieve Settings
		// --------------------------------------------

		$settings = array(
			'datatype'			=> '',
			'data_source'		=> '',

			'filename'			=> '',
			'remote_url'		=> '',
			'http_auth_username'=> '',
			'http_auth_password'=> '',
			'ftp_host'			=> '',
			'ftp_username'		=> '',
			'ftp_password'		=> '',
			'ftp_port'			=> '',
			'ftp_path'			=> ''
		);

		foreach($settings as $setting => $value)
		{
			$this->cached_vars['importer_'.$setting] = $value;
		}

		// --------------------------------------------
		//  Retrieve Profile Data from Database and Set View Variables
		// --------------------------------------------

		$query = ee()->db
					->get_where(
						'exp_importer_profiles',
						array(
							'profile_id' => ee()->input->get_post('profile_id')
						)
					);

		if ($query->num_rows() == 0)
		{
			return $this->error_page('invalid_importer_profile_id');
		}

		$datatype = '';

		foreach($query->result_array() as $row)
		{
			foreach($row as $key => $value)
			{
				if ( $key == 'datatype')
				{
					$datatype = $value;
				}

				$this->cached_vars['importer_'.$key] = $value;
			}
		}

		// --------------------------------------------
		//  Valid DataType
		// --------------------------------------------

		$datatypes = $this->actions()->list_datatypes();

		if ( ! isset($datatypes[$datatype]))
		{
			return $this->error_page($this->base.'&msg=invalid_datatype_given');
		}

		// --------------------------------------------
		//  Encryption Required for Decoding Username/Passwords
		// --------------------------------------------

		ee()->load->library('encrypt');

		if (ee()->config->item('encryption_key') == '')
		{
			ee()->encrypt->set_key(
				md5(ee()->db->username . ee()->db->password)
			);
		}

		$encode_me = array(
			'http_auth_username',
			'http_auth_password',
			'ftp_username',
			'ftp_password'
		);

		// --------------------------------------------
		//  Retrieve Profile Settings
		// --------------------------------------------

		$query = ee()->db->get_where(
			'exp_importer_profile_settings',
			array('profile_id' => ee()->input->get_post('profile_id'))
		);

		foreach($query->result_array() as $row)
		{
			if (in_array($row['setting'], $encode_me))
			{
				$row['value'] = ee()->encrypt->decode(base64_decode($row['value']));
			}

			$settings[$row['setting']] = $row['value'];
			$this->cached_vars['importer_'.$row['setting']] = $row['value'];
		}

		// --------------------------------------------
		//  More Data Sources
		// --------------------------------------------

		$this->cached_vars['data_sources'] = $this->actions()->default_data_sources;

		$data_sources = $this->actions()->load_datatype($datatype)->data_sources;

		if ( ! empty($data_sources) && is_array($data_sources))
		{
			$this->cached_vars['data_sources'] = $data_sources;
		}

		$this->cached_vars['additional_source_fields'] = '';

		if ( method_exists($this->actions()->load_datatype($datatype), 'data_source_fields_form'))
		{
			$this->cached_vars['additional_source_fields'] = $this->actions()->load_datatype($datatype)->data_source_fields_form($settings);
		}

		// --------------------------------------------
		//  Additional Fields for Data Type?
		// --------------------------------------------

		if (method_exists($this->actions()->load_datatype($datatype), 'profile_source_fields_form'))
		{
			$this->cached_vars['additional_datatype_fields'] = $this->actions()->load_datatype($datatype)->profile_source_fields_form($settings);
		}

		// --------------------------------------------
		//  Build Page
		// --------------------------------------------

		$this->cached_vars['importer_profile_id'] = ee()->input->get_post('profile_id');

		$this->add_crumb(lang('importer_profile_source').' ('.$this->cached_vars['importer_name'].')');

		$this->cached_vars['current_page'] = $this->view('profile_source.html', NULL, TRUE);

		ee()->cp->load_package_js('importer_profile_source');

		return $this->ee_cp_view('index.html');
	}
	// END edit_profile_source()


	// --------------------------------------------------------------------

	/**
	 *	Save the Profile Source
	 *
	 *	Saves the Channel for Import and the settings to retrieve the data that will be imported.
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function save_profile_source()
	{
		// --------------------------------------------
		//  Retrieve DataType based on Profile ID
		// --------------------------------------------

		$settings = array();

		if ( empty($_POST['profile_id']) OR ! is_numeric($_POST['profile_id']))
		{
			return $this->error_page('invalid_importer_profile_id');
		}

		$query = ee()->db
					->select('profile_id, datatype')
					->where('profile_id', $_POST['profile_id'])
					->get('exp_importer_profiles');

		if ($query->num_rows() == 0)
		{
			return $this->error_page('invalid_importer_profile_id');
		}

		$profile_id = $query->row('profile_id');
		$datatype	= $query->row('datatype');

		// --------------------------------------------
		//  We Need a Cache Folder to Do Our Work - Check!
		// --------------------------------------------

		if ( ($basepath = $this->actions()->check_cache_directory()) === FALSE)
		{
			return $this->error_page('unable_to_create_importer_directory');
		}

		// --------------------------------------------
		//  Data Validation - Data Source
		// --------------------------------------------

		$data_sources = $this->actions()->load_datatype($datatype)->data_sources;

		if (empty($data_sources) OR ! is_array($data_sources))
		{
			$data_sources = $this->actions()->default_data_sources;
		}

		if ( ! in_array(ee()->input->post('data_source'), $data_sources))
		{
			return $this->error_page('invalid_data_source_submitted');
		}

		$settings['data_source'] = ee()->input->post('data_source');

		$data = '';

		// --------------------------------------------
		//  Custom Data Source!
		// --------------------------------------------

		if ( ! in_array($settings['data_source'], $this->actions()->default_data_sources))
		{
			$method_name = 'settings_'.$settings['data_source'];

			if ( method_exists($this->actions()->load_datatype($datatype), $method_name))
			{
				$return = $this->actions()->load_datatype($datatype)->{$method_name}($this);

				if ( $return === FALSE)
				{
					return $this->error_page(lang('unable_to_load_data_source'));
				}
				elseif (! is_array($return))
				{
					return $return;
				}

				$settings = array_merge($settings, $return);
			}
		}

		// --------------------------------------------
		//  Local File - Validation
		// --------------------------------------------

		if ($settings['data_source'] == 'filename')
		{
			if ( ee()->input->post('filename') === FALSE or
				ee()->input->post('filename') == '')
			{
				return $this->error_page('invalid_data_source_submitted');
			}

			$filename = trim(ee()->input->post('filename'));

			if (substr($filename, 0, 1) !== '/')
			{
				return $this->error_page('invalid_filename_fullpath');
			}

			if ( ! file_exists($filename))
			{
				return $this->error_page('invalid_filename_not_found');
			}

			$settings['filename'] = $filename;
		}

		// --------------------------------------------
		//  Remote URL
		// --------------------------------------------

		if ($settings['data_source'] == 'url')
		{
			if ( ee()->input->post('remote_url') === FALSE or
				ee()->input->post('remote_url') == '')
			{
				return $this->error_page('invalid_data_source_submitted');
			}

			$settings['remote_url'] = trim(ee()->input->post('remote_url'));

			if (ee()->input->post('http_auth_username') !== FALSE)
			{
				$settings['http_auth_username'] = trim(ee()->input->post('http_auth_username'));
			}

			if (ee()->input->post('http_auth_password') !== FALSE)
			{
				$settings['http_auth_password'] = trim(ee()->input->post('http_auth_password'));
			}
		}

		// --------------------------------------------
		//  FTP
		// --------------------------------------------

		if ($settings['data_source'] == 'ftp')
		{
			$required = array(
				'ftp_host',
				'ftp_username',
				'ftp_password',
				'ftp_port',
				'ftp_path'
			);

			foreach($required as $field)
			{
				if ( ee()->input->post($field) === FALSE or
					ee()->input->post($field) == '')
				{
					return $this->error_page('invalid_data_source_submitted');
				}

				$settings[$field] = trim(ee()->input->post($field));
			}
		}

		// --------------------------------------------
		//  SFTP
		// --------------------------------------------

		if ($settings['data_source'] == 'sftp')
		{
			$required = array(
				'ftp_host',
				'ftp_username',
				'ftp_password', 'ftp_port', 'ftp_path');

			foreach($required as $field)
			{
				if ( ee()->input->post($field) === FALSE or
					ee()->input->post($field) == '')
				{
					return $this->error_page('invalid_data_source_submitted');
				}

				$settings[$field] = trim(ee()->input->post($field));
			}
		}

		// --------------------------------------------
		//  Uploaded File
		// --------------------------------------------

		if ($settings['data_source'] == 'manual_upload')
		{
			if ( ! isset($_FILES['manual_upload']['name']))
			{
				return $this->error_page('invalid_data_source_submitted');
			}
		}

		// --------------------------------------------
		//  Encryption Required for Encoding Username/Passwords
		// --------------------------------------------

		ee()->load->library('encrypt');

		if (ee()->config->item('encryption_key') == '')
		{
			ee()->encrypt->set_key(
				md5(ee()->db->username.ee()->db->password)
			);
		}

		$encode_me = array(	'http_auth_username',
							'http_auth_password',
							'ftp_username',
							'ftp_password');

		foreach($encode_me as $var)
		{
			if ( isset($settings[$var]))
			{
				$settings[$var] = base64_encode(ee()->encrypt->encode($settings[$var]));
			}
		}

		// --------------------------------------------
		//  Retrieve Source Data
		// --------------------------------------------

		if (($error = $this->actions()->retrieve_source_data($datatype, $settings)) !== TRUE)
		{
			return $this->error_page($error);
		}

		// Source Data must be a non-empty string or array
		if ( empty($this->actions()->source_data) OR ( ! is_string($this->actions()->source_data) && ! is_array($this->actions()->source_data)))
		{
			return $this->error_page('problem_retreiving_data');
		}

		// --------------------------------------------
		//  Load DataType Class and Validate Any DataType Fields
		// --------------------------------------------

		$datatypes = $this->actions()->list_datatypes();

		if (! isset($datatypes[$datatype]))
		{
			return $this->error_page('invalid_importer_profile_datatype');
		}

		if (method_exists($this->actions()->load_datatype($datatype), 'validate_profile_source_fields'))
		{
			// Error!
			if (($check = $this->actions()->load_datatype($datatype)->validate_profile_source_fields()) !== TRUE)
			{
				return $check;
			}
		}

		if (method_exists($this->actions()->load_datatype($datatype), 'profile_source_fields'))
		{
			foreach($this->actions()->load_datatype($datatype)->profile_source_fields($this) as $field)
			{
				$settings[$field] = trim(ee()->input->post($field));
			}
		}

		if ($settings['data_source'] == 'manual_upload')
		{
			$data_array = $this->actions()->load_datatype($datatype)->parse_data($this->actions()->source_data, $settings);

			if ( ! empty($data_array[0]))
			{
				$settings['example_data_array'][0] = $data_array[0];
			}

			if ( ! empty($data_array[1]))
			{
				$settings['example_data_array'][1] = $data_array[1];
			}

			$settings['example_data_array'] = (empty($settings['example_data_array'])) ? '' : base64_encode(serialize($settings['example_data_array']));
			unset($data_array);
		}

		// --------------------------------------------
		//  Save Settings, Silly Serpentine
		// --------------------------------------------

		ee()->db->where_in('setting', array_keys($settings));
		ee()->db->delete('exp_importer_profile_settings', array('profile_id' => $profile_id));

		$insert = array();

		foreach($settings as $setting => $value)
		{
			$insert[] = array(	'profile_id'	=> $profile_id,
								'setting'		=> $setting,
								'value'			=> $value);
		}

		ee()->db->insert_batch('exp_importer_profile_settings', $insert);

		// --------------------------------------------
		//  Display the Configuration Form
		// --------------------------------------------

		return ee()->functions->redirect(
			$this->base . AMP .
				'method=settings_form' . AMP .
				'profile_id=' . $profile_id
		);
	}
	// END save_profile_source()


	// --------------------------------------------------------------------

	/**
	 *	AJAX Caller for Data Types and Content Types
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function ajax()
	{
		if ( empty($_GET['datatype']) && empty($_GET['content_type']))
		{
			exit(lang('Invalid Request'));
		}

		if ( empty($_GET['call']))
		{
			exit(lang('Method Required'));
		}

		if ( isset($_GET['datatype']))
		{
			$datatypes = $this->actions()->list_datatypes();

			if ( ! isset($datatypes[$_GET['datatype']]))
			{
				exit(lang('Invalid Data Type'));
			}

			if ( method_exists($this->actions()->load_datatype($_GET['datatype']), $_GET['call']))
			{
				$this->actions()->load_datatype($_GET['datatype'])->{$_GET['call']}($this);
			}
		}

		if ( isset($_GET['content_type']))
		{
			$content_types = $this->actions()->list_content_types();

			if ( ! isset($content_types[$_GET['content_type']]))
			{
				exit(lang('Invalid Content Type'));
			}

			if ( method_exists($this->actions()->load_content_type($_GET['content_type']), $_GET['call']))
			{
				$this->actions()->load_content_type($_GET['content_type'])->{$_GET['call']}($this);
			}
		}

		exit('Request Failed');
	}


	// --------------------------------------------------------------------

	/**
	 *	The Complete Profile Settings Form for this Content Type
	 *
	 *	Both data type and content type are abstracted in Importer now.  Data type will retrieve
	 * 	and parse the data. Content type will take that data and put into a type of content. Each
	 *  content type will have a different settings form.
	 *
	 *	@access		public
	 *	@param		integer		$profile_id - Profile that we are editing
	 *	@param		string		$datatype - Kind of data (ex: XML)
	 *	@param		array		$data - The data, typically just retrieved by save_profile_source()
	 *	@param		array		$settings - Current settings for this importer profile
	 *	@return		string
	 */

	public function settings_form($profile_id = 0, $data = array(), $return_data = FALSE)
	{
		// --------------------------------------------
		//  Direct Request?  Must Find Profile ID and DataType
		// --------------------------------------------

		$direct_request = FALSE;

		if ( $profile_id === 0)
		{
			if (ee()->input->get_post('profile_id') === FALSE OR
				! ctype_digit(ee()->input->get_post('profile_id')))
			{
				return ($return_data) ? FALSE : $this->index();
			}

			$direct_request = TRUE;

			$profile_id = ee()->input->get_post('profile_id');
		}

		$query = ee()->db
					->select('profile_id, datatype, content_type, name')
					->where('profile_id', $profile_id)
					->get('importer_profiles');

		if ($query->num_rows() == 0)
		{
			if ($return_data)
			{
				return FALSE;
			}
			else
			{
				return ee()->functions->redirect($this->base);
			}
		}

		$profile_id		= $query->row('profile_id');
		$profile_name	= $query->row('name');
		$datatype		= $query->row('datatype');
		$content_type	= $query->row('content_type');

		// --------------------------------------------
		//  Retrieve All Settings for Profile
		// --------------------------------------------

		$settings = array(
			'profile_id'	=> $profile_id,
			'datatype'		=> $datatype,
			'profile_name'	=> $profile_name
		);

		$query = ee()->db->get_where(
			'exp_importer_profile_settings',
			array('profile_id' => $profile_id)
		);

		foreach($query->result_array() as $row)
		{
			$settings[$row['setting']] = $row['value'];
		}

		// --------------------------------------------
		//  Direct Request?  Retrieve Source Data and Parse
		// --------------------------------------------

		if ($direct_request === TRUE)
		{
			if ( ! isset($settings['data_source']))
			{
				return ($return_data) ? FALSE : $this->error_page('unable_to_retrieve_source_data');
			}
			elseif ($settings['data_source'] == 'manual_upload')
			{
				$data_array	= ( ! empty($settings['example_data_array'])) ?
								unserialize(base64_decode($settings['example_data_array'])) :
								FALSE;
			}
			else
			{
				if (($error = $this->actions()->retrieve_source_data($datatype, $settings)) !== TRUE)
				{
					return ($return_data) ? FALSE : $this->error_page($error);
				}

				if ( empty($this->actions()->source_data))
				{
					return ($return_data) ? FALSE : $this->error_page('problem_retreiving_data');
				}

				$data_array = $this->actions()->load_datatype($datatype)->parse_data($this->actions()->source_data, $settings, 0, 1);
			}
		}
		else
		{
			$data_array = $this->actions()->load_datatype($datatype)->parse_data($data, $settings, 0, 1);
		}

		if ( ! is_array($data_array) OR empty($data_array))
		{
			return ($return_data) ? FALSE : $this->error_page('source_data_contained_invalid_data');
		}

		$this->cached_vars['importer_profile_id'] = $profile_id;

		// --------------------------------------------
		//  Parse Data, Find First "Example" Values, and Retrieve Content Type Form
		// --------------------------------------------

		if ( empty($data_array))
		{
			return ($return_data) ? FALSE : $this->error_page('invalid_data_received');
		}

		$first_data_array = unserialize(array_shift($data_array));

		if ( empty($first_data_array))
		{
			return ($return_data) ? FALSE : $this->error_page('invalid_data_received');
		}

		$elements = $this->actions()->element_options($first_data_array);

		if ($return_data === TRUE)
		{
			return array('elements' => $elements, 'settings' => $settings);
		}

		$obj = $this->actions()->load_content_type($content_type);
		$obj->cached_vars = &$this->cached_vars;
		$obj->crumbs = &$this->crumbs;

		$this->cached_vars['current_page'] = $obj->settings_form($elements, $settings);

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	// END settings_form()


	// --------------------------------------------------------------------

	/**
	 *	Save Content Type Settings
	 *
	 *	Saves the settings for Content Type and also
	 *	has the option of running an import now.
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function save_settings()
	{
		// --------------------------------------------
		//  Retrieve All Current Settings
		// --------------------------------------------

		if ( empty($_POST['profile_id']) OR ! is_numeric($_POST['profile_id']))
		{
			return $this->error_page('invalid_importer_profile_id');
		}

		$query = ee()->db
					->select('profile_id, datatype, content_type')
					->from('exp_importer_profiles');
		$query = $query->where('profile_id', $_POST['profile_id'])->get();

		if ($query->num_rows() == 0)
		{
			return $this->error_page('invalid_importer_profile_id');
		}

		$profile_id		= $query->row('profile_id');
		$datatype		= $query->row('datatype');
		$content_type	= $query->row('content_type');

		// Add this to settings here, because we send
		// the whole array to the content type class.
		$settings = array('datatype' => $datatype);

		$query = ee()->db->get_where(
			'exp_importer_profile_settings',
			array('profile_id' => $profile_id)
		);

		foreach($query->result_array() as $row)
		{
			$settings[$row['setting']] = $row['value'];
		}

		// --------------------------------------------
		//  Retrieve Source Data, Used for Validation
		// --------------------------------------------

		if ($settings['data_source'] == 'manual_upload')
		{
			$data_array	= ( ! empty($settings['example_data_array'])) ?
							unserialize(base64_decode($settings['example_data_array'])) :
							FALSE;
		}
		else
		{
			if (($error = $this->actions()->retrieve_source_data($datatype, $settings)) !== TRUE)
			{
				return $this->error_page($error);
			}

			if ( empty($this->actions()->source_data))
			{
				return $this->error_page('problem_retreiving_data');
			}

			$data_array = $this->actions()
								->load_datatype($datatype)
								->parse_data(
									$this->actions()->source_data,
									$settings
								);
		}

		// --------------------------------------------
		//  Parse Data Source, Find First Array, Then a List of Allowed Elements
		// --------------------------------------------

		if ( ! is_array($data_array) OR empty($data_array))
		{
			return $this->error_page('source_data_contained_invalid_data');
		}

		$first_data_row = unserialize(array_shift($data_array));

		if ( empty($first_data_row))
		{
			return $this->error_page('invalid_data_received');
		}

		$elements = $this->actions()->element_options($first_data_row);

		// --------------------------------------------
		//  Load Content Type and Validate Fields
		// --------------------------------------------

		$content_types = $this->actions()->list_content_types();

		if (! isset($content_types[$content_type]))
		{
			return $this->error_page('invalid_importer_profile_datatype');
		}

		$insert_settings = array();

		// Validation by Content Type class
		// If FALSE is returned, there was an unspecified problem.
		// If not an array, the Content Type is returning an error to us.
		// If an array, then we have a list of settings to insert!  YAY!!!
		// Otherwise, we expect an array and store those settings!

		$check = $this->actions()
					->load_content_type($content_type, $this)
					->save_settings($profile_id, $elements, $settings);

		if ($check === FALSE)
		{
			return $this->error_page('unable_to_successfully_save_settings');
		}
		elseif( ! is_array($check))
		{
			return $check;
		}
		else
		{
			$insert_settings = $check;
		}

		// Does not go into profile_settings table.
		unset($insert_settings['datatype']);

		// --------------------------------------------
		//  Save Settings, Silly Serpentine
		// --------------------------------------------

		ee()->db->where_in('setting', array_keys($insert_settings));
		ee()->db->delete(
			'exp_importer_profile_settings',
			array('profile_id' => $profile_id)
		);

		if ( ! empty($insert_settings))
		{
			$insert = array();

			foreach($insert_settings as $setting => $value)
			{
				$insert[] = array(	'profile_id'	=> $profile_id,
									'setting'		=> $setting,
									'value'			=> $value);
			}

			ee()->db->insert_batch('exp_importer_profile_settings', $insert);
		}

		// --------------------------------------------
		//  AJAX Response
		// --------------------------------------------

		if ($this->is_ajax_request())
		{
			$this->send_ajax_response(array(
				'success' => TRUE,
				'heading' => lang('importer_profile_updated'),
				'message' => lang('success_importer_settings_updated'),
				'content' => lang('success_importer_settings_updated')
			));
		}


		// --------------------------------------------
		//  Finished?  Back to Index with Success Message
		// --------------------------------------------

		if ( ! isset($_POST['submit_import']))
		{
			ee()->functions->redirect($this->base.'&msg=success_importer_settings_updated');
		}

		// --------------------------------------------
		//  Importing?  Forward to Importing Method
		// --------------------------------------------

		if ($settings['data_source'] == 'manual_upload')
		{
			ee()->functions->redirect($this->base.'&method=manual_import&profile_id='.$profile_id);
		}
		else
		{
			ee()->functions->redirect($this->base.'&method=perform_import&profile_id='.$profile_id);
		}
	}
	// END save_settings()


	// --------------------------------------------------------------------

	/**
	 *	Perform Manual Upload Import
	 *
	 *	Requires a form for manually uploading a file prior to doing the import
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function manual_import()
	{
		// --------------------------------------------
		//  Profile ID
		// --------------------------------------------

		if (ee()->input->get_post('profile_id') === FALSE OR
			! ctype_digit(ee()->input->get_post('profile_id')))
		{
			return $this->index();
		}

		$query = ee()->db
					->select('profile_id')
					->where('profile_id', ee()->input->get_post('profile_id'))
					->get('exp_importer_profiles');

		if ($query->num_rows() == 0)
		{
			return $this->index();
		}

		$this->cached_vars['importer_profile_id'] = $query->row('profile_id');

		// --------------------------------------------
		//  Manual Upload Form - Very Simple...
		// --------------------------------------------

		$this->add_crumb(lang('manual_import_form'));

		$this->cached_vars['current_page'] = $this->view('manual_import_form.html', NULL, TRUE);

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	// END perform_manual_import()

	// --------------------------------------------------------------------

	/**
	 *	Import via CP
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function perform_import()
	{
		// --------------------------------------------
		//  Profile ID
		// --------------------------------------------

		if (ee()->input->get_post('profile_id') === FALSE OR
			! ctype_digit(ee()->input->get_post('profile_id')))
		{
			return $this->index();
		}

		$query = ee()->db
					->select('profile_id')
					->where('profile_id', ee()->input->get_post('profile_id'))
					->get('importer_profiles');

		if ($query->num_rows() == 0)
		{
			return $this->index();
		}

		$profile_id = $query->row('profile_id');

		// --------------------------------------------
		//  Perform Import - $return is either TRUE/FALSE/batch hash
		//	- If FALSE, there was an error
		//	- If TRUE, data was imported and no need for batch processing
		//	- If (string), batch processing
		// --------------------------------------------

		$return = $this->actions()->start_import($profile_id);

		if ($return === FALSE)
		{
			if ($this->is_ajax_request())
			{
				$this->send_ajax_response(array(
					'success' => TRUE,
					'heading' => lang('failure_of_import'),
					'message' => $this->actions()->error,
					'content' => $this->actions()->error
				));
			}
			else
			{
				return $this->error_page($this->actions()->error);
			}
		}

		// --------------------------------------------
		//  Batch Importing?
		// --------------------------------------------

		if ($this->actions()->batch_processing === TRUE)
		{
			return $this->batch_import($return);
		}

		// --------------------------------------------
		//  Lack of Failure, Thus Success!
		// --------------------------------------------

		if ($this->is_ajax_request())
		{
			$this->send_ajax_response(array(
				'success' => TRUE,
				'heading' => lang('successful_import'),
				'message' => lang('import_was_successfully_completed'),
				'content' => lang('import_was_successfully_completed')
			));
		}

		// --------------------------------------------
		//  Return Debugging Information
		// --------------------------------------------

		$this->cached_vars['output'] = $this->actions()->statistics_output($return);

		// --------------------------------------------
		//  Manual Upload Form - Very Simple...
		// --------------------------------------------

		$this->add_crumb(lang('successful_import'));

		$this->cached_vars['current_page'] = $this->view('perform_import.html', NULL, TRUE);

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	// END perform_import()


	// --------------------------------------------------------------------

	/**
	 *	Batch Import
	 *
	 *	Uses ajax to break up an import into chunks
	 *	in a way that wont hog resources (as much)
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function batch_import($hash)
	{
		// --------------------------------------------
		//  Find Batch Meta Data
		// --------------------------------------------

		$query = ee()->db->query(
			"SELECT ibd.profile_id, ibd.batch_number, ibd.batch_hash
			 FROM exp_importer_batches AS ib, exp_importer_batch_data AS ibd
			 WHERE ib.batch_hash = '".ee()->db->escape_str($hash)."'
			 AND ib.batch_hash = ibd.batch_hash
			 AND ib.finished = 'n'
			 ORDER BY ibd.batch_number DESC
			 LIMIT 1"
		);

		if ($query->num_rows() == 0)
		{
			ee()->functions->redirect($this->base.'&msg=importer_failure');
		}

		$items = array();

		for ($i = 1, $s = $query->row('batch_number'); $i <=$s; ++$i)
		{
			$items[$i] = lang('importer_batch_number_').$i;
		}

		$items_out = array();

		foreach ($items as $item_id => $item_title)
		{
			$items_out[] = array(
				'itemId'	=> $item_id,
				'itemTitle'	=> htmlentities($item_title, ENT_QUOTES)
			);
		}

		$this->cached_vars['items']					= json_encode($items_out);
		$this->cached_vars['total_items_count'] 	= count($items_out);

		$this->cached_vars['return_uri'] = $this->base;

		$this->cached_vars['button_label']				= lang('importer_perform_import');
		$this->cached_vars['complete_message']			= lang('importer_import_complete');
		$this->cached_vars['purpose_message']			= lang('importer_batch_purpose');
		$this->cached_vars['total_updated_message']		= lang('importer_number_of_batches_imported');
		$this->cached_vars['updating_counts_message']	= lang('performing_import_for_batch_');

		// -------------------------------------
		//	URLs
		// -------------------------------------

		// Throttling Requires doing it via some manner of CP Request.  This is one of the more
		// elegant solutions compared to the half dozen other ones we thought up (including doing it
		// through the module's CP pages, which would have added more load than I liked).
		$this->cached_vars['importer_batch_import_ajax_url'] = str_replace(AMP, '&',
			BASE.
			AMP.'C=javascript'.
			AMP.'M=load'.
			AMP.'file=ext_scripts'.
			AMP.'call=importer_batch_import'.
			AMP.'batch_hash='.$hash
		);

		$this->cached_vars['importer_import_statistics_ajax_url'] = str_replace(AMP, '&',
			BASE.
			AMP.'C=javascript'.
			AMP.'M=load'.
			AMP.'file=ext_scripts'.
			AMP.'call=importer_import_statistics'.
			AMP.'batch_hash='.$hash
		);

		// -------------------------------------
		//	jQuery UI stuff
		// -------------------------------------


		ee()->cp->add_js_script(array('ui' => 'progressbar'));

		ee()->cp->load_package_js('importer_batch_import');
		ee()->cp->load_package_css('importer_batch_import');

		//----------------------------------------
		//	 Build page
		//----------------------------------------

		$this->add_crumb(lang('batch_importer'));

		$this->cached_vars['current_page'] = $this->view('batch_import.html', NULL, TRUE);

		$this->cached_vars['module_menu_highlight'] = 'module_homepage';

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	//END batch_import


	// --------------------------------------------------------------------

	/**
	 *	Clear Batches
	 *
	 *	@access		public
	 *	@return		string
	 */
    public function clear_batches()
    {
        ee()->db->query("TRUNCATE exp_importer_batches");
        ee()->db->query("TRUNCATE exp_importer_batch_data");

        ee()->functions->redirect($this->base.'&msg=batch_data_successfully_emptied');
    }

	// --------------------------------------------------------------------

	/**
	 *	Preferences for Module
	 *
	 *	@access		public
	 *	@param		string		$message - That little message display thingy (of Doom)
	 *	@return		string
	 */

	public function preferences($message = '')
	{
		//--------------------------------------------
		//	Message from God, Dr. Jones
		//--------------------------------------------

		if ($message == '' AND ee()->input->get_post('msg') !== FALSE)
		{
			$message = lang(ee()->input->get_post('msg'));
		}

		// --------------------------------------------
		//	Fetch Channels with Site Label
		// --------------------------------------------

		$this->cached_vars['channels'] = array();

		$query = ee()->db->query("SELECT {$this->sc->db->channel_id} AS channel_id,
										 {$this->sc->db->channel_title} AS channel_title,
										 site_id
								FROM {$this->sc->db->channels}
								ORDER BY site_id, {$this->sc->db->channel_name}");

		foreach($query->result_array() as $row)
		{
			$this->cached_vars['channels'][$row['site_id']][$row['channel_id']] = $row['channel_title'];
		}

		// --------------------------------------------
		//	Fetch Member Groups
		// --------------------------------------------

		$this->cached_vars['member_groups'] = array('all' => lang('all_member_groups'));

		$groups_query = ee()->db->query("SELECT group_id, group_title FROM exp_member_groups
										WHERE group_id NOT IN (2,4)
										AND site_id = '".ee()->db->escape_str(ee()->config->item('site_id'))."'
										ORDER BY FIELD(group_id, 1) DESC, group_title");

		foreach($groups_query->result_array() as $row)
		{
			$this->cached_vars['member_groups'][$row['group_id']] = $row['group_title'];
		}

		// ----------------------------------
		//	Fetch the Preferences
		// ----------------------------------

		$this->cached_vars['prefs'] = $this->actions()->module_preferences();

		//----------------------------------------
		//	 Build page
		//----------------------------------------

		$this->add_crumb(lang('importer_preferences'));

		$this->cached_vars['message'] 	= $message;

		$this->cached_vars['current_page'] = $this->view('preferences.html', NULL, TRUE);

		$this->cached_vars['module_menu_highlight'] = 'module_preferences';

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	// END preferences()

	// --------------------------------------------------------------------

	/**
	 * Saves the Preferences
	 *
	 * @access	public
	 * @return	null
	 */

	public function save_preferences()
	{
		// ----------------------------------
		//	Check for Preference Values
		// ----------------------------------

		$inserts = array();

		foreach(array_keys($this->actions()->default_preferences) as $field)
		{
			$value = '';

			if ( isset($_POST[$field]))
			{
				$value = (is_array($_POST[$field])) ? implode('|', $_POST[$field]) : $_POST[$field];
			}

			$inserts[] = array('preference_name'	=> $field,
							   'preference_value'	=> $value);
		}

		// ----------------------------------
		//	Clear and Re-Insert
		// ----------------------------------

		ee()->db->query("TRUNCATE exp_importer_preferences");

		foreach($inserts as $insert)
		{
			ee()->db->query(ee()->db->insert_string('exp_importer_preferences', $insert));
		}

		// ----------------------------------
		//	Redirect to Homepage with Message
		// ----------------------------------

		ee()->functions->redirect($this->base.'&method=preferences&msg=importer_preferences_updated');
	}
	/* END save_preferences() */

	// --------------------------------------------------------------------

	/**
	 *	Delete Importer Profile Confirmation Form
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function delete_profile_confirm()
	{
		if (ee()->input->get_post('profile_id') === FALSE OR ! ctype_digit(ee()->input->get_post('profile_id')))
		{
			return $this->index();
		}

		$this->cached_vars['delete'] = array(ee()->input->get_post('profile_id'));

		// --------------------------------------------
		//	Crumbs and Page Title
		// --------------------------------------------

		$replace[] = count($this->cached_vars['delete']);
		$replace[] = ( count($this->cached_vars['delete']) == 1 ) ? 'profile' : 'profiles';

		$search	= array( '%i%', '%profiles%' );

		$this->cached_vars['delete_question'] = str_replace( $search, $replace, lang('profile_delete_question') );

		$this->cached_vars['form_uri'] = $this->base.'&method=delete_profile';

		// --------------------------------------------
		//  Build Page
		// --------------------------------------------

		$this->add_crumb(lang('importer_profile_delete_confirm'));

		$this->build_crumbs(); // Required only for delete_confirm.html usage
		$this->cached_vars['current_page'] = $this->view('delete_confirm.html', NULL, TRUE);

		return $this->ee_cp_view('index.html');
	}
	/* END delete_profile_confirm() */


	// --------------------------------------------------------------------

	/**
	 *	Delete Importer Profile
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function delete_profile()
	{
		if (ee()->input->post('delete') === FALSE or ! is_array(ee()->input->post('delete')) OR count(ee()->input->post('delete')) == 0)
		{
			return $this->index();
		}

		$query	= ee()->db->query("SELECT name FROM exp_importer_profiles
								   WHERE profile_id IN ('".implode("','", ee()->db->escape_str($_POST['delete']))."')");

		if ($query->num_rows() > 0)
		{
			ee()->db->where_in('profile_id', ee()->input->post('delete'))->delete('exp_importer_profiles');
			ee()->db->where_in('profile_id', ee()->input->post('delete'))->delete('exp_importer_profile_settings');
		}

		$message = ($query->num_rows() == 1) ?
					lang('importer_profile_deleted') :
					str_replace( '%i%', $query->num_rows(), lang('importer_profiles_deleted') );

		return $this->index($message);
	}
	/* END delete_field() */

	// --------------------------------------------------------------------

	/**
	 *	Import Log
	 *
	 *	@access		public
	 *	@param		string		$message - That little message display thingy
	 *	@return		string
	 */

	public function import_log($message = '')
	{
		//--------------------------------------------
		//	Message
		//--------------------------------------------

		if ($message == '' AND ee()->input->get_post('msg') !== FALSE)
		{
			$message = lang(ee()->input->get_post('msg'));
		}

		// --------------------------------------------
		//  List of Import Data Types
		// --------------------------------------------

		$this->cached_vars['datatypes'] = array();

		foreach($this->actions()->list_datatypes() as $name => $path)
		{
			$this->cached_vars['datatypes'][$name] = $this->actions()->load_datatype($name)->label;
		}

		// --------------------------------------------
		//  Load Content Type Language Files - Required for Details
		// --------------------------------------------

		$content_types = $this->actions()->list_content_types();

		foreach($content_types as $content_type => $file)
		{
			if (is_dir(PATH_THIRD.'importer/content_types/importer.content_type.'.$content_type))
			{
				ee()->lang->load('content_type.'.$content_type,
								 ee()->lang->user_lang,
								 FALSE,
								 TRUE,
								 PATH_THIRD.'importer/content_types/importer.content_type.'.$content_type.'/');
			}
			else
			{
				ee()->lang->load('content_type.'.$content_type, ee()->lang->user_lang, FALSE, TRUE, PATH_THIRD.'importer.content_type.'.$content_type.'/');
			}
		}

		// --------------------------------------------
		//  List of Imports
		// --------------------------------------------

		$query = ee()->db->query("SELECT ip.*, il.date, il.details, il.log_id
								  FROM exp_importer_profiles AS ip, exp_importer_log AS il
								  WHERE il.profile_id = ip.profile_id
								  ORDER BY il.date DESC");

		$this->cached_vars['imports'] = array();

		foreach($query->result_array() as $row)
		{
			$row['datatype']	= (isset($this->cached_vars['datatypes'][$row['datatype']])) ? $this->cached_vars['datatypes'][$row['datatype']] : lang('invalid_datatype');
			$row['date']		= $this->human_time($row['date']);
			$row['details']		= $this->actions()->statistics_output(unserialize(base64_decode($row['details'])));

			$this->cached_vars['imports'][] = $row;
		}

		//----------------------------------------
		//	 Build page
		//----------------------------------------

		$this->cached_vars['module_menu_highlight'] = 'module_import_log';
		$this->add_crumb(lang('import_log'));

		$this->cached_vars['message'] 	= $message;

		$this->cached_vars['current_page'] = $this->view('importer_log.html', NULL, TRUE);

		ee()->cp->load_package_js('importer_log');

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('index.html');
	}
	// END import_log()

	// --------------------------------------------------------------------

	/**
	 *	Delete Importer Logs Confirmation Form
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function delete_logs_confirm()
	{
		if (ee()->input->get_post('selected') === FALSE OR ! is_array(ee()->input->get_post('selected')))
		{
			return $this->index();
		}

		$this->cached_vars['delete'] = ee()->input->get_post('selected');

		// --------------------------------------------
		//	Crumbs and Page Title
		// --------------------------------------------

		$var		= ( count($this->cached_vars['delete']) == 1 ) ? 'log_delete_question' : 'logs_delete_question';

		$this->cached_vars['delete_question'] = str_replace( '%i%',
															count($this->cached_vars['delete']),
															lang($var) );

		$this->cached_vars['form_uri'] = $this->base.'&method=delete_logs';

		// --------------------------------------------
		//  Build Page
		// --------------------------------------------

		$this->add_crumb(lang('importer_logs_delete_confirm'));

		$this->build_crumbs(); // Required only for delete_confirm.html usage
		$this->cached_vars['current_page'] = $this->view('delete_confirm.html', NULL, TRUE);

		return $this->ee_cp_view('index.html');
	}
	/* END delete_logs_confirm() */


	// --------------------------------------------------------------------

	/**
	 *	Delete Importer Logs
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function delete_logs()
	{
		if (ee()->input->post('delete') === FALSE or ! is_array(ee()->input->post('delete')) OR count(ee()->input->post('delete')) == 0)
		{
			return $this->index();
		}

		$query	= ee()->db->query("SELECT log_id FROM exp_importer_log
								   WHERE log_id IN ('".implode("','", ee()->db->escape_str($_POST['delete']))."')");

		if ($query->num_rows() > 0)
		{
			ee()->db->where_in('log_id', ee()->input->post('delete'))->delete('exp_importer_log');
		}

		$message = ($query->num_rows() == 1) ?
					lang('importer_log_deleted') :
					str_replace( '%i%', $query->num_rows(), lang('importer_logs_deleted') );

		return $this->import_log($message);
	}
	/* END delete_field() */


	// --------------------------------------------------------------------

	/**
	 * Module Upgrading
	 *
	 * @access	public
	 * @return	bool
	 */
	public function importer_module_update()
	{
		if ( ! isset($_POST['run_update']) OR $_POST['run_update'] != 'y')
		{
			$this->add_crumb(lang('update_importer_module'));
			$this->cached_vars['form_url'] = $this->base.'&method=importer_module_update';
			return $this->ee_cp_view('update_module.html');
		}

		require_once $this->addon_path.'upd.importer.php';

		$U = new Importer_upd();

		if ($U->update() !== TRUE)
		{
			return ee()->functions->redirect($this->base . AMP . 'msg=update_failure');
		}
		else
		{
			return ee()->functions->redirect($this->base . AMP . 'msg=update_successful');
		}
	}
	// END Importer_module_update()


	// --------------------------------------------------------------------

	/**
	 * Error Page
	 *
	 * @access	public
	 * @param	string|array	$error	Error message to display
	 * @return	null
	 */

	public function error_page($error = '')
	{
		if (is_array($error))
		{
			$error = implode(', ', $error);
		}

		$error_message = (
			preg_match("/[a-z0-9\_]+/i", $error)
		) ? lang($error) : $error;

		// -------------------------------------
		//  Output
		// -------------------------------------

		return $this->show_error($error_message);
	}
	// END error_page()
}
// END CLASS Importer_mcp