<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing Content Type
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.6
 * @filesource	importer/content_type.importer.php
 */

require_once 'addon_builder/module_builder.php';

class Importer_content_type extends Module_builder_importer
{
	public $label		= '';
	public $name		= '';
	public $version		= '0.0.0';

	private $cached				= array();
	private $default_settings	= array();
	protected $errors			= array();
	protected $caller			= FALSE;
	protected $first_party		= TRUE;

	// That which will easily go into this content type.
	// Our defaults included with the system are CSV, JSON,
	// and XML which come in as array with keys
	public $allowed_datatypes   = array();

	public $importer_theme_url	= '';
	public $importer_theme_path	= '';

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

		$this->name = str_replace('Importer_content_type_', '', get_class($this));

		if ($this->first_party === TRUE)
		{
			ee()->lang->load(
				'content_type.'.$this->name,
				ee()->lang->user_lang,
				FALSE,
				TRUE,
				PATH_THIRD .
					'importer/content_types/importer.content_type.' .
					$this->name.'/'
			);
		}
		else
		{
			ee()->lang->load(
				'content_type.'.$this->name,
				ee()->lang->user_lang,
				FALSE,
				TRUE,
				PATH_THIRD.'importer.content_type.'.$this->name.'/'
			);
		}

		$this->label = lang('content_type_'.$this->name.'_label');

		$this->importer_theme_url = $this->sc->addon_theme_url;
		$this->importer_theme_path = $this->sc->addon_theme_path;
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
	public function settings_form(array $values = array())
	{
		return ''; // Son, you got no game.
	}
	// END settings_form

	// --------------------------------------------------------------------

	/**
	 *	Array of Setting Fields
	 *
	 *	@access		public
	 *	@return		array
	 */

	public function save_settings()
	{
		return $this->default_settings;
	}
	// END save_settings()

	// --------------------------------------------------------------------

	/**
	 *	Validate Notification Fields
	 *
	 *	Likely every content type will have this in the settings form so we create a parent class
	 *	method for validating the fields.  So say we all...or some...actually, just me...
	 *
	 *	@access		public
	 *	@return		bool|string  - Returns either TRUE or an error message
	 */

	public function validate_notification_fields()
	{
		// --------------------------------------------
		//  No Notification Emails? Nothing to Validate!
		// --------------------------------------------

		if ( empty($_POST['notification_emails']))
		{
			return TRUE;
		}

		// --------------------------------------------
		//  Validate Notification Emails
		// --------------------------------------------

		$vars = $this->validate_emails($_POST['notification_emails']);

		if ( ! empty($vars['bad']))
		{
			return $this->error_page('error_invalid_notification_emails');
		}

		// --------------------------------------------
		//  Validate CC Emails, if necessary
		// --------------------------------------------

		if ( ! empty($_POST['notification_cc']))
		{
			$vars = $this->validate_emails($_POST['notification_cc']);

			if ( ! empty($vars['bad']))
			{
				return $this->error_page('error_invalid_notification_cc');
			}
		}

		// --------------------------------------------
		//  No errors?  Well, I guess we need a Subject and Message, no?
		// --------------------------------------------

		if ( empty($_POST['notification_subject']) OR empty($_POST['notification_message']))
		{
			return $this->error_page('error_invalid_notification_subject_message_required');
		}

		return TRUE;
	}
	//  END validate_notification_fields();


	// --------------------------------------------------------------------

	/**
	 * Get errors
	 *
	 * @access	public
	 * @return	arrat
	 */
	public function errors()
	{
		return $this->errors;
	}

	// --------------------------------------------------------------------

	/**
	 * Set errors
	 *
	 * Sets error using a language key and optional field name
	 *
	 * @access	private
	 * @param	string	optional field name
	 * @return	mixed
	 */
	protected function set_error($errors, $field = '')
	{
		if (empty($errors)) return;

		if (is_array($errors))
		{
			foreach($errors as $error)
			{
				$value = $this->set_error($error);
			}

			return;
		}
		else
		{
			$value = (preg_match("/[a-z0-9\_]+/i", $errors)) ? lang($errors) : $errors;
		}

		if ($field != '')
		{
			$this->errors[$field] = $value;
		}
		else
		{
			$this->errors[] = $value;
		}

		return FALSE;
	}
	//END set_error


	// --------------------------------------------------------------------

	/**
	 * Error Page
	 *
	 * @access	public
	 * @param	string	$error	Error message to display
	 * @return	null
	 */

	public function error_page($error = '')
	{
		$error_message = (
			preg_match("/[a-z0-9\_]+/i", $error)
		) ? lang($error) : $error;

		return $this->show_error($error_message);
	}
	// END error_page()


	// --------------------------------------------------------------------

	/**
	 * Return Time and Memory Usage
	 *
	 * @access	public
	 * @param	integer	$start_time
	 * @return	string
	 */

	public function debug_time_memory($start_time)
	{
		return ": ".round(
			memory_get_usage() / 1024 / 1024, 2
		) . ' MB ('.round(microtime(TRUE) - $start_time, 4).')';
	}
	//END debug_time_memory


	// --------------------------------------------------------------------

	/**
	 * Add Memory Statiistic
	 *
	 * @access	public
	 * @param	string	$message	message to log time with
	 * @param	int		$start_time	start time to log
	 */

	public function add_memory_stat($message, $start_time)
	{
		$this->statistics['debugging'][] = ((string) $message) .
								$this->debug_time_memory($start_time);
	}
	//END add_memory_stat


	// --------------------------------------------------------------------

	/**
	 *	Create URL Title
	 *
	 *	The ExpressionEngine url_title creator was
	 *	forcing the usage of callback no matter
	 *	if there were non-ASCII characters or not.
	 *	It was taking a god awful time to load
	 *	and process, so I wrote my own.
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */

	public function importer_url_title($str)
	{
		$separator = ee()->config->item('word_separator');

		if (UTF8_ENABLED)
		{
			$str = utf8_decode($str);

			if (preg_match('/[\x80-\xFF]/', $str))
			{
				ee()->load->helper('text');

				$str = preg_replace_callback(
					'/(.)/',
					'convert_accented_characters',
					$str
				);
			}
		}

		if ($separator == 'dash')
		{
			$search		= '_';
			$replace	= '-';
		}
		else
		{
			$search		= '-';
			$replace	= '_';
		}

		$trans = array(
			'&\#\d+?;'			=> '',
			'&\S+?;'			=> '',
			'\s+'				=> $replace,
			'[^a-z0-9\-\._]'	=> '',
			$search.'+'			=> $replace,
			$search.'$'			=> $replace,
			'^'.$search			=> $replace,
			'\.+$'				=> ''
		);

		$str = strip_tags($str);

		foreach ($trans as $key => $val)
		{
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		$str = strtolower($str);

		return trim(stripslashes($str));
	}
	//END importer_url_title
}
// END CLASS Importer_datatype