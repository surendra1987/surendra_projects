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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\webapi\schema_objects;

use stdClass;
use totara_catalog\dataformatter\formatter;
use totara_catalog\local\config;
use totara_catalog\provider_handler;

class item {
    /**
     * @var int
     */
    public $itemid;

    /**
     * @var bool
     */
    public $featured;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $redirecturl;

    /**
     * @var bool
     */
    public $image_enabled;

    /**
     * @var object
     */
    public $image;

    /**
     * @var string
     */
    public $logo;

    /**
     * @var string
     */
    public $objecttype;

    /**
     * @var string
     */
    public $type_label;

    /**
     * @var bool
     */
    public $hero_data_text_enabled;

    /**
     * @var bool
     */
    public $hero_data_icon_enabled;

    /**
     * @var string
     */
    public $hero_data_type;

    /**
     * @var string
     */
    public $hero_data_text;

    /**
     * @var object
     */
    public $hero_data_icon;

    /**
     * @var bool
     */
    public $description_enabled;

    /**
     * @var string
     */
    public $description;

    /**
     * @var bool
     */
    public $progress_bar_enabled;

    /**
     * @var object
     */
    public $progress_bar;

    /**
     * @var bool
     */
    public $text_placeholders_enabled;

    /**
     * @var array
     */
    public $text_placeholders;

    /**
     * @var bool
     */
    public $icon_placeholders_enabled;

    /**
     * @var array
     */
    public $icon_placeholders;

    /**
     * @param array $items
     * @param provider_handler $provider_handler
     * @param config $config
     * @return array
     */
    public static function make_items(array $items, provider_handler $provider_handler, config $config): array {
        $required_data_holders = [];
        foreach ($items as $item) {
            if (empty($required_data_holders[$item->objecttype])) {
                $provider = $provider_handler->get_provider($item->objecttype);
                $required_data_holders[$item->objecttype] = \totara_catalog\output\item::get_required_dataholders($provider);
            }
        }
        $items = $provider_handler->get_data_for_objects($items, $required_data_holders);
        $group = [];
        foreach ($items as $item) {
            $group[] = static::instance($item, $provider_handler, $config);
        }

        return $group;
    }

    /**
     * @param stdClass $object
     * @param provider_handler $provider
     * @param config $config
     * @return self
     */
    public static function instance(stdClass $object, provider_handler $provider, config $config) {
        $provider = $provider->get_provider($object->objecttype);
        $item = new self();
        $item->itemid = $object->id;
        $item->featured = !empty($object->featured);
        $item->objecttype = $object->objecttype;
        $item->type_label = $provider->get_name();

        $titledataholderkey = $provider->get_config('item_title');
        if (empty($provider->get_dataholders(formatter::TYPE_PLACEHOLDER_TITLE)[$titledataholderkey])) {
            $titledataholders = $provider->get_dataholders(formatter::TYPE_PLACEHOLDER_TITLE);
            $firsttitledataholder = reset($titledataholders);
            $titledataholderkey = $firsttitledataholder->key;
        }
        $item->title = $object->data[formatter::TYPE_PLACEHOLDER_TITLE][$titledataholderkey] ?? '';
        $item->redirecturl = $provider->get_details_link($object->objectid)->button->url;

        $item->image_enabled = (bool)$config->get_value('image_enabled');
        if ($item->image_enabled) {
            $item->image = $object->data[formatter::TYPE_PLACEHOLDER_IMAGE][$provider->get_data_holder_config('image')] ?? '';
            if (empty($item->image->url)) {
                $item->image = null;
            }
            $item->logo = $object->data[formatter::TYPE_PLACEHOLDER_IMAGE][$provider->get_data_holder_config('logo')] ?? '';
            if (empty($item->logo->url)) {
                $item->logo = null;
            }
        }

        $item->hero_data_text_enabled = false;
        $item->hero_data_icon_enabled = false;
        $item->hero_data_type = $config->get_value('hero_data_type');
        if ($item->hero_data_type == 'text') {
            $item->hero_data_text = $object->data[formatter::TYPE_PLACEHOLDER_TEXT][$provider->get_config('hero_data_text')] ?? '';
            $item->hero_data_text_enabled = true;
        } elseif ($item->hero_data_type == 'icon') {
            $item->hero_data_icon = $object->data[formatter::TYPE_PLACEHOLDER_ICON][$provider->get_config('hero_data_icon')] ?? '';
            $item->hero_data_icon_enabled = true;
        }

        $item->description_enabled = (bool)$config->get_value('item_description_enabled');
        if ($item->description_enabled) {
            $item->description = $object->data[formatter::TYPE_PLACEHOLDER_TEXT][$provider->get_config('item_description')] ?? '';
        }

        $item->progress_bar_enabled = (bool)$config->get_value('progress_bar_enabled');
        if ($item->progress_bar_enabled) {
            $progressdataholderkey = $provider->get_data_holder_config('progressbar');
            if (!empty($object->data[formatter::TYPE_PLACEHOLDER_PROGRESS][$progressdataholderkey])) {
                $item->progress_bar = $object->data[formatter::TYPE_PLACEHOLDER_PROGRESS][$progressdataholderkey];
            }
        }

        $additionaltextplaceholdercount = $config->get_value('item_additional_text_count');
        $item->text_placeholders_enabled = $additionaltextplaceholdercount > 0;
        if ($item->text_placeholders_enabled) {
            $item->text_placeholders = [];

            $textdataholders = $provider->get_dataholders(formatter::TYPE_PLACEHOLDER_TEXT);

            for ($i = 0; $i < $additionaltextplaceholdercount; $i++) {
                $key = $provider->get_config('item_additional_text')[$i] ?? null;
                $value = $object->data[formatter::TYPE_PLACEHOLDER_TEXT][$key] ?? '';

                $placeholder = (object)[
                    'data_exists' => !empty($value),
                    'data' => $value,
                    'show_label' => false,
                    'label' => null,
                ];

                if (!empty($provider->get_config('item_additional_text_label')[$i]) && !empty($textdataholders[$key])) {
                    $placeholder->show_label = true;
                    $placeholder->label = $textdataholders[$key]->name;
                }

                $item->text_placeholders[] = $placeholder;
            }
        }

        $item->icon_placeholders_enabled = (bool)$config->get_value('item_additional_icons_enabled');
        if ($item->icon_placeholders_enabled) {
            $item->icon_placeholders = [];

            foreach ($provider->get_config('item_additional_icons') as $additionaliconplaceholder) {
                $icons = $object->data[formatter::TYPE_PLACEHOLDER_ICONS][$additionaliconplaceholder] ?? [];
                $item->icon_placeholders = array_merge($item->icon_placeholders, $icons);
            }
        }

        return $item;
    }

}
