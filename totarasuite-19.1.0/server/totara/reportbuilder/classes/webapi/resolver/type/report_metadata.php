<?php

namespace totara_reportbuilder\webapi\resolver\type;

use core\format;
use core\webapi\execution_context;
use core\webapi\formatter\field\text_field_formatter;
use core\webapi\type_resolver;

/**
 * Report metadata type resolver for the get_report query
 */
class report_metadata extends type_resolver {

    /**
     * Resolves a report creations source field.
     *
     * @param string $field - the name of the field that GraphQL is seeking to resolve, e.g. 'fullname'
     * @param mixed $source - an object from the query resolver whose name matches the name of this class
     * @param array $args - any additional arguments provided by GraphQL, such as the requested string format
     * @param execution_context $ec
     * @return mixed
     */
    public static function resolve(string $field, mixed $source, array $args, execution_context $ec) {
        return match ($field) {
            'title', 'abstract', 'description' => self::get_formatted_string_field($field, $source, $args),
            'records_per_page' => $source[$field],
            default => null,
        };
    }

    /**
     * Format the non-numeric fields on the report metadata depending on the
     * user-provided format preference.
     *
     * @param string $field
     * @param mixed $source
     * @param array $args
     * @return array|mixed|string|null
     * @throws \coding_exception
     */
    private static function get_formatted_string_field(string $field, mixed $source, array $args) {
        $context = $source['context'];
        $format = $args['format'] ?? format::FORMAT_HTML;
        $formatter = new text_field_formatter($format, $context);

        switch ($format) {
            case format::FORMAT_HTML:
                $formatter->set_pluginfile_url_options($context, 'totara_reportbuilder', 'report_builder', $source['report_id']);
                return $formatter->format($source[$field]);
            case format::FORMAT_PLAIN:
                return html_to_text($source[$field], 0, false);
            case format::FORMAT_RAW:
            default:
                return $source[$field];
        }
    }
}