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
 * @author Kunle Odusan <kunle.odusan@totaralearning.com>
 * @package totara_webapi
 */

namespace totara_webapi;

use Exception;
use GraphQL\Error\ClientAware;
use Throwable;

/**
 * Class client_aware_exception
 *
 * @package totara_webapi
 */
class client_aware_exception extends Exception implements ClientAware {

    public const CATEGORY_INTERNAL = 'internal';

    /**
     * Exception wrapped as client aware.
     *
     * @var Throwable
     */
    private Throwable $exception;

    /**
     * Category for exception.
     *
     * @var string
     */
    private string $category;

    /**
     * Is clientSafe.
     *
     * @var bool
     */
    private bool $is_client_safe;

    /**
     * client_aware_exception constructor.
     *
     * @param Throwable $exception
     * @param array $data
     */
    public function __construct(Throwable $exception, array $data = []) {
        $exception_data = array_merge([
            'category' => self::CATEGORY_INTERNAL,
        ], $data);

        $this->exception = $exception;

        $this->category = $exception_data['category'];
        $this->run_category_action();

        $this->is_client_safe = $exception_data['category'] !== self::CATEGORY_INTERNAL;
        parent::__construct($exception->getMessage(), $exception->getCode());
    }

    // phpcs:ignore
    public function isClientSafe(): bool {
        return $this->is_client_safe;
    }

    /**
     * Returns the active category. Call get_category instead.
     *
     * @deprecated since Totara 19
     * @return string
     */
    // phpcs:ignore
    public function getCategory(): string {
        debugging($this::class . '->getCategory call is deprecated and should be replaced with get_category');
        return $this->get_category();
    }

    /**
     * The category this exception is grouped as.
     * Defaults to internal, meaning no error will be shown.
     *
     * @return string
     */
    public function get_category(): string {
        return $this->category;
    }

    /**
     * Set the category. Deprecated, instead create a new exception with category set in the payload.
     * By the time this is called the exception has been processed, so calling this will make little difference.
     *
     * @param string $category
     * @return void
     * @depreacted since Totara 19
     */
    public function set_category(string $category): void {
        $this->category = $category;
    }

    /**
     * Run action based on the category.
     * Do not add any new actions here, instead override the client_exception class and handle them internally.
     *
     * @return void
     */
    private function run_category_action(): void {
        global $SESSION;

        switch ($this->category) {
            case 'require_login':
                $SESSION->wantsurl = get_local_referer(false);
                break;
            default:
                break;
        }
    }
}
