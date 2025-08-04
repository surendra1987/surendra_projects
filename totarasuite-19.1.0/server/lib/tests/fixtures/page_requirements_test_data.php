<?php

return [
    'string_for_js' => [
        [
            'method' => 'string_for_js',
            'calls' => [
                ['course', 'core'],
                ['add', 'core'],
                ['add', 'core'],
            ],
            'expected_end_code' => [
                '"course":"Course"',
                '"add":"Add"',
            ],
        ]
    ],

    'js' => [
        [
            'method' => 'js',
            'calls' => [
                ['/lib/cookies.js', true],
                ['/lib/cookies.js'],
            ],
            'expected_top_of_body_code' => [
                '/lib/cookies.js',
            ],
            'expected_end_code' => [
                '/lib/cookies.js',
            ],
        ]
    ],

    'amd_js' => [
        [
            'method' => 'js_amd_inline',
            'calls' => [
                ['alert(1)'],
            ],
            'expected_end_code' => [
                'alert(1)',
            ],
        ]
    ],

    'js_init_variables' => [
        [
            'method' => 'data_for_js',
            'calls' => [
                ['foo', 'foobar'],
                ['foo', 'foobar', true],
            ],
            'expected_head_code' => [
                'foobar',
            ],
            'expected_end_code' => [
                'foobar',
            ],
        ]
    ],

    'js_calls' => [
        [
            'method' => 'js_function_call',
            'calls' => [
                ['mynormalfn', ['foo', 'bar']],
                ['mydomfn', ['foo', 'bar'], true],
            ],
            'expected_end_code' => [
                'mynormalfn("foo", "bar")',
                'mydomfn("foo", "bar")',
            ],
        ]
    ],

    'js_init_code' => [
        [
            'method' => 'js_init_code',
            'calls' => [
                ['foo();'],
                ['bar();', true],
            ],
            'expected_end_code' => [
                'foo();',
                'bar();',
            ],
        ]
    ],

    'yui_module' => [
        [
            'method' => 'yui_module',
            'calls' => [
                ['my-yui-module-a', 'my.yui.module.init', ['foo', 'bar']],
                ['my-yui-module-b', 'my.yui.module.init', ['baz', 'qux'], null, true],
            ],
            'expected_end_code' => [
                'my-yui-module-a',
                'my-yui-module-b',
                'my.yui.module.init("foo", "bar")',
                'my.yui.module.init("baz", "qux")',
            ],
        ]
    ],

    'yui_event_handlers' => [
        [
            'method' => 'event_handler',
            'calls' => [
                ['.foo', 'click', 'bar', ['baz', 'qux']],
            ],
            'expected_end_code' => [
                'Y.on(\'click\', bar, ".foo", null, ["baz","qux"]);',
            ],
        ]
    ],

    'css_theme' => [
        [
            'method' => 'css_theme',
            'calls' => [
                [new \moodle_url('/theme/foobar/style.css')],
            ],
            'expected_head_code' => [
                '/theme/foobar/style.css',
            ],
        ]
    ],

    'css' => [
        [
            'method' => 'css',
            'calls' => [
                [new \moodle_url('/lib/foo.css')],
            ],
            'expected_head_code' => [
                '/lib/foo.css',
            ],
        ]
    ]
];
