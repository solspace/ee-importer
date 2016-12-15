<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - User Side
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.6
 * @filesource	importer/mod.importer.php
 */

require_once 'addon_builder/module_builder.php';

class Importer extends Module_builder_importer
{
	// --------------------------------------------------------------------

	/**
	 *	AJAX Action Request for FTP/SFTP Connection Testing
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function ajax_connnection_test()
	{
		// --------------------------------------------
		//  AJAX Request?  If not, we bail
		// --------------------------------------------

		if ($this->is_ajax_request() === FALSE)
		{
			exit('');
		}

		$this->load_session();
		ee()->load->library('stats');

		// --------------------------------------------
		//  Incoming Variables Validation
		// --------------------------------------------

		$required = array('ftp_host', 'ftp_username', 'ftp_password', 'ftp_port', 'ftp_path', 'data_source');

		foreach($required as $field)
		{
			if ( ee()->input->post($field) == FALSE OR ee()->input->post($field) == '')
			{
				$this->send_ajax_response(array('success' => FALSE,
												'heading' => lang('error_importer_ftp_test'),
												'message' => lang('invalid_or_missing_fields'),
												'content' => lang('invalid_or_missing_fields')));
			}
		}

		// --------------------------------------------
		//  Host is a Host?  Port is a Number?
		// --------------------------------------------

		if ( ! is_numeric(ee()->input->post('ftp_port')) OR
			 ! stristr(ee()->input->post('ftp_host'), '.') OR
			 ! in_array(ee()->input->post('data_source'), array('ftp', 'sftp')))
		{
			$this->send_ajax_response(array('success' => FALSE,
											'heading' => lang('error_importer_ftp_test'),
											'message' => lang('invalid_or_missing_fields'),
											'content' => lang('invalid_or_missing_fields')));
		}

		// --------------------------------------------
		//  Send to FTP or SFTP Library.  Output Errors, If Necessary
		// --------------------------------------------

		if ( ee()->input->post('data_source') == 'ftp')
		{
			ee()->load->library('solspace_ftp');

			$config['hostname'] 	= ee()->input->post('ftp_host');
			$config['username'] 	= ee()->input->post('ftp_username');
			$config['password'] 	= ee()->input->post('ftp_password');
			$config['port'] 		= ee()->input->post('ftp_port');
			$config['debug']		= TRUE;

			$connection = ee()->solspace_ftp->connect($config);

			if ($connection === FALSE)
			{
				$this->send_ajax_response(array('success' => FALSE,
												'heading' => lang('error_importer_ftp_test'),
												'message' => ee()->solspace_ftp->error,
												'content' => ee()->solspace_ftp->error));
			}

			$modified = ee()->solspace_ftp->last_modified(ee()->input->post('ftp_path'));

			ee()->solspace_ftp->close();

			if ($modified == FALSE)
			{
				$this->send_ajax_response(array('success' => FALSE,
												'heading' => lang('error_importer_ftp_test'),
												'message' => ee()->solspace_ftp->error,
												'content' => ee()->solspace_ftp->error));
			}
		}
		else
		{
			require_once PATH_THIRD.'importer/libraries/phpseclib/Net/SFTP.php';

			// define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

			$sftp = new Net_SFTP(ee()->input->post('ftp_host'));

			if ($sftp->login(ee()->input->post('ftp_username'),ee()->input->post('ftp_password')) == FALSE)
			{
				//print_r($sftp->getSFTPErrors());

				$this->send_ajax_response(array('success' => FALSE,
												'heading' => lang('error_importer_ftp_test'),
												'message' => lang('error_sftp_connection_failure'),
												'content' => lang('error_sftp_connection_failure')));
			}

			// I used sie to determine if a file existed.  In SFTP, it would be similar to pull stats
			// as the library uses that to get the size.  However, we use size in FTP, and I wanted
			// to be consistent in the code.

			$modified = $sftp->size(ee()->input->post('ftp_path'));

			$sftp->disconnect();

			if ($modified == FALSE)
			{
				$this->send_ajax_response(array('success' => FALSE,
												'heading' => lang('error_importer_ftp_test'),
												'message' => lang('error_sftp_file_failure'),
												'content' => lang('error_sftp_file_failure')));
			}
		}

		// --------------------------------------------
		//  Lack of Failure, Thus Success!
		// --------------------------------------------

		$this->send_ajax_response(array('success' => TRUE,
										'heading' => lang('success_importer_ftp_test'),
										'message' => lang('connection_test_successful_file_found'),
										'content' => lang('connection_test_successful_file_found')));



	}
	// END ajax_connection_test()

	// --------------------------------------------------------------------

	/**
	 *	Import Statistics for a Batch - Action Request
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function import_statistics()
	{
		if (REQ != 'ACTION' && REQ != 'CP') return;

		$this->load_session();
		ee()->load->library('stats');

		// --------------------------------------------
		//  Validate Hash and Find Import Data
		// --------------------------------------------

		if (ee()->input->get_post('batch_hash') === FALSE OR
			! is_string(ee()->input->get_post('batch_hash')) OR
			strlen(ee()->input->get_post('batch_hash')) != 13)
		{
			exit;
		}

		$query = ee()->db->select('details')->from('exp_importer_log')->where('batch_hash', ee()->input->get_post('batch_hash'))->get();

		if ($query->num_rows() == 0)
		{
			exit('');
		}

		// --------------------------------------------
		//  Return Debugging Information
		// --------------------------------------------

		exit(ee()->output->_display($this->actions()->statistics_output(unserialize(base64_decode($query->row('details'))))));
	}
	// END import_statistics()

	// --------------------------------------------------------------------

	/**
	 *	Batch Import - Action Request
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function batch_import($query = NULL)
	{
		if (REQ != 'ACTION' && REQ != 'CP') return;

		$this->load_session();
		ee()->load->library('stats');

		// --------------------------------------------
		//  Validate Hash and Find Profile ID
		// --------------------------------------------

		if ($query == NULL)
		{
			if (ee()->input->get_post('batch_hash') === FALSE OR
				! is_string(ee()->input->get_post('batch_hash')) OR
				strlen(ee()->input->get_post('batch_hash')) != 13 OR
				ee()->input->get_post('batch_number') === FALSE OR
				! is_numeric(ee()->input->get_post('batch_number')))
			{
				exit;
			}

			$query = ee()->db->from('exp_importer_batch_data')->where('batch_hash', ee()->input->get_post('batch_hash'));
			$query->where('batch_number', ee()->input->get_post('batch_number'));
			$query = $query->get();

			if ($query->num_rows() == 0)
			{
				if ($this->is_ajax_request())
				{
					$this->send_ajax_response(array('success' => FALSE,
													'heading' => lang('failure_of_import'),
													'message' => lang('importer_invalid_batch'),
													'content' => lang('importer_invalid_batch')));
				}
				else
				{
					exit(lang('importer_invalid_batch'));
				}
			}
		}

		// --------------------------------------------
		//  Extract Variables
		// --------------------------------------------

		$batch_hash		= $query->row('batch_hash');
		$batch_number	= $query->row('batch_number');
		$data_array		= unserialize(base64_decode($query->row('batch_data')));

		// --------------------------------------------
		//  Settings and Stats for Batch
		// --------------------------------------------

		$query = ee()->db->query("SELECT details FROM exp_importer_batches
								  WHERE batch_hash = '".ee()->db->escape_str($batch_hash)."'");

		$details = unserialize(base64_decode($query->row('details')));

		// --------------------------------------------
		//  Perform Import - $return contains an array of stats about import
		// --------------------------------------------

		$return = $this->actions()->import_batch($details['settings'], $data_array, $details['stats']);

		if ($return === FALSE)
		{
			if ($this->is_ajax_request())
			{
				$this->send_ajax_response(array('success' => FALSE,
												'heading' => lang('failure_of_import'),
												'message' => $this->actions()->error,
												'content' => $this->actions()->error));
			}
			else
			{
				exit(ee()->output->_display($this->actions()->statistics_output($this->actions()->error)));
			}
		}

		// --------------------------------------------
		//  Finished with this Batch!
		// --------------------------------------------

		ee()->db->query(ee()->db->update_string('exp_importer_batch_data',
												array('finished'	 => 'y'),
												array('batch_hash'	 => $batch_hash,
													  'batch_number' => $batch_number)));

		// --------------------------------------------
		//  All Batches Finished?
		// --------------------------------------------

		$query = ee()->db->query("SELECT COUNT(*) AS count FROM exp_importer_batch_data
								  WHERE batch_hash = '".ee()->db->escape_str($batch_hash)."'
								  AND finished = 'n'");

		$finished = ($query->row('count') == 0) ? TRUE : FALSE;

		// --------------------------------------------
		//  All Batches Finished?
		//	- If last batch, we complete import and remove batches
		//	- If not, we only update 'details'
		// --------------------------------------------

		if ($finished === TRUE)
		{
			$stats = $this->actions()->complete_import($details['settings'], $return, $batch_hash);

			ee()->db->query("DELETE FROM exp_importer_batches WHERE batch_hash = '".ee()->db->escape_str($batch_hash)."'");
			ee()->db->query("DELETE FROM exp_importer_batch_data WHERE batch_hash = '".ee()->db->escape_str($batch_hash)."'");
		}
		else
		{
			 ee()->db->query(ee()->db->update_string('exp_importer_batches',

							array('details'	 	=> base64_encode(serialize(array('stats'	=> array_merge($details['stats'], $return),
																				 'settings' => $details['settings'])))),
							array('batch_hash'	 => $batch_hash)));
		}

		// --------------------------------------------
		//  Lack of Failure, Thus Success!
		// --------------------------------------------

		if ($this->is_ajax_request())
		{
			$this->send_ajax_response(array('success' => TRUE,
											'heading' => lang('successful_import'),
											'message' => lang('import_was_successfully_completed'),
											'content' => lang('import_was_successfully_completed')));
		}

		// --------------------------------------------
		//  Return Debugging Information
		// --------------------------------------------

		$output	= '';
		$debug	= TRUE; // Will be a module preference

		if ($debug === TRUE)
		{
			$output = $this->actions()->statistics_output($return);
		}

		exit(ee()->output->_display($output));
	}
	// END batch_import()

	// --------------------------------------------------------------------

	/**
	 *	Cron Import - Action Request
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function cron_import()
	{
		if (REQ != 'ACTION') return;

		$this->load_session();
		ee()->load->library('stats');

		// --------------------------------------------
		//  Validate Hash and Find Profile ID
		// --------------------------------------------

		if (ee()->input->get_post('hash') === FALSE OR
			! is_string(ee()->input->get_post('hash')) OR
			strlen(ee()->input->get_post('hash')) != 32)
		{
			exit;
		}

		$query = ee()->db->select('profile_id')->from('exp_importer_profiles');
		$query = $query->where('hash', ee()->input->get_post('hash'))->get();

		if ($query->num_rows() == 0)
		{
			return FALSE;
		}

		$profile_id = $query->row('profile_id');

		// --------------------------------------------
		//  Batch Processing?
		//	- Find oldest batch_date and lowest batch number
		// --------------------------------------------

		if ($this->check_yes(ee()->input->get_post('batch')) === TRUE)
		{
			// Clear out batches older than a week
			ee()->db->query("DELETE FROM exp_importer_batches WHERE batch_date < ".(time() - 7 * 24 * 60 * 60));
			ee()->db->query("DELETE FROM exp_importer_batch_data WHERE batch_date < ".(time() - 7 * 24 * 60 * 60));

			$query = ee()->db->query("SELECT ibd.*
									  FROM exp_importer_batches AS ib, exp_importer_batch_data AS ibd
									  WHERE ib.profile_id = ".ceil($profile_id)."
									  AND ib.batch_hash = ibd.batch_hash
									  AND ib.finished = 'n' AND ibd.finished = 'n'
									  ORDER BY ib.batch_date ASC, ibd.batch_number ASC
									  LIMIT 1");

			if ($query->num_rows() == 0)
			{
				exit(ee()->output->_display(lang('no_batches_to_process')));
			}

			return $this->batch_import($query);
		}

		// --------------------------------------------
		//  Perform Import - $return is either TRUE/FALSE/batch hash
		//	- If FALSE, there was an error
		//	- If TRUE, data was imported and no need for batch processing
		//	- If (string), batch processing
		// --------------------------------------------

		$return = $this->actions()->start_import($profile_id, 'cron');

		if ($return === FALSE)
		{
			if ($this->is_ajax_request())
			{
				$this->send_ajax_response(array('success' => TRUE,
												'heading' => lang('failure_of_import'),
												'message' => $this->actions()->error,
												'content' => $this->actions()->error));
			}
			else
			{
				exit(ee()->output->_display($this->actions()->statistics_output($this->actions()->error)));
			}
		}

		// --------------------------------------------
		//  Batch Importing?  We're All Done Here!
		// --------------------------------------------

		if ($this->actions()->batch_processing === TRUE)
		{
			if ($this->is_ajax_request())
			{
				$this->send_ajax_response(array('success' => TRUE,
												'heading' => lang('successful_import'),
												'message' => lang('batch_import_started'),
												'content' => lang('batch_import_started')));
			}
			else
			{
				exit(ee()->output->_display(lang('batch_import_started')));
			}
		}

		// --------------------------------------------
		//  Lack of Failure, Thus Success!
		// --------------------------------------------

		if ($this->is_ajax_request())
		{
			$this->send_ajax_response(array('success' => TRUE,
											'heading' => lang('successful_import'),
											'message' => lang('import_was_successfully_completed'),
											'content' => lang('import_was_successfully_completed')));
		}

		// --------------------------------------------
		//  Return Debugging Information
		// --------------------------------------------

		$output	= '';
		$debug	= TRUE; // Will be a module preference

		if ($debug === TRUE)
		{
			$output = $this->actions()->statistics_output($return);
		}

		exit(ee()->output->_display($output));
	}
	// END cron_import()

}
// END Importer Class