<?php
//kontroler bazowy, który będzie dziedziczony przez wszystkie kontrolery
namespace App\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;
abstract class Controller
{
    protected $containerInterface;
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
    }
    protected function render(
        Response $response,
        $template,
        $data = []
        ) {
            $html = $this->containerInterface
                ->get('view')
                ->render($template, $data);
            $response->getBody()->write($html);
            return $response;
    }
}