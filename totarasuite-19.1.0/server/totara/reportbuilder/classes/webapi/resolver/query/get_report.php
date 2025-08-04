<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package totara_reportbuilder
 */

namespace totara_reportbuilder\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\string_field_formatter;
use core\webapi\formatter\field\text_field_formatter;
use core\webapi\formatter\field\textarea_field_formatter;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use DateTime;
use dml_exception;
use dml_missing_record_exception;
use moodle_recordset;
use rb_config;
use reportbuilder;
use totara_reportbuilder\exception\reportbuilder_api_exception;

/**
 * Retrieve a given reportbuilder report in JSON format.
 */
class get_report extends query_resolver {

    private const SUPPORTED_FORMATTERS = [
        'DATE' => date_field_formatter::class,
        'STRING' => string_field_formatter::class,
        'TEXT' => text_field_formatter::class,
        'TEXTAREA' => textarea_field_formatter::class,
    ];

    private const FORMATTER_TO_TARGET_FORMAT_MAP = [
        'DATE' => ['ISO8601', 'TIMESTAMP'],
        'STRING' => ['RAW', 'PLAIN', 'HTML'],
        'TEXT' => ['RAW', 'PLAIN', 'HTML', 'MOBILE'],
        'TEXTAREA' => ['RAW', 'PLAIN', 'HTML'],
    ];

    /**
     * Resolve the logic of the query.
     *
     * @param array $args - see the totara_reportbuilder_get_report graphql file for the args
     * @param execution_context $ec - null. This value should be set by the resolver.
     * @return array - an array with a "columns" object and a "rows" object
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $DB;
        $input = $args['input'] ?? [];
        $report_id = (int)$input['report_id'] ?? null;
        // Check that the report exists, throw an exception if not
        try {
            $DB->get_record('report_builder', ['id' => $report_id], '*', MUST_EXIST);
        } catch (dml_missing_record_exception $e) {
            throw new reportbuilder_api_exception("Cannot find report with ID {$report_id}");
        }
        $page = (int)($input['page'] ?? 1);
        $sid = (int)($input['saved_search_id'] ?? 0);
        $sort_by = $input['sort_by'] ?? null;
        $display_report_pagination = $input['display_report_pagination'] ?? false;
        $custom_formats = $input['custom_formats'] ?? null;

        static::validate_saved_search_id($sid, $report_id);
        // We set the saved search ID to the report builder config
        // so that the report builder instance can do the filtering
        // automatically for us.
        $config = new rb_config();
        $config->set_sid($sid);

        $reportbuilder = reportbuilder::create($report_id, $config);
        if (isset($sort_by)) {
            static::validate_sort_column($reportbuilder, $sort_by);
        }
        $rs = static::get_report_recordset($reportbuilder, $page, $sort_by);

        $report_metadata = static::get_report_metadata($reportbuilder);
        $report_pagination = static::get_report_pagination($reportbuilder, $page, $display_report_pagination);
        $unformatted_columns_and_rows = static::get_report_data($reportbuilder, $rs, 'api');
        $columns_and_rows = static::format_report_data($reportbuilder, $unformatted_columns_and_rows, $custom_formats);

        return array_merge($report_metadata, $report_pagination, $columns_and_rows);
    }

    /**
     * Use the reportbuilder to retrieve the set of records from the given page of the report.
     * If there are no records, we return an empty set.
     *
     * @param reportbuilder $reportbuilder
     * @param int $page
     * @param array|null $sort_by
     * @return moodle_recordset
     * @throws dml_exception
     */
    private static function get_report_recordset(reportbuilder $reportbuilder, int $page, ?array $sort_by): moodle_recordset {
        list($sql, $params) = $reportbuilder->build_query(false, true, true);

        if (isset($sort_by)) {
            $sort_direction = $sort_by['direction'] ?? 'DESC';
            $order = " ORDER BY {$sort_by['column']} {$sort_direction}";
        } else {
            $order = $reportbuilder->get_report_sort();
        }

        $report_db = $reportbuilder->get_report_db();
        $records_per_page = $reportbuilder->recordsperpage;
        $start_from_record_number = $records_per_page * ($page - 1);
        return $report_db->get_recordset_sql($sql . $order, $params, $start_from_record_number, $records_per_page);
    }

    /**
     * Process the report data we got from reportbuilder into a format
     * that we can send back to GraphQL.
     * [
     *      'columns' => [
     *          {
     *              'key' => column_name,
     *              'value' => Readable column name
     *         },
     *          ...
     *      ],
     *      'rows' => [
     *          {
     *              'data' => [
     *                  value_1,
     *                  value_2,
     *                  ...
     *              ],
     *              ...
     *          }
     *      ]
     * ]
     *
     * @param reportbuilder $reportbuilder
     * @param moodle_recordset $rs
     * @param string $format
     * @return array[]
     */
    private static function get_report_data(reportbuilder $reportbuilder, moodle_recordset $rs, string $format): array {
        $columns = [];
        $columns_to_include = [];
        $column_index = 0;
        foreach ($reportbuilder->columns as $column) {

            if (!$column->display_column(true)) {
                // Columns that aren't display columns are not included in the processed data row anyway,
                // so we don't need to count them for the purposes of the columns to include
                continue;
            }
            if ($column->hidden) {
                // Hidden columns will be included in the processed data row by default,
                // but we want to filter them out, so we have to count their index here.
                $column_index++;
                continue;
            }

            $columns[] = (object)[
                'key' => $column->type . '_' . $column->value,
                'label' => $column->heading
            ];

            $columns_to_include[] = $column_index;
            $column_index++;
        }

        $columns_to_include = array_flip($columns_to_include);
        $rows = [];
        foreach ($rs as $record) {
            $row = $reportbuilder->src->process_data_row($record, $format, $reportbuilder);
            $cells = array_intersect_key($row, $columns_to_include);
            $rows[] = (object)['data' => $cells];
        }

        return [
            'columns' => $columns,
            'rows' => $rows
        ];
    }

    /**
     * Formats the report data according to any formats provided in the API call.
     *
     * @param reportbuilder $reportbuilder
     * @param array $data
     * @param array|null $custom_formats
     * @return array
     */
    protected static function format_report_data(reportbuilder $reportbuilder, array $data, ?array $custom_formats = null): array {
        if (empty($custom_formats)) {
            return $data;
        }

        $formatters_to_apply = static::get_formatters_to_apply($reportbuilder, $data['columns'], $custom_formats);

        foreach ($data['rows'] as $column => $row) {
            foreach ($formatters_to_apply as $index => $formatter) {
                if (is_string($row->data[$index]) && $formatter instanceof date_field_formatter) {
                    try {
                        $date = new DateTime($row->data[$index]);
                    } catch (\Exception $e) {
                        // If we get a malformed string exception, we were probably passed a timestamp string
                        $date = new DateTime();
                        $date->setTimestamp($row->data[$index]);
                    }
                    $row->data[$index] = $date->getTimestamp();
                }
                $row->data[$index] = $formatter->format($row->data[$index]);
            }
            $data['rows'][$column] = $row;
        }

        return $data;
    }

    /**
     * Get the list of formatters to be applied to each column
     * This method is responsible for validating the given formatting options,
     * we will throw an exception if any of the options are incorrect.
     *
     * @param reportbuilder $reportbuilder
     * @param array $columns - the data['columns'] object with keys 'key', 'label'
     * @param array $custom_formats - the user-specified formats with keys 'columns'; 'target_format'; and 'formatter'
     * @return array
     * @throws reportbuilder_api_exception
     */
    private static function get_formatters_to_apply(reportbuilder $reportbuilder, array $columns, array $custom_formats): array {
        $column_keys = array_column($columns, 'key');
        $column_key_positions = array_flip($column_keys);

        $formatters_to_apply = [];
        foreach ($custom_formats as $format) {
            $formatter = $format['formatter'] ?? null;
            if (!array_key_exists($formatter, self::SUPPORTED_FORMATTERS)) {
                throw new reportbuilder_api_exception("Invalid custom format formatter provided: {$formatter}");
            }
            $formatter_class = self::SUPPORTED_FORMATTERS[$formatter];

            $target_format = strtoupper($format['target_format']) ?? null;
            // We will find out if the target format is valid later - it is too flexible for us to provide a rubric to check against here
            if (!$target_format) {
                throw new reportbuilder_api_exception("No target_format has been provided for the formatter {$formatter}");
            }
            if (!in_array($target_format, self::FORMATTER_TO_TARGET_FORMAT_MAP[$formatter])) {
                throw new reportbuilder_api_exception("Invalid format ({$target_format}) given for the formatter of the type {$formatter}. " .
                    "Please check that you have the correct formatter for the type of data you want to modify.");
            }

            $column = $format['column'] ?? null;
            if (!$column) {
                throw new reportbuilder_api_exception("No column has been provided for the formatter {$formatter}");
            }

            if (!isset($column_key_positions[$column])) {
                throw new reportbuilder_api_exception("Unable to resolve a column with the name \"{$column}\" on the report");
            }
            $column_position = $column_key_positions[$column];

            $formatter_obj = new $formatter_class($target_format, $reportbuilder->get_context());
            if ($formatter_obj instanceof text_field_formatter) {
                $formatter_obj->disabled_pluginfile_url_rewrite();
            }
            $formatters_to_apply[$column_position] = $formatter_obj;
        }
        return $formatters_to_apply;
    }

    /**
     * Check that the saved search ID exists, belongs to the report, and is visible to the API user.
     *
     * @param int $sid
     * @param int $report_id
     * @throws reportbuilder_api_exception
     */
    private static function validate_saved_search_id(int $sid, int $report_id): void {
        global $DB, $USER;

        if ($sid == 0) {
            return;
        }

        try {
            $sid_record = $DB->get_record('report_builder_saved', ['id' => $sid], '*', MUST_EXIST);
        } catch (dml_missing_record_exception $e) {
            throw new reportbuilder_api_exception("Cannot find saved search with ID {$sid}");
        }

        if (!$sid_record->ispublic && $USER->id != $sid_record->userid) {
            throw new reportbuilder_api_exception("Provided saved search ({$sid}) is not a shared search and does not belong to this user.");
        }

        if ($report_id !== (int)$sid_record->reportid) {
            throw new reportbuilder_api_exception("Provided saved search ID ({$sid}) does not belong to report {$report_id}");
        }
    }

    /**
     * Check that the column exists on the report, and that it is
     * visible.
     *
     * @param reportbuilder $reportbuilder - the report instance containing all the columns of the report
     * @param array $sort_by - the object containing the column we want to find
     * @throws reportbuilder_api_exception
     */
    private static function validate_sort_column(reportbuilder $reportbuilder, array $sort_by): void {
        $columns = $reportbuilder->get_columns();
        $found = false;
        foreach ($columns as $column) {
            // We expect to receive the sort column in the format <type>_<value>
            $column_name_match = $column->type . '_' . $column->value === $sort_by['column'];
            $found = ($column_name_match && $column->display_column() && !$column->nosort && !$column->hidden);
            if ($found) {
                break;
            }
        }
        if (!$found) {
            throw new reportbuilder_api_exception('Invalid sort column: ' . $sort_by['column']);
        }
    }

    /**
     * Get an array of metadata fields that tell the user about the report.
     *
     * @param reportbuilder $reportbuilder
     * @return array
     */
    private static function get_report_metadata(reportbuilder $reportbuilder): array {
        $metadata = [
            'context' => $reportbuilder->get_context(),
            'report_id' => $reportbuilder->get_id(),
            'title' => $reportbuilder->fullname,
            'abstract' => $reportbuilder->summary,
            'description' => $reportbuilder->description,
            'records_per_page' => $reportbuilder->recordsperpage
        ];
        return ['report_metadata' => $metadata];
    }

    /**
     * Get the next report page and the total number of pages in the report
     *
     * @param reportbuilder $reportbuilder
     * @param int $page - the current page that the user is viewing
     * @param bool $display_report_pagination - whether we need to do the expensive count query
     * @return array
     * @throws dml_exception
     */
    private static function get_report_pagination(reportbuilder $reportbuilder, int $page, bool $display_report_pagination): array {
        if (!$display_report_pagination) {
            return ['report_pagination' => [
                'next_cursor' => null,
                'total_pages' => null
            ]];
        }

        $record_count = static::get_report_count($reportbuilder);
        $records_per_page = $reportbuilder->recordsperpage;
        $total_page_count = ceil($record_count / $records_per_page);
        $next = $total_page_count > $page ? $page + 1 : null;

        return ['report_pagination' => [
            'next_cursor' => $next,
            'total_pages' => $total_page_count
        ]];
    }

    /**
     * Get the total number of records for a report after filtering.
     * This query is expensive, so we only want to do it if the user requests it.
     *
     * @param reportbuilder $reportbuilder
     * @return int
     * @throws dml_exception
     */
    private static function get_report_count(reportbuilder $reportbuilder): int {
        list($sql, $params) = $reportbuilder->build_query(true, true, true);
        $report_db = $reportbuilder->get_report_db();
        return $report_db->count_records_sql($sql, $params);
    }

    /**
     * Requirements before we can resolve this endpoint
     *
     * @return class-string[]
     */
    public static function get_middleware(): array {
        return [
            require_login::class
        ];
    }

    /**
     * Complexity points used when resolving this query.
     * This number is a representation of how computationally expensive
     * it is to run this query.
     * It is based off the number of records retrieved from
     * the DB, and how many fields we are returning.
     *
     * @return int
     */
    public static function cost_per_call(): int {
        return 50;
    }
}
