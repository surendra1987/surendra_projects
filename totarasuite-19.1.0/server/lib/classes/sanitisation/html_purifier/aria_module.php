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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core
 */

namespace core\sanitisation\html_purifier;

class aria_module extends \HTMLPurifier_HTMLModule {
    /**
     * @type string
     */
    public $name = 'Aria';

    /**
     * @type array
     */
    public $attr_collections = [
        'Common' => [
            // Accessibility atributes as of WAI-ARIA 1.2 (W3C Recommendation 06 June 2023)
            // Using "Text" instead of "CDATA" breaks HTMLPurifier (test failures) for some reason.
            // Text and CDATA are aliases.
            'role' => 'CDATA',
            'aria-activedescendant' => 'CDATA',
            'aria-atomic' => 'CDATA',
            'aria-autocomplete' => 'CDATA',
            'aria-busy' => 'CDATA',
            'aria-checked' => 'CDATA',
            'aria-colcount' => 'CDATA',
            'aria-colindex,' => 'CDATA',
            'aria-colspan' => 'CDATA',
            'aria-controls' => 'CDATA',
            'aria-current' => 'CDATA',
            'aria-describedby' => 'CDATA',
            'aria-details' => 'CDATA',
            'aria-disabled' => 'CDATA',
            'aria-dropeffect' => 'CDATA',
            'aria-errormessage' => 'CDATA',
            'aria-expanded' => 'CDATA',
            'aria-flowto' => 'CDATA',
            'aria-grabbed' => 'CDATA',
            'aria-haspopup' => 'CDATA',
            'aria-hidden' => 'CDATA',
            'aria-invalid' => 'CDATA',
            'aria-keyshortcuts' => 'CDATA',
            'aria-label' => 'CDATA',
            'aria-labelledby' => 'CDATA',
            'aria-level' => 'CDATA',
            'aria-live' => 'CDATA',
            'aria-modal' => 'CDATA',
            'aria-multiline' => 'CDATA',
            'aria-multiselectable' => 'CDATA',
            'aria-orientation' => 'CDATA',
            'aria-owns' => 'CDATA',
            'aria-placeholder' => 'CDATA',
            'aria-posinset' => 'CDATA',
            'aria-pressed' => 'CDATA',
            'aria-readonly' => 'CDATA',
            'aria-relevant' => 'CDATA',
            'aria-required' => 'CDATA',
            'aria-roledescription' => 'CDATA',
            'aria-rowcount' => 'CDATA',
            'aria-rowindex' => 'CDATA',
            'aria-rowspan' => 'CDATA',
            'aria-selected' => 'CDATA',
            'aria-setsize' => 'CDATA',
            'aria-sort' => 'CDATA',
            'aria-valuemax' => 'CDATA',
            'aria-valuemin' => 'CDATA',
            'aria-valuenow' => 'CDATA',
            'aria-valuetext' => 'CDATA',
        ]
    ];
}
