/**
 * This file is part of Totara Learn
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
 * @package totara_core
 */

define([], function() {
    /**
     * My Reports widget.
     *
     * Handles hiding/showing reports by tenant.
     *
     * @class
     */
    function MyReports() {
        this.tenant = 'ANY';
        this.canCreate = null;
        this.containers = [];
    }

    /**
     * Attach to DOM element
     *
     * @param {Element} el
     */
    MyReports.prototype.attach = function(el) {
        const self = this;
        this.el = el;

        // <select> for tenant
        this.tenantSelectorEl = el.querySelector('#myreports-tenant');
        if (this.tenantSelectorEl) {
            this.tenantSelectorEl.addEventListener('change', function() {
                self.tenant = self.tenantSelectorEl.value;
                const option = self.tenantSelectorEl.querySelector('option[value="' + self.tenant + '"]');
                self.canCreate = option && option.getAttribute('data-tw-can-create') == 1;
                self.update();
            });
        }

        // create button
        this.createButtonEl = this.el.querySelector('a.totara_core__myreports__title__btn');
        if (this.createButtonEl) {
            this.createButtonEl.addEventListener('click', function(e) {
                if (self.createButtonEl.hasAttribute('disabled')) {
                    e.preventDefault();
                }
            });
        }

        // count
        this.countEl = this.el.querySelector('.totara_core__myreports__count');

        // tiles
        this.tilesEl = this.el.querySelector('.totara_core__myreports__list');

        // Capture tenant items inside item containers.
        this.el.querySelectorAll('[data-tw-tenant-item-container]').forEach(function(el) {
            el = el.querySelector(el.getAttribute('data-tw-tenant-item-container'));
            self.containers.push({
                el: el,
                elements: el.querySelectorAll(':scope > [data-tw-tenant-item]')
            });
        });
    };

    /**
     * Update UI after data change.
     */
    MyReports.prototype.update = function() {
        if (this.createButtonEl) {
            const url = new URL(this.createButtonEl.href);
            if (this.tenant === 'ANY' || this.tenant === 'NONE' || !this.canCreate) {
                url.searchParams.delete('tenantid');
            } else {
                url.searchParams.set('tenantid', this.tenant);
            }
            this.createButtonEl.href = url;
            if (this.canCreate) {
                this.createButtonEl.removeAttribute('disabled');
                this.createButtonEl.removeAttribute('aria-disabled');
            } else {
                this.createButtonEl.setAttribute('disabled', '');
                this.createButtonEl.setAttribute('aria-disabled', '');
            }
        }

        this.filterToTenant();

        if (this.countEl) {
            const count = Array.from(this.tilesEl.children).filter(function(child) {
                return getComputedStyle(child).display != 'none';
            }).length;
            // eslint-disable-next-line no-nested-ternary
            this.countEl.textContent = this.countEl.getAttribute('data-tw-text-template-' + (count > 0 ? (count > 1 ? 'many' : 'one') : 'none'))
                .replace(/\{\$a\}/, count);
        }
    };

    /**
     * Apply tenant filter.
     */
    MyReports.prototype.filterToTenant = function() {
        const self = this;

        this.el.querySelectorAll('[data-tw-tenant-item]:not([data-tw-tenant-item-container] [data-tw-tenant-item])').forEach(function(el) {
            el.style.display = self.isTenantVisible(el.getAttribute('data-tw-tenantid')) ? '' : 'none';
        });

        // Tenant items may be in a container. if they are, instead of the
        // normal approach of using display: none, we remove the hidden items
        // from the container entirely.
        this.containers.forEach(function(container) {
            container.el.innerHTML = '';
            container.elements.forEach(function(el) {
                if (self.isTenantVisible(el.getAttribute('data-tw-tenantid'))) {
                    container.el.appendChild(el);
                }
            });
        });
    };

    /**
     * Check if the provided tenant ID should be shown.
     *
     * @param {number|string|null} tenantId
     * @returns {boolean}
     */
    MyReports.prototype.isTenantVisible = function(tenantId) {
        if (this.tenant === 'ANY') {
            return true;
        } else if (this.tenant === 'NONE') {
            return !tenantId;
        } else {
            return tenantId == this.tenant;
        }
    };

    /**
     * Initialisation method.
     *
     * @param {Element} el
     */
    return {
        init: function(el) {
            (new MyReports()).attach(el);
        }
    };
});
