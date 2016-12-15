<?php

$lang = $L = array(

'content_type_channel_entries_label' =>
"Channel Entries",

'importer_channel_entries_settings_form' =>
"Channel Entries Settings Form",

'importer_channel_entry_title'=>
"Entry Title",

'importer_channel_title'=>
"Entry Title",

'importer_channel_entry_url_title'=>
"Entry URL Title",

'importer_channel_author' =>
"Author",

'importer_channel_author_field_type' =>
"Author Field Type",

'importer_channel_author_field_type_subtext' =>
"Remember to select the type of member data that the author field contains.",

'importer_channel_entry_url_title_subtext' =>
"If not set, the URL Title will be automatically created from Title.",

'importer_author_field' =>
"Author Field",

'importer_author_field_subtext' =>
"Entries will be assigned to the member in this field. If the field does not match any existing member, the default author will be assigned. Remember to select the type of member data that the author field contains.",

'importer_channel_status' =>
"Status",

'importer_channel_status_subtext' =>
"Choose either a default status from the list or the imported field that will contain the status.",

'importer_member_id' =>
"Member ID",

'importer_email_address' =>
"Email Address",

'importer_username' =>
"Username",

'importer_screen_name' =>
"Screen Name",

'content_type_for_example' =>
"eg,",

'channel_custom_fields' =>
"Channel Custom Fields",

'channel_custom_field_default' =>
"Default Value",

'importer_channel_categories' =>
"Categories",

'importer_channel_default_categories_subtext'=>
"Entries will be assigned to the categories in the element(s) AND any default categories you select.",

'importer_add_categories_to_group' =>
"Add New Categories to Category Group",

'importer_add_categories_to_group_subtext' =>
"If you choose a Category group, then unmatched categories will be created and inserted into that Category group.",

'importer_no_new_categories' =>
"Do NOT create new categories",

'importer_channel_category_delimiter' =>
"Category Delimiter",

'importer_channel_category_delimiter_subtext' =>
"For when all of your categories are located in a single element. For example, 'One, Two, Three'
will create 3 categories if the delimiter is a comma.",

'importer_duplicate_field' =>
"Duplicate Field",

'importer_channel_title' =>
"Entry Title",

'importer_duplicate_field_subtext' =>
"The module will use this field to prevent duplicates and to map data correctly when updating entries.
(One example would be a unique product ID code or SKU assigned to an entry in the XML source.) ",

'importer_duplicate_field_two' =>
"Duplicate Field, Part Deux",

'importer_duplicate_field_two_subtext' =>
"In some cases your source data may not include a single unique piece of data. In that case, you can
select a second field to help create a unique key for your entry.",

'importer_duplicate_entry_action' =>
"Duplicate Entry Action",

'importer_do_nothing' =>
"Do Nothing",

'importer_delete_entry' =>
"Delete Entry",

'importer_update_entry' =>
"Update Entry",

'importer_delete_entry_insert_new' =>
"Delete Old Entry, Insert New Entry",

'importer_duplicate_entry_category_action' =>
"Duplicate Entry Action for Categories",

'importer_categories_delete_old_add_new' =>
"Delete Old Categories and Add the New",

'importer_categories_keep_old_add_new' =>
"Keep Old Categories and Add the New",

'importer_duplicate_entry_status_action' =>
"Duplicate Entry Action for Status",

'importer_update_status' =>
"Update Status",

'importer_do_not_update_status' =>
"Do NOT Update Status",

'channel_custom_field_element' =>
"Element",

'importer_custom_field_default_show' =>
"Show",

'importer_custom_field_default_hide' =>
"Hide",

'importer_channel_meta_fields' =>
"Meta Fields",

'importer_channel_solspace_tags' =>
"Solspace Tags",

'importer_channel_solspace_tags_subtext' =>
"Separate any default Tags with the delimiter specified below.",

'importer_solspace_tags_delimiter' =>
"Tags Delimiter",

'importer_solspace_tags_delimiter_subtext' =>
"For when all of your tags are located in a single element. For example, 'Five, Seven, Eleven'
will create 3 tags if the delimiter is a comma.",

'importer_time_of_import' =>
"Time of Import",

'importer_channel_entry_date' =>
"Entry Date",

'importer_channel_entry_date_subtext' =>
"Accepts Unix timestamp, or just about any English textual datetime description.",

'importer_entry_date' =>
"Entry Date",

'importer_channel_expiration_date' =>
"Expiration Date",

'importer_channel_expiration_date_subtext' =>
"Accepts Unix timestamp, or just about any English textual datetime description.",

'importer_expiration_date' =>
"Expiration Date",

'importer_entry_date_offset' =>
"Offset",

'importer_entry_date_offset_seconds' =>
"seconds",

'importer_duplicate_entries' =>
"Duplicate Entries",

'importer_channel_comments' =>
"Comments",

// --------------------------------------------
//  Date Field Type
// --------------------------------------------

'date_field_type_subtext' =>
"The Date Field Type can have data in the following forms:
<br /> - As a single UNIX Timestamp (ex: 987654321)
<br /> - As an EE Human readable time (ex: 2012-01-31 09:26 AM)",

// --------------------------------------------
//  Playa Field Type
// --------------------------------------------

'playa_field_type_subtext' =>
"The Playa Field Type can have data in the following forms:
<br /> - As a list of entry ids separated by a comma or bar (|)
<br /> - As stored in the EE database with an entry id, title, and url title ([###] Entry Title - entry_url_title)
and multiple entries separated by a line break.",


// --------------------------------------------
//  Matrix Field Type
// --------------------------------------------

'matrix_field_type_subtext' =>
"Importer supports the following Matrix Field Types:
<br /> - Date
<br /> - Playa
<br /> - Text
<br /> - Wygwam",

'Matrix field not configured in Custom Field settings.' =>
"Matrix field not configured in Custom Field settings.",

// --------------------------------------------
//  Errors
// --------------------------------------------

'error_unable_to_find_channel_in_the_database_this_should_never_happen' =>
"Error: Unable to find Channel in the Database. This should never happen.",

'error_invalid_importer_title_element_selected' =>
"Error: Invalid Title Element Selected. Please, go back and select an item for importing a title or set a default value.",

'error_importer_custom_field_required' =>
"Error: A Custom Field is required (%field%) but did not have an element or default value.",

'error_invalid_importer_custom_field_element_selected' =>
"Error: Invalid Custom Field (%field%) Element Selected.",

'error_invalid_importer_element_selected' =>
"Error: Invalid Element selected for field: %field%",

'error_invalid_entry_date_offset' =>
"Error: Invalid entry date offset submitted.",

'error_invalid_default_status' =>
"Error: Invalid default status submitted.",

'error_invalid_default_author' =>
"Error: Invalid default author submitted.",

'error_invalid_default_categories' =>
"Error: Invalid default categories submitted.",

'error_invalid_notification_emails' =>
"Error: Invalid Notication Emails submitted",

'error_invalid_notification_cc' =>
"Error: Invalid Notication CC Emails submitted",

'error_invalid_notification_subject_message_required' =>
"Error:  If sending a notification, a subject and message are required.",

'importer_unable_to_find_default_author' =>
"Unable to find the default author in the database.",

'importer_author_has_invalid_permissions' =>
"Author has invalid permissions for posting to the selected channel.",

'importer_title_element_not_found' =>
"Title element could not be found in the data source.",

'error_invalid_new_category_group' =>
"Error:  Invalid Category Group selected",

'error_incomplete_settings' =>
"Error: Import settings are incomplete.",

'error_channel_not_found' =>
"Error: Channel in settings not found.",

// END

''=>''
);