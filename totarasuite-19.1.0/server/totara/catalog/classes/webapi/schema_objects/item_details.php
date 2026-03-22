<?php
/**
 * This file is part of Totara Core
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\webapi\schema_objects;

use stdClass;
use totara_catalog\dataformatter\formatter;
use totara_catalog\local\config;
use totara_catalog\provider;

/**
 * Represents information shown in the details panel of an item.
 */
class item_details {
    /** @var string */
    public $title = null;

    /** @var object */
    public $manage_link = null;

    /** @var object */
    public $details_link = null;

    /** @var string */
    public $rich_text = null;

    /** @var string */
    public $description = null;

    /** @var array */
    public $text_placeholders = null;

    /** @var array */
    public $icon_placeholders = null;

    /**
     * @param stdClass $record
     * @param provider $provider
     * @param config $config
     * @return self
     */
    public static function from_record(stdClass $record, provider $provider, config $config): self {
        $result = new self();

        if ($config->get_value('details_title_enabled')) {
            $titledataholderkey = $provider->get_config('details_title');
            $result->title = $record->data[formatter::TYPE_PLACEHOLDER_TITLE][$titledataholderkey] ?? '';
        }

        $result->manage_link = $provider->get_manage_link($record->objectid);

        $result->details_link = $provider->get_details_link($record->objectid);

        if ($config->get_value('rich_text_content_enabled')) {
            $richtextdataholderkey = $provider->get_config('rich_text');
            $result->rich_text = $record->data[formatter::TYPE_PLACEHOLDER_RICH_TEXT][$richtextdataholderkey] ?? '';
        }

        if ($config->get_value('details_description_enabled')) {
            $descriptiondataholderkey = $provider->get_config('details_description');
            $result->description = $record->data[formatter::TYPE_PLACEHOLDER_TEXT][$descriptiondataholderkey] ?? '';
        }

        $additionaltextplaceholdercount = $config->get_value('details_additional_text_count');
        if ($additionaltextplaceholdercount > 0) {
            $result->text_placeholders = [];

            $textdataholders = $provider->get_dataholders(formatter::TYPE_PLACEHOLDER_TEXT);
            $additionaltextplaceholders = $provider->get_config('details_additional_text');
            $additionaltextlabels = $provider->get_config('details_additional_text_label');

            for ($i = 0; $i < $additionaltextplaceholdercount; $i++) {
                $key = $additionaltextplaceholders[$i] ?? null;
                $value = $record->data[formatter::TYPE_PLACEHOLDER_TEXT][$key] ?? '';

                $placeholder = (object)[
                    'data_exists' => !empty($value),
                    'data' => $value,
                    'show_label' => false,
                    'label' => null,
                ];

                if (!empty($additionaltextlabels[$i]) && !empty($textdataholders[$key])) {
                    $placeholder->show_label = true;
                    $placeholder->label = $textdataholders[$key]->name;
                }

                $result->text_placeholders[] = $placeholder;
            }
        }

        if ($config->get_value('details_additional_icons_enabled')) {
            $result->icon_placeholders = [];

            $additionaliconplaceholders = $provider->get_config('details_additional_icons');

            foreach ($additionaliconplaceholders as $additionaliconplaceholder) {
                $icons = $record->data[formatter::TYPE_PLACEHOLDER_ICONS][$additionaliconplaceholder] ?? [];
                $result->icon_placeholders = array_merge($result->icon_placeholders, $icons);
            }
        }

        return $result;
    }
}
