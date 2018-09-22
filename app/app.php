<?php
    date_default_timezone_set('America/Los_Angeles');
    require_once __DIR__."/../vendor/autoload.php";
    require_once __DIR__."/../src/ProfitCalculator.php";

    $app = new Silex\Application();

    $server = 'mysql:host=localhost:8889;dbname=file-processor';
    $username = 'root';
    $password = 'root';
    $DB = new PDO($server, $username, $password);

    $app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/../views'));
    $app['debug'] = true;
    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();

    $app->get("/", function() use ($app) {
        $blank_form = array();
        return $app['twig']->render('home.html.twig', array('blank_form' => $blank_form));
    });

    $app->post("/upload-csv", function() use ($app) {
        $file = $_POST['file'];
        $blank_form = array();
        if (!$file || strtolower(pathinfo($file, PATHINFO_EXTENSION)) != 'csv') {
            array_push($blank_form, "empty");
            return $app['twig']->render('home.html.twig', array('blank_form' => $blank_form));
        } else {
            return $app['twig']->render('output.html.twig');
        }
    });
    
    
    return $app;