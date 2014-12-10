<?php

/**
 * Importer - Brazilian Portuguese Language
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2014, Solspace, Inc.
 * @link		http://solspace.com/docs/importer
 * @license		http://www.solspace.com/license_agreement
 * @version		2.1.0
 * @filesource	importer/language/brazilian/lang.importer.php
 *
 * Translated to Brazilian Portuguese by MarchiMedia
 */

$lang = $L = array(

/**	----------------------------------------
/**	Necessário para apágina MÓDULOS
/**	----------------------------------------*/

'importer_module_name'						=>
'Importer',

'importer_module_description'				=>
'Importa registros de origens CSV, JSON, e XML.',

'importer'									=>
'Importer',

'importer_module_version' =>
"Versão",

// --------------------------------------------
//  menu Principal do painel
// --------------------------------------------

'importer_preferences' =>
"Preferências",

'importer_homepage' =>
"Página Inicial",

'online_documentation' =>
"Documentação Online",

'importer_yes' =>
"Sim",

'importer_no' =>
"Não",

// --------------------------------------------
//  Homepage/Importações
// --------------------------------------------

'create_new_importer' =>
"Criar Novo Perfil de Importação",

'no_importer_profiles' =>
"Não existem perfis de importação ainda",

'saved_imports' =>
"Importações Salvas",

'importer_name' =>
"Nome",

'importer_edit' =>
"Editar",

'importer_datatype' =>
"Tipo de Dados",

'importer_cron_urls' =>
"URLs de Cron",

'importer_cron' =>
"Cron",

'importer_cron_batch' =>
"Processamento em Lote",

'importer_batch_processing_explaination' =>
"Importer possui um limite de 100 itens importados por vez para prevenir o esgotamento dos recursos do servidor.
Se o número de itens para serem importados excedem esta quantidade, o processamento em lote é acionado automaticamente.  
Para utilizar o processamento em lote com um cron, você precisa definir um segundo cron para processar o lote.",

'right_click_to_copy' =>
"clique direito para a cópia",

'importer_delete' =>
"Excluir",

'importer_run_profile' =>
"Executar",

'importer_run_now' =>
"Executar Agora",

'importer_profile_name' =>
"Nome do Perfil",

'importer_instructions' =>
"Instruções",

'importer_instructions_subtext' =>
"Qualquer detalhe ou instruções que você deseja manter à vista quando fizer importações com este perfil.",

'importer_profile_deleted' =>
"Perfil de Importação Excluído",

'importer_profiles_deleted' =>
"%i% Perfis de Importação Excluídos",

"profile_delete_question" =>
"Tem certeza que você deseja excluir %i% %profiles%?",

"action_can_not_be_undone" =>
"Esta ação não poderá ser desfeita.",

'importer_profile_delete_confirm' =>
"Comfirmação de Exclusão de Perfil de Importação",

'manual_upload_no_cron' =>
"Upload manula, Sem Cron Disponível",

'manual_import_form' =>
"Formulário de Importação Manual",

'importer_continue' =>
"Continuar",

'save_and_continue' =>
"Salvar e Continuar",

'save_and_finish' =>
"Salvar e Finalizar",

'importer_profile' =>
"Perfil de Importação",

'importer_edit_profile' =>
"Editar Perfil de Importação",

'importer_new_profile' =>
"Novo Perfil de Importação",

'importer_profile_updated' =>
"Perfil de Importação Atualizado",

'invalid_importer_profile_name' =>
"Nome de Perfil de Importação inválido",

'error_duplicate_profile_name' =>
"Erro: Um nome perfil de importação duplicado foi inserido",

'success_importer_profile_name_updated' =>
"O nome do Perfil de Importação e  as instruções foram atualizadas com sucesso",

'success_importer_settings_updated' =>
"As configurações de Perfil de Importação foram atualizadas com sucesso",

'invalid_importer_profile_id' =>
"ID de Perfil de Importação Inválido",

'invalid_importer_profile_datatype' =>
"Tipo de Dados para importação inválidos",

'invalid_datatype_given' =>
"Tipos de dados inválidos obtidos",

'invalid_datatype' =>
"Tipo de Dados Inválidos",

'importer_channel' =>
"Importar Canal",

'importer_channel_subtext' =>
"O Canal que as origens destes dados serão importados.",

'choose_channel' =>
"Escolha um Canal",

'importer_profile_source' =>
"Origem do Perfil de Importação",

'import_source' =>
"Origem da Importação",

'choose_data_source' =>
"Selecione a Origem dos Dados",

'importer_data_source' =>
"Origem dos Dados",

'importer_data_source_subtext' =>
"Os dados para a Importação podem ser obtidos pelos métodos listados. Escolha um e preencha os detalhes necessários. Uma tentativa será efetuada para obter o arquivo quando você clicar enviar.",

'importer_data_source_filename' =>
"Arquivo Local",

'importer_data_source_url' =>
"URL Remota",

'importer_data_source_ftp' =>
"Arquivo via FTP",

'importer_data_source_sftp' =>
"Arquivo via SFTP",

'choose_data_source' =>
"Escolha a Origem dos Dados",

'importer_local_filename' =>
"Nome do Arquivo Local",

'importer_local_filename_subtext' =>
"O caminho absoluto para o arquivo localizado no seu servidor.",

'importer_remote_url' =>
"URL Remota",

'importer_remote_url_subtext' =>
"A URL no seu site para o seu arquivo.  Ambos http:// e https:// são suportados.",

'importer_http_auth_username' =>
"Nome de Usuário HTTP - Autenticação",

'importer_http_auth_username_subtext' =>
"O arquivo remoto pode estar protegido por autenticação HTTP. Nota: O nome de Usuário e a Senha serão encriptados antes de serem armazenados no banco de dados do ExpressionEngine por razões de segurança.",

'importer_http_auth_password' =>
"Senha HTTP",

'importer_ftp_host' =>
"Host",

'importer_ftp_username' =>
"Usuário",

'importer_ftp_password' =>
"Senha",

'importer_ftp_password_subtext' =>
"Nota: O Nome de Usuário e a Senha serão encriptados antes de serem armazenados no banco de dados do ExpressionEngine por razões de segurança.",

'importer_ftp_port' =>
"Porta",

'importer_ftp_path' =>
"Caminho no Servidor Remoto",

'importer_manual_upload' =>
"Upload Manual",

'importer_data_source_manual_upload' =>
"Subir um arquivo manualmente",

'importer_manual_upload' =>
"Upload Manual de Arquivo",

'importer_manual_upload_subtext' =>
"Esta opção significa que cada vez que você utilizar este perfil de Importação, você precisará subir manualmente um aqrquivo do seu computador local.",

'importer_ftp_test' =>
"Testar Conexão FTP/SFTP ",

'ftp_perform_connection_test' =>
"Executar Teste de Conexão",

'importer_ftp_test_subtext' =>
"Usando AJAX, um teste de conexão irá ser executado e o arquivo será detectado mas não será baixado para assegurar que suas configurações funcionem.",

'import_location' =>
"Local de Importação",

// --------------------------------------------
//  Erros de Formulário
// --------------------------------------------

'invalid_channel_submitted' =>
"Canal Inválido fornecido",

'invalid_channel_permissions' =>
"Você não possui permissão para publicar no canal fornecido e não poderá efetuar importações nele.",

'invalid_data_source_submitted' =>
"Origem inválida dos Dados fornecidos.  Por favor assegure-se que você selecionou uma origem de dados válida e preencheu os campos necessários.",

'invalid_filename_fullpath' =>
"Nome de arquvo e caminho inválidos. Por favor tenha certeza que é um caminho ABSOLUTO no servidor.",

'invalid_filename_not_found' =>
"Caminho fornecido inválido.  O arquivo não foi localizado no servidor.",

'invalid_remote_url_not_found' =>
"URL remota inválida.  Uma tentativa de obter o arquivo resultou em falha.",

'problem_retreiving_file_data' =>
"Problema ao recuperar os dados do arquivo.",

'source_data_contained_invalid_data' =>
"A origem dos arquivos dos dados contem nenhum dado ou dados inválidos.",

'failure_downloading_remote_file' =>
"Falha em baixar arquivo remoto do servidor.",

'unable_to_create_unzipping_directory' =>
"Não foi possível criar uma pasta para descomprimir seu arquivo na pasta de cache do ExpressionEngine, por favor verifique suas permissões de escrita na pasta.",

'unable_to_create_importer_directory' =>
"Não foi possível criar uma pasta para armazenar os dados de importação napasta de cache do ExpressionEngine, por favor verifque suas permissões de escrita na pasta.",

'error_unable_to_read_data_file' =>
"Erro:  Não foi possível ler o arquivo dos dados.",

'importer_memory_usage_warning' =>
"Importer determinou que a origem dos dados é muito grande para que seu servidor processe.",

// --------------------------------------------
//  Configurações do Formulário - GLOBAL 
// --------------------------------------------

'invalid_data_received' =>
"os dados recebidos da sua origem de dados não sào válidos e não foi possível efetuar a sua leitura.",

'invald_content_type_settings' =>
"Configurações de Tipo de Conteúdo Inválidos",

'importer_notifications' =>
"Avisos",

'importer_notification_emails' =>
"E-mails",

'importer_notification_emails_subtext' =>
"Separe múltiplos com vírgulas ou quebras de linha.",

'importer_notification_cc' =>
"CC",

'importer_notification_cc_subtext' =>
"Separe múltiplos com vírgulas ou quebras de linha.",

'importer_notification_subject' =>
"Assunto",

'importer_notification_message' =>
"Mensagem",

'importer_notification_message_subtext' =>
"As seguintes variáveis estão disponíveis:<br />
{author_ids}, {channel_id}, {site_id},<br />
{profile_name}, {content_type}, {datatype},<br />
{emails}, {email_cc},<br />
{import_date}, {import_ip_address}, {import_location},<br />
{last_import_date}, {run_time},<br />
{start_or_end},<br />
{total_inserted}, {total_updated}, {entries_deleted}",

'importer_notification_rules' =>
"Regras",

'importer_notification_rules_disabled' =>
"Não enviar e-mail",

'importer_notification_rules_start' =>
"Enviar e-mail no início da importação",

'importer_notification_rules_end' =>
"Enviar e-mail no final da Importação",

'importer_notification_rules_start_end' =>
"Enviar e-mail no início e no final da importação",

'importer_element' =>
"Elemento",

'importer_default_value' =>
"Valor Padrão",

'importer_modal_save' =>
"Salvar",

'save_and_do_import' =>
"Salvar e Efetuar Importação",

// --------------------------------------------
//  Log de Importação
// --------------------------------------------

'import_log' =>
"Log de Importação",

'import_date' =>
"Data",

'import_details' =>
"Detalhes",

'no_imports_logged' =>
"Nenhuma importação Logada",

'importer_delete_logs' =>
"Deletar Logs",

"logs_delete_question" =>
"Tem certeza que deseja excluir %i% logs?",

"log_delete_question" =>
"Tem certeza que deseja excluir este log?",

'importer_logs_delete_confirm' =>
"Confirmação de Exclusão de Logs de Importação",

'importer_log_deleted' =>
"Logs de Importação Excluídos",

'importer_logs_deleted' =>
"%i% Logs de Importação Excluídos",

// --------------------------------------------
//  teste de conexão AJAX
// --------------------------------------------

'error_ajax_request' =>
"Erro: A Solicitação AJAX não foi efetuada com sucesso",

'error_importer_ftp_test' =>
"Erro: A conexão FTP/SFTP Falhou",

'invalid_or_missing_fields' =>
"Campos Inválidos ou Perdidos. Por favor tenha certeza que TODOS os campos foram preenchidos.",

'ftp_file_does_not_exist' =>
"FTP: O Arquivo não existe",

'ftp_ssl_not_supported' =>
"As Conexões SSL não são suportadas no seu Servidor.",

'ftp_unable_to_connect' =>
"Não foi possível criar uma conexão no Servidor FTP.",

'ftp_unable_to_login' =>
"Não foi possível logar no seu servidor FTP com as credenciais que você forneceu.",

'ftp_bad_local_path' =>
"Caminho inválido fornecido para baixar os arquivos via FTP.",

'ftp_local_path_not_writable' =>
"O caminho do local fornecido para baixar o(s) arquivo(s) não possui permissão de escrita.",

'ftp_bad_local_path' =>
"O caminho local fornecido não está sendo aceito pela Biblioteca FTP.",

'ftp_bad_remote_path' =>
"O caminho remoto fornecido não é válido.",

'ftp_bad_remote_file' =>
"O caminho remoto fornecido não é válido.",

'ftp_unable_to_download' =>
"Não foi possível baixar o arquivo no seu servidor FTP.",

'ftp_file_does_not_exist' =>
"O arquivo no servidor FTP parece não existir.",

'error_sftp_connection_failure' =>
"Falha na conexão SFTP do Servidor",

'error_sftp_file_failure' =>
"Não foi possível encontrar o arquivo no servidor.",

'success_importer_ftp_test' =>
"Teste de Conexão efetuado com SUCESSO!",

'connection_test_successful_file_found' =>
"O teste de conexão foi realizado com sucesso e o arquivo foi localizado no servidor.",

'modal_close_button' =>
"Fechar",

'beginning_connection_test' =>
"Iniciando Teste de Conexão",

'connection_test_underway_please_standby' =>
"Seu teste de conexão FTP/SFTP está a caminho.  Por favor seja paciente, pode levar até 15 segundos para a confirmação.",

'modal_press_esc_to_close' =>
"Pressione a tecla ESC para fechar a janela.",

'modal_press_esc_to_close_and_discard' =>
"Pressione a tecla ESC para fechar a janela e descartar as alterações",

'additional_data_type_fields' =>
"Campos de Dados Adicionais",

// --------------------------------------------
//  Configurações de Envio de Erros - GERAL
// --------------------------------------------

'error_invalid_notification_emails' =>
"Erro: Notificação inválida de E-mails fornecido",

'error_invalid_notification_cc' =>
"Erro: Notificação inválida de CC E-mails fornecido",

'error_invalid_notification_subject_message_required' =>
"Erro:  Se enviar uma notificação, um assunto e mensagens são necessárias.",

// --------------------------------------------
//  Importação via Cron
// --------------------------------------------

'successful_import' =>
"Importação efetuada com sucesso",

'import_was_successfully_completed' =>
"A importação foi efetuada completamente e sem erros.",

'failure_of_import' =>
"Falha de Importação",

'importer_invalid_batch' =>
"Solicitação de Processamento de Lote Inválido",

'batch_import_started' =>
"Importação Pocessada em Lote iniciada com sucesso",

'no_batches_to_process' =>
"Sem processamentos em lote para executar",

// --------------------------------------------
//  Estatísticas
// --------------------------------------------

'entries_inserted' =>
"Registros Inseridos",

'entries_updated' =>
"Registros Atualizados",

'entries_deleted' =>
"Registros Excluídos",

'deleted_entries' =>
"Registros Excluídos",

'total_entries' =>
"Total de Registros",

'entry_ids' =>
"IDs dos Registros",

'inserted_entry_ids' =>
"IDs dos Registros Inseridos",

'updated_entry_ids' =>
"IDs dos Registros Atualizados",

'author_ids' =>
"IDs dos Autores",

'debugging' =>
"Debugging",

'start_time' =>
"Data Inicial",

'end_time' =>
"Data Final",

'run_time' =>
"Horário de Execução",

'site_id' =>
"ID do Site",

'number_of_queries' =>
"Número de Queries",

// -------------------------------------
//	Importação Básica
// -------------------------------------

'batch_importer' =>
"Importação em Lote",

'importer_batch_purpose' =>
"Isto irá efetuar uma importação em lotes e você será avisado quando o processo estiver completado.
Clique 'Executar Importação' para iniciar.",

'importer_perform_import' =>
"Executar Importação",

"performing_import_for_batch_" =>
"Executar importação para o lote: ",

'importer_percent_completed' =>
"Percentual Completado",

'importer_number_of_batches_imported' =>
"Número de Lotes Importados",

'importer_pause' =>
"Pausa",

'importer_resume' =>
"Resumo",

'return_to_importer_homepage' =>
"Você pode agora retornar à Página Inicial da Homepage.",

'importer_import_complete' =>
"Importação Completa",

'importer_invalid_values' =>
"Valores Inválidos Recebidos",

'importer_batch_number_' =>
"Lote #",

//----------------------------------------
//  Erros
//----------------------------------------

'invalid_request' =>
"Solicitação Inválida",

'importer_module_disabled' =>
"O módulo de importação está atualmente desativado.  Por favor tenha certeza que que ele está instalado e atualizado, ao ir no painel de controle do módulo na área de módulos do ExpressionEngine",

'disable_module_to_disable_extension' =>
"Para desativar esta extensão, você deve desativar seu <a href='%url%'>módule</a> correspondente.",

'enable_module_to_enable_extension' =>
"Para ativar esta extensão, você deve instalar seu <a href='%url%'>módule</a> correspondente.",

'cp_jquery_requred' =>
"A extensão 'jQuery para o Painel de Controle' deve estar <a href='%extensions_url%'>ativada</a> para utilizar este módulo.",

//----------------------------------------
//  Rotina de atualização
//----------------------------------------

'update_importer_module' =>
"Atualizar módulo de importação",

'importer_update_message' =>
"Você recentemente subiu uma nova versão do Importer, por favor clique aqui para executar o script de atualização.",

"update_successful" =>
"O módulo foi atualizadop com sucesso.",

"update_failure" =>
"Ocorreu um erro ao tentar atualizar seu módulo para a última versão.",

'required_field_was_empty' =>
"Um campo necessário foi deixado em branco, por favor retorne e preencha todo os campos.",


// END
''=>''
);
?>