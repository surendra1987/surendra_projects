/**
 * This file is part of Totara Learn
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
 * @author Maxime Claudel <maxime.claudel@totara.com>
 * @package totara_catalog
 * @subpackage search_suggestion
 */

define([], function() {

  /**
   * Class constructor for the SearchSuggestion.
   *
   * @class
   * @constructor
   */
  function SearchSuggestion() {
    if (!(this instanceof SearchSuggestion)) {
      return new SearchSuggestion();
    }
    this.widget = '';
  }

  SearchSuggestion.prototype = {

    /**
     * Add event listeners
     *
     */
    events: function() {
      const that = this;

      // Click handler
      this.widget.addEventListener('click', function(e) {
        if (!e.target || !e.target.closest('a')) {
          return;
        }
        e.preventDefault();
        const element = e.target.closest('a');
        const suggestedSearch = element.getAttribute('data-suggestion');
        that.triggerEvent('accepted', {'suggestion': suggestedSearch});
      });
    },

    /**
     * Set parent
     *
     * @param {node} parent
     */
    setParent: function(parent) {
      this.widget = parent;
    },

    /**
     * Trigger event
     *
     * @param {string} eventName
     * @param {object} data
     */
    triggerEvent: function(eventName, data) {
      const propagateEvent = new CustomEvent('totara_catalog/search_suggestion:' + eventName, {
        bubbles: true,
        detail: data
      });
      this.widget.dispatchEvent(propagateEvent);
    }
  };

  /**
   * Initialisation method
   *
   * @param {node} parent
   * @returns {Object} promise
   */
  const init = function(parent) {
    return new Promise(function(resolve) {
      const wgt = new SearchSuggestion();
      wgt.setParent(parent);
      wgt.events();
      resolve(wgt);
    });
  };

  return {
    init: init
  };
});