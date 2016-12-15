<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Actions
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.6
 * @filesource	importer/act.importer.php
 */

require_once 'addon_builder/addon_builder.php';

class Importer_actions extends Addon_builder_importer
{

	private $datatypes				= array();
	private $content_types			= array();
	public $module_preferences		= array();

	private $datatype_objects		= array();
	private $content_type_objects	= array();

	public $default_data_sources	= array('filename', 'url', 'ftp', 'sftp', 'manual_upload');
	public $source_data				= '';
	public $error					= '';

	protected $batch_size			= 100;
	public $batch_processing		= FALSE;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct()
	{
		parent::__construct();

		// Added to the GMT/UTC time set in the exp_importer_hits table
		// Makes our normalized Stats offset for the server time.
		if ( empty($this->time_offset) OR ! ctype_digit($this->time_offset))
		{

			if (version_compare($this->ee_version, '2.6.0', '<'))
			{
				$this->time_offset = 0;

				if ($tz = ee()->config->item('server_timezone'))
				{
					$this->time_offset += ee()->localize->zones[$tz] * 3600;
				}

				$this->time_offset = ee()->localize->set_server_offset($this->time_offset);
			}
			else
			{
				$this->time_offset = $this->timezone_offset();
			}

		}

		// --------------------------------------------
		//	Default Preferences
		// --------------------------------------------

		$this->default_preferences = array(

			'track_views_member_groups'						=> array('all'),
			'display_importer_fieldtype_for_channels'		=> array('all'),
			'display_importer_stats_in_edit'				=> 'n',
			'display_importer_stats_in_edit_time_range'		=> 'this_month',
			'display_importer_stats_in_edit_label'			=> lang('importer_stats'),
			'display_live_views_importer_stats'				=> 'n',
			'display_importer_stats_column_in_edit'			=> array('all'),
			'display_importer_stats_fieldtype_in_publish'	=> array('all'),
			'display_statistics_in_cp'						=> array('all'),
			'display_charts_in_cp'							=> array('all'),
			'display_preferences_in_cp'						=> array('all'),
			'display_documentation_in_cp'					=> array('all'),
		);

		// --------------------------------------------
		//  Memory and Time Helpers
		// --------------------------------------------

		register_tick_function('tick_handler_importer');

		@set_time_limit(0);
	}
	// END constructor


	// --------------------------------------------------------------------

	/**
	 *  Get the Preferences for This Module
	 *
	 * @access	public
	 * @return	array
	 */

	public function module_preferences($refresh = FALSE)
	{
		if (count($this->module_preferences) > 0 && $refresh === FALSE)
		{
			return $this->module_preferences;
		}

		// --------------------------------------------
		//  Default Values Guaranteed - No money back, method is provided as is... :-)
		// --------------------------------------------

		$this->module_preferences = $this->default_preferences;

		if (ee()->db->table_exists('exp_importer_preferences') == FALSE)
		{
			return $this->module_preferences;
		}

		// --------------------------------------------
		//  Values in Database
		// --------------------------------------------

		$query = ee()->db->get('exp_importer_preferences');

		foreach($query->result_array() as $row)
		{
			if ( is_array($this->default_preferences[$row['preference_name']]))
			{
				$this->module_preferences[$row['preference_name']] = explode('|', $row['preference_value']);
			}
			else
			{
				$this->module_preferences[$row['preference_name']] = $row['preference_value'];
			}
		}

		// Return!
		return $this->module_preferences;
	}
	// END module_preferences()


	// --------------------------------------------------------------------

	/**
	 *	List of Available DataTypes
	 *
	 *	@access		public
	 *	@return		array
	 */
	function list_datatypes()
	{
		if ( ! empty($this->datatypes))
		{
			return $this->datatypes;
		}

		ee()->load->helper('directory');

		$paths = array(PATH_THIRD, PATH_THIRD.'importer/datatypes/');

		foreach($paths as $path)
		{
			if (($map = directory_map($path, 2)) !== FALSE)
			{
				foreach ($map as $name => $files)
				{
					if (strncasecmp($name, 'importer.datatype.', 18) != 0) continue;
					if ( ! is_array($files)) continue;

					$datatype_name	= substr($name, 18);
					$file_name 		= 'datatype.'.$datatype_name.'.php';

					if ( ! in_array($file_name, $files)) continue;

					$this->datatypes[strtolower($datatype_name)] = $path.$name.'/'.$file_name;
				}
			}
		}

		ksort($this->datatypes);

		return $this->datatypes;
	}
	// END list_datatypes()


	// --------------------------------------------------------------------

	/**
	 *	List of Available Content Types - ex: Channel
	 *
	 *	@access		public
	 *	@return		array
	 */
	function list_content_types()
	{
		if ( ! empty($this->content_types))
		{
			return $this->content_types;
		}

		ee()->load->helper('directory');

		$paths = array(PATH_THIRD, PATH_THIRD.'importer/content_types/');

		foreach($paths as $path)
		{
			if (($map = directory_map($path, 2)) !== FALSE)
			{
				foreach ($map as $name => $files)
				{
					if (strncasecmp($name, 'importer.content_type.', 22) != 0) continue;
					if ( ! is_array($files)) continue;

					$datatype_name	= substr($name, 22);
					$file_name 		= 'content_type.'.$datatype_name.'.php';

					if ( ! in_array($file_name, $files)) continue;

					$this->content_types[strtolower($datatype_name)] = $path.$name.'/'.$file_name;
				}
			}
		}

		ksort($this->content_types);

		return $this->content_types;
	}
	// END list_content_types()


	// --------------------------------------------------------------------

	/**
	 *	Load Data Type
	 *
	 *	Load's a DataType into a Class Variable
	 *
	 *	@access		public
	 *	@param		string	$datatype - Well, how else are we going to load it?
	 *	@return		object
	 */

	public function load_datatype($datatype)
	{
		if ( isset($this->datatype_objects[$datatype]))
		{
			return $this->datatype_objects[$datatype];
		}

		$datatypes = $this->list_datatypes();

		if ( ! isset($datatypes[$datatype]))
		{
			return FALSE;
		}

		require_once $datatypes[$datatype];

		$class_name = 'Importer_datatype_'.$datatype;

		return $this->datatype_objects[$datatype] = new $class_name();
	}
	// END load_datatype

	// --------------------------------------------------------------------

	/**
	 *	Load Content Type
	 *
	 *	Loads a Content Type into a Class Variable
	 *
	 *	@access		public
	 *	@param		string	$content_type - Well, how else are we going to load it?
	 *	@return		object
	 */

	public function load_content_type($content_type, $caller = FALSE)
	{
		if ( isset($this->content_type_objects[$content_type]))
		{
			return $this->content_type_objects[$content_type];
		}

		$content_types = $this->list_content_types();

		if ( ! isset($content_types[$content_type]))
		{
			return FALSE;
		}

		require_once $content_types[$content_type];

		$class_name = 'Importer_content_type_'.$content_type;

		$CT = $this->content_type_objects[$content_type] = new $class_name();

		if ($caller != FALSE)
		{
			$CT->cached_vars =& $caller->cached_vars;
		}

		return $CT;
	}
	// END load_content_type


	// --------------------------------------------------------------------

	/**
	 *	Detect if Remote File Exists
	 *
	 *	@access		public
	 *	@param		string	- The URL
	 *	@return		string
	 */
	function detect_remote_file_exists($url)
	{
		// --------------------------------------------
		//	file_get_contents()
		// --------------------------------------------

		if ((bool) @ini_get('allow_url_fopen') !== FALSE)
		{
			return file_exists($url);
		}

		// --------------------------------------------
		//  fsockopen() - Last but only slightly least...
		// --------------------------------------------

		$user_agent = ini_get('user_agent');

		$parts	= parse_url($url);
		$host	= $parts['host'];
		$path	= (!isset($parts['path'])) ? '/' : $parts['path'];
		$port	= ($parts['scheme'] == "https") ? '443' : '80';
		$ssl	= ($parts['scheme'] == "https") ? 'ssl://' : '';

		if (isset($parts['query']) AND $parts['query'] != '')
		{
			$path .= '?'.$parts['query'];
		}

		$fp = @fsockopen($ssl.$host, $port, $error_num, $error_str, 7);

		if (is_resource($fp))
		{
			$getpost	= 'GET ';

			fputs($fp, $getpost.$path." HTTP/1.0\r\n" );
			fputs($fp, "Host: ".$host . "\r\n" );
			fputs($fp,  "User-Agent: ".$user_agent."r\n");
			fputs($fp, "Connection: close\r\n\r\n");

			/* ------------------------------
			/*  This error suppression has to do with a PHP bug involving
			/*  SSL connections: http://bugs.php.net/bug.php?id=23220
			/* ------------------------------*/

			$old_level = error_reporting(0);

			$headers = '';

			while ( ! feof($fp) && ! preg_match("/\r\n\r\n$/", $headers))
			{
				$headers .= trim(fgets($fp, 128));
			}

			error_reporting($old_level);

			fclose($fp);

			if ( stristr($headers, '200 OK'))
			{
				return TRUE;
			}
		}

		return FALSE;
	}
	// END detect_remote_file_exists

	// --------------------------------------------------------------------

	/**
	 *	Check Cache Directory
	 *
	 *	Insures that we have a working cache directory so we can download and process files.
	 *
	 *	@access		public
	 *	@return		bool|string
	 */

	function check_cache_directory()
	{
		// --------------------------------------------
		//  We Need a Cache Folder to Do Our Work - Check!
		// --------------------------------------------

		if ( ! is_dir(APPPATH.'cache/') OR ! is_really_writable(APPPATH.'cache/'))
		{
			return FALSE;
		}

		$basepath = APPPATH.'cache/importer/';

		if ( ! @is_dir($basepath))
		{
			if ( ! @mkdir($basepath, DIR_WRITE_MODE))
			{
				return FALSE;
			}

			@chmod($basepath, DIR_WRITE_MODE);
		}

		return $basepath;
	}
	// END check_cache_directory()


	// --------------------------------------------------------------------

	/**
	 *	Load Data Source
	 *
	 *	@access		public
	 *	@param		array
	 *	@return		bool|string
	 */

	public function retrieve_source_data($datatype, $settings, $offset = 0, $limit = 500)
	{
		if ( ($basepath = $this->check_cache_directory()) === FALSE)
		{
			return lang('unable_to_create_importer_directory');
		}

		// Er, what happened?
		if ( ! isset($settings['data_source']))
		{
			return lang('problem_retreiving_data');
		}

		// --------------------------------------------
		//  Custom Data Source!
		// --------------------------------------------

		if ( ! in_array($settings['data_source'], $this->default_data_sources))
		{
			// Datatypes can have their own custom data sources, as many as they want
			// So, each data source has a unique retrieval method.
			// They can also have offset/limit restrictions
			// Ex: retrieve_ee1x_galleries()
			$method_name = 'retrieve_'.$settings['data_source'];

			if ( method_exists($this->load_datatype($datatype), $method_name))
			{
				$this->source_data = $this->actions()->load_datatype($datatype)->{$method_name}($this, $settings, $offset, $limit);
			}
		}

		// --------------------------------------------
		//  Local File
		// --------------------------------------------

		if ($settings['data_source'] == 'filename')
		{
			$settings['filename'] = $settings['filename'];

			$this->source_data = $this->load_local_file($settings['filename']);

			if ($this->source_data === FALSE)
			{
				return lang('problem_retreiving_file_data');
			}
		}

		// --------------------------------------------
		//  Encryption Required for Decoding Username/Passwords
		// --------------------------------------------

		if ($settings['data_source'] != 'manual_upload')
		{
			ee()->load->library('encrypt');

			if (ee()->config->item('encryption_key') == '')
			{
				ee()->encrypt->set_key(md5(ee()->db->username.ee()->db->password));
			}
		}

		// --------------------------------------------
		//  Remote URL
		// --------------------------------------------

		if ($settings['data_source'] == 'url')
		{
			if ( ! empty($settings['http_auth_username']))
			{
				if ( ($this->source_data = $this->fetch_url($settings['remote_url'],
															array(),
															ee()->encrypt->decode(base64_decode($settings['http_auth_username'])),
															ee()->encrypt->decode(base64_decode($settings['http_auth_password'])))) === FALSE)
				{
					return lang('invalid_remote_url_not_found');
				}
			}
			elseif ( ($this->source_data = $this->fetch_url($settings['remote_url'])) === FALSE)
			{
				return lang('invalid_remote_url_not_found');
			}

			// --------------------------------------------
			//  Store as File to Convert Zip/Gzip
			// --------------------------------------------

			$x			= explode('/', $settings['remote_url']);
			$filename	= $this->clean_filename($x[count($x)-1]);
			$file_info	= pathinfo($filename);

			if ($file_info['extension'] == 'zip' OR $file_info['extension'] != 'gz')
			{
				$temp_file	= APPPATH.'cache/importer/url_'.date('Y-m-d_H-i').'_'.$filename;

				if ($this->write_file($temp_file, $this->source_data) == FALSE)
				{
					return $this->errors;
				}

				// --------------------------------------------
				//  Load File Data - Decompress
				// --------------------------------------------

				$this->source_data = $this->load_local_file($temp_file);

				if ($this->source_data === FALSE)
				{
					return lang('problem_retreiving_file_data');
				}
			}
		}

		// --------------------------------------------
		//  FTP
		// --------------------------------------------

		if ($settings['data_source'] == 'ftp')
		{
			ee()->load->library('solspace_ftp');

			$config['hostname'] 	= $settings['ftp_host'];
			$config['username'] 	= ee()->encrypt->decode(base64_decode($settings['ftp_username']));
			$config['password'] 	= ee()->encrypt->decode(base64_decode($settings['ftp_password']));
			$config['port'] 		= $settings['ftp_port'];
			$config['debug']		= TRUE;

			$connection = ee()->solspace_ftp->connect($config);

			if ($connection === FALSE)
			{
				return lang(ee()->solspace_ftp->error);
			}

			// --------------------------------------------
			//  Create Temporary File for Data
			// --------------------------------------------

			$x = explode('/', $settings['ftp_path']);
			$filename = $this->clean_filename($x[count($x)-1]);

			$temp_file = APPPATH.'cache/importer/ftp_'.date('Y-m-d_H-i').'_'.$filename;

			$result = ee()->solspace_ftp->download($settings['ftp_path'], $temp_file);

			if ($result === FALSE OR ! file_exists($temp_file))
			{
				return lang('failure_downloading_remote_file');
			}

			ee()->solspace_ftp->close();

			// --------------------------------------------
			//  Load File Data - Decompress if Necessary
			// --------------------------------------------

			$this->source_data = $this->load_local_file($temp_file);

			if ($this->source_data === FALSE)
			{
				return lang('problem_retreiving_file_data');
			}
		}

		// --------------------------------------------
		//  SFTP
		// --------------------------------------------

		if ($settings['data_source'] == 'sftp')
		{
			require_once PATH_THIRD.'importer/libraries/phpseclib/Net/SFTP.php';

			// define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

			$sftp = new Net_SFTP($settings['ftp_host']);

			if ($sftp->login(ee()->encrypt->decode(base64_decode($settings['ftp_username'])),
							 ee()->encrypt->decode(base64_decode($settings['ftp_password']))) == FALSE)
			{
				//print_r($sftp->getSFTPErrors());

				return lang(lang('error_importer_ftp_test'));
			}

			// --------------------------------------------
			//  Create Temporary File for Data
			// --------------------------------------------

			$x = explode('/', $settings['ftp_path']);
			$filename = $this->clean_filename($x[count($x)-1]);

			$temp_file = APPPATH.'cache/importer/sftp_'.date('Y-m-d_H-i').'_'.$filename;

			// http://phpseclib.sourceforge.net/documentation/net.html#net_sftp_get
			// Net_SFTP will actually return the file data to us, if we only have the first argument
			// However, if it is zip/gzip, we want a local file to decode

			$result = $sftp->get($settings['ftp_path'], $temp_file);

			if ($result === FALSE OR ! file_exists($temp_file))
			{
				return lang('failure_downloading_remote_file');
			}

			$sftp->disconnect();

			// --------------------------------------------
			//  Load File Data - Decompress if Necessary
			// --------------------------------------------

			$this->source_data = $this->load_local_file($temp_file);

			if ($this->source_data === FALSE)
			{
				return lang('problem_retreiving_file_data');
			}
		}

		// --------------------------------------------
		//  Uploaded File
		// --------------------------------------------

		if ($settings['data_source'] == 'manual_upload')
		{
			if ( ! isset($_FILES['manual_upload']['name']))
			{
				return lang('invalid_data_source_submitted');
			}

			// --------------------------------------------
			//  Configure Upload
			// --------------------------------------------

			$original_filename = $_FILES['manual_upload']['name'];
			$clean_filename = 'manual_'.date('Y-m-d_H-i').'_'.$this->clean_filename($original_filename);

			ee()->load->helper('xss');

			$config = array(
				'file_name'		=> $clean_filename,
				'upload_path'	=> APPPATH.'cache/importer/',
				'allowed_types'	=> 'txt|xml|rss|atom|html|csv|json|js',
				'xss_clean'		=> xss_check(),
				'overwrite'		=> TRUE
			);

			// --------------------------------------------
			//  Upload File
			// --------------------------------------------

			ee()->load->library('upload');
			ee()->upload->initialize($config);

			if ( ! ee()->upload->do_upload('manual_upload'))
			{
				return lang(ee()->upload->display_errors());
			}

			$file = ee()->upload->data();

			// (try to) Set proper permissions
			@chmod($file['full_path'], DIR_WRITE_MODE);

			// --------------------------------------------
			//  Load File Data - Decompress if Necessary
			// --------------------------------------------

			$temp_file = $file['full_path'];
			$this->source_data = $this->load_local_file($temp_file);

			if ($this->source_data === FALSE)
			{
				return lang('problem_retreiving_file_data');
			}
		}

		// --------------------------------------------
		//  Source Data must exist
		//  - can either be returned as an array (DB) or as a string (file)
		// --------------------------------------------

		if ( empty($this->source_data) OR ( ! is_string($this->source_data) && ! is_array($this->source_data)))
		{
			return lang('unable_to_retrieve_source_data');
		}

		// --------------------------------------------
		//  Memory Constraints for String Source Data
		// --------------------------------------------

		if ( is_string($this->source_data))
		{
			$current	= memory_get_usage();
			$limit		= ini_get('memory_limit');
			$last		= strtolower($limit[strlen($limit)-1]);

			switch($last)
			{
				// The 'G' modifier is available since PHP 5.1.0
				case 'g':
					$limit = substr($limit,0,(strlen($limit)-1));
					$limit *= 1024 * 1024 * 1024;
				break;
				case 'm':
					$limit = substr($limit,0,(strlen($limit)-1));
					$limit *= 1024 * 1024;
				break;
				case 'k':
					$limit = substr($limit,0,(strlen($limit)-1));
					$limit *= 1024;
				break;
			}

			$difference = $limit - $current;

			// 1,000,000 characters ~= 1MB
			$size = (strlen($this->source_data) / (1000 * 1000)) * 1024 * 1024;

			// Assumption that processing of this data will take 2.0 times the size of it.
			if ($difference < ($size * 2.0))
			{
				//echo $difference."\n<br />\n".$size."\n<br />\n";
				return lang('importer_memory_usage_warning');
			}
		}

		// --------------------------------------------
		//  All Done Here.
		// --------------------------------------------

		return TRUE; // Success!
	}
	// END load_source()

	// --------------------------------------------------------------------

	/**
	 *	Load Local File
	 *
	 *	Loads a file on the local file system.  Will determine if it is a zip or gzip file and
	 *	decompress, if necessary
	 *
	 *	echo $this->actions()->load_local_file(APPPATH.'cache/current_version.zip');
	 *
	 *	@access		public
	 *	@param		string
	 *	@return		string
	 */

	public function load_local_file($path)
	{
		$file_info = pathinfo($path);

		// --------------------------------------------
		//  Just Get Out of Here...
		// --------------------------------------------

		if ($file_info['extension'] != 'zip' && $file_info['extension'] != 'gz')
		{
			return file_get_contents($path);
		}

		// --------------------------------------------
		//  We Need a Cache Folder to Do Our Work
		// --------------------------------------------

		if ( ! is_dir(APPPATH.'cache/') OR ! is_really_writable(APPPATH.'cache/'))
		{
			$this->error = lang('unable_to_create_unzipping_directory');
			return FALSE;
		}

		$basepath = APPPATH.'cache/importer/';

		if ( ! @is_dir($basepath))
		{
			if ( ! @mkdir($basepath, DIR_WRITE_MODE))
			{
				$this->error = lang('unable_to_create_unzipping_directory');
				return FALSE;
			}

			@chmod($basepath, DIR_WRITE_MODE);
		}

		// --------------------------------------------
		//  Zip File.  Work Work Work...
		// --------------------------------------------

		if ($file_info['extension'] == 'zip' )
		{
			@include_once(APPPATH.'libraries/Pclzip.php');

			if ( ! class_exists('PclZip')) return FALSE;

			// The chdir apparently breaks CI's view loading, so store a reference and reset after unzip
			$_ref = getcwd();

			$zip = new PclZip($path);
			chdir($basepath);

			$ok = @$zip->extract('');
			//var_dump($ok);

			if ( (int) $ok == 0)
			{
				// Error
				$this->error = $zip->error_string;
				return FALSE;
			}

			if (is_array($ok))
			{
				foreach($ok as $file)
				{
					if ( isset($file['stored_filename']))
					{
						// This is what we need to find in PATH_CACHE now...
						if ( file_exists($basepath.$file['stored_filename']))
						{
							$data = $this->load_local_file($basepath.$file['stored_filename']);

							// Remove file.
							unlink($basepath.$file['stored_filename']);

							// Fix loader scope
							chdir($_ref);

							return $data;
						}
					}
				}
			}

			// Fix loader scope
			chdir($_ref);
		}

		// --------------------------------------------
		//  Gzip. Requires ZLib.  In the future, may try $execute = "gunzip -".$path." $file";
		// --------------------------------------------

		if ($file_info['extension'] == 'gz')
		{
			if ( function_exists( 'gzfile' ) === TRUE )
			{
				if ( is_array( $output = @gzfile( $path ) ) === TRUE )
				{
					return implode( $output );
				}
			}
		}

		$this->error('error_unable_to_read_data_file');
		return FALSE;
	}
	// load_local_file()

	// --------------------------------------------------------------------

	/**
	 *	Clean up File Name for File Directory
	 *
	 *	@access		public
	 *	@param		string 		$original_filename
	 *	@param		string		$directory_path
	 *	@param		bool		$duplicate_check
	 *	@return		string
	 */

	function clean_filename($filename, $directory_path = '', $duplicate_check = FALSE)
	{
		$i = 1;
		$ext = '';

		// Remove spaces and sanitize based on CI Security
		$filename = preg_replace("/\s+/", '_', $filename);
		$filename = ee()->security->sanitize_filename($filename);

		if (strpos($filename, '.') !== FALSE)
		{
			$parts		= explode('.', $filename);
			$ext		= array_pop($parts);  // Find the last one.
			$filename	= implode('.', $parts);
		}

		$ext = '.'.$ext;

		// --------------------------------------------
		//  Check for Duplicate, Add Number if Exists
		// --------------------------------------------

		if ($duplicate_check == TRUE)
		{
			$basename = $filename;

			while (file_exists($directory_path.$filename.$ext))
			{
				$filename = $basename.'_'.$i++;
			}
		}

		return $filename.$ext;
	}
	// END clean_filename()

	// --------------------------------------------------------------------

	/**
	 *	Element Options
	 *
	 *	@access		public
	 *	@param		array		// Default Values
	 *	@return		string
	 */
	public function element_options($data, $past = '')
	{
		$return = array();

		if ( $past != '')
		{
			$past .= '/';
		}

		if ( ! is_array($data))
		{
			return $data;
		}

		foreach($data as $key => $value)
		{
			if ( ! is_array($value))
			{
				$return[$past.$key] = $value;
			}
			// Empty Array
			elseif ( count($value) == 0)
			{
				$return[$past.$key.'/[array]'] = array();
			}
			/* We have an array of values

			JSON Ex:
				"matrix":
				[
					{"cell_one":"Text One","cell_two":"2012-02-28 10:28 AM", "cell_three":"1,2,3"},
					{"cell_one":"Text Two","cell_two":"2012-02-29 10:28 AM", "cell_three":"[1] Getting to Know ExpressionEngine - getting_to_know_expressionengine"}
				]

			We pop off the first one and use its keys.
			*/

			elseif( isset($value[0]))
			{
				if (is_string($value[0]))
				{
					$return[$past.$key] = $value[0];
				}
				else
				{
					$return = array_merge($return, $this->element_options($value[0], $past.$key.'/[array]'));
				}

				// $return[$past.$key] = $value[0];
			}
			else
			{
				$return = array_merge($return, $this->element_options($value, $past.$key));
			}
		}

		return $return;
	}
	// END element_options

	// --------------------------------------------------------------------

	/**
	 *	Find Element
	 *
	 *	Finds an Element, first occurrence detected, and returns it.
	 *
	 *	@access		public
	 *	@param		string		$element - That for which we hunt
	 *	@param		array		$data - Array of parsed elements
	 *	@param		bool		$duplicates - Some elements might have duplicates, so we find it first and all its siblings
	 *	@return		mixed       NULL if not found, value if found
	 */

	public static function find_element( $element, $data, $duplicates = FALSE )
	{
		// Find element(s)
		$return = Importer_actions::find_element_auxillary($element, $data, $duplicates);

		// --------------------------------------------
		//  Remove White Space
		// --------------------------------------------

		if ( $return === NULL) return NULL;

		if (is_string($return)) return trim($return);

		if ( is_array($return))
		{
			// This caused problems for people, so we stop it.
			//array_walk($return, create_function('&$val', 'if (is_string($val)) $val = trim($val);'));
		}

		return $return;
	}


	// --------------------------------------------------------------------

	/**
	 *	Find Element Auxillary
	 *
	 *	Finds an Element, first occurrence detected, and returns it.
	 *
	 * @static
	 *	@access		public
	 *	@param		string		$element - That for which we hunt
	 *	@param		array		$data - Array of parsed elements
	 *	@param		bool		$duplicates - Some elements might have duplicates, so we find it first and all its siblings
	 *	@return		mixed       NULL if not found, value if found
	 */

	public static function find_element_auxillary( $element, $data, $duplicates = FALSE )
	{
		if ( empty( $data )) return NULL;
		if ( ! is_string($element) OR $element == '') return NULL;

		// --------------------------------------------
		//  Break it down, cycle through, find the data. Save the world, kiss the girl, lunch!
		// --------------------------------------------

		if ( stristr($element, '/'))
		{
			$original_data = $data; // Store in case it is not found.

			$x = explode('/', $element);

			while(count($x) > 0)
			{
				$y = array_shift($x);

				// Ex:  /matrix/[array]/cell_one + $duplicates = TRUE
				// In this case, we have the matrix primary element, which is parsed and $data
				// is an array of elements. So, we cycle through that array and pull out the
				// field we want and put all of those values into an array that we return.
				if ($y === '[array]')
				{
					if (is_array($data))
					{
						if (isset($data[0]))
						{
							$next = array_shift($x);

							// There is no next element, this is it! Return!
							if ( ! isset($next))
							{
								return $data;
							}

							// --------------------------------------------
							//  Cycle Through Array Looking for Subelements Inside them
							// --------------------------------------------

							$lower_data = array();

							foreach($data as $subkey => $subvalue)
							{
								unset($data[$subkey]);
								$lower_element = Importer_actions::find_element_auxillary($next, $subvalue, $duplicates);

								if ( ! empty($lower_element))
								{
									$lower_data[] = $lower_element;
								}
							}

							$data = (empty($lower_data)) ? FALSE : $lower_data;
						}
						// Empty array, so no data. return NULL;
						else
						{
							return NULL;
						}
					}
					// Not an array at all? If string, return that, if not...FALSE!
					else
					{
						$data = (is_string($data)) ? $data : NULL;
					}
				}
				else
				{
					$data = Importer_actions::find_element_auxillary($y, $data, $duplicates);
				}

				if ($data === NULL) break;
			}


			if ($data !== FALSE) return $data;

			// Reset data to original.
			$data = $original_data;
		}

		// --------------------------------------------
		//  Oh, there it is!  Just return.
		// --------------------------------------------

		if (isset($data[$element]))
		{
			if ( is_array($data[$element]))
			{
				// Empty Array
				if (count($data[$element]) == 0)
				{
					return ($duplicates == FALSE) ? '' : array();
				}

				// Array that does is not associative, we return first
				if (isset($data[$element][0]) && $duplicates == FALSE)
				{
					/* We can get an array of values
					ex:
						"matrix":
							[
								{"cell_one":"Text One","cell_two":"2012-02-28 10:28 AM", "cell_three":"1,2,3"},
								{"cell_one":"Text Two","cell_two":"2012-02-29 10:28 AM", "cell_three":"[1] Getting to Know ExpressionEngine - getting_to_know_expressionengine"}
							],

					If duplicates are not allowed, we only return the first one.
					Duplicates are currently only allowed for custom field types, like Matrix and Playa.
					*/

					return array_shift($data[$element]);
				}
			}

			return $data[$element];
		}

		// --------------------------------------------
		//  If array, we cycle through and check.  Do recursive parsing, if necessary
		// --------------------------------------------

		if ( is_array($data))
		{
			foreach ( $data as $key => $val )
			{
				if ( is_array( $val ) )
				{
					$return = Importer_actions::find_element_auxillary($element, $val, $duplicates);

					if ($return !== NULL)
					{
						if ( is_array($return) && isset($return[0]) && $duplicates == FALSE)
						{
							return array_shift($return);
						}

						return $return;
					}
				}
			}
		}

		return NULL;
	}
	// END find_element_auxillary()


	// --------------------------------------------------------------------

	/**
	 *	Starts an Import
	 *
	 *	Essentially, it takes the profile and starts an import.
	 *	If there are batches, it stores
	 *	those in the database.
	 *
	 *	@access		public
	 *	@param		integer		$profile_id
	 *	@return		bool|string	FALSE if error, string if batches,
	 *							TRUE if no batches and successful
	 */
	public function start_import($profile_id, $location='cp')
	{
		if ( ! ctype_digit($profile_id))
		{
			return FALSE;
		}

		ee()->lang->load('importer');

		$this->batch_processing = FALSE;
		ee()->db->save_queries	= TRUE;

		// --------------------------------------------
		//  Retrieve Settings
		// --------------------------------------------

		$profile_data = $this->data->get_profile_data($profile_id);

		if (empty($profile_data))
		{
			return FALSE;
		}

		$settings['profile_name']	= $name			= $profile_data['name'];
		$settings['profile_id']		= $profile_id	= $profile_data['profile_id'];
		$settings['datatype']		= $datatype		= $profile_data['datatype'];
		$settings['last_import']	= $last_import	= $profile_data['last_import'];
		$settings['content_type']	= $content_type = $profile_data['content_type'];

		$query = ee()->db->get_where(
			'exp_importer_profile_settings',
			array('profile_id' => $profile_id)
		);

		foreach($query->result_array() as $row)
		{
			$settings[$row['setting']] = $row['value'];
		}

		// --------------------------------------------
		//  Start Notifications
		// --------------------------------------------

		$stats['start_time']		= microtime(TRUE);
		$stats['debugging']			= array();
		$stats['run_time']			= 0;
		$stats['import_location']	= $location;
		$stats['number_of_queries']	= 0;
		$start_query_amount 		= count(ee()->db->queries);
		$stats['import_location']	= $location;

		$this->send_notifications('start', $settings, $stats);

		// --------------------------------------------
		//  Retrieve Data
		// --------------------------------------------

		$datatype_batch_enabled = FALSE;

		$stats['debugging'][] = "Retrieve Data - START: " .
									round(memory_get_usage() / 1024 / 1024, 2).' MB';

		// Does this datatype have its own data source
		// that has batch fetching of data enabled?
		// If so, we load the batches in the DB to
		// help keep track but we do not store data.
		if ($this->load_datatype($datatype)->batch_enabled === TRUE &&
			! in_array($settings['data_source'], $this->default_data_sources))
		{
			$datatype_batch_enabled = TRUE;

			$method_name = 'count_'.$settings['data_source'];

			if ( ! method_exists($this->load_datatype($datatype), $method_name))
			{
				$this->error = lang('datatype_is_missing_batch_processing_method');
				return FALSE;
			}

			$total_data_rows = $this->actions()
									->load_datatype($datatype)
									->{$method_name}($settings);

			// Get first batch!
			if (($error = $this->retrieve_source_data($datatype, $settings, 0, $this->batch_size)) !== TRUE)
			{
				$this->error = $error;
				return FALSE;
			}
		}
		else
		{
			if (($error = $this->retrieve_source_data($datatype, $settings)) !== TRUE)
			{
				$this->error = $error;
				return FALSE;
			}
		}

		// Could be an empty string, empty array, or FALSE
		if ( empty($this->source_data))
		{
			$this->error = lang('problem_retreiving_data');
			return FALSE;
		}

		// --------------------------------------------
		//  Parse Data
		// --------------------------------------------

		$stats['debugging'][] = "Parse Data - START: ".round(memory_get_usage() / 1024 / 1024, 2).' MB';

		$data_array = $this->load_datatype($datatype)->parse_data($this->source_data, $settings);

		$stats['debugging'][] = "Parse Data - END: ".round(memory_get_usage() / 1024 / 1024, 2).' MB';

		if ($datatype_batch_enabled !== TRUE)
		{
			$total_data_rows = count($data_array);
		}

		// --------------------------------------------
		//  Insure we have valid data array
		// --------------------------------------------

		if ( ! is_array($data_array) OR empty($data_array))
		{
			$this->error = lang('source_data_contained_invalid_data');
			return FALSE;
		}

		$stats['number_of_queries']	+= (count(ee()->db->queries) - $start_query_amount);

		// --------------------------------------------
		//  Batch Processing Start
		// --------------------------------------------

		if ($total_data_rows > $this->batch_size)
		{
			$this->batch_processing = TRUE;
			$batch_hash				= uniqid();

			$stats['number_of_queries'] += $total_data_rows + 1; // The batch queries below

			// --------------------------------------------
			//  Insert Batch Meta Data
			// --------------------------------------------

			$data = array(	'batch_id' 		=> NULL,
							'profile_id'	=> $settings['profile_id'],
							'batch_hash'	=> $batch_hash,
							'details'		=> base64_encode(serialize(array('stats' => $stats, 'settings' => $settings))),
							'batch_date'	=> time(),
							'finished'		=> 'n');

			ee()->db->query(ee()->db->insert_string('exp_importer_batches', $data));

			// --------------------------------------------
			//  Insert Batches of Data
			// --------------------------------------------

			$data = array(	'profile_id'	=> $settings['profile_id'],
							'batch_hash'	=> $batch_hash,
							'batch_number'	=> 1,
							'batch_data'	=> '',
							'batch_date'	=> time(),
							'finished'		=> 'n');

			$items = array();

			for ($i = 1, $s = (int) ceil($total_data_rows / $this->batch_size); $i <= $s; ++$i)
			{
				$data['batch_number']	= $i;
				$data['batch_data']     = '';

				// --------------------------------------------
				//  Data Type Batch Retrieval
				//  - done on request, except for the first one, which we got above to validate
				// --------------------------------------------

				if ($datatype_batch_enabled === TRUE)
				{
					if ($i != 1)
					{
						if (($error = $this->retrieve_source_data($datatype, $settings, (($i-1) * $this->batch_size), $this->batch_size)) !== TRUE)
						{
							$this->error = $error;
							return FALSE;
						}

						// Could be an empty string, empty array, or FALSE
						if ( empty($this->source_data))
						{
							$this->error = lang('problem_retreiving_batch_data');
							return FALSE;
						}

						$data_array = $this->load_datatype($datatype)->parse_data($this->source_data, $settings);
					}

					$data['batch_data']		= base64_encode(serialize($data_array));
				}
				else
				{
					// Data comes in one big array, so we need to slice it up.
					$data['batch_data']		= base64_encode(serialize(array_slice($data_array,
																				(($i-1) * $this->batch_size),
																				$this->batch_size)));
				}

				ee()->db->query(ee()->db->insert_string('exp_importer_batch_data', $data));
			}

			// --------------------------------------------
			//  We Simply Store the Batches and Return
			// --------------------------------------------

			return $batch_hash;
		}

		// --------------------------------------------
		//  No Batches?  Continue On!
		// --------------------------------------------

		$return = $this->import_batch($settings, $data_array, $stats);

		if ($return == FALSE)
		{
			return FALSE;
		}

		return $this->complete_import($settings, $return);
	}

	// --------------------------------------------------------------------

	/**
	 *	Import Batch
	 *
	 *	Import a batch of data
	 *
	 *	@access		public
	 *	@param		array 	$settings
	 *	@param		array	$data_array
	 *	@return		bool
	 */

	public function import_batch($settings, $data_array, $stats)
	{
		ee()->db->save_queries	= TRUE;
		$start_query_amount		= count(ee()->db->queries);

		// --------------------------------------------
		//  Load Content Type and Perform Import
		// --------------------------------------------

		$content_types = $this->list_content_types();

		if (! isset($content_types[$settings['content_type']]))
		{
			$this->error = lang('invalid_importer_profile_datatype');
			return FALSE;
		}

		// Perform the Import
		if (($return = $this->load_content_type($settings['content_type'])->perform_import($settings, $data_array)) === FALSE)
		{
			// Call the content type's errors() method to fetch the array of errors
			$this->error = $this->load_content_type($settings['content_type'])->errors();
			return FALSE;
		}

		// --------------------------------------------
		//  Update Stats
		// --------------------------------------------

		foreach($return as $key => $value)
		{
			if ( ! isset($stats[$key]))
			{
				$stats[$key] = $value;
			}
			elseif(is_numeric($value) &&
				   in_array($key, array('entries_inserted', 'entries_updated', 'entries_deleted', 'total_entries')))
			{
				// Already set and is an integer
				$stats[$key] += $value;
			}
			elseif ( is_array($value) &&
					in_array($key, array('author_ids', 'entry_ids', 'inserted_entry_ids', 'updated_entry_ids', 'debugging')))
			{
				$stats[$key] = array_unique(array_merge($stats[$key], $value));
			}
		}

		$stats['number_of_queries']	+= (count(ee()->db->queries) - $start_query_amount);

		// --------------------------------------------
		//  Return Stats
		// --------------------------------------------

		return $stats;
	}
	// END import_batch()

	// --------------------------------------------------------------------

	/**
	 *	Complete Import
	 *
	 *	Updates all of information to say the import is complete and sends out the notification
	 *
	 *	@access		public
	 *	@param		array		$settings
	 *	@param		array		$stats - All of the import data
	 *	@return		string
	 */

	public function complete_import($settings, $stats, $hash = '')
	{
		// --------------------------------------------
		//  Update Last Import Date
		// --------------------------------------------

		ee()->db->where('profile_id', $settings['profile_id']);
		ee()->db->update('exp_importer_profiles', array('last_import' => ee()->localize->now));
		$stats['number_of_queries']	+= 1;

		// --------------------------------------------
		//  Start and End Times
		// --------------------------------------------

		$stats['end_time']			= microtime(TRUE);
		$stats['run_time']			= round($stats['end_time'] - $stats['start_time'], 3).' seconds';

		$stats['start_time']		= $this->human_time($stats['start_time']);
		$stats['end_time']			= $this->human_time($stats['end_time']);

		// --------------------------------------------
		//  Clean Up Location
		// --------------------------------------------

		$stats['import_location'] = ($stats['import_location'] == 'cron') ? "Cron" : 'Control Panel';

		// --------------------------------------------
		//  End Notifications
		// --------------------------------------------

		$this->send_notifications('end', $settings, $stats);

		// --------------------------------------------
		//  Log the Import
		// --------------------------------------------

		$insert					= array();
		$insert['profile_id']	= $settings['profile_id'];
		$insert['batch_hash']	= $hash;
		$insert['date']			= ee()->localize->now;
		$insert['details']		= base64_encode(serialize($stats));

		ee()->db->insert('exp_importer_log', $insert);

		return $stats;
	}
	// END complete_import()

	// --------------------------------------------------------------------

	/**
	 *	Send Notifications
	 *
	 *	Send the import notifications
	 *
	 *	@access		public
	 *	@param		string		$type - start/end
	 *	@return		bool
	 */
	public function send_notifications($type='end', $settings, $stats = array())
	{
		// --------------------------------------------
		//  To Send or Not To Send?
		//	- Notification Rules options: disabled, start, end, start_end
		// --------------------------------------------

		if ( empty($settings['notification_emails']))
		{
			return;
		}

		if ( $settings['notification_rules'] == 'disabled')
		{
			return;
		}

		if ($type == 'start' && $settings['notification_rules'] == 'end')
		{
			return;
		}

		if ($type == 'end' && $settings['notification_rules'] == 'start')
		{
			return;
		}

		// --------------------------------------------
		//  Parsing of variables inside subject and message
		// --------------------------------------------

		$subject = $settings['notification_subject'];
		$message = $settings['notification_message'];

		$vars = array(	'{author_ids}'			=> ( isset($stats['author_ids'])) ? implode(', ', $stats['author_ids']) : '',
						'{email_cc}'			=> (isset($settings['notification_cc'])) ? $settings['notification_cc'] : '',
						'{emails}'				=> $settings['notification_emails'],
						'{import_date}'			=> $this->convert_timestamp('%r', ee()->localize->now),
						'{import_ip_address}'	=> ee()->input->ip_address(),
						'{import_location}'		=> $stats['import_location'],
						'{last_import_date}'	=> $this->convert_timestamp('%r', $settings['last_import']),
						'{run_time}'			=> $stats['run_time'],
						'{site_id}'				=> ( isset($stats['site_id'])) ? $stats['site_id'] : ee()->config->item('site_id'),
						'{start_or_end}'		=> $type,
						'{profile_name}'		=> $settings['profile_name'],
						'{total_inserted}'		=> ( isset($stats['entries_inserted'])) ? $stats['entries_inserted'] : 0,
						'{total_updated}'		=> ( isset($stats['entries_updated'])) ? $stats['entries_updated'] : 0,
						'{entries_deleted}'		=> ( isset($stats['entries_deleted'])) ? $stats['entries_deleted'] : 0,
						'{content_type}'		=> $settings['content_type'],
						'{datatype}'			=> $settings['datatype'],
						'{channel_id}'			=> (isset($settings['channel_id'])) ? $settings['channel_id'] : '',
		);

		$subject = str_replace(array_keys($vars), array_values($vars), $subject);
		$message = str_replace(array_keys($vars), array_values($vars), $message);

		// --------------------------------------------
		//  Send a Notification, Please
		// --------------------------------------------

		ee()->load->library('email');
		ee()->load->helper('text');

		ee()->email->EE_initialize();
		ee()->email->wordwrap = false;
		ee()->email->from(ee()->config->item('webmaster_email'), ee()->config->item('webmaster_name'));
		ee()->email->to($settings['notification_emails']);

		if ( ! empty($settings['notification_cc']))
		{
			ee()->email->cc($settings['notification_cc']);
		}

		ee()->email->reply_to(ee()->config->item('webmaster_email'));
		ee()->email->subject($subject);
		ee()->email->message(entities_to_ascii($message));
		ee()->email->send();
	}
	// END send_notifications()

	// --------------------------------------------------------------------

	/**
	 *	Debugging Output
	 *
	 *	@access		public
	 *	@param		array
	 *	@return		string
	 */
	function statistics_output($report)
	{
		if (IMPORTER_DEBUGGING !== TRUE)
		{
			unset($report['debugging']);
			unset($report['entry_ids']);
			unset($report['author_ids']);
			unset($report['inserted_entry_ids']);
			unset($report['updated_entry_ids']);
		}

		// Because the EE Content Language file overrides our language variable on Run
		ee()->lang->language['entries_deleted'] = ee()->lang->language['deleted_entries'];

		// --------------------------------------------
		//  Create the Unordered List output
		// --------------------------------------------

		$output = "\n<ul class='importer_statistics'>";

		foreach($report as $name => $values)
		{
			$output .= '<li><strong>'.lang($name).': </strong>';

			if (is_array($values))
			{
				$output .= "\n<ul>";

				foreach($values as $val)
				{
					$output .= '<li>'.$val."</li>\n";
				}

				$output .= "</ul>\n";
			}
			else
			{
				$output .= $values;
			}

			$output .= "</li>\n";
		}

		$output .= "</ul>\n";

		return $output;
	}
	// END debugging_output()

}
/* END Importer_actions Class */


// --------------------------------------------------------------------

/**
 *	Automatically Adjusts Memory Limit during Imports
 *
 *	@access		public
 *	@return		null
 */

declare(ticks=3);

function tick_handler_importer()
{
	static $enabled = TRUE;

	if ( $enabled !== TRUE)
	{
		return;
	}

	$current	= memory_get_usage();
	$limit		= ini_get('memory_limit');
	$last		= strtolower($limit[strlen($limit)-1]);

	switch($last)
	{
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$limit = substr($limit,0,(strlen($limit)-1));
			$limit *= 1024 * 1024 * 1024;
		break;
		case 'm':
			$limit = substr($limit,0,(strlen($limit)-1));
			$limit *= 1024 * 1024;
		break;
		case 'k':
			$limit = substr($limit,0,(strlen($limit)-1));
			$limit *= 1024;
		break;
	}

	$delta = ($current / $limit) * 100; // % memory used

	$threshold = 90; // % threshold before adding more memory

	if ($delta >= $threshold)
	{
		// add 20% more than the original memory limit
		$increase = round($limit * 0.20, 0);

		$new = (int) ($increase + $limit);

		// No greater than 500MB
		if ( $new > (500*1024*1024) OR ! ini_set('memory_limit', $new))
		{
			$enabled = FALSE;
			@unregister_tick_function('tick_handler_importer');
		}
		else
		{
			//echo "{$delta}% Memory limit. Adjusted dynamically to $new\n";
		}

		usleep(500000); // 0.5 seconds
	}
}
// END tick_handler_importer()