<?php
use Psr\Http\Message\ResponseInterface as Response; //to co serwer zwraca do klienta
use Psr\Http\Message\ServerRequestInterface as Request; //to co klient wysyła do serwera
use Slim\Factory\AppFactory;
use DI\Container;
use App\Controller\FirstController; 
use App\Controller\SecondController;
use App\Controller\SearchController;
use App\Controller\ApiController;
use App\Controller\ShopController;
use App\Controller\MoviesController;
use App\Controller\ExceptionController;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();

//require __DIR__ . '/../public/DI.php';
//$class1 = new FirstClass();
//$class2 = new SecondClass($class1);

//echo $class2->getValue(); //wyświetl "First"
//echo $class1->getValue(); //wyświetl "First"

AppFactory::setContainer($container);

$container->set('view', function() {
    return new Mustache\Engine([
        'loader' => new Mustache\Loader\FilesystemLoader(
            __DIR__ . '/../views', 
            ['extension' => '']
        )
    ]);
});

$app = AppFactory::create(); //create new instance of Slim application

$app->get('/', [FirstController::class, 'homepage']);

$app->get('/test', function (Request $request, Response $response) { //get do pobierania danych
    $response->getBody()->write("Hello, world!");
    return $response;
});

$app->get('/hello/jane', function (Request $request, Response $response) {
    $response->getBody()->write(sprintf("Hello, Jane!"));
    return $response;
});


//$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
//    $name = ucfirst($args['name']);
//    $response->getBody()->write(sprintf("Hello, %s!", $name));
//    return $response;
//});

$app->get('/hello', '\App\Controller\SecondController:hello');

$app->get('/default', '\App\Controller\ShopController:default');

$app->get('/details/{id:[0-4]+}', '\App\Controller\ShopController:details');

$app->get('/search', '\App\Controller\SearchController:search');

$app->get('/form', '\App\Controller\SearchController:form');

$app->get('/api', '\App\Controller\ApiController:search');

$app->get('/calculate', '\App\Controller\ApiController:calculate'); //kalkulator

//filmy
$app->get('/movies', '\App\Controller\MoviesController:allMovies');

$app->get('/movies/search', '\App\Controller\MoviesController:searchMovies');

$app->get('/movies/{id}', '\App\Controller\MoviesController:movieById');



$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $html = $this->get('view')->render('hello.html', [
        'name' => ucfirst($args['name'])
        ]);
    $response->getBody()->write($html);
    return $response;
});

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
//pierwszy parametr - czy szczegoły błędów mają być wyświetlane w przeglądarce, 
//drugi - czy mają być zapisywane w logach aplikacji, 
//trzeci - czy do logów mają być dołączane szczegóły błędów

$errorMiddleware->setErrorHandler(
        Slim\Exception\HttpNotFoundException::class,
        function (Psr\Http\Message\ServerRequestInterface $request) use ($container){
        $controller = new App\Controller\ExceptionController($container);
        return $controller->notFound($request);
        }
        );

$app->run();