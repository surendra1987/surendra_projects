<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package core
 */
namespace core\json_editor\formatter;

use core\json_editor\helper\value_helper;
use core\json_editor\node\abstraction\inline_node;
use core\json_editor\node\node;
use core\json_editor\schema;
use html_writer;

/**
 * Treat formatter like as middle-ware where we would want to modify the HTML/TEXT output to looks completely different
 * from the original output from the node provide to us.
 *
 * The formatter will be working closely to the front-end framework. If the front-end framework want to output
 * the json editor content into a completely different HTML/TEXT markup, then extend this class and start
 * overriding the functionalities.
 *
 * Formats a FORMAT_JSON_EDITOR document
 */
abstract class formatter {
    /**
     * Constant to print html content.
     * @var string
     */
    public const HTML = 'html';

    /**
     * Constant to print text content.
     * @var string
     */
    public const TEXT = 'text';

    /**
     * formatter constructor.
     * Preventing the children implementation from having a complicated construction.
     */
    final public function __construct() {
        $this->init();
    }

    /**
     * @return void
     */
    protected function init(): void {
    }

    /**
     * @param array $document
     * @return string
     */
    public function to_html(array $document): string {
        $contents = [];
        if (!array_key_exists('type', $document) || 'doc' !== $document['type']) {
            debugging("Invalid document being passed to the formatter", DEBUG_DEVELOPER);
            return '';
        }

        if (array_key_exists('content', $document) && is_array($document['content'])) {
            $contents = $document['content'];
        }

        return $this->print_nodes($contents, self::HTML);
    }

    /**
     * @param array $document
     * @return string
     */
    public function to_text(array $document): string {
        $contents = [];

        if (!array_key_exists('type', $document) || 'doc' !== $document['type']) {
            debugging("Invalid document being passed to the formatter", DEBUG_DEVELOPER);
            return '';
        }

        if (array_key_exists('content', $document) && is_array($document['content'])) {
            $contents = $document['content'];
        }

        return $this->print_nodes($contents, self::TEXT);
    }

    /**
     * Rendering array of raw nodes depending on the $format type.
     *
     * @param array $raw_nodes
     * @param string $format_type
     *
     * @return string
     */
    final public function print_nodes(array $raw_nodes, string $format_type): string {
        if (empty($raw_nodes)) {
            return '';
        }

        $content = '';
        foreach ($raw_nodes as $raw_node) {
            $content .= $this->print_node($raw_node, $format_type);
        }

        return $content;
    }

    /**
     * Printing a single node, into either HTML or TEXT.
     *
     * @param array     $raw_node
     * @param string    $format_type
     * @return string
     */
    final public function print_node(array $raw_node, string $format_type): string {
        $type = $raw_node['type'];

        $schema = schema::instance();
        $node = $schema->get_node($type, $raw_node);

        if (null === $node) {
            debugging("Attempting to format unknown node type '{$type}'", DEBUG_DEVELOPER);
            return '';
        }

        switch ($format_type) {
            case self::HTML:
                return $this->print_html_node($node);

            case self::TEXT:
                return $this->print_text_node($node);

            default:
                throw new \coding_exception("Unknown format");
        }
    }

    /**
     * By default, formatter will use whatever the default implementation from the node, and it is up
     * to the children to use any different kind of implementation for rendering the html content.
     *
     * @param node $node
     * @return string
     */
    protected function print_html_node(node $node): string {
        $content = $node->to_html($this);
        if ($node instanceof inline_node) {
            $content = $this->apply_marks($node->get_raw_marks(), self::HTML, $content);
        }
        return $content;
    }

    /**
     * @param node $node
     * @return string
     */
    protected function print_text_node(node $node): string {
        $content = $node->to_text($this);
        if ($node instanceof inline_node) {
            $content = $this->apply_marks($node->get_raw_marks(), self::TEXT, $content);
        }
        return $content;
    }

    /**
     * Apply marks to rendered content.
     *
     * @param null|array $raw_marks
     * @param string $format_type
     * @param string $content
     * @return string
     */
    final public function apply_marks(?array $raw_marks, string $format_type, string $content): string {
        if (empty($raw_marks)) {
            return $content;
        }
        foreach ($raw_marks as $mark) {
            $content = $this->apply_mark($mark, $format_type, $content);
        }
        return $content;
    }

    /**
     * Apply a single mark to rendered content.
     *
     * @param array $raw_mark
     * @param string $format_type
     * @param string $content
     * @return string
     */
    final public function apply_mark(array $raw_mark, string $format_type, string $content): string {
        switch ($format_type) {
            case self::HTML:
                return $this->print_html_mark($raw_mark, $content);

            case self::TEXT:
                return $this->print_text_mark($raw_mark, $content);

            default:
                throw new \coding_exception("Unknown format");
        }
    }

    /**
     * @param array $mark
     * @param string $content
     * @return string
     */
    private function print_html_mark(array $mark, string $content): string {
        $type = $mark['type'];
        switch ($type) {
            case 'underline':
                $content = html_writer::tag('u', $content);
                break;

            case 'strong':
            case 'em':
                $content = html_writer::tag($type, $content);
                break;

            case 'link':
                $url = $mark['attrs']['href'] ?? null;
                $url = value_helper::clean_url($url);
                if (isset($mark['attrs']['open_in_new_window']) && $mark['attrs']['open_in_new_window']) {
                    $content = html_writer::tag('a', $content, ['href' => $url, 'target' => '_blank']);
                } else {
                    $content = html_writer::tag('a', $content, ['href' => $url]);
                }
                break;

            default:
                debugging("Invalid mark type '{$type}'", DEBUG_DEVELOPER);
        }

        return $content;
    }

    /**
     * @param array $mark
     * @param string $content
     * @return string
     */
    private function print_text_mark(array $mark, string $content): string {
        $type = $mark['type'];
        switch ($type) {
            case 'underline':
                $content = "__{$content}__";
                break;

            case 'strong':
                $content = "**{$content}**";
                break;

            case 'em':
                $content = "_{$content}_";
                break;

            case 'link':
                $url = $mark['attrs']['href'] ?? null;
                $url = value_helper::clean_url($url);
                $content = "{$content} ({$url})";
                break;

            default:
                debugging("Invalid mark type '{$type}'", DEBUG_DEVELOPER);
        }

        return $content;
    }
}