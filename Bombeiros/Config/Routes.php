<?php

$routes->group('bombeiros', ['namespace' => 'Bombeiros\Controllers'], function ($routes) {
  $routes->get('/', 'Bombeiros::index');
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
  $routes->get('baixar_comprovante/(:num)', 'Bombeiros::baixar_comprovante/$1');
  $routes->get('visualizar_comprovante/(:num)', 'Bombeiros::visualizar_comprovante/$1');
  $routes->post('buscar_unidade', 'Bombeiros::buscar_unidade');
  $routes->post('salvar_unidade', 'Bombeiros::salvar_unidade');
  $routes->post('deletar_unidade', 'Bombeiros::deletar_unidade');
});