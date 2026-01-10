<?php

defined('PLUGINPATH') or exit('No direct script access allowed');

/*
Plugin Name: Controle Bombeiros Mirins
Description: Gestão de alunos, mensalidades e análise de dados.
Version: 1.0.0
Requires at least: 2.8
*/

app_hooks()->add_filter('app_filter_staff_left_menu', function ($sidebar_menu) {
  $lang_name = "Bombeiros";
  if (function_exists('lang')) {
    $translated = lang('bombeiros');
    if ($translated && $translated !== 'bombeiros') {
      $lang_name = $translated;
    }
  }

  $sidebar_menu["bombeiros"] = array(
    "name" => $lang_name,
    "url" => "bombeiros",
    "class" => "fa-fire",
    "position" => 3
  );

  return $sidebar_menu;
});

if (function_exists('db_connect')) {
  try {
    $db = db_connect();
    if ($db) {
      $dbprefix = $db->getPrefix();

      // 1. Tabela siamesa_unidades
      $table_name = $dbprefix . 'siamesa_unidades';
      if (!$db->tableExists($table_name)) {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nome_unidade` varchar(255) NOT NULL COMMENT 'Nome da unidade',
          `cidade` varchar(255) NOT NULL COMMENT 'Cidade da unidade',
          `endereco` varchar(500) DEFAULT NULL COMMENT 'Endereço completo',
          `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
          `deleted` tinyint(1) DEFAULT 0,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_status` (`status`),
          KEY `idx_cidade` (`cidade`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->query($sql);
      }


      $table_name = $dbprefix . 'siamesa_responsaveis';
      if (!$db->tableExists($table_name)) {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nome` varchar(255) NOT NULL,
          `cpf` varchar(20) DEFAULT NULL,
          `whats` varchar(20) DEFAULT NULL,
          `celular` varchar(20) DEFAULT NULL,
          `email` varchar(255) DEFAULT NULL,
          `endereco` varchar(500) DEFAULT NULL,
          `deleted` tinyint(1) DEFAULT 0,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_cpf` (`cpf`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->query($sql);
      }


      $table_name = $dbprefix . 'siamesa_alunos';
      if (!$db->tableExists($table_name)) {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `unidade_id` int(11) NOT NULL COMMENT 'ID da unidade (FK)',
          `responsavel_id` int(11) NOT NULL COMMENT 'ID do responsável (FK)',
          `nome_aluno` varchar(255) NOT NULL,
          `nascimento_aluno` date DEFAULT NULL,
          `rg_aluno` varchar(50) DEFAULT NULL,
          `cpf_aluno` varchar(20) DEFAULT NULL,
          `turma` varchar(50) DEFAULT NULL COMMENT 'Horário da turma',
          `quer_camisa` tinyint(1) DEFAULT 0,
          `tamanho_camisa` varchar(10) DEFAULT NULL,
          `tamanho_camiseta` varchar(10) DEFAULT NULL,
          `valor_mensalidade` decimal(10,2) DEFAULT 150.00,
          `data_matricula` date DEFAULT NULL,
          `data_inicio` date DEFAULT NULL,
          `status` enum('Ativo','Cancelado') DEFAULT 'Ativo',
          `deleted` tinyint(1) DEFAULT 0,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_unidade` (`unidade_id`),
          KEY `idx_responsavel` (`responsavel_id`),
          KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->query($sql);
      }


      $table_name = $dbprefix . 'siamesa_cobrancas';
      if (!$db->tableExists($table_name)) {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `aluno_id` int(11) NOT NULL COMMENT 'ID do aluno (FK)',
          `vencimento` date NOT NULL,
          `valor` decimal(10,2) NOT NULL,
          `competencia` varchar(20) DEFAULT NULL COMMENT 'Mês/Ano (MM/YYYY)',
          `status` enum('Pendente','Pago','Cancelado') DEFAULT 'Pendente',
          `tipo` varchar(50) DEFAULT 'Mensalidade' COMMENT 'Tipo: Mensalidade, Camiseta, etc.',
          `data_pagamento` datetime DEFAULT NULL,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_aluno` (`aluno_id`),
          KEY `idx_vencimento` (`vencimento`),
          KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->query($sql);
      }


      $table_name = $dbprefix . 'siamesa_presenca';
      if (!$db->tableExists($table_name)) {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `aluno_id` int(11) NOT NULL COMMENT 'ID do aluno (FK)',
          `data_aula` date NOT NULL,
          `status` tinyint(1) DEFAULT 0 COMMENT '0=Falta, 1=Presente',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_aluno` (`aluno_id`),
          KEY `idx_data` (`data_aula`),
          UNIQUE KEY `unique_aluno_data` (`aluno_id`, `data_aula`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->query($sql);
      }


      $table_name = $dbprefix . 'siamesa_comprovantes';
      if (!$db->tableExists($table_name)) {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `numero_comprovante` varchar(50) DEFAULT NULL COMMENT 'Nº do comprovante',
          `data_emissao` date DEFAULT NULL COMMENT 'Data de emissão do comprovante',
          `responsavel_id` int(11) NOT NULL COMMENT 'ID do responsável (FK)',
          `responsavel_nome` varchar(255) DEFAULT NULL COMMENT 'Nome do responsável',
          `responsavel_cpf` varchar(20) DEFAULT NULL COMMENT 'CPF do responsável formatado',
          `aluno_id` int(11) NOT NULL COMMENT 'ID do aluno (FK)',
          `aluno_nome` varchar(255) DEFAULT NULL COMMENT 'Nome do aluno',
          `aluno_nome_adicional` varchar(255) DEFAULT NULL COMMENT 'Nome do segundo aluno (se houver)',
          `mensalidade_numero` tinyint(4) DEFAULT NULL COMMENT '1=1º Mensalidade, 2=2º, etc. (1-6)',
          `valor` decimal(10,2) NOT NULL COMMENT 'Valor em R$',
          `forma_pagamento` enum('BOLETO','CRÉDITO','DÉBITO','PIX') DEFAULT NULL COMMENT 'Forma de pagamento',
          `conferido_por` varchar(255) DEFAULT NULL COMMENT 'Nome de quem conferiu',
          `data_conferencia` date DEFAULT NULL COMMENT 'Data da conferência',
          `cobranca_id` int(11) DEFAULT NULL COMMENT 'ID da cobrança relacionada (FK)',
          `arquivo_path` varchar(500) DEFAULT NULL COMMENT 'Caminho do arquivo PDF gerado',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `deleted` tinyint(1) DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `idx_responsavel` (`responsavel_id`),
          KEY `idx_aluno` (`aluno_id`),
          KEY `idx_cobranca` (`cobranca_id`),
          KEY `idx_numero` (`numero_comprovante`),
          KEY `idx_data_emissao` (`data_emissao`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->query($sql);
      }
    }
  } catch (\Exception $e) {
    log_message('error', 'Erro ao criar tabelas do plugin Bombeiros: ' . $e->getMessage());
  }
}