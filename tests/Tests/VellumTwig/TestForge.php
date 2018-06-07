<?php

namespace Tests\VellumTwig;

use Tests\VellumTwig\Components\Component;
use Vellum\Contracts\Components\RenderableInterface;
use Vellum\Contracts\Renderers\RenderInterface;
use Vellum\Forge\ForgeInterface;

class TestForge implements ForgeInterface
{
    /** @var RenderInterface */
    private $renderer;

    public function __construct(RenderInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function create(
        string $component_type,
        string $name,
        array $arguments
    ): RenderableInterface {
        return new Component($arguments, $this->renderer);
    }

    public function getRenderer(): RenderInterface
    {
        return $this->renderer;
    }
}
