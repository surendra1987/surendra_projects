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

namespace totara_tui\local\mediation\javascript;

/**
 * Implements replacement of language string tags (##str:...##)
 */
class lang_string_replacer {
    /** @var \core_string_manager */
    private \core_string_manager $string_manager;

    /** @var string */
    private const PREFIX = '##str:';
    /** @var int */
    private const PREFIX_LENGTH = 6;
    /** @var string */
    private const SUFFIX = '##';
    /** @var int */
    private const SUFFIX_LENGTH = 2;

    /**
     * Patterns for how many times the string has been encoded (JSON.stringify).
     * @var array
     */
    private const STRING_ENCODING_PATTERNS = [
        0 => '"',
        1 => '\\"',
        2 => '\\"\\\\\\"',
    ];

    /**
     * @param \core_string_manager $string_manager
     */
    public function __construct(\core_string_manager $string_manager) {
        $this->string_manager = $string_manager;
    }

    /**
     * Replace tags in content.
     *
     * @param string $content
     * @param string $lang Current language (e.g. 'en')
     * @return string
     */
    public function replace_content(string $content, string $lang) {
        $index = 0;
        $end = strlen($content);
        $out_code = '';

        while ($index < $end) {
            $tag_start = strpos($content, self::PREFIX, $index);
            $tag_end = $tag_start !== false ? strpos($content, self::SUFFIX, $tag_start + self::PREFIX_LENGTH) : false;
            if ($tag_start !== false || $tag_end !== false) {
                // find encoding level
                $quote_index = strrpos($content, '"', 0 - ($end - $tag_start));
                $encoding_level = 0;
                foreach (self::STRING_ENCODING_PATTERNS as $i => $pattern) {
                    if (substr($content, $quote_index + 1 - strlen($pattern), strlen($pattern)) === $pattern) {
                        $encoding_level = $i;
                    }
                }

                // output code before tag
                $out_code .= substr($content, $index, $tag_start - $index);

                // replace content
                $tag_value_start = $tag_start + self::PREFIX_LENGTH;
                $str_result = $this->get_tag_replacement(substr($content, $tag_value_start, $tag_end - $tag_value_start), $lang);

                if ($str_result === null) {
                    $index = $tag_end + self::SUFFIX_LENGTH;
                    $out_code .= substr($content, $tag_start, $index - $tag_start);
                    break;
                }

                // reencode to match encoding level
                for ($i = 0; $i < $encoding_level + 1; $i++) {
                    $str_result = json_encode($str_result);
                    $str_result = substr($str_result, 1, -1); // strip outermost ""
                }

                // output
                $out_code .= $str_result;
                $index = $tag_end + self::SUFFIX_LENGTH;
            } else {
                break;
            }
        }

        // output remaining code
        $out_code .= substr($content, $index);

        return $out_code;
    }

    /**
     * Get replacement for tag value.
     *
     * @param mixed $tag 
     * @param mixed $lang 
     * @return null|string|bool 
     */
    private function get_tag_replacement($tag, $lang) {
        $index = strpos($tag, ':');
        if ($index === 0) {
            return null;
        }
        $op = substr($tag, 0, $index);
        $params = explode(',', substr($tag, $index + 1));
        switch ($op) {
            case 'get':
                return $this->string_manager->get_string($params[0], $params[1], null, $lang);
                break;
            case 'try':
                return $this->string_manager->string_exists($params[0], $params[1])
                    ? $this->string_manager->get_string($params[0], $params[1], null, $lang)
                    : null;
                break;
            case 'has':
                return $this->string_manager->string_exists($params[0], $params[1]);
                break;
            default:
                return null;
        }
    }
}
