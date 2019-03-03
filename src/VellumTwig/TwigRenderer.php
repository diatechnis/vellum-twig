<?php

namespace VellumTwig;

use Vellum\Contracts\Components\ComponentInterface;
use Vellum\Contracts\FetchDataInterface;
use Vellum\Path\TemplatePathInterface;

class TwigRenderer implements \Vellum\Contracts\Renderers\RenderInterface
{
    /** @var \Twig\Environment */
    private $twig;
    /** @var TemplatePathInterface|null */
    private $template_path_resolver;

    public function __construct(
        \Twig\Environment $twig,
        TemplatePathInterface $path_resolver
    ) {
        $this->twig = $twig;
        $this->template_path_resolver = $path_resolver;
    }

    public function render(ComponentInterface $component): string
    {
        $arguments = $component->getArguments();

        $data = ['arguments' => $arguments];

        if ($component instanceof FetchDataInterface) {
            $data = array_merge($data, $component->getData());
        }

        $template_path = $this->template_path_resolver->resolve(
            $component,
            false
        );

        return $this->twig->render($template_path, $data);
    }
}
