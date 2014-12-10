<?php

$lang = $L = array(

'content_type_channel_label' =>
"Canaux",

'importer_channel_settings_form' =>
"Formulaire de paramétrage de canal",

'importer_channel_entry_title'=>
"Titre de l'article",

'importer_channel_title'=>
"Titre de l'article",

'importer_channel_author' =>
"Auteur",

'importer_channel_author_field_type' =>
"Type de champ Auteur",

'importer_channel_author_field_type_subtext' =>
"Pensez à sélectionner le type de données de membre que le champ Auteur contient.",

'importer_channel_entry_title_subtext' =>
"Le titre URL va être automatiquement créé à partir de ceci.",

'importer_author_field' =>
"Champ Auteur",

'importer_author_field_subtext' =>
"Les articles seront affectés au membre inscrit dans ce champ. Si le champ ne correspond à aucun membre existant, l'Auteur par défaut sera affecté. Pensez à sélectionner le type de données de membre que le champ Auteur contient.",

'importer_channel_status' =>
"Statut",

'importer_channel_status_subtext' =>
"Choisissez soit un statut par défaut à partir de la liste, soit le champ importé qui contiendra le statut.",

'importer_member_id' =>
"ID de Membre",

'importer_email_address' =>
"Adresse Email",

'importer_username' =>
"Nom d'utilisateur",

'importer_screen_name' =>
"Pseudo",

'content_type_for_example' =>
"p. ex.,",

'channel_custom_fields' =>
"Champs personnalisés de canal",

'channel_custom_field_default' =>
"Valeur par défaut",

'importer_channel_categories' =>
"Catégories",

'importer_channel_default_categories_subtext'=>
"Les articles seront affectés aux catégories des éléments ET aux catégories par défaut que vous sélectionnerez.",

'importer_add_categories_to_group' =>
"Ajouter de nouvelles catégories au groupe de catégories",

'importer_add_categories_to_group_subtext' =>
"Si vous choisissez un groupe de catégories, alors les catégories non rattachées seront créées et insérées dans ce groupe de catégories.",

'importer_no_new_categories' =>
"NE PAS créer de nouvelles catégories",

'importer_channel_category_delimiter' =>
"Délimiteur de catégorie",

'importer_channel_category_delimiter_subtext' =>
"Quand toutes vos catégories sont intégrées dans un seul élément. Par exemple, 'Un, Deux, Trois'
créera 3 catégories si le délimiteur est une virgule.",

'importer_duplicate_field' =>
"Champ dupliqué", /** verb or noun ? */

'importer_channel_title' =>
"Titre de l'article",

'importer_duplicate_field_subtext' =>
"Le module utilisera ce champ pour éviter les doublons et pour mapper correctement les données lors de la mise à jour des articles.
(Un example pourrait être un code ou une référence produit unique assigné à un article dans la source XML). ",

'importer_duplicate_field_two' =>
"Champ dupliqué, Partie Deux", /**verb or noun ? */

'importer_duplicate_field_two_subtext' =>
"Dans certain cas, votre source de données n'incluera peut-être pas une unique sorte de données. Dans ce cas, vous pouvez sélectionner un deuxième champ pour permettre la création d'une clef unique pour votre article.",

'importer_duplicate_entry_action' =>
"Action pour article dupliqué",

'importer_do_nothing' =>
"Ne rien faire",

'importer_delete_entry' =>
"Supprimer l'article",

'importer_update_entry' =>
"Mettre à jour l'article",

'importer_delete_entry_insert_new' =>
"Supprimer l'ancien article, Insérer un nouvel article",

'importer_duplicate_entry_category_action' =>
"Action pour les catégories (article dupliqué)",

'importer_categories_delete_old_add_new' =>
"Supprimer les anciennes catégories et ajouter les nouvelles",

'importer_categories_keep_old_add_new' =>
"Conserver les anciennes catégories et ajouter les nouvelles",

'importer_duplicate_entry_status_action' =>
"Action pour le statut (article dupliqué)",

'importer_update_status' =>
"Mettre à jour le statut",

'importer_do_not_update_status' =>
"Ne PAS mettre à jour le statut",

'channel_custom_field_element' =>
"Élément",

'importer_custom_field_default_show' =>
"Montrer",

'importer_custom_field_default_hide' =>
"Cacher",

'importer_channel_meta_fields' =>
"Champs Méta",

'importer_channel_solspace_tags' =>
"Étiquettes (Tags) Solspace",

'importer_channel_solspace_tags_subtext' =>
"Séparer toutes les étiquettes par défaut avec le délimiteur indiqué ci-dessous.",

'importer_solspace_tags_delimiter' =>
"Délimiteur étiquette Solspace",

'importer_solspace_tags_delimiter_subtext' =>
"Lorsque toutes vos étiquettes sont présentes dans un seul élément. Par exemple, 'cinq, sept, onze' créera 3 étiquettes si le délimiteur est une virgule.",

'importer_time_of_import' =>
"Heure d'importation",

'importer_channel_entry_date' =>
"Date de l'article",

'importer_channel_entry_date_subtext' =>
"L'horodatage au format UNIX est accepté, ainsi que presque toutes les descriptions littérales anglo-saxonnes d'une date.",

'importer_entry_date' =>
"Date de l'article",

'importer_channel_expiration_date' =>
"Date d'expiration",

'importer_channel_expiration_date_subtext' =>
"L'horodatage au format UNIX est accepté, ainsi que presque toutes les descriptions littérales anglo-saxonnes d'une date.",

'importer_expiration_date' =>
"Date d'expiration",

'importer_entry_date_offset' =>
"Décalage",

'importer_entry_date_offset_seconds' =>
"secondes",

'importer_duplicate_entries' =>
"Articles dupliqués",

'importer_channel_comments' =>
"Commentaires",

// --------------------------------------------
//  Date Field Type
// --------------------------------------------

'date_field_type_subtext' =>
"Le type de champ Date peut contenir des données formatées comme suit :
<br /> - selon un horodatage UNIX unique (ex.: 987654321)
<br /> - selon un format EE de type anglo-saxon (ex: 2012-01-31 09:26 AM)",

// --------------------------------------------
//  Playa Field Type
// --------------------------------------------

'playa_field_type_subtext' =>
"Le type de champ Playa peut contenir des données formatées comme suit :
<br /> - selon une liste d'IDs d'article séparés par une virgule ou une barre verticale (|)
<br /> - telles qu'elles sont enregistrées dans la base de données EE, avec un ID d'article, un titre et un URL qui correspond au titre ([###] Titre Article - entry_url_title), les entrées multiples étant séparées par un saut de ligne.",


// --------------------------------------------
//  Matrix Field Type
// --------------------------------------------

'matrix_field_type_subtext' =>
"Importer supporte les types de champ Matrix suivants :
<br /> - Date
<br /> - Playa
<br /> - Texte
<br /> - Wygwam",

'Matrix field not configured in Custom Field settings.' =>
"Champ Matrix non configuré dans les paramètres de champ personnalisé.",

// --------------------------------------------
//  Errors
// --------------------------------------------

'error_invalid_importer_title_element_selected' =>
"Erreur : élément Titre sélectionné non valide. Merci de revenir en arrière et de sélectionner un élément afin d'importer un titre, ou définissez une valeur par défaut.",

'error_importer_custom_field_required' =>
"Erreur : un champ personnalisé (%field%) est requis mais ne contient pas d'élément ou de valeur par défaut.",

'error_invalid_importer_custom_field_element_selected' =>
"Erreur : l'élément de champ personnalisé (%field%) sélectionné est non valide.",

'error_invalid_importer_element_selected' =>
"Erreur : un élément sélectionné pour le champ %field% est non valide.",

'error_invalid_entry_date_offset' =>
"Erreur : un décalage de date d'article soumis est non valide.",

'error_invalid_default_status' =>
"Erreur : un statut par défaut soumis est non valide.",

'error_invalid_default_author' =>
"Erreur : un auteur par défaut soumis est non valide.",

'error_invalid_default_categories' =>
"Erreur : des catégories par défaut soumises sont non valides.",

'error_invalid_notification_emails' =>
"Erreur : des adresses courriel de notification soumises sont non valides.",

'error_invalid_notification_cc' =>
"Erreur : des adresses courriel (CC) de notification soumises sont non valides.",

'error_invalid_notification_subject_message_required' =>
"Erreur : si vous envoyez une notification, un sujet et un message sont tous deux obligatoires.",

'importer_unable_to_find_default_author' =>
"Dans l'incapacité de trouver l'auteur par défaut dans la base de données.",

'importer_author_has_invalid_permissions' =>
"L'Auteur n'a pas les autorisations suffisantes pour écrire dans le canal sélectionné.",

'importer_title_element_not_found' =>
"L'élément Titre n'a pas pu être trouvé dans la source des données.",

'error_invalid_new_category_group' =>
"Erreur : groupe de catégories sélectionné non valide.",

// END

''=>''
);
?>