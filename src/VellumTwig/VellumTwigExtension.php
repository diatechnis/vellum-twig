<?php

namespace VellumTwig;

use Twig\Environment;
use Vellum\Contracts\Components\RenderableInterface;
use Vellum\Contracts\Renderers\RenderInterface;
use Vellum\Path\ClassPathInterface;
use Vellum\Path\TemplatePathInterface;

class VellumTwigExtension extends \Twig\Extension\AbstractExtension
{
    /** @var ClassPathInterface */
    private $class_resolver;
    /** @var RenderInterface */
    private $renderer;

    private function __construct(
        ClassPathInterface $class_resolver,
        TwigRenderer $renderer
    ) {
        $this->class_resolver = $class_resolver;
        $this->renderer = $renderer;
    }

    public function getFunctions(): array
    {
        $functions = [];

        $options = ['is_safe' => ['html']];

        foreach (['component', 'widget', 'section', 'layout'] as $item) {
            $component_closure = $this->getComponentClosure($item);

            $functions[] = new \Twig\TwigFunction(
                'vellum_' . $item,
                $component_closure,
                $options
            );
        }

        return $functions;
    }

    private function getComponentClosure(string $component_type): \Closure
    {
        //TODO Add the ability to render twig files without a component class?
        $resolve = $this->class_resolver;
        $render = $this->renderer;

        return function (
            $name,
            $arguments = []
        ) use ($resolve, $render, $component_type) {
            $class = $resolve->resolve($component_type, $name);

            /** @var RenderableInterface $component */
            $component = new $class(
                $arguments,
                $render
            );

            return $component->render();
        };
    }

    public static function extendTwig(
        Environment $twig,
        ClassPathInterface $class_resolver,
        TemplatePathInterface $template_resolver
    ): Environment {
        $renderer = new TwigRenderer($twig, $template_resolver);
        $extension = new self($class_resolver, $renderer);

        $twig->addExtension($extension);

        return $twig;
    }
}
