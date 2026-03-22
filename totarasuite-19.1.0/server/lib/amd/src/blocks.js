/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @author Brian Barnes <brian.barnes@totaralms.com>
 * @package core_blocks
  */

define(['jquery', 'core/templates'], function ($, templates) {
    const FOCUSABLE_ELEMENT_SELECTOR = [
        'a[href]',
        'area[href]',
        'input:not([disabled]):not([type="hidden"]):not([aria-hidden])',
        'select:not([disabled]):not([aria-hidden])',
        'textarea:not([disabled]):not([aria-hidden])',
        'button:not([disabled]):not([aria-hidden])',
        'iframe',
        'object',
        'embed',
        '[contenteditable]',
        '[tabindex]',
    ].join(', ');

    /**
     * Check if the specified element is visible for focus purposes
     *
     * @param {Element} el
     * @returns {boolean}
     */
    function visibleForFocus(el) {
        const computedStyle = getComputedStyle(el);
        return (
            computedStyle.display !== 'none' &&
            computedStyle.visibility !== 'hidden' &&
            !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length)
        );
    }

    /**
     * Hook skip block links and focus the next thing after the skip link target instead.
     * (typically the skip link for the next block)
     */
    function hookBlockSkipLinks() {
        const blockSkipLinks = document.querySelectorAll('.skip-block');
        blockSkipLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                const href = link.getAttribute('href');
                if (!href.startsWith('#')) {
                    return;
                }

                const target = document.querySelector(href);

                // Find all focusable elements, then find the first one after our target.
                const elements = document.querySelectorAll(href + ', ' + FOCUSABLE_ELEMENT_SELECTOR);
                const index = Array.prototype.indexOf.call(elements, target);
                if (index === -1) {
                    return;
                }
                var nextFocus = null;
                for (var i = index + 1; i < elements.length - 1; i++) {
                    if (visibleForFocus(elements[i])) {
                        nextFocus = elements[i];
                        break;
                    }
                }

                if (nextFocus) {
                    nextFocus.focus();
                    e.preventDefault();
                }
            });
        });
    }

    return {
        pageInit: function() {
            hookBlockSkipLinks();
        },

        init: function(config) {
            var block = $('#' + config.id);
            var title = block.find('.title');
            var action = block.find('.block_action');
            var collapsible = action.data('collapsible');

            function handleClick(e) {
                e.preventDefault();
                var hide = !block.hasClass('hidden');
                M.util.set_user_preference(config.preference, hide);
                if (hide) {
                    block.addClass('hidden');
                    block.find('.block-hider-show').focus();
                } else {
                    block.removeClass('hidden');
                    block.find('.block-hider-hide').focus();
                }
            }

            function handlekeypress(e) {
                e.preventDefault();
                if (e.keyCode == 13) { //allow hide/show via enter key
                    handleClick(this);
                }
            }

            if (title && action && collapsible) {
                templates.renderIcon('block-hide', config.tooltipVisible).done(function (html) {
                    var hideicon = $('<a href="#" title="' + config.tooltipVisible + '" class="block-hider-hide" role="button" aria-expanded="true">' + html + '</a>');

                    hideicon.click(handleClick);
                    hideicon.keypress(handlekeypress);
                    action.append(hideicon);
                });
                templates.renderIcon('block-show', config.tooltipHidden).done(function (html) {
                    var showicon = $('<a href="#" title="' + config.tooltipHidden + '" class="block-hider-show" role="button" aria-expanded="false">' + html + '</a>');

                    showicon.click(handleClick);
                    showicon.keypress(handlekeypress);
                    action.append(showicon);
                });
            }

        }
    };
});
