<?php
/**
 * @author mkelly
 * @date 4/17/18
 */

namespace Tests\VellumTwig;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Vellum\Path\SimpleClassPathResolver;
use Vellum\Path\SimpleTemplatePathResolver;
use VellumTwig\TwigRenderer;
use PHPUnit\Framework\TestCase;
use VellumTwig\VellumTwigExtension;

class TwigRendererTest extends TestCase
{
    /** @var Environment */
    private $twig;

    protected function setUp() {
        parent::setUp();

        $this->twig = new Environment(new ChainLoader([
            new ArrayLoader([
                'component_test' => '{{ vellum_component("Component") }}'
            ]),
            new FilesystemLoader([\dirname(__DIR__, 2) . '/templates'])
        ]));

        VellumTwigExtension::extendTwig(
            $this->twig,
            new SimpleClassPathResolver('Tests\\VellumTwig'),
            new SimpleTemplatePathResolver('.twig')
        );
    }

    public function test_component_renders()
    {
        $html = $this->twig->render('component_test');

        $this->assertEquals('<h2>Test Element</h2>', trim($html));
    }
}
