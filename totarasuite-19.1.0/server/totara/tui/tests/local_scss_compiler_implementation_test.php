<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package totara_tui
 */

defined('MOODLE_INTERNAL') || die();

class totara_tui_local_scss_compiler_implementation_test extends \core_phpunit\testcase {
    /**
     * Try to normalize some of the formatting of the generated CSS.
     *
     * @param string $css
     * @return string
     */
    private function normalize(string $css): string {
        return trim(preg_replace('/\s+/', ' ', $css));
    }

    /**
     * Get the compiled CSS
     *
     * @param testable_tui_scss_compiler_implementation $compiler
     * @param string $code
     * @return string
     */
    private function compile(testable_tui_scss_compiler_implementation $compiler, string $code): string {
        return $this->normalize($compiler->compileString($code)->getCss());
    }

    /**
     * Get fake content to use in tests
     *
     * @return string[]
     */
    private function get_output_fake_content(): array {
        return [
            '/dir/foo_noimport.scss' => '$col: red; .foo { $col: orange; color: rgba(blue, 0.5); }',
            '/dir/foo.scss' => '$col: red; .foo { $col: orange; color: rgba(blue, 0.5); } @import "bar"; @import "baz", "qux";',
            '/dir/bar.scss' => '$col: green;',
            '/dir/baz.scss' => '$col: pink; a { width: 3px; }',
            '/dir/qux.scss' => '$col: orangered;',

            '/dir/blocks.scss' =>
                '$v: 1;
                a { color: green; }
                @media screen (min-width 1200px) and print { div { index: 1; } }
                @supports (display: grid) { div { display: grid; } }
                @font-face { font-family: "Open Sans"; src: url("/"); }
                @keyframes { 0% { opacity: 0; } 100% { opacity: 1; } }
                @at-root { .im-root { color: red; } }
                @generic-directive { color: green; }',

            '/dir/mixins.scss' => '@mixin red { color: red; }',

            '/dir/include.scss' => ':root { @include red; }',

            '/dir/fns.scss' =>
                '$tui-transitions: (
                    \'form\': (
                        function: linear,
                        duration: 0s,
                    ),
                );

                @function transition($type, $property) {
                    $tv: map-get($tui-transitions, $type);
                    @return $property map-get($tv, \'function\') map-get($tv, \'duration\');
                }',
        ];
    }

    /**
     * Create a testable compiler instance.
     *
     * @return testable_tui_scss_compiler_implementation
     */
    private function create_compiler(): testable_tui_scss_compiler_implementation {
        require_once(__DIR__ . '/fixtures/testable_tui_scss_compiler_implementation.php');

        $compiler = new testable_tui_scss_compiler_implementation();

        $transform_mock_content = $this->getMockForAbstractClass(\ScssPhp\ScssPhp\Transforms\Transform::class);
        $transform_mock_content->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function (\ScssPhp\ScssPhp\Transforms\Resource $resource) {
                $code = $resource->getCode();
                $resource->setCode("sample { content: '" . str_replace(["'", "\n"], ["\\'", '\A'], $code) . "'; }");
            }));

        $transform_mock_prefix = $this->getMockForAbstractClass(\ScssPhp\ScssPhp\Transforms\Transform::class);
        $transform_mock_prefix->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function (\ScssPhp\ScssPhp\Transforms\Resource $resource) {
                $code = $resource->getCode();
                $resource->setCode("a { display: none; } $code");
            }));

        $compiler->register_transform('content', $transform_mock_content);
        $compiler->register_transform('prefix', $transform_mock_prefix);
        $compiler->register_transform('definitions_only', new \totara_tui\local\scss\transforms\definitions_only());
        $compiler->register_transform('output_only', new \totara_tui\local\scss\transforms\output_only());

        $compiler->set_fake_content('/dir', $this->get_output_fake_content());

        return $compiler;
    }

    /**
     * Test transform functionality works and is ordered correctly
     */
    public function test_simple_code_transform() {
        $compiler = $this->create_compiler();
        $compiler->set_fake_content('/dir', [
            '/dir/foo.scss' => '.foo { color: red }',
        ]);

        $this->assertEquals(
            'sample { content: "a { display: none; } .foo { color: red }"; }',
            $this->compile($compiler, "@import 'content!prefix!foo';")
        );
    }

    /**
     * Test 'output_only' transform (stripping SCSS vars)
     */
    public function test_output_only_transform() {
        $compiler = $this->create_compiler();

        $this->assertEquals(
            '.foo { color: rgba(0, 0, 255, 0.5); } a { color: blue; }',
            $this->compile($compiler, '$col: blue; @import "output_only!foo_noimport"; a { color: $col; }')
        );

        $this->assertEquals(
            '.foo { color: rgba(0, 0, 255, 0.5); } a { width: 3px; } a { color: azure; }',
            $this->compile($compiler, '$col: azure; @import "output_only!foo"; a { color: $col; }')
        );

        $this->assertEquals(
            $this->normalize(
                'a { color: green; }
                @media screen (min-width 1200px) and print { div { index: 1; } }
                @supports (display: grid) { div { display: grid; } }
                @font-face { font-family: "Open Sans"; src: url("/"); }
                @keyframes { 0% { opacity: 0; } 100% { opacity: 1; } }
                .im-root { color: red; }
                @generic-directive { color: green; }
                a { color: blue; fail: 0; }'
            ),
            $this->compile($compiler, '$v: 0; $col: blue; @import "output_only!blocks"; a { color: $col; fail: $v; }')
        );

        $src = '@import "output_only!fns"; button { transition: transition("form", color); }';
        $this->assertEquals(
            'button { transition: transition("form", color); }',
            $this->compile($compiler, $src)
        );

        // test mixin definition is not imported
        $src = '@mixin red { color: not-red; } @import "output_only!mixins"; a { @include red(); }';
        $this->assertEquals(
            'a { color: not-red; }',
            $this->compile($compiler, $src)
        );

        // check include() is allowed
        $src = '@mixin red { --color: red; } @import "output_only!include";';
        $this->assertEquals(
            ':root { --color: red; }',
            $this->compile($compiler, $src)
        );
    }

    /**
     * Test 'definitions_only' transform (stripping CSS properties)
     */
    public function test_definitions_only_transform() {
        $compiler = $this->create_compiler();

        $this->assertEquals(
            "a { color: orangered; }",
            $this->compile($compiler, '$col: blue; @import "definitions_only!foo"; a { color: $col; }')
        );

        $this->assertEquals(
            'a { v: 1; }',
            $this->compile($compiler, '$v: 0; @import "definitions_only!blocks"; a { v: $v; }')
        );

        $src = '@import "definitions_only!fns"; button { transition: transition("form", color); }';
        $this->assertEquals(
            'button { transition: color linear 0s; }',
            $this->compile($compiler, $src)
        );

        // test mixin definition is imported
        $src = '@mixin red { color: not-red; } @import "definitions_only!mixins"; a { @include red(); }';
        $this->assertEquals(
            'a { color: red; }',
            $this->compile($compiler, $src)
        );
    }

    /**
     * Regression test: an older implementation would remove variables from the function body,
     * breaking when you included a definitions_only version followed by an output_only version.
     */
    public function test_output_transform_functions() {
        $compiler = $this->create_compiler();

        $src = '@import "definitions_only!fns"; @import "output_only!fns"; button { transition: transition("form", color); }';
        $this->assertEquals(
            'button { transition: color linear 0s; }',
            $this->compile($compiler, $src)
        );
    }

    /**
     * Test a similar compilation to what tui_scss would do compiling a theme
     */
    public function test_theme_order() {
        $compiler = $this->create_compiler();
        $compiler->setImportPaths(['/dir']);
        $compiler->set_fake_content('/dir', [
            '/dir/theme_main/_variables.scss' => ':root { --color: red; } $color: red;',
            '/dir/theme_main/tui_bundle.scss' => ':root { --component-color: red; } .component { width: 100px; color: $color; }',
            '/dir/theme_custom/_variables.scss' => ':root { --color: blue; } $color: blue;',
            '/dir/theme_custom/tui_bundle.scss' => ':root { --component-color: blue; } .component { width: 200px; color: $color; }',
        ]);

        $this->assertEquals(
            ':root { --color: red; } :root { --component-color: red; } .component { width: 100px; color: blue; } ' .
            ':root { --color: blue; } :root { --component-color: blue; } .component { width: 200px; color: blue; }',
            $this->compile($compiler, '
                @import "definitions_only!theme_main/_variables";
                @import "definitions_only!theme_custom/_variables";
                @import "output_only!theme_main/_variables";
                @import "theme_main/tui_bundle";
                @import "output_only!theme_custom/_variables";
                @import "theme_custom/tui_bundle";
            ')
        );
    }

    /**
     * Test a similar compilation to what tui_scss would do compiling a component
     */
    public function test_component_order() {
        $compiler = $this->create_compiler();
        $compiler->set_fake_content('/dir', [
            '/dir/theme_main/_variables.scss' => ':root { --color: red; } $color: red;',
            '/dir/theme_custom/_variables.scss' => ':root { --color: blue; } $color: blue;',
            '/dir/component/_variables.scss' => ':root { --color: green; } $color: green;',
            '/dir/component/tui_bundle.scss' =>
                ':root { --component-color: blue; } $color: purple !default; .component { width: 200px; color: $color; }',
        ]);

        $this->assertEquals(
            ':root { --color: green; } :root { --component-color: blue; } .component { width: 200px; color: blue; }',
            $this->compile($compiler, '
                @import "definitions_only!component/_variables";
                @import "definitions_only!theme_main/_variables";
                @import "definitions_only!theme_custom/_variables";
                @import "output_only!component/_variables";
                @import "component/tui_bundle";
            ')
        );
    }

    /**
     * Test a similar compilation to what tui_scss (used to) do compiling CSS vars to extract for legacy browsers
     */
    public function test_theme_css_vars() {
        $compiler = $this->create_compiler();
        $compiler->set_fake_content('/dir', [
            '/dir/theme_main/_variables.scss' => ':root { --color: red; } $color: red;',
            '/dir/theme_custom/_variables.scss' => ':root { --color: blue; } $color: blue;',
        ]);

        $this->assertEquals(
            ':root { --color: red; } :root { --color: blue; }',
            $this->compile($compiler, '
                @import "output_only!theme_main/_variables";
                @import "output_only!theme_custom/_variables";
            ')
        );
    }

    /**
     * Test that invalid imports throw an exception.
     */
    public function test_invalid_import() {
        $compiler = $this->create_compiler();

        $this->assert_throws(
            \ScssPhp\ScssPhp\Exception\SassException::class,
            function () use ($compiler) {
                $this->compile($compiler, "@import 'some/invalid/file';");
            }
        );
    }

    /**
     * Assert that the provided callback throws an exception that is an instance of the provided class.
     *
     * @param string $exceptionClass Class name.
     * @param callable $callback Callback to invoke.
     */
    private function assert_throws(string $exceptionClass, callable $callback): void {
        try {
            $callback();
        } catch (\Throwable $exception) {
            $this->assertInstanceOf($exceptionClass, $exception, 'An unexpected exception was thrown');
            return;
        }
        $this->fail("Expected $exceptionClass to be thrown");
    }
}
