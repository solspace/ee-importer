<?php

/**
 * Importer - Dutch Language
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2014, Solspace, Inc.
 * @link		http://solspace.com/docs/importer
 * @license		http://www.solspace.com/license_agreement
 * @version		2.1.0
 * @filesource	importer/language/dutch/lang.importer.php
 *
 * Translated to Dutch by N/A
 */

$lang = $L = array(

/**	----------------------------------------
/**	Required for modules page
/**	----------------------------------------*/

'importer_module_name'						=>
'Importer',

'importer_module_description'				=>
'Importeer berichten uit CSV-, JSON-, en XML-bestanden.',

'importer'									=>
'Importer',

'importer_module_version' =>
"Version",

// --------------------------------------------
//  CP Main Menu
// --------------------------------------------

'importer_preferences' =>
"Voorkeuren",

'importer_homepage' =>
"Startpagina",

'online_documentation' =>
"Online documentatie",

'importer_yes' =>
"Ja",

'importer_no' =>
"Nee",

// --------------------------------------------
//  Homepage/Imports
// --------------------------------------------

'create_new_importer' =>
"Maak nieuw profiel",

'no_importer_profiles' =>
"Geen vorig profiel",

'saved_imports' =>
"Bewaarde imports",

'importer_name' =>
"Naam",

'importer_edit' =>
"Wijzig",

'importer_datatype' =>
"Datatype",

'importer_cron_urls' =>
"Cron URLs",

'importer_cron' =>
"Cron",

'importer_cron_batch' =>
"Bulkverwerking",

'importer_batch_processing_explaination' =>
"Importer heeft een ingebouwde limiet van 100 berichten per keer om het vastlopen van de server te voorkomen. Indien het aantal berichten dat je importeert meer is dan 100, wordt er automatisch begonnen met bulkverwerking. Om bulkverwerking te gebruiken met cron, moet je een tweede cron instellen om de partijen te verwerken.",

'right_click_to_copy' =>
"Klik rechts om te kopiëren",

'importer_delete' =>
"Verwijder",

'importer_run_profile' =>
"Voer uit",

'importer_run_now' =>
"Nu uitvoeren",

'importer_profile_name' =>
"Profielnaam",

'importer_instructions' =>
"Instructies",

'importer_instructions_subtext' =>
"Alle details of instructies die je misschien bij de hand wil hebben als je importeert met dit profiel.",

'importer_profile_deleted' =>
"Profiel verwijderd",

'importer_profiles_deleted' =>
"%i% profielen verwijderd",

"profile_delete_question" =>
"Ben je zeker dat je %i% %profiles% wil verwijderen?",

"action_can_not_be_undone" =>
"Je kan deze actie niet ongedaan maken.",

'importer_profile_delete_confirm' =>
"Bevestiging profielen verwijderen",

'manual_upload_no_cron' =>
"Handmatige upload, geen cron beschikbaar",

'manual_import_form' =>
"Handmatige uploadormulier",

'importer_continue' =>
"Volgende",

'save_and_continue' =>
"Opslaan en volgende",

'save_and_finish' =>
"Opslaan en klaar",

'importer_profile' =>
"Profiel",

'importer_edit_profile' =>
"Profiel aanpassen",

'importer_new_profile' =>
"Nieuw profiel",

'importer_profile_updated' =>
"Profiel bijgewerkt",

'invalid_importer_profile_name' =>
"Ongeldige profielnaam opgegeven",

'error_duplicate_profile_name' =>
"Fout: bestaande profielnaam opgegeven",

'success_importer_profile_name_updated' =>
"Naam en instructies van het profiel zijn gewijzigd",

'success_importer_settings_updated' =>
"Instellingen van het profiel zijn gewijzigd",

'invalid_importer_profile_id' =>
"Ongeldig profiel ID",

'invalid_importer_profile_datatype' =>
"Ongeldig profieldatatype opgegeven",

'invalid_datatype_given' =>
"Ongeldig datatype opgegeven",

'invalid_datatype' =>
"Ongeldig datatype",

'importer_channel' =>
"Importeerkanaal",

'importer_channel_subtext' =>
"Het kanaal waarin deze data zal worden geïmporteerd.",

'choose_channel' =>
"Kies een kanaal",

'importer_profile_source' =>
"Porfielbron",

'import_source' =>
"Importeerbron",

'choose_data_source' =>
"Kies databron",

'importer_data_source' =>
"Databron",

'importer_data_source_subtext' =>
"De data voor Importer kunnen ingevoerd worden op de opgegeven manieren. Kies er een en vul de benodigde details in. Er zal geprobeerd worden het bestand op te halen als je op Verzenden klikt.",

'importer_data_source_filename' =>
"Lokaal bestand",

'importer_data_source_url' =>
"URL",

'importer_data_source_ftp' =>
"Bestand via FTP",

'importer_data_source_sftp' =>
"Bestand via SFTP",

'choose_data_source' =>
"Kies databron",

'importer_local_filename' =>
"Lokale bestandsnaam",

'importer_local_filename_subtext' =>
"Het absolute pad van het bestand op je server.",

'importer_remote_url' =>
"URL",

'importer_remote_url_subtext' =>
"De URL van het bestand op de website. http:// en https:// worden allebei ondersteund.",

'importer_http_auth_username' =>
"HTTP Auth Username",

'importer_http_auth_username_subtext' =>
"Het bestand is misschien beveiligd met HTTP Authentication. Opmerking: om veiligheidsredenen worden gebruikersnaam en wachtwoord eerst versleuteld voor ze worden opgeslagen in de ExpressionEngine database.",

'importer_http_auth_password' =>
"HTTP Auth wachtwoord",

'importer_ftp_host' =>
"Host",

'importer_ftp_username' =>
"Gebruikersnaam",

'importer_ftp_password' =>
"Wachtwoord",

'importer_ftp_password_subtext' =>
"Opmerking: om veiligheidsredenen worden gebruikersnaam en wachtwoord eerst versleuteld voor ze worden opgeslagen in de ExpressionEngine database.",

'importer_ftp_port' =>
"Poort",

'importer_ftp_path' =>
"Serverpad",

'importer_manual_upload' =>
"Handmatige upload",

'importer_data_source_manual_upload' =>
"Bestand handnatig uploaden",

'importer_manual_upload' =>
"Bestand handmatig uploaden",

'importer_manual_upload_subtext' =>
"Deze optie betekent dat je handmatig een bestand moet uploaden, telkens je dit profiel wil gebruiken.",

'importer_ftp_test' =>
"Test FTP/SFTP-verbinding",

'ftp_perform_connection_test' =>
"Voer verbindingstest uit",

'importer_ftp_test_subtext' =>
"Een verbindingstest zal (met AJAX) uitgevoerd worden om te controleren of je instellingen correct zijn. Er wordt gezocht naar het bestand, maar het bestand zal niet gedownload worden.",

'import_location' =>
"Importeerlocatie",

// --------------------------------------------
//  Source Form Errors
// --------------------------------------------

'invalid_channel_submitted' =>
"Ongeldig kanaal opgegeven",

'invalid_channel_permissions' =>
"Je hebt geen toelating om in het opgegeven kanaal te posten, en kan er dus ook niet in importeren.",

'invalid_data_source_submitted' =>
"Ongeldige databron opgegeven. Controleer of je een databron hebt opgegeven en alle verplichte velden hebt ingevuld.",

'invalid_filename_fullpath' =>
"Ongeldig pad opgegeven. Controleer dat je een absoluut serverpad hebt opgegeven.",

'invalid_filename_not_found' =>
"Ongeldige bestandsnaam opgegeven. Het bestand werd niet gevonden op de server.",

'invalid_remote_url_not_found' =>
"Ongeldige URL opgegeven. Het bestand werd niet gevonden.",

'problem_retreiving_file_data' =>
"Probleem met het ontvangen van de bestandsdata.",

'source_data_contained_invalid_data' =>
"Het bestand bevatte geen data of ongeldige data.",

'failure_downloading_remote_file' =>
"Fout bij het downloaden van het bestand van de server.",

'unable_to_create_unzipping_directory' =>
"Er kan geen nieuwe map gemaakt worden in de ExpressionEngine cachemap om het bestand te unzippen. Controleer de schrijfbevoegdheden.",

'unable_to_create_importer_directory' =>
"Er kan geen nieuwe map gemaakt worden in de ExpressionEngine cachemap om importeerdata op te slaan. Controleer de schrijfbevoegdheden.",

'error_unable_to_read_data_file' =>
"Fout: kan bestand niet lezen.",

'importer_memory_usage_warning' =>
"Importer heeft vastgesteld dat het bestand te groot is voor je server om te verwerken.",

// --------------------------------------------
//  Settings Form - GLOBAL 
// --------------------------------------------

'invalid_data_received' =>
"De data van je bronbestand was niet geldig en onleesbaar.",

'invald_content_type_settings' =>
"Ongeldige inhoudstypeinstellingen",

'importer_notifications' =>
"Berichten",

'importer_notification_emails' =>
"E-mails",

'importer_notification_emails_subtext' =>
"Scheid meerdere met komma's of nieuwe lijnen.",

'importer_notification_cc' =>
"CC",

'importer_notification_cc_subtext' =>
"Scheid meerdere met komma's of nieuwe lijnen.",

'importer_notification_subject' =>
"Onderwerp",

'importer_notification_message' =>
"Bericht",

'importer_notification_message_subtext' =>
"Volgende variabelen kunnen gebruikt worden:<br />
{author_ids}, {channel_id}, {site_id},<br />
{profile_name}, {content_type}, {datatype},<br />
{emails}, {email_cc},<br />
{import_date}, {import_ip_address}, {import_location},<br />
{last_import_date}, {run_time},<br />
{start_or_end},<br />
{total_inserted}, {total_updated}, {entries_deleted}",

'importer_notification_rules' =>
"Regels",

'importer_notification_rules_disabled' =>
"Geen e-mail sturen",

'importer_notification_rules_start' =>
"Stuur e-mail bij begin van importeren",

'importer_notification_rules_end' =>
"Stuur e-mail bij einde van importeren",

'importer_notification_rules_start_end' =>
"Stuur e-mail bij begin en einde van importeren",

'importer_element' =>
"Element",

'importer_default_value' =>
"Standaardwaarde",

'importer_modal_save' =>
"Opslaan",

'save_and_do_import' =>
"Opslaan en importeren",

// --------------------------------------------
//  Import Log
// --------------------------------------------

'import_log' =>
"Importeerlog",

'import_date' =>
"Datum",

'import_details' =>
"Details",

'no_imports_logged' =>
"Geen imports gelogd",

'importer_delete_logs' =>
"Verwijder logs",

"logs_delete_question" =>
"Ben je zeker dat je %i% logs wil verwijderen?",

"log_delete_question" =>
"Ben je zeker dat je dit log wil verwijderen?",

'importer_logs_delete_confirm' =>
"Bevestiging logs verwijderen",

'importer_log_deleted' =>
"Logs verwijderd",

'importer_logs_deleted' =>
"%i% logs verwijderd",

// --------------------------------------------
//  AJAX Connection Test
// --------------------------------------------

'error_ajax_request' =>
"Fout: AJAX Request is mislukt",

'error_importer_ftp_test' =>
"Fout: FTP/SFTP-verbindingstest is mislukt",

'invalid_or_missing_fields' =>
"Ongeldige of niet ingevulde velden. Controleer of alle velden zijn ingevuld.",

'ftp_file_does_not_exist' =>
"FTP: bestand bestaat niet",

'ftp_ssl_not_supported' =>
"SSL-verbindingen worden niet ondersteund op je server.",

'ftp_unable_to_connect' =>
"Kan geen verbinding maken met de FTP-server.",

'ftp_unable_to_login' =>
"Ken niet inloggen op de FTP-server met de opgegeven inloggegevens.",

'ftp_bad_local_path' =>
"Geen geldig lokaal pad opgegeven om de ftp-bestanden te downloaden.",

'ftp_local_path_not_writable' =>
"Het lokale pad om de FTP-bestanden naar te downloaden is niet beschrijfbaar",

'ftp_bad_local_path' =>
"Het lokale pad wordt niet aanvaard oor de FTP-bibliotheek.",

'ftp_bad_remote_path' =>
"Het opgegeven serverpad is niet geldig.",

'ftp_bad_remote_file' =>
"Het opgegeven bestandspad is niet geldig.",

'ftp_unable_to_download' =>
"Kan het bestand niet downloaden van je FTP-server.",

'ftp_file_does_not_exist' =>
"Het bestand lijkt niet te bestaan op je FTP-server.",

'error_sftp_connection_failure' =>
"SFTP-server verbindingsfout",

'error_sftp_file_failure' =>
"Kan het bestand niet vinden op de server.",

'success_importer_ftp_test' =>
"Verbinsingstest geslaagd!",

'connection_test_successful_file_found' =>
"Verbinsingstest is geslaagd en het bestand is gevonden op de server!",

'modal_close_button' =>
"Sluiten",

'beginning_connection_test' =>
"Verbindingstest starten",

'connection_test_underway_please_standby' =>
"Je FTP/SFTP verbindingstest is nu bezig. Wees gedudldig, dit kan 15 seconden duren.",

'modal_press_esc_to_close' =>
"Druk op ESC om het venster te sluiten.",

'modal_press_esc_to_close_and_discard' =>
"Druk op ESC om het venster te sluiten en de wijzigingen niet op te slaan.",

'additional_data_type_fields' =>
"Extra datatypevelden",

// --------------------------------------------
//  Setting Submission Errors - GLOBAL
// --------------------------------------------

'error_invalid_notification_emails' =>
"Fout: ongeldige e-mailadressen opgegeven",

'error_invalid_notification_cc' =>
"Fout: ongeldige cc-e-mailadressen opgegeven",

'error_invalid_notification_subject_message_required' =>
"Fout: een onderwerp en bericht zijn verplicht voor het versturen van e-mails.",

// --------------------------------------------
//  Cron Import
// --------------------------------------------

'successful_import' =>
"Geslaagde import",

'import_was_successfully_completed' =>
"Het importeren is goed afgerond zonder fouten.",

'failure_of_import' =>
"Importeerfout",

'importer_invalid_batch' =>
"Ongeldige partij gevraagd",

'batch_import_started' =>
"Bulkimporteren goed begonnen",

'no_batches_to_process' =>
"Geen partijen om te verwerken",

// --------------------------------------------
//  Statistics
// --------------------------------------------

'entries_inserted' =>
"Berichten ingevoerd",

'entries_updated' =>
"Berichten gewijzigd",

'entries_deleted' =>
"Berichten verwijderd",

'deleted_entries' =>
"Berichten verwijderd",

'total_entries' =>
"Totaal berichten",

'entry_ids' =>
"Bericht IDs",

'inserted_entry_ids' =>
"Ingevoerde bericht IDs",

'updated_entry_ids' =>
"Gewijzigde bericht IDs",

'author_ids' =>
"Auteur IDs",

'debugging' =>
"Foutopsporing",

'start_time' =>
"Starttijd",

'end_time' =>
"Eindtijd",

'run_time' =>
"Looptijd",

'site_id' =>
"Site ID",

'number_of_queries' =>
"Aantal queries",

// -------------------------------------
//	Batch Import
// -------------------------------------

'batch_importer' =>
"Bulk importeren",

'importer_batch_purpose' =>
"Dit importeert in delen en zal je informeren als de import klaar is. Klik op Importeren om te beginnen.",

'importer_perform_import' =>
"Importeren",

"performing_import_for_batch_" =>
"Importeren voor bulk: ",

'importer_percent_completed' =>
"percent voltooid",

'importer_number_of_batches_imported' =>
"Aantal delen geïmporteerd",

'importer_pause' =>
"Pauze",

'importer_resume' =>
"Hervatten",

'return_to_importer_homepage' =>
"Je kan nu terugkeren naar de Importer startpagina.",

'importer_import_complete' =>
"Import voltooid",

'importer_invalid_values' =>
"Ongeldige waardes ontvangen",

'importer_batch_number_' =>
"Deel nr",

//----------------------------------------
//  Errors
//----------------------------------------

'invalid_request' =>
"Ongeldige vraag",

'importer_module_disabled' =>
"De Importmodule is momenteel uitgeschakeld. Controleer of de module is geïnstalleerd en up-to-date is in module controlepaneel in het ExpressionEngine controlepaneel.",

'disable_module_to_disable_extension' =>
"m deze extensie uit te schakelen, moet je de bijbehorende <a href='%url%'>module</a> uitschakelen.",

'enable_module_to_enable_extension' =>
"Om deze extensie in te schakelen, moet je de bijbehorende <a href='%url%'>module</a> inschakelen.",

'cp_jquery_requred' =>
"De 'jQuery for the Control Panel' extensie moet <a href='%extensions_url%'>ingeschakeld</a> zijn om deze module te kunnn gebruiken.",

//----------------------------------------
//  Update routine
//----------------------------------------

'update_importer_module' =>
"Importermodule updaten",

'importer_update_message' =>
"Je hebt een nieuwe versie van Importer geüpload. Klik hier om het updatescript uit te voeren.",

"update_successful" =>
"De module is geüpdatet.",

"update_failure" =>
"Er is iets misgelopen bij het updaten naar de meest recente versie.",

'required_field_was_empty' =>
"Er is een verplicht veld niet ingevuld. Ge terug en vul alle velden in.",


// END
''=>''
);
?>