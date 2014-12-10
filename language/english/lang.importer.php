<?php

/**
 * Importer - Language
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2014, Solspace, Inc.
 * @link		http://solspace.com/docs/importer
 * @license		http://www.solspace.com/license_agreement
 * @version		2.2.5
 * @filesource	importer/language/english/lang.importer.php
 */

$lang = array(

//	----------------------------------------
//	Required for modules page
//	----------------------------------------

'importer_module_name'						=>
'Importer',

'importer_module_description'				=>
'Import multiple kinds of data from local or remote data sources into ExpressionEngine content.',

'importer'									=>
'Importer',

'importer_module_version' =>
"Version",

// --------------------------------------------
//  CP Main Menu
// --------------------------------------------

'importer_homepage' =>
"Profiles",

'online_documentation' =>
"Online Documentation",

'importer_yes' =>
"Yes",

'importer_no' =>
"No",

'clear_batches' =>
'Clear Batches',

// --------------------------------------------
//  Homepage/Imports
// --------------------------------------------

'create_new_importer' =>
"Create New Import Profile",

'no_importer_profiles' =>
"No previous importer profiles",

'saved_imports' =>
"Saved Imports",

'importer_name' =>
"Name",

'importer_edit' =>
"Edit",

'importer_datatype' =>
"DataType",

'importer_content_type' =>
"Content Type",

'importer_cron_urls' =>
"Cron URLs",

'importer_cron' =>
"Cron",

'from' =>
"From",

'to' =>
"to",

'importer_cron_batch' =>
"Batch Processing",

'importer_batch_processing_explaination' =>
"Importer has a built in limit of 100 items imported at a time to prevent exceeding a server's resources.
If the number of items to be imported exceeds this amount, batch processing is triggered automatically.
To use batch processing with a cron, you need to set up a second cron to process the batches.",

'right_click_to_copy' =>
"right click to copy",

'importer_delete' =>
"Delete",

'importer_run_profile' =>
"Run",

'importer_run_now' =>
"Run Now",

'importer_profile_name' =>
"Profile Name",

'importer_instructions' =>
"Instructions",

'importer_instructions_subtext' =>
"Any details or instructions you may want to keep handy when doing imports done with this profile.",

'importer_profile_deleted' =>
"Importer Profile Deleted",

'importer_profiles_deleted' =>
"%i% Importer Profiles Deleted",

"profile_delete_question" =>
"Are you sure you want to delete %i% %profiles%?",

"action_can_not_be_undone" =>
"This action cannot be undone.",

'importer_profile_delete_confirm' =>
"Delete Importer Profile Confirmation",

'manual_upload_no_cron' =>
"Manual Upload, No Cron Available",

'manual_import_form' =>
"Manual Import Form",

'importer_continue' =>
"Continue",

'save_and_continue' =>
"Save and Continue",

'save_and_finish' =>
"Save and Finish",

'importer_profile' =>
"Importer Profile",

'importer_edit_profile' =>
"Edit Importer Profile",

'importer_new_profile' =>
"New Importer Profile",

'importer_profile_updated' =>
"Importer Profile Updated",

'invalid_importer_profile_name' =>
"Invalid Importer Profile Name submitted",

'error_duplicate_profile_name' =>
"Error: A Duplicate Importer Profile name was submitted",

'success_importer_profile_name_updated' =>
"Importer Profile's name and instructions were updated successfully",

'success_importer_settings_updated' =>
"Importer Profile's settings were updated successfully",

'invalid_importer_profile_id' =>
"Invalid Importer Profile ID",

'invalid_importer_profile_datatype' =>
"Invalid Importer Profile Data Type Submitted",

'invalid_importer_profile_content_type' =>
"Invalid Importer Profile Content Type Submitted",

'unable_to_successfully_save_settings' =>
"Unable to Successfully Save Settings",

'invalid_datatype_given' =>
"Invalid Datatype Given",

'invalid_content_type_given' =>
"Invalid Content Type Given",

'invalid_datatype' =>
"Invalid Data Type",

'importer_channel' =>
"Import Channel",

'importer_channel_subtext' =>
"The Channel to which this data source will be imported.",

'choose_channel' =>
"Choose a Channel",

'importer_profile_source' =>
"Importer Profile Source",

'import_source' =>
"Import Source",

'choose_data_source' =>
"Choose Data Source",

'importer_data_source' =>
"Data Source",

'importer_data_source_subtext' =>
"The data for Importer can be retrieved by the listed methods. Choose one and fill out the required details. An attempt
will be made to retrieve the data when you click submit.",

'importer_data_source_filename' =>
"Local File",

'importer_data_source_url' =>
"Remote URL",

'importer_data_source_ftp' =>
"File via FTP",

'importer_data_source_sftp' =>
"File via SFTP",

'choose_data_source' =>
"Choose Data Source",

'importer_local_filename' =>
"Local Filename",

'importer_local_filename_subtext' =>
"The absolute file path for the file located on your server.",

'importer_remote_url' =>
"Remote URL",

'importer_remote_url_subtext' =>
"The full website URL for the file.  Both http:// and https:// are supported.",

'importer_http_auth_username' =>
"HTTP Auth Username",

'importer_http_auth_username_subtext' =>
"The remote file may be protected by HTTP Authentication. Note: Username and Password will be encrypted prior to being stored in the ExpressionEngine database for security reasons.",

'importer_http_auth_password' =>
"HTTP Auth Password",

'importer_ftp_host' =>
"Host",

'importer_ftp_username' =>
"Username",

'importer_ftp_password' =>
"Password",

'importer_ftp_password_subtext' =>
"Note: Username and Password will be encrypted prior to being stored in the ExpressionEngine database for security reasons.",

'importer_ftp_port' =>
"Port",

'importer_ftp_path' =>
"Path on Remote Server",

'importer_manual_upload' =>
"Manual Upload",

'importer_data_source_manual_upload' =>
"Manually Upload File",

'importer_manual_upload' =>
"Manual Upload File",

'importer_manual_upload_subtext' =>
"This option means that every time you use this Importer Profile, you will need to manually upload
a file from your local computer.",

'importer_ftp_test' =>
"Test FTP/SFTP Connection",

'ftp_perform_connection_test' =>
"Perform Connection Test",

'importer_ftp_test_subtext' =>
"Using AJAX, a connection test will be performed and the file will be detected but not downloaded to insure your settings work.",

'import_location' =>
"Import Location",

// --------------------------------------------
//  Source Form Errors
// --------------------------------------------

'invalid_channel_submitted' =>
"Invalid Channel Submitted",

'invalid_channel_permissions' =>
"You do not have permission to post to the submitted Channel and cannot Import into it.",

'invalid_data_source_submitted' =>
"Invalid Data Source submitted.  Please insure you selected a data source and filled out the required fields.",

'invalid_filename_fullpath' =>
"Invalid filename path submitted. Please insure that it is an absolute server path.",

'invalid_filename_not_found' =>
"Invalid filename path submitted.  The file was not found on the server.",

'invalid_remote_url_not_found' =>
"Invalid Remote URL submitted.  An attempt to retrieve the file resulted in failure.",

'problem_retreiving_file_data' =>
"Problem retrieving the file data.",

'problem_retreiving_data' =>
"Problem retrieving the data.",

'problem_retreiving_batch_data' =>
"Problem retrieving batch data.",

'datatype_is_missing_batch_processing_method' =>
'Datatype is missing batch processing method.',

'source_data_contained_invalid_data' =>
"The source data file contained either no data or invalid data.",

'failure_downloading_remote_file' =>
"Failure downloading remote file from the server.",

'unable_to_create_unzipping_directory' =>
"Unable to create a directory for unzipping your file in the ExpressionEngine cache directory, please check your folder's writing permissions.",

'unable_to_create_importer_directory' =>
"Unable to create a directory for storing Importer data files in the ExpressionEngine cache directory, please check your folder's writing permissions.",

'error_unable_to_read_data_file' =>
"Error: Unable to read data file.",

'importer_memory_usage_warning' =>
"Importer has determined that the data source is too large for your server to process.",

'unable_to_load_data_source' =>
"Unable to Load Data Source",

'unable_to_retrieve_source_data' =>
"Unable to Retrieve Source Data",

// --------------------------------------------
//  Settings Form - GLOBAL
// --------------------------------------------

'invalid_data_received' =>
"The data received from your data source was not valid and was unable to be read.",

'invald_content_type_settings' =>
"Invalid Content Type Settings",

'importer_notifications' =>
"Notifications",

'importer_notification_emails' =>
"Emails",

'importer_notification_emails_subtext' =>
"Separate multiples with commas or linebreaks.",

'importer_notification_cc' =>
"CC",

'importer_notification_cc_subtext' =>
"Separate multiples with commas or linebreaks.",

'importer_notification_subject' =>
"Subject",

'importer_notification_message' =>
"Message",

'importer_notification_message_subtext' =>
"The following variables are available:<br />
{author_ids}, {channel_id}, {site_id},<br />
{profile_name}, {content_type}, {datatype},<br />
{emails}, {email_cc},<br />
{import_date}, {import_ip_address}, {import_location},<br />
{last_import_date}, {run_time},<br />
{start_or_end},<br />
{total_inserted}, {total_updated}, {entries_deleted}",

'importer_notification_rules' =>
"Rules",

'importer_notification_rules_disabled' =>
"Send no email",

'importer_notification_rules_start' =>
"Send email at the start of import",

'importer_notification_rules_end' =>
"Send email at the end of import",

'importer_notification_rules_start_end' =>
"Send email at the start and end of import",

'importer_element' =>
"Element",

'importer_default_value' =>
"Default Value",

'importer_modal_save' =>
"Save",

'save_and_do_import' =>
"Save and Do Import",

// --------------------------------------------
//  Import Log
// --------------------------------------------

'import_log' =>
"Import Log",

'import_date' =>
"Date",

'import_details' =>
"Details",

'no_imports_logged' =>
"No Imports Logged",

'importer_delete_logs' =>
"Delete Logs",

"logs_delete_question" =>
"Are you sure you want to delete %i% logs?",

"log_delete_question" =>
"Are you sure you want to delete this log?",

'importer_logs_delete_confirm' =>
"Delete Importer Logs Confirmation",

'importer_log_deleted' =>
"Importer Log Deleted",

'importer_logs_deleted' =>
"%i% Importer Logs Deleted",

// --------------------------------------------
//  AJAX Connection Test
// --------------------------------------------

'error_ajax_request' =>
"Error: AJAX Request was not successful",

'error_importer_ftp_test' =>
"Error: FTP/SFTP Connection Test Failed",

'invalid_or_missing_fields' =>
"Invalid or Missing Fields. Please insure that all fields were filled out.",

'ftp_file_does_not_exist' =>
"FTP: File Does Not Exist",

'ftp_ssl_not_supported' =>
"SSL Connections are Not Supported on Your Server.",

'ftp_unable_to_connect' =>
"Unable to create a connection to the FTP Server.",

'ftp_unable_to_login' =>
"Unable to log into your FTP Server with the credentials submitted.",

'ftp_bad_local_path' =>
"Bad Local Path provided for downloading the FTP file(s).",

'ftp_local_path_not_writable' =>
"The Local Path provided for downloading the FTP file(s) is not writable.",

'ftp_bad_local_path' =>
"The Local Path provided is not being accepted by the FTP Library.",

'ftp_bad_remote_path' =>
"The Remote Path provided is not valid.",

'ftp_bad_remote_file' =>
"The Remote File Path provided is not valid.",

'ftp_unable_to_download' =>
"Unable to download the file on your FTP Server.",

'ftp_file_does_not_exist' =>
"The file on the FTP Server does not seem to exist.",

'error_sftp_connection_failure' =>
"SFTP Server Connection Failure",

'error_sftp_file_failure' =>
"Unable to locate file on server.",

'success_importer_ftp_test' =>
"Successful Connection Test!",

'connection_test_successful_file_found' =>
"Connection test was successful and the file was found on the server.",

'modal_close_button' =>
"Close",

'beginning_connection_test' =>
"Beginning Connection Test",

'connection_test_underway_please_standby' =>
"Your FTP/SFTP Connection Test is now underway.  Please be patient, this make take up to 15 seconds to confirm.",

'modal_press_esc_to_close' =>
"Press the ESC key to close window.",

'modal_press_esc_to_close_and_discard' =>
"Press the ESC key to close window and discard changes.",

'additional_data_type_fields' =>
"Additional Data Type Fields",

// --------------------------------------------
//  Setting Submission Errors - GLOBAL
// --------------------------------------------

'error_invalid_notification_emails' =>
"Error: Invalid Notication Emails submitted",

'error_invalid_notification_cc' =>
"Error: Invalid Notication CC Emails submitted",

'error_invalid_notification_subject_message_required' =>
"Error:  If sending a notification, a subject and message are required.",

// --------------------------------------------
//  Cron Import
// --------------------------------------------

'successful_import' =>
"Successful Import",

'import_was_successfully_completed' =>
"The Import was completed successfully and without error.",

'failure_of_import' =>
"Failure of Import",

'importer_invalid_batch' =>
"Invalid Batch Requested",

'batch_import_started' =>
"Batch Importing Process Successfully Started",

'no_batches_to_process' =>
"No Batches to Process",

'batch_data_successfully_emptied' =>
"All Batches have been successfully emptied from the database.",

// --------------------------------------------
//  Statistics
// --------------------------------------------

'entries_inserted' =>
"Entries Inserted",

'entries_updated' =>
"Entries Updated",

'entries_deleted' =>
"Entries Deleted",

'deleted_entries' =>
"Entries Deleted",

'total_entries' =>
"Total Entries",

'entry_ids' =>
"Entry IDs",

'inserted_entry_ids' =>
"Inserted Entry IDs",

'updated_entry_ids' =>
"Updated Entry IDs",

'author_ids' =>
"Author IDs",

'debugging' =>
"Debugging",

'start_time' =>
"Start Time",

'end_time' =>
"End Time",

'run_time' =>
"Run Time",

'site_id' =>
"Site ID",

'number_of_queries' =>
"Number of Queries",

'categories_created' =>
"Categories Created",

// -------------------------------------
//	Batch Import
// -------------------------------------

'batch_importer' =>
"Batch Importer",

'importer_batch_purpose' =>
"This will do the import in batches and will notify you when the import is complete.
Click 'Perform Import' to begin.",

'importer_perform_import' =>
"Perform Import",

"performing_import_for_batch_" =>
"Performing import for batch: ",

'importer_percent_completed' =>
"Percent Completed",

'importer_number_of_batches_imported' =>
"Number of Batches Imported",

'importer_pause' =>
"Pause",

'importer_resume' =>
"Resume",

'return_to_importer_homepage' =>
"You may now return to the Importer Homepage.",

'importer_import_complete' =>
"Import Complete",

'importer_invalid_values' =>
"Invalid Values Received",

'importer_batch_number_' =>
"Batch #",

//----------------------------------------
//  Errors
//----------------------------------------

'invalid_request' =>
"Invalid Request",

'importer_module_disabled' =>
"The Importer module is currently disabled.  Please insure it is installed and up to date by going
to the module's control panel in the ExpressionEngine Control Panel",

'disable_module_to_disable_extension' =>
"To disable this extension, you must disable its corresponding <a href='%url%'>module</a>.",

'enable_module_to_enable_extension' =>
"To enable this extension, you must install its corresponding <a href='%url%'>module</a>.",

'cp_jquery_requred' =>
"The 'jQuery for the Control Panel' extension must be <a href='%extensions_url%'>enabled</a> to use this module.",

//----------------------------------------
//  Update routine
//----------------------------------------

'update_importer_module' =>
"Update Importer Module",

'importer_update_message' =>
"You have recently uploaded a new version of Importer, please click here to run the update script.",

"update_successful" =>
"The module was successfully updated.",

"update_failure" =>
"There was an error while trying to update your module to the latest version.",

'required_field_was_empty' =>
"A required field was left empty, please go back and fill out all fields.",


// END
''=>''
);