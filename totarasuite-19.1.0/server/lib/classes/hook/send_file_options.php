<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core
 */

namespace core\hook;

use totara_core\hook\base;

/**
 * Hook to determine options for send_file.
 */
class send_file_options extends base {
    /** @var string */
    private string $mime_type;
    
    /** @var bool */
    private bool $requested_force_download;
    
    /** @var bool */
    private bool $force_download;

    /** @var array */
    private array $options;

    /** @var int */
    private int $lifetime;

    public function __construct(string $mime_type, bool $force_download, array $options, int $lifetime) {
        $this->mime_type = $mime_type;
        $this->force_download = $force_download;
        $this->requested_force_download = $force_download;
        $this->options = $options;
        $this->lifetime = $lifetime;
    }

    /**
     * Get the mime type.
     *
     * @return string
     */
    public function get_mime_type() {
        return $this->mime_type;
    }

    /**
     * Get whether the original request asked to force the download.
     *
     * @return bool
     */
    public function get_requested_force_download() {
        return $this->requested_force_download;
    }

    /**
     * Get whether to force this file to be downloaded, instead of opening in the browser.
     *
     * @return bool
     */
    public function get_force_download() {
        return $this->force_download;
    }

    /**
     * Get additional options passed to send_file.
     *
     * @return array
     */
    public function get_options() {
        return $this->options;
    }

    /**
     * Get cache lifetime (0 = don't cache)
     *
     * @return int
     */
    public function get_lifetime() {
        return $this->lifetime;
    }

    /**
     * Set mime type.
     *
     * @param string $mime_type
     * @return void
     */
    public function set_mime_type(string $mime_type) {
        $this->mime_type = $mime_type;
    }

    /**
     * Set whether to force a download
     *
     * @param bool $force_download
     * @return void
     */
    public function set_force_download(bool $force_download) {
        $this->force_download = $force_download;
    }

    /**
     * Set options.
     *
     * @param array $options
     * @return void
     */
    public function set_options(array $options) {
        $this->options = $options;
    }

    /**
     * Set cache lifetime.
     *
     * @param int $lifetime
     * @return void
     */
    public function set_lifetime(int $lifetime) {
        $this->lifetime = $lifetime;
    }

    /**
     * Set all the params.
     *
     * @param string $mime_type 
     * @param bool $force_download 
     * @param array $options 
     * @param int $lifetime 
     * @return void 
     */
    public function set_all(string $mime_type, bool $force_download, array $options, int $lifetime) {
        $this->set_mime_type($mime_type);
        $this->set_force_download($force_download);
        $this->set_options($options);
        $this->set_lifetime($lifetime);
    }

    /**
     * Get all the params, in the same order they are passed to set_all/constructor.
     *
     * @return array
     */
    public function get_all() {
        return [$this->mime_type, $this->force_download, $this->options, $this->lifetime];
    }
}
