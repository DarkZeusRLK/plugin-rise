<?php

// Garante que a variável $routes existe (Segurança do CodeIgniter 4)
if (!isset($routes)) {
  $routes = \Config\Services::routes(true);
}

// Grupo de rotas do plugin
$routes->group('bombeiros', ['namespace' => 'Bombeiros\Controllers'], function ($routes) {

  // === ROTAS PRINCIPAIS (GET) ===
  $routes->get('/', 'Bombeiros::index');
  $routes->get('index', 'Bombeiros::index');

  // === ROTAS ESPECÍFICAS JÁ MAPEADAS (Sua lista) ===
  $routes->post('salvar', 'Bombeiros::salvar');
  $routes->post('lista_chamada', 'Bombeiros::lista_chamada');
  $routes->post('salvar_presenca', 'Bombeiros::salvar_presenca');
  $routes->get('lista_pagamentos', 'Bombeiros::lista_pagamentos');
  $routes->get('financeiro_resumo', 'Bombeiros::financeiro_resumo');
  $routes->post('baixar_pagamento', 'Bombeiros::baixar_pagamento');
  $routes->post('importar_csv', 'Bombeiros::importar_csv');
  $routes->post('deletar', 'Bombeiros::deletar');
  $routes->get('lista_responsaveis', 'Bombeiros::lista_responsaveis');
  $routes->post('salvar_responsavel', 'Bombeiros::salvar_responsavel');
  $routes->post('deletar_responsavel', 'Bombeiros::deletar_responsavel');
  $routes->post('buscar_dados_comprovante', 'Bombeiros::buscar_dados_comprovante');
  $routes->post('gerar_comprovante', 'Bombeiros::gerar_comprovante');
  // Rotas com parâmetros (ID)
  $routes->get('baixar_comprovante/(:num)', 'Bombeiros::baixar_comprovante/$1');
  $routes->get('visualizar_comprovante/(:num)', 'Bombeiros::visualizar_comprovante/$1');

  // Rotas de Unidade
  $routes->post('buscar_unidade', 'Bombeiros::buscar_unidade');
  $routes->post('salvar_unidade', 'Bombeiros::salvar_unidade');
  $routes->post('deletar_unidade', 'Bombeiros::deletar_unidade');

  // === ROTAS DE INTELIGÊNCIA ARTIFICIAL ===
  $routes->post('upload_e_ler_ia', 'Bombeiros::upload_e_ler_ia');
  $routes->post('processar_arquivo_word', 'Bombeiros::processar_arquivo_word');

  // === ROTAS CORINGA (SEGURANÇA CONTRA ERRO 404) ===
  // Se o CodeIgniter não achar a rota acima, ele tenta encaixar aqui.
  // Isso resolve o problema de "Can't find a route for..."

  // Para qualquer POST (ex: bombeiros/minha_nova_funcao)
  $routes->post('(:segment)', 'Bombeiros::$1');

  // Para qualquer GET simples
  $routes->get('(:segment)', 'Bombeiros::$1');

  // Para GET com parâmetros (ex: bombeiros/funcao/123)
  $routes->get('(:segment)/(:any)', 'Bombeiros::$1/$2');
});