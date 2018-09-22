<?php
    date_default_timezone_set('America/Los_Angeles');
    require_once __DIR__."/../vendor/autoload.php";
    require_once __DIR__."/../src/ProfitCalculator.php";

    $app = new Silex\Application();

    $app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/../views'));
    $app['debug'] = true;
    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();

    $app->get("/", function() use ($app) {
        $error_message = '';
        return $app['twig']->render('home.html.twig', array('error_message' => $error_message));
    });

    $app->post("/upload-csv", function() use ($app) {
        $filename = $_FILES['file']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($filename);
        $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $uploadFile = move_uploaded_file(!$_FILES['file']['tmp_name'], $target_file);
        if($fileType != "csv") {
            $error_message = "Sorry, only CSV files are allowed.";
            return $app['twig']->render('home.html.twig', array('error_message' => $error_message));
        } else if (!$filename) {
            $error_message = 'Please submit a file for processing.';
            return $app['twig']->render('home.html.twig', array('error_message' => $error_message));
        } else if ($_FILES['file']['error'] > 0) {
            $error_message = 'An error ocurred when uploading that file. Please try again.';
            return $app['twig']->render('home.html.twig', array('error_message' => $error_message));
        } else {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                $new_products_table = new ProfitCalculator($target_file);
                $csv_table_array = $new_products_table->parseCSV();
                return $app['twig']->render('output.html.twig', array('product_table' => $csv_table_array));
            } else {
                $error_message = 'Error uploading file, check destination is writeable.';
                return $app['twig']->render('home.html.twig', array('error_message' => $error_message));
            }

        }
    });
    
    
    return $app;