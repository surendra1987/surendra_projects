<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package totara_tui
 */

use core\testing\mock\mock_string_manager;
use totara_tui\local\mediation\javascript\lang_string_replacer;

class totara_tui_local_mediation_javascript_lang_string_replacer_test extends \core_phpunit\testcase {
    public function create_instance(): lang_string_replacer {
        $sm = new mock_string_manager();
        $sm->mock_strings['totara_core']['poweredbyxhtml'] = 'Powered by <a href="{$a->url}">{$a->totaralearn}</a>';
        return new lang_string_replacer($sm);
    }

    public function test_replace_str_tags() {
        $inst = $this->create_instance();
        $rawjs = <<<'EOF'
console.log("##str:get:poweredbyxhtml,totara_core##");
EOF;
        $result = $inst->replace_content($rawjs, 'en');
        $replacedjs = <<<'EOF'
console.log("Powered by <a href=\"{$a->url}\">{$a->totaralearn}<\/a>");
EOF;
        $this->assertEquals($replacedjs, $result);
    }

    public function test_replace_multiple_encoded_str_tags() {
        $inst = $this->create_instance();

        // json encoded
        $rawjs = <<<'EOF'
console.log(JSON.parse("\"##str:get:poweredbyxhtml,totara_core##\""));
EOF;
        $result = $inst->replace_content($rawjs, 'en');
        $replacedjs = <<<'EOF'
console.log(JSON.parse("\"Powered by <a href=\\\"{$a->url}\\\">{$a->totaralearn}<\\\/a>\""));
EOF;
        $this->assertEquals($replacedjs, $result);

        // double json encoded
        $rawjs = <<<'EOF'
console.log(JSON.parse(JSON.parse("\"\\\"##str:get:poweredbyxhtml,totara_core##\\\"\"")));
EOF;
        $result = $inst->replace_content($rawjs, 'en');
        $replacedjs = <<<'EOF'
console.log(JSON.parse(JSON.parse("\"\\\"Powered by <a href=\\\\\\\"{$a->url}\\\\\\\">{$a->totaralearn}<\\\\\\\/a>\\\"\"")));
EOF;
        $this->assertEquals($replacedjs, $result);
    }

    public function test_replace_merged_str_tags() {
        $inst = $this->create_instance();

        // single encoded
        $rawjs = <<<'EOF'
console.log("##str:get:add,core## ##str:get:remove,core##");
EOF;
        $result = $inst->replace_content($rawjs, 'en');
        $replacedjs = <<<'EOF'
console.log("[add,core] [remove,core]");
EOF;
        $this->assertEquals($replacedjs, $result);

        // json encoded
        $rawjs = <<<'EOF'
console.log(JSON.parse("\"##str:get:add,core## ##str:get:remove,core##\""));
EOF;
        $result = $inst->replace_content($rawjs, 'en');
        $replacedjs = <<<'EOF'
console.log(JSON.parse("\"[add,core] [remove,core]\""));
EOF;
        $this->assertEquals($replacedjs, $result);
    }

    public function test_webpack_dev_mode_eval() {
        $inst = $this->create_instance();
        $rawjs =  <<<'EOF'
eval("this.$str.__r(\"##str:get:poweredbyxhtml,totara_core##\", { url: 'https://www.totara.com/', totaralearn: \"##str:get:totaralearn,totara_core##\" });");
EOF;
        $result = $inst->replace_content($rawjs, 'en');
        $replacedjs = <<<'EOF'
eval("this.$str.__r(\"Powered by <a href=\\\"{$a->url}\\\">{$a->totaralearn}<\\\/a>\", { url: 'https://www.totara.com/', totaralearn: \"[totaralearn,totara_core]\" });");
EOF;
        $this->assertEquals($replacedjs, $result);
    }

    public function test_edge_cases() {
        $inst = $this->create_instance();
        $this->assertEquals('', $inst->replace_content('', 'en'));
        $this->assertEquals('foo', $inst->replace_content('foo', 'en'));
        $this->assertEquals('foo##str:blorp:add,core##bar', $inst->replace_content('foo##str:blorp:add,core##bar', 'en'));
    }

    public function test_outside_string() {
        // this is not supported (##str## must _always_ be inside a string), but make sure it doesn't die completely
        $inst = $this->create_instance();
        $this->assertEquals('[add,core]', $inst->replace_content('##str:get:add,core##', 'en'));
        $this->assertEquals('foo[add,core]bar', $inst->replace_content('foo##str:get:add,core##bar', 'en'));
    }
}
