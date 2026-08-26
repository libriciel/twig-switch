<?php
declare(strict_types=1);

namespace buzzingpixel\twigswitch\Test;

use buzzingpixel\twigswitch\SwitchTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;

class SwitchTwigExtensionTest extends TestCase
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    private function render(string $template, array $context = []): string
    {
        $twig = new Environment(
            new ArrayLoader(['template' => $template])
        );
        $twig->addExtension(new SwitchTwigExtension());

        return $twig->render('template', $context);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function testSwitchMatchesCase(): void
    {
        $result = $this->render(
            <<<'TWIG'
{% switch value %}
{% case "foo" %}
foo
{% case "bar" %}
bar
{% default %}
default
{% endswitch %}
TWIG,
            ['value' => 'bar']
        );

        $this->assertSame('bar', trim($result));
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function testSwitchUsesDefault(): void
    {
        $result = $this->render(
            <<<'TWIG'
{% switch value %}
{% case "foo" %}
foo
{% default %}
default
{% endswitch %}
TWIG,
            ['value' => 'other']
        );

        $this->assertSame('default', trim($result));
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function testSwitchWithoutDefaultReturnsNothing(): void
    {
        $result = $this->render(
            <<<'TWIG'
{% switch value %}
{% case "foo" %}
foo
{% endswitch %}
TWIG,
            ['value' => 'other']
        );

        $this->assertSame('', trim($result));
    }

    /**
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function testCaseSupportsMultipleValues(): void
    {
        $template = <<<'TWIG'
{% switch value %}
{% case "foo" or "bar" %}
matched
{% default %}
default
{% endswitch %}
TWIG;

        $this->assertSame(
            'matched',
            trim($this->render($template, ['value' => 'foo']))
        );
        $this->assertSame(
            'matched',
            trim($this->render($template, ['value' => 'bar']))
        );
        $this->assertSame(
            'default',
            trim($this->render($template, ['value' => 'baz']))
        );
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function testWhitespaceBetweenSwitchAndFirstCase(): void
    {
        $result = $this->render(
            <<<'TWIG'
{% switch value %}


    {% case "foo" %}
foo
{% default %}
default
{% endswitch %}
TWIG,
            ['value' => 'foo']
        );

        $this->assertSame('foo', trim($result));
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function testSwitchAcceptsExpression(): void
    {
        $result = $this->render(
            <<<'TWIG'
{% switch prefix ~ suffix %}
{% case "foobar" %}
matched
{% default %}
default
{% endswitch %}
TWIG,
            [
                'prefix' => 'foo',
                'suffix' => 'bar',
            ]
        );

        $this->assertSame('matched', trim($result));
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function testCaseSupportsNumericValues(): void
    {
        $result = $this->render(
            <<<'TWIG'
{% switch value %}
{% case 1 %}
one
{% case 2 %}
two
{% default %}
default
{% endswitch %}
TWIG,
            ['value' => 2]
        );

        $this->assertSame('two', trim($result));
    }

    /**
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function testInvalidSwitchSyntaxThrowsSyntaxError(): void
    {
        $this->expectException(SyntaxError::class);

        $this->render(
            <<<'TWIG'
{% switch value %}
{% unexpected %}
foo
{% endswitch %}
TWIG,
            ['value' => 'foo']
        );
    }
}
