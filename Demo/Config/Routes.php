<?php

$routes->group('demo', ['namespace' => 'Demo\Controllers'], function ($routes) {
  $routes->get('/', 'Demo::index');
  $routes->post('salvar_template', 'Demo::salvar_template');
  $routes->post('salvar_ads', 'Demo::salvar_ads');
  $routes->get('obter_template', 'Demo::obter_template');
  $routes->get('(:any)', 'Demo::$1');
  $routes->post('(:any)', 'Demo::$1');
});

$routes->group('demo_settings', ['namespace' => 'Demo\Controllers'], function ($routes) {
  $routes->get('/', 'Demo_settings::index');
  $routes->get('(:any)', 'Demo_settings::$1');
  $routes->post('(:any)', 'Demo_settings::$1');
});

