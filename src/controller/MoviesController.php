<?php

namespace App\Controller;

use JsonException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use RuntimeException;
use UnexpectedValueException;

class MoviesController extends Controller
{
    public function allMovies(Request $request, Response $response){
        return $this->jsonResponse($response, $this->loadMovies());
    }

    public function movieById(Request $request, Response $response, array $args){
        $id = filter_var($args['id'], FILTER_VALIDATE_INT);

        if ($id === false || $id < 1 || $id > 4) {
            throw new HttpNotFoundException($request, "Nie znaleziono filmu o podanym ID");
        }

        foreach ($this->loadMovies() as $movie) {
            if ((int) $movie['id'] === $id) {
                return $this->jsonResponse($response, $movie);
            }
        }
    }

    //wyszukiwanie po tytule
    public function searchMovies(Request $request, Response $response){
        $query = $request->getQueryParams()['title'] ?? null;

        if (!is_string($query) || trim($query) === '') {
            return $this->jsonResponse($response, ['error' => 'Wymagane jest podanie tytułu filmu'], 400);
        }

        $query = trim($query);
        $movies = array_values(array_filter(
            $this->loadMovies(),
            static fn (array $movie): bool => stripos($movie['title'], $query) !== false
        ));

        return $this->jsonResponse($response, $movies);
    }

    
    private function loadMovies(){
        $file = __DIR__ . '/../../data/movies.json';
        $content = file_get_contents($file);

        if ($content === false) {
            throw new RuntimeException('Nie można odczytać danych z pliku.');
        }

        try {
            $movies = json_decode($content, true);
        } catch (JsonException $e) {
            throw new RuntimeException('Nie można zdekodować danych JSON: ' . $e->getMessage());
        }

        if (!is_array($movies) || array_is_list($movies) === false) {
            throw new UnexpectedValueException('Dane filmów w pliku JSON nie są tablicą.');
        }

        return $movies;
    }

    private function jsonResponse(Response $response, array $data, int $status = 200){
        $response->getBody()->write(json_encode($data));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
