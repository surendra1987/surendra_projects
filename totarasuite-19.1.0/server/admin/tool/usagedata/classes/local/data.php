<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

namespace tool_usagedata\local;

use coding_exception;
use core_component;
use Exception;
use Throwable;
use tool_usagedata\export;

/**
 * Utilised to add additional functionality for export classes when exporting from the tool.
 */
final class data {

    public const LOOKUP_NAMESPACE = 'usagedata';
    private const NS_SEPARATOR = '\\';

    /** @var string The class namespace for the export class this instance is based on */
    public string $export_class;
    /** @var string The component the data belongs to - this is automatically set by the ::$export_class's namespace or provided by the export class*/
    public string $component;
    /** @var string|mixed Automatically obtained from the ::$component */
    public string $component_type;
    /** @var string|mixed Automatically obtained from the ::$component */
    public ?string $component_name;
    /** @var string Automatically obtained from the ::$component */
    public string $name;

    /** @var array Stores summary of the error, if encountered, for purposes of exporting */
    private array $error = [];

    /** @var export|null The instance of the export class */
    private ?export $instance;

    /**
     * @param string $export_class The export class namespace this data class should be based off
     * @throws coding_exception
     */
    public function __construct(string $export_class) {
        $export_class = trim($export_class, self::NS_SEPARATOR);
        $bits = explode(self::NS_SEPARATOR, $export_class);
        $component = reset($bits);

        $this->export_class = $export_class;

        if (!in_array($this->instance()->get_type(), [export::TYPE_ARRAY, export::TYPE_OBJECT])) {
            throw new coding_exception('Type must be set correctly on export classes');
        }

        if (defined($export_class . '::COMPONENT')) {
            $this->component = $export_class::COMPONENT;
        } else {
            $this->component = $component;
        }

        [$component_type, $component_name] = core_component::normalize_component($this->component);
        if ($component_type === 'core' && empty($component_name)) {
            $component_name = 'core';
        }
        $this->component_type = $component_type;
        $this->component_name = $component_name;
        $this->name = str_replace(
            $component . self::NS_SEPARATOR . self::LOOKUP_NAMESPACE . self::NS_SEPARATOR,
            '',
            $this->export_class
        );
    }

    /**
     * Retrieves the underlying export class this data class is based on
     * @return export
     */
    private function instance(): export {
        if (!isset($this->instance)) {
            $this->instance = new $this->export_class();
        }
        return $this->instance;
    }

    /**
     * Retrieves the export data from the underlying export class
     * This method is intended to be used when outputting the exported data to JSON.
     *
     * @return array|object Returns the output of the underlying export class instance,
     * Except when the output is empty and the export is of type TYPE_OBJECT
     * In this case, an empty object is returned
     */
    private function get_export_data() {
        $export_data = $this->instance()->export();

        if ($this->instance()->get_type() === export::TYPE_OBJECT && empty($export_data)) {
            return (object) [];
        }

        return $export_data;
    }

    /**
     * Exports the data, which may either be null, an array or empty object.
     * This method is intended to be used when outputting the exported data to JSON.
     *
     * If exporting the data throws an error, this is handled separately and may be retrieved after calling this method
     * using the ::get_error() method
     *
     * @return array|object|null
     */
    public function export() {

        try {
            return $this->get_export_data();
        } catch (Exception $exception) {
            $this->handle_error('Exception', $exception);
        } catch (Throwable $throwable) {
            $this->handle_error('Throwable', $throwable);
        }

        return null;
    }

    /**
     * Compiles a given error into a standard structure for use of exporting
     *
     * If debugging is enabled, the given $exception's class and $exception->getMessage() output will be added to the error output
     *
     * An error handled by this method can be retrieved via the ::get_error() method
     *
     * @param $error_type string The type of error encountered (Exception, Throwable, etc.)
     * @param $exception Throwable The error thrown
     * @return void
     */
    public function handle_error(string $error_type, Throwable $exception): void {
        error_log(get_class($exception) . ' ' . $exception->getMessage());

        $this->error['component_type'] = $this->component_type;
        $this->error['component_name'] = $this->component_name;
        $this->error['export_name'] = $this->name;
        $this->error['description'] = $error_type . ' occurred';
        if (debugging()) {
            $error_message = $exception->getMessage();

            $this->error['error'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
            ];

            error_log($exception->getTraceAsString());
            debugging(get_class($exception) . ' ' . $error_message, DEBUG_DEVELOPER);
        }
    }

    /**
     * Retrieves errors handled by ::handle_error()
     * If no error was caught, this will return an empty array.
     * @return array
     */
    public function get_error(): array {
        return $this->error;
    }

    /**
     * Retrieves the user-facing summary of the underlying export class
     * @return string - The summary
     */
    public function purpose(): string {
        return $this->instance()->get_summary();
    }
}