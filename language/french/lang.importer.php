<?php

 /**
 * Importer - Language
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2013, Solspace, Inc.
 * @link		http://solspace.com/docs/importer
 * @version		2.2.0
 * @filesource 	importer/language/french/lang.importer.php
 */

$lang = $L = array(

/**	----------------------------------------
/**	Required for modules page
/**	----------------------------------------*/

'importer_module_name'						=>
'Importer / Importateur',

'importer_module_description'				=>
'Importer des entrées à partir de sources CSV, JSON et XML.',

'importer'									=>
'Importer / Importateur',

'importer_module_version' =>
"Version",

// --------------------------------------------
//  CP Main Menu
// --------------------------------------------

'importer_preferences' =>
"Préférences",

'importer_homepage' =>
"Page d'accueil",

'online_documentation' =>
"Documentation en ligne",

'importer_yes' =>
"Oui",

'importer_no' =>
"Non",

// --------------------------------------------
//  Homepage/Imports
// --------------------------------------------

'create_new_importer' =>
"Créer un nouveau profil d'importation",

'no_importer_profiles' =>
"Aucun profil d'importation existant",

'saved_imports' =>
"Importations enregistrées",

'importer_name' =>
"Nom",

'importer_edit' =>
"Éditer",

'importer_datatype' =>
"Type de données",

'importer_cron_urls' =>
"URLs Cron",

'importer_cron' =>
"Cron",

'importer_cron_batch' =>
"Traitement par lots",

'importer_batch_processing_explaination' =>
"Importer a une limite interne fixée à 100 éléments importables simultanément, et ce afin d'éviter de dépasser les ressources serveur.
Si le nombre d'éléments à importer excède ce total, le traitement par lots est déclenché automatiquement.
Afin d'utiliser le traitement par lots avec un travail Cron, vous devez définir un deuxième Cron pour traiter les lots.",

'right_click_to_copy' =>
"clique droit pour copier",

'importer_delete' =>
"Supprimer",

'importer_run_profile' =>
"Exécuter",

'importer_run_now' =>
"Exécuter maintenant",

'importer_profile_name' =>
"Nom de profil",

'importer_instructions' =>
"Instructions",

'importer_instructions_subtext' =>
"Tous les détails ou instructions que vous voudriez avoir sous la main au moment de réaliser des importations avec ce profil.",

'importer_profile_deleted' =>
"Profil Importer supprimé",

'importer_profiles_deleted' =>
"%i% profils Importer supprimés",

"profile_delete_question" =>
"Êtes-vous certain de vouloir supprimer %i% %profiles% ?",

"action_can_not_be_undone" =>
"Cette action ne peut pas être annulée.",

'importer_profile_delete_confirm' =>
"Confirmation de suppression de profil Importer",

'manual_upload_no_cron' =>
"Téléversement manuel, aucun Cron disponible",

'manual_import_form' =>
"Import manuel à partir de",

'importer_continue' =>
"Continuer",

'save_and_continue' =>
"Enregistrer et Continuer",

'save_and_finish' =>
"Enregistrer et Terminer",

'importer_profile' =>
"Profil Importer",

'importer_edit_profile' =>
"Éditer le profil Importer",

'importer_new_profile' =>
"Nouveau profil Importer",

'importer_profile_updated' =>
"Profil Importer mis à jour",

'invalid_importer_profile_name' =>
"Le nom de profil Importer soumis est invalide",

'error_duplicate_profile_name' =>
"Erreur : un doublon de nom de profil Importer a été soumis",

'success_importer_profile_name_updated' =>
"Le nom et les instructions liés au profil Importer ont été mis à jour avec succès",

'success_importer_settings_updated' =>
"Les paramètres du profil Importer ont été mis à jour avec succès",

'invalid_importer_profile_id' =>
"ID de profil Importer invalide",

'invalid_importer_profile_datatype' =>
"Le type de données soumis pour ce profil Importer est invalide",

'invalid_datatype_given' =>
"Un type de données invalide a été fourni",

'invalid_datatype' =>
"Type de données invalide",

'importer_channel' =>
"Canal d'import",

'importer_channel_subtext' =>
"Le canal dans lequel cette source de données sera importée.",

'choose_channel' =>
"Choisissez un canal",

'importer_profile_source' =>
"Source de profil Importer",

'import_source' =>
"Source d'import",

'choose_data_source' =>
"Choisissez la source de données",

'importer_data_source' =>
"Source de données",

'importer_data_source_subtext' =>
"Les données destinées à Importer peuvent être récupérées selon les méthodes listées ici. Choisissez-en une et renseignez les détails requis. Un essai sera effectué pour récupérer le fichier quand vous cliquerez sur Soumettre.",

'importer_data_source_filename' =>
"Fichier local",

'importer_data_source_url' =>
"URL distante",

'importer_data_source_ftp' =>
"Fichier via FTP",

'importer_data_source_sftp' =>
"Fichier via SFTP",

'choose_data_source' =>
"Choisissez la source de données",

'importer_local_filename' =>
"Nom de fichier local",

'importer_local_filename_subtext' =>
"Le chemin absolu d'enregistrement du fichier placé sur votre serveur.",

'importer_remote_url' =>
"URL distante",

'importer_remote_url_subtext' =>
"L'adresse URL complète du fichier. http:// et https:// sont tous les deux supportés.",

'importer_http_auth_username' =>
"Identifiant (nom d'utilisateur) pour l'authentification HTTP",

'importer_http_auth_username_subtext' =>
"Le fichier distant est peut-être protégé par une authentification HTTP. Note : pour des raisons de sécurité, l'identifiant et le mot de passe seront encryptés AVANT d'être stockés dans la base de données ExpressionEngine.",

'importer_http_auth_password' =>
"Mot de passe d'authentification HTTP",

'importer_ftp_host' =>
"Hôte",

'importer_ftp_username' =>
"Identifiant",

'importer_ftp_password' =>
"Mot de passe",

'importer_ftp_password_subtext' =>
"Note : pour des raisons de sécurité, l'identifiant et le mot de passe seront encryptés AVANT d'être stockés dans la base de données ExpressionEngine.",

'importer_ftp_port' =>
"Port",

'importer_ftp_path' =>
"Chemin sur le serveur distant",

'importer_manual_upload' =>
"Téléchargement manuel",

'importer_data_source_manual_upload' =>
"Télécharger manuellement le fichier",

'importer_manual_upload' =>
"Fichier téléchargé manuellement",

'importer_manual_upload_subtext' =>
"Cette option signifie qu'à chaque fois que vous utiliserez ce profil Importer, vous devrez télécharger manuellement un fichier à partir de votre ordinateur en local.",

'importer_ftp_test' =>
"Tester la connexion FTP/SFTP",

'ftp_perform_connection_test' =>
"Réaliser un test de connexion",

'importer_ftp_test_subtext' =>
"Une connexion de test va être réalisée en utilisant AJAX et le fichier sera détecté (mais pas téléchargé) afin de s'assurer que vos paramètres sont opérationnels.",

'import_location' =>
"Emplacement d'importation",

// --------------------------------------------
//  Source Form Errors
// --------------------------------------------

'invalid_channel_submitted' =>
"Canal soumis invalide",

'invalid_channel_permissions' =>
"Vous n'avez pas les droits nécessaires pour poster dans le canal soumis et ne pouvez donc pas y réaliser un import.",

'invalid_data_source_submitted' =>
"Source de données soumise invalide. Merci de vous assurer que vous avez sélectionné une source de données et que vous avez renseigné les champs requis.",

'invalid_filename_fullpath' =>
"Chemin du fichier soumis invalide. Merci de vous assurer que c'est un chemin absolu de serveur.",

'invalid_filename_not_found' =>
"Chemin du fichier soumis invalide. Le fichier n'a pas été trouvé sur le serveur.",

'invalid_remote_url_not_found' =>
"URL distante soumise invalide. Une tentative pour récupérer le fichier a échoué.",

'problem_retreiving_file_data' =>
"Problème lors de la récupération des données du fichier.",

'source_data_contained_invalid_data' =>
"Le fichier source contenait soit aucune donnée, soit des données invalides.",

'failure_downloading_remote_file' =>
"Échec lors du téléchargement du fichier distant à partir du serveur.",

'unable_to_create_unzipping_directory' =>
"Impossible de créer un répertoire pour décompresser votre fichier dans le répertoire de cache d'ExpressionEngine. Merci de vérifier vos droits d'écriture sur le dossier concerné.",

'unable_to_create_importer_directory' =>
"Impossible de créer un répertoire pour stocker les fichiers de données Importer dans le cache d'ExpressionEngine. Merci de vérifier vos droits d'écriture sur le dossier concerné.",

'error_unable_to_read_data_file' =>
"Erreur : impossible de lire le fichier de données.",

'importer_memory_usage_warning' =>
"Importer a déterminé que la taille de la source de données est trop importante pour que votre serveur la prenne en charge.",

// --------------------------------------------
//  Settings Form - GLOBAL
// --------------------------------------------

'invalid_data_received' =>
"Les données reçues de votre source de données n'étaient pas valides et n'ont pas pu être lues.",

'invald_content_type_settings' =>
"Paramètres de type de contenu invalides",

'importer_notifications' =>
"Notifications",

'importer_notification_emails' =>
"Courriels",

'importer_notification_emails_subtext' =>
"Séparer les adresses multiples avec des virgules ou des retour à la ligne.",

'importer_notification_cc' =>
"CC",

'importer_notification_cc_subtext' =>
"Séparer les adresses multiples avec des virgules ou des retours à la ligne.",

'importer_notification_subject' =>
"Objet",

'importer_notification_message' =>
"Message",

'importer_notification_message_subtext' =>
"Les variables suivantes sont disponibles :<br />
{author_ids}, {channel_id}, {site_id},<br />
{profile_name}, {content_type}, {datatype},<br />
{emails}, {email_cc},<br />
{import_date}, {import_ip_address}, {import_location},<br />
{last_import_date}, {run_time},<br />
{start_or_end},<br />
{total_inserted}, {total_updated}, {entries_deleted}",

'importer_notification_rules' =>
"Règles",

'importer_notification_rules_disabled' =>
"Ne pas envoyer de courriel",

'importer_notification_rules_start' =>
"Envoyer un courriel au début de l'importation",

'importer_notification_rules_end' =>
"Envoyer un courriel à la fin de l'importation",

'importer_notification_rules_start_end' =>
"Envoyer un courriel au début et à la fin de l'importation",

'importer_element' =>
"Élément",

'importer_default_value' =>
"Valeur par défaut",

'importer_modal_save' =>
"Enregistrer",

'save_and_do_import' =>
"Enregistrer et réaliser l'import",

// --------------------------------------------
//  Import Log
// --------------------------------------------

'import_log' =>
"Journal de l'import",

'import_date' =>
"Date",

'import_details' =>
"Détails",

'no_imports_logged' =>
"Aucune importation dans le journal",

'importer_delete_logs' =>
"Supprimer les journaux",

"logs_delete_question" =>
"Êtes-vous certain de vouloir supprimer %i% journaux ?",

"log_delete_question" =>
"Êtes-vous certain de vouloir supprimer ce journal ?",

'importer_logs_delete_confirm' =>
"Confirmation de suppression du journal Importer",

'importer_log_deleted' =>
"Journal Importer supprimé",

'importer_logs_deleted' =>
"%i% journaux Importer supprimés",

// --------------------------------------------
//  AJAX Connection Test
// --------------------------------------------

'error_ajax_request' =>
"Erreur : la requête AJAX a échoué",

'error_importer_ftp_test' =>
"Erreur : le test de connexion FTP/SFTP a échoué",

'invalid_or_missing_fields' =>
"Champs invalides ou manquants. Merci de vous assurer que tous les champs ont bien été renseignés.",

'ftp_file_does_not_exist' =>
"FTP : le fichier n'existe pas",

'ftp_ssl_not_supported' =>
"Les connexions SSL ne sont pas supportées par votre serveur.",

'ftp_unable_to_connect' =>
"Impossible de créer une connexion avec le serveur FTP.",

'ftp_unable_to_login' =>
"Impossible de se connecter au serveur FTP avec les données d'identification soumises.",

'ftp_bad_local_path' =>
"Le chemin local fourni pour télécharger le(s) fichier(s) FTP est erroné.",

'ftp_local_path_not_writable' =>
"Le chemin local fourni pour télécharger le(s) fichier(s) FTP n'est pas inscriptible.",

'ftp_bad_local_path' =>
"Le chemin local fourni n'est pas accepté par la bibliothèque FTP.",

'ftp_bad_remote_path' =>
"Le chemin distant fourni n'est pas valide.",

'ftp_bad_remote_file' =>
"Le chemin du fichier distant fourni n'est pas valide.",

'ftp_unable_to_download' =>
"Impossible de télécharger le fichier à partir de votre serveur FTP.",

'ftp_file_does_not_exist' =>
"Le fichier ne semble pas exister sur votre serveur FTP.",

'error_sftp_connection_failure' =>
"Échec de la connexion au serveur SFTP.",

'error_sftp_file_failure' =>
"Impossible de trouver le fichier sur le serveur.",

'success_importer_ftp_test' =>
"Test de connexion réussi !",

'connection_test_successful_file_found' =>
"Le test de connexion a fonctionné et le fichier a été trouvé sur le serveur.",

'modal_close_button' =>
"Fermer",

'beginning_connection_test' =>
"Début du test de connexion",

'connection_test_underway_please_standby' =>
"Votre test de connexion FTP/SFTP est en cours. Merci de patienter, la confirmation vous parviendra dans les 15 secondes.",

'modal_press_esc_to_close' =>
"Appuyer sur la touche ESC/Échap pour fermer la fenêtre.",

'modal_press_esc_to_close_and_discard' =>
"Appuyer sur la touche ESC/Échap pour fermer la fenêtre et abandonner les modifications.",

'additional_data_type_fields' =>
"Champs additionnels de type de données",

// --------------------------------------------
//  Setting Submission Errors - GLOBAL
// --------------------------------------------

'error_invalid_notification_emails' =>
"Erreur : adresses de notification soumises invalides",

'error_invalid_notification_cc' =>
"Erreur : adresses de notification (CC) soumises invalides",

'error_invalid_notification_subject_message_required' =>
"Erreur : si vous envoyer un courriel de notification, un Objet et un Message sont tous deux obligatoires.",

// --------------------------------------------
//  Cron Import
// --------------------------------------------

'successful_import' =>
"Import réussi",

'import_was_successfully_completed' =>
"L'import a été effectué dans sa totalité et sans erreur.",

'failure_of_import' =>
"Échec de l'import",

'importer_invalid_batch' =>
"Lot demandé invalide",

'batch_import_started' =>
"Le processus d'importation en lot a débuté avec succès",

'no_batches_to_process' =>
"Aucun lot à traiter",

// --------------------------------------------
//  Statistics
// --------------------------------------------

'entries_inserted' =>
"Entrées insérées",

'entries_updated' =>
"Entrées mises à jour",

'entries_deleted' =>
"Entrées supprimées",

'deleted_entries' =>
"Entrées supprimées",

'total_entries' =>
"Total des entrées",

'entry_ids' =>
"IDs des entrées",

'inserted_entry_ids' =>
"IDs des entrées insérées",

'updated_entry_ids' =>
"IDs des entrées mises à jour",

'author_ids' =>
"IDs des auteurs",

'debugging' =>
"Débogage",

'start_time' =>
"Heure de début",

'end_time' =>
"Heure de fin",

'run_time' =>
"Durée d'exécution",

'site_id' =>
"ID du site",

'number_of_queries' =>
"Nombre de requêtes",

// -------------------------------------
//	Batch Import
// -------------------------------------

'batch_importer' =>
"Fonction d'import en lot",

'importer_batch_purpose' =>
"Ceci va réaliser les imports sous forme de lots et vous serez prévenu une fois l'import terminé.
Cliquer sur 'Réaliser l'Import' pour démarrer.",

'importer_perform_import' =>
"Réaliser l'Import",

"performing_import_for_batch_" =>
"En cours d'import pour le lot : ",

'importer_percent_completed' =>
"Pourcentage achevé",

'importer_number_of_batches_imported' =>
"Nombre de lots importés",

'importer_pause' =>
"Pause",

'importer_resume' =>
"Reprendre",

'return_to_importer_homepage' =>
"Vous pouvez désormais retourner sur la Page d'accueil d'Importer.",

'importer_import_complete' =>
"Import terminé",

'importer_invalid_values' =>
"Valeurs reçues invalides",

'importer_batch_number_' =>
"Lot n°",

//----------------------------------------
//  Errors
//----------------------------------------

'invalid_request' =>
"Demande invalide",

'importer_module_disabled' =>
"Le module Importer est actuellement désactivé. Merci de vous assurer qu'il est installé et à jour
en vous rendant sur le panneau de contrôle du module situé dans le Tableau de Bord ExpressionEngine",

'disable_module_to_disable_extension' =>
"Pour désactiver cette extension, vous devez désactiver son <a href='%url%'>module</a> correspondant.",

'enable_module_to_enable_extension' =>
"Pour activer cette extension, vous devez installer son <a href='%url%'>module</a> correspondant.",

'cp_jquery_requred' =>
"L'extension 'jQuery pour le Panneau de Contrôle' doit être <a href='%extensions_url%'>activée</a> pour utiliser ce module.",

//----------------------------------------
//  Update routine
//----------------------------------------

'update_importer_module' =>
"Mettre à jour le module Importer",

'importer_update_message' =>
"Vous avez récemment télechargé une nouvelle version d'Importer, merci de cliquer ici afin d'exécuter le script de mise à jour.",

"update_successful" =>
"Le module a été mis à jour avec succès.",

"update_failure" =>
"Une erreur s'est produite lors de la tentative de mise à jour du module vers la dernière version.",

'required_field_was_empty' =>
"Un champ obligatoire est resté vide, merci de revenir en arrière et de renseigner TOUS les champs.",


// END
''=>''
);
?>