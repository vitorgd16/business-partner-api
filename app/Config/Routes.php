<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setTranslateURIDashes(false);
$routes->set404Override(function () {
    http_response_code(404);
    echo "404 - Not Found";
    die();
});

//Necessário para o funcionamento do CORS, aceitamos qualquer requisição para o método OPTIONS
$routes->options('(.+)', function () {
    http_response_code(200);
});

//Rotas da área da api
$routes->group('api', static function ($routes) {
    //Rotas V1 da api
    $routes->group('v1', static function ($routes) {
        //Rotas para controle dos parceiros de negócio do sistema
        $baseRoute = "business-partner";
        $routes->get($baseRoute, 'Admin\BusinessPartner::getData');
        $routes->get("$baseRoute/(:segment)", 'Admin\BusinessPartner::getData/$1');
        $routes->post($baseRoute, 'Admin\BusinessPartner::saveData');
        $routes->put("$baseRoute/(:segment)", 'Admin\BusinessPartner::saveData/$1');
        $routes->delete("$baseRoute/(:segment)", 'Admin\BusinessPartner::remove/$1');
        $baseRoute = null;
    });
});
