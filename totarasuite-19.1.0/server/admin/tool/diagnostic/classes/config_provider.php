<?php

namespace tool_diagnostic;

/**
 * A provider interface to allow different providers for the config
 */
interface config_provider {

    /**
     * returns the config as an array
     *
     * @return array
     */
    public function get_config(): array;

}
