<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week3', 'ddwt18', 'ddwt18');

/* Create Router instance */
$router = new \Bramus\Router\Router();

/* set credentials */
$cred = set_cred("ddwt18","ddwt18");

// Add routes here
$router->before('GET|POST|PUT|DELETE', '/API/.*', function() use($cred){
    // Validate authentication
    if (!check_cred($cred)){
        echo 'Authentication required.';
        http_response_code(401);
        die();
    }
    echo "Succesfully authenticated";
});

$router->mount('/API', function() use ($router, $db) {
    http_content_type('application/json');

    // will result in '/API/'
    $router->get('/', function() {
        echo 'API';
    });

    /* GET for reading all series */
    $router->get('/series', function() use($db) {
        // Retrieve and output information
        $array = get_series($db);
        echo json_encode($array);
    });

    /* GET for reading individual series */
    $router->get('/series/(\d+)', function($id) use($db) {
        // Retrieve and output information
        $array = get_serieinfo($db,$id);
        echo json_encode($array);
    });

    /* GET for removing individual series */
    $router->get('/series/delete/(\d+)', function($id) use($db) {
        // Remove and output information
        $array = remove_serie($db,$id);
        echo json_encode($array);
    });

    /* POST for adding individual series */
    $router->post('/series', function() use($db) {
        // add and output information
        $array = add_serie($db,$_POST);
        echo json_encode($array);
    });

    /* PUT for updating individual series */
    $router->put('/series/(\d+)', function($id) use($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        // update and output information
        $serie_info = $_PUT + ["serie_id" => $id];
        $array = update_serie($db,$serie_info);
        echo json_encode($array);
    });

    // custom 404 error
    $router->set404(function() {
        header('HTTP/1.1 404 Not Found');
        // ... do something special here
    });
});

/* Run the router */
$router->run();
