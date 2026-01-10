<?php

defined('PLUGINPATH') or exit('No direct script access allowed');

/*
  Plugin Name: Analise_De_Dados_Dark
  Description: É um  plugin.
  Version: 1.0
  Requires at least: 3.0
  Author: DarkZeus
 */

//add menu item to left menu
app_hooks()->add_filter('app_filter_staff_left_menu', function ($sidebar_menu) {

    $sidebar_menu["demo_bombeiros"] = array(
        "name" => "demo_bombeiros_key",   // <--- O segredo está aqui (chave única)
        "url" => "demo",
        "class" => "fa-fire-extinguisher",
        "position" => 3
    );

    return $sidebar_menu;
});

//add admin setting menu item
app_hooks()->add_filter('app_filter_admin_settings_menu', function ($settings_menu) {
    $settings_menu["plugins"][] = array("name" => "demo", "url" => "demo_settings");
    return $settings_menu;
});

//install dependencies
register_installation_hook("Demo", function ($item_purchase_code) {
    /*
     * you can verify the item puchase code from here if you want. 
     * you'll get the inputted puchase code with $item_purchase_code variable
     * use exit(); here if there is anything doesn't meet it's requirements
     */

    $this_is_required = true;
    if (!$this_is_required) {
        echo json_encode(array("success" => false, "message" => "This is required!"));
        exit();
    }

    try {
        //run installation sql
        $db = db_connect('default');
        $dbprefix = get_db_prefix();

        // Cria a tabela
        $sql_query = "CREATE TABLE IF NOT EXISTS `" . $dbprefix . "demo_settings` (
            `setting_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
            `setting_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
            `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'app',
            `deleted` tinyint(1) NOT NULL DEFAULT '0',
            UNIQUE KEY `setting_name` (`setting_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $db->query($sql_query);

        // Verifica se houve erro na criação da tabela
        $error_table = $db->error();
        if (!empty($error_table) && isset($error_table['code']) && $error_table['code'] != 0) {
            throw new \Exception("Erro ao criar tabela: " . ($error_table['message'] ?? 'Erro desconhecido'));
        }

        // Prepara os dados para inserção
        $file_demo_value = serialize(array(
            'file_name' => 'demo_file61b4cfad0a1ec-Demo.png',
            'file_size' => '6233',
            'file_id' => null,
            'service_type' => null
        ));

        $setting_demo_value = 'Some value here';

        // Escapa os valores para SQL (usa addslashes que é compatível com todos os ambientes)
        $file_demo_escaped = addslashes($file_demo_value);
        $setting_demo_escaped = addslashes($setting_demo_value);

        // Usa INSERT IGNORE para evitar erro se os dados já existirem (mais eficiente)
        $sql_insert = "INSERT IGNORE INTO `" . $dbprefix . "demo_settings` (`setting_name`, `setting_value`, `deleted`) VALUES 
            ('file_demo', '" . $file_demo_escaped . "', 0),
            ('setting_demo', '" . $setting_demo_escaped . "', 0)";

        $db->query($sql_insert);

        // Verifica se houve erro (mesmo com IGNORE, pode haver outros erros de sintaxe)
        $error_insert = $db->error();
        if (!empty($error_insert) && isset($error_insert['code']) && $error_insert['code'] != 0) {
            throw new \Exception("Erro ao inserir dados padrão: " . ($error_insert['message'] ?? 'Erro desconhecido'));
        }

    } catch (\Exception $e) {
        // Log do erro e retorna mensagem de erro
        log_message('error', 'Erro na instalação do plugin Demo: ' . $e->getMessage());
        echo json_encode(array("success" => false, "message" => "Erro na instalação: " . $e->getMessage()));
        exit();
    }
});

//add setting link to the plugin setting
app_hooks()->add_filter('app_filter_action_links_of_Demo', function () {
    $action_links_array = array(
        anchor(get_uri("demo"), "Demo"),
        anchor(get_uri("demo_settings"), "Demo settings"),
    );

    return $action_links_array;
});

//update plugin
register_update_hook("Demo", function () {
    echo "Please follow this instructions to update:";
    echo "<br />";
    echo "Your logic to update...";
});

//uninstallation: remove data from database
register_uninstallation_hook("Demo", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "demo_settings`;";
    $db->query($sql_query);
});
