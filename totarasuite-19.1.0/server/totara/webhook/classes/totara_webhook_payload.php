<?php
/**
 * This file is part of Totara TXP
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
 * @author  Kelsey Scheurich <kelsey.scheurich@totara.com>
 * @package totara_webhook
 */

namespace totara_webhook;

class totara_webhook_payload {

    private int $attempt;
    private array $body;
    private string $event;
    private int $time_created;
    private ?int $time_sent;
    private int $webhook_id;

    /**
     * @return int
     */
    public function get_attempt(): int {
        return $this->attempt;
    }

    /**
     * @return array
     */
    public function get_body(): array {
        return $this->body;
    }

    /**
     * @return string
     */
    public function get_event(): string {
        return $this->event;
    }

    /**
     * @return int
     */
    public function get_time_created(): int {
        return $this->time_created;
    }

    /**
     * @return int|null
     */
    public function get_time_sent(): ?int {
        return $this->time_sent;
    }

    /**
     * @return int
     */
    public function get_webhook_id(): int {
        return $this->webhook_id;
    }

    /**
     * @param array $body
     * @return void
     */
    public function set_body(array $body): void {
        $this->body = $body;
    }

    /**
     * @param int $attempt
     * @param array $body
     * @param string $event
     * @param int $webhook_id
     */
    public function __construct(int $attempt, array $body, string $event, int $webhook_id, int $time_created) {
        $this->time_sent = null;

        $this->attempt = $attempt;
        $this->body = $body;
        $this->event = $event;
        $this->webhook_id = $webhook_id;
        $this->time_created = $time_created;
    }

    public function to_request_body(): array {
        $this->time_sent = time();
        return [
            'attempt' => $this->attempt,
            'body' => $this->body,
            'event' => $this->event,
            'time_created' => $this->time_created,
            'time_sent' => $this->time_sent,
            'webhook_id' => $this->webhook_id,
        ];
    }

}
