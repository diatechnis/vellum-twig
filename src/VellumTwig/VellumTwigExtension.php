<?php

namespace VellumTwig;

use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
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
    /** @var array */
    private $global_arguments;

    private function __construct(
        ClassPathInterface $class_resolver,
        TwigRenderer $renderer,
        array $global_arguments = []
    ) {
        $this->class_resolver = $class_resolver;
        $this->renderer = $renderer;
        $this->global_arguments = $global_arguments;
    }

    public function getFunctions(): array
    {
        $functions = [];

        $options = ['is_safe' => ['html']];

        $global_arguments = $this->global_arguments;

        foreach (['component', 'widget', 'section', 'layout'] as $item) {
            $component_closure = $this->getComponentClosure(
                $item,
                $global_arguments
            );

            $functions[] = new \Twig\TwigFunction(
                'vellum_' . $item,
                $component_closure,
                $options
            );
        }

        $resolve = $this->class_resolver;
        $render = $this->renderer;

        $functions[] = new \Twig\TwigFunction(
            'vellum_page',
            function (
                $url,
                $arguments = []
            ) use ($resolve, $render, $global_arguments) {
                $name = ucfirst(trim($url, '/ '));

                if (strpos($name, '/') !== false) {
                    print 'Need to rework vellum_page function'; exit;
                }

                $class = $resolve->resolve('page', $name);

                $arguments = \array_merge($global_arguments, $arguments);

                /** @var RenderableInterface $component */
                $component = new $class(
                    $arguments,
                    $render
                );

                return $component->render();
            },
            $options
        );

        return $functions;
    }

    private function getComponentClosure(
        string $component_type,
        array $global_arguments = []
    ): \Closure
    {
        //TODO Add the ability to render twig files without a component class?
        $resolve = $this->class_resolver;
        $render = $this->renderer;

        return function (
            $name,
            $arguments = []
        ) use ($resolve, $render, $component_type, $global_arguments) {
            $class = $resolve->resolve($component_type, $name);

            $arguments = \array_merge($global_arguments, $arguments);

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
        TemplatePathInterface $template_resolver,
        array $global_arguments = []
    ): Environment {
        $renderer = new TwigRenderer($twig, $template_resolver);
        $extension = new self($class_resolver, $renderer, $global_arguments);

        $twig->addExtension($extension);
        
        return self::addTemplatesToLoader($twig, $template_resolver);
    }

    private static function addTemplatesToLoader(
        Environment $twig,
        TemplatePathInterface $template_resolver
    ): Environment {
        $loader = $twig->getLoader();

        $filesystem = new FilesystemLoader(
            $template_resolver->getBasePath()
        );

        if (ChainLoader::class === \get_class($loader)) {
            $loader->addLoader($filesystem);
        } else {
            $twig->setLoader(new ChainLoader([$loader, $filesystem]));
        }

        return $twig;
    }
}
