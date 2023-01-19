<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Conia\Boiler\Engine;
use Conia\Boiler\Error\RendererException;
use Conia\Chuck\Psr\Factory;
use Conia\Chuck\Renderer\Renderer as RendererInterface;
use Conia\Chuck\Response;
use Throwable;
use Traversable;

/**
 * @psalm-import-type DirsInput from \Conia\Boiler\Engine
 */
class Renderer implements RendererInterface
{
    /** @psalm-param DirsInput $dirs */
    public function __construct(
        protected string|array $dirs,
        protected Factory $factory,
        protected bool $autoescape = true,
        protected array $defaults = [],
    ) {
    }

    public function render(mixed $data, mixed ...$args): string
    {
        if ($data instanceof Traversable) {
            $context = iterator_to_array($data);
        } elseif (is_array($data)) {
            $context = $data;
        } else {
            throw new RendererException('The template context must be an array or a Traversable');
        }

        try {
            $templateName = (string)$args[0];
            assert(!empty($templateName));
        } catch (Throwable) {
            throw new RendererException('The template must be passed to the renderer');
        }

        if (is_string($this->dirs)) {
            $this->dirs = [$this->dirs];
        } else {
            if (count($this->dirs) === 0) {
                throw new RendererException('T');
            }
        }

        $engine = $this->createEngine($this->dirs);

        return $engine->render($templateName, $context);
    }

    public function response(mixed $data, mixed ...$args): Response
    {
        $response = new Response($this->factory->response(
            (int)($args['statusCode'] ?? 200),
            (string)($args['reasonPhrase'] ?? ''),
        ), $this->factory);

        return $response
            ->header('Content-Type', (string)(($args['contentType'] ?? null) ?: 'text/html'))
            ->body($this->render($data, ...$args));
    }

    /** @psalm-param DirsInput $dirs */
    protected function createEngine(string|array $dirs): Engine
    {
        return new Engine($dirs, defaults: $this->defaults);
    }
}