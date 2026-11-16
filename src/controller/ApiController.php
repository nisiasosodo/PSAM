<?php
    namespace App\Controller;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    class ApiController extends Controller{

        public function search(Request $request, Response $response){
            $albums = json_decode(file_get_contents(__DIR__ . '/../../data/albums.json'), true);
            $query = $request->getQueryParams()['q'];

            if(!$query){
                $response->getBody()->write(
                    json_encode([
                        'error' => 'Invalid query parameter'
                    ])
                );
                return $response
                    ->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            if ($query){
                $albums = array_values(array_filter($albums, function($album) use ($query){
                    return strpos($album['title'], $query) !== false ||
                    strpos($album['artist'], $query) !== false;
            }));
            }
            $response->getBody()->write(
                json_encode($albums)
                );
            return $response
                ->withHeader('Content-Type', 'application/json');
        }


        public function calculate(Request $request, Response $response){
            $queryParams = $request->getQueryParams();

            foreach (['a', 'b', 'operation'] as $requiredParam) {
                if (!array_key_exists($requiredParam, $queryParams)) {
                    return $this->jsonResponse($response, ['error' => "Brakuje parametru: {$requiredParam}"], 400);
                }
            }

            if (!is_numeric($queryParams['a']) || !is_numeric($queryParams['b'])) {
                return $this->jsonResponse($response, ['error' => 'Parametry a, b muszą być liczbami'], 400);
            }

            $a = (float) $queryParams['a'];
            $b = (float) $queryParams['b'];
            $operation = $queryParams['operation'];

            if (!is_string($operation) || !in_array($operation, ['add', 'sub', 'mul', 'div'], true)) {
                return $this->jsonResponse($response, ['error' => 'Nieznana operacja'], 400);
            }

            if ($operation === 'div' && $b === 0.0) {
                return $this->jsonResponse($response, ['error' => 'Nie wolno dzielić przez 0.'], 400);
            }

            switch ($operation) {
                case 'add':
                    $result = $a + $b;
                    break;
                case 'sub':
                    $result = $a - $b;
                    break;
                case 'mul':
                    $result = $a * $b;
                    break;
                case 'div':
                    $result = $a / $b;
                    break;
            }

            return $this->jsonResponse($response, ['result' => $result]);
        }

        private function jsonResponse(Response $response, array $data, int $status = 200)
        {
            $response->getBody()->write(json_encode($data));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        }
        
    }