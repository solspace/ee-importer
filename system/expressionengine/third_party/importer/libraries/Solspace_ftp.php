<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - FTP
 *
 * Extends the CodeIgniter FTP class to do things a bit more to my liking.
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/libraries/Solspace_ftp.php
 */

require_once(BASEPATH.'libraries/Ftp.php');

// http://stackoverflow.com/questions/3028898/php-codeigniter-ftp-timout

// ------------------------------------------------------------------------

/**
 * FTP Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/ftp.html
 */
class Solspace_ftp extends CI_FTP {

	public $ssl_mode	= FALSE;
	public $timeout		= 15;
	public $error		= '';
	public $show_error	= FALSE;

	/**
	 * Constructor - Sets Preferences
	 *
	 * The constructor can be passed an array of config values
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		/*
		Config: hostname, username, password, port,
				passive, debug, ssl_mode, show_error, timeout
		*/

		log_message('debug', "Solspace FTP Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * FTP Connect
	 *
	 * @access	public
	 * @param	array	 the connection values
	 * @return	bool
	 */
	function connect($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

		if($this->ssl_mode == TRUE)
		{
			if (function_exists('ftp_ssl_connect'))
			{
				$this->conn_id = @ftp_ssl_connect($this->hostname, $this->port, $this->timeout);
			}
			else
			{
				$this->_error('ftp_ssl_not_supported');
			}
		}
		else
		{
			$this->conn_id = @ftp_connect($this->hostname, $this->port, $this->timeout);
		}


		if ($this->conn_id === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_connect');
			}

			return FALSE;
		}

		if ( ! $this->_login())
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_login');
			}
			return FALSE;
		}

		// Set passive mode if needed
		if ($this->passive == TRUE)
		{
			ftp_pasv($this->conn_id, TRUE);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Download a file from a remote server to the local server
	 *
	 * Modified from the CI FTP to allow some better error reporting.  The CI one had virtually none
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function download($rem_path, $loc_path, $mode = 'auto')
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// get remote folder/filename
		$rem_folder   = dirname($rem_path);
		$rem_filename = basename($rem_path);

		// --------------------------------------------
		//  Check if it is a Local Directory or the File
		// --------------------------------------------

		if (@is_dir($loc_path))
		{
			$loc_folder   = rtrim($loc_path, '/');
			$loc_filename = $rem_filename;
		}
		else
		{
			$loc_folder   = dirname($loc_path);
			$loc_filename = basename($loc_path);
		}

		// --------------------------------------------
		//  Validate Local Path
		// --------------------------------------------

		if ( $loc_folder != '.'  && ! @is_dir($loc_folder))
		{
			$this->_error('ftp_bad_local_path');
			return FALSE;
		}
		// check that loc path and file are writable
		elseif ( ! is_really_writable($loc_folder) OR
				(file_exists($loc_folder.'/'.$loc_filename) && ! @is_really_writable($loc_folder.'/'.$loc_filename)))
		{
			$this->_error('ftp_local_path_not_writable');
			return FALSE;
		}

		// --------------------------------------------
		//  Switch Directories
		// --------------------------------------------

		if ( ! @chdir($loc_folder))
		{
			$this->_error('ftp_bad_local_path');
			return FALSE;
		}

		if ( $rem_folder != '.' && ! $this->changedir($rem_folder))
		{
			$this->_error('ftp_bad_remote_path');
			return FALSE;
		}

		// --------------------------------------------
		//  Validate Remote File
		// --------------------------------------------

		$found_file = FALSE;
		$files = $this->list_files();

		if ( ! is_array($files) OR empty($files))
		{
			$this->_error('ftp_bad_remote_file');
			return FALSE;
		}

		foreach ($files as $f)
		{
			if ($f == $rem_filename)
			{
				$found_file = TRUE;
				break;
			}
		}

		if ($found_file === FALSE)
		{
			$this->_error('ftp_bad_remote_file');
			return FALSE;
		}

		// --------------------------------------------
		//  Move Remote File to Local
		// --------------------------------------------

		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = $this->_getext($rem_path);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		// download the file
		$result = @ftp_get($this->conn_id, $loc_folder.'/'.$loc_filename, $rem_filename, $mode);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_download');
			}

			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns Last Modified Time for File
	 *
	 * @access	public
	 * @return	string|integer
	 */
	function last_modified($path = '')
	{
		if ( ! $this->_is_conn() OR empty($path))
		{
			return FALSE;
		}

		$time = ftp_mdtm($this->conn_id, $path);

		if ($time == -1)
		{
			$this->_error('ftp_file_does_not_exist');
			return FALSE;
		}

		return $time;
	}

	// ------------------------------------------------------------------------

	/**
	 * Display error message
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	function _error($line)
	{
		$this->error = $line;

		if ( $this->show_error == TRUE)
		{
			$CI =& get_instance();
			$CI->lang->load('ftp');
			show_error($CI->lang->line($line));
		}
	}


}
// END FTP Class

/* End of file Ftp.php */
/* Location: ./system/libraries/Ftp.php */