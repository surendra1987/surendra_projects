/*
 * This file is part of Totara LMS
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
 * @author Cody Finegan <cody.finegan@totaralearning.com>
 * @package block_totara_recently_viewed
 */

define(['jquery'], function ($) {
    var init = function (block_id) {
        var blockSelector = '#inst' + block_id;
        // Only care about tile-based blocks
        if ($(blockSelector).find('.block-totara-recently-viewed.block-trv-tiles').length === 0) {
            return;
        }

        /**
         * Calculate the sizing inside the block
         */
        function calculateBlockSize() {
            // Only run if the block is inside the main, top or bottom panels
            var block = $('#region-main, #block-region-top, #block-region-bottom').find(blockSelector + ':first');
            if (block.length === 0 || block.is('.hidden')) {
                return;
            }

            var width = block.width();
            var cardsPerRow = Math.floor(width / 214);
            if (!cardsPerRow) {
                cardsPerRow = 1;
            }
            var widthPercentage = 100 / cardsPerRow;
            if (cardsPerRow === 1) {
                block.find('.block-trv-layout-horizontal').addClass('block-trv-layout-horizontal-single');
            } else {
                block.find('.block-trv-layout-horizontal').removeClass('block-trv-layout-horizontal-single');
            }

            block.find('li.block-trv-card-li').css('width', widthPercentage + '%');
        }

        // Calculate any sizes we need. We give it a slight delay to let
        // the block event finish first + it prevents it from running
        // multiple times at once.
        var timer;
        var debouncedCalculate = function() {
            clearTimeout(timer);
            timer = setTimeout(calculateBlockSize, 200);
        };

        // Run it once to start with
        calculateBlockSize();

        // Recalculate whenever the block size changes
        var rso = new ResizeObserver(debouncedCalculate);
        var blockEl = document.querySelector(blockSelector);
        if (blockEl) {
            rso.observe(blockEl);
        }
    };

    return {
        init: init
    };
});