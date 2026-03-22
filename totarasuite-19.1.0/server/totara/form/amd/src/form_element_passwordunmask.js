/**
 * This file is part of Totara LMS
 *
 * Copyright (C) 2016 onwards Totara Learning Solutions LTD
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
 * @author  Sam Hemelryk <sam.hemelryk@totaralms.com>
 * @package totara_form
 */

/**
 * @module  totara_form/form_element_passwordunmask
 * @class   PasswordUnmaskElement
 * @author  Sam Hemelryk <sam.hemelryk@totaralms.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'totara_form/form'], function($, Form) {

    /**
     * Password unmask element
     *
     * @class
     * @constructor
     * @augments Form.Element
     *
     * @param {(Form|Group)} parent
     * @param {string} type
     * @param {string} id
     * @param {HTMLElement} node
     */
    function PasswordUnmaskElement(parent, type, id, node) {

        if (!(this instanceof PasswordUnmaskElement)) {
            return new PasswordUnmaskElement(parent, type, id, node);
        }

        this.input = null;
        this.unmaskinput = null;

        Form.Element.apply(this, arguments);

    }

    PasswordUnmaskElement.prototype = Object.create(Form.Element.prototype);
    PasswordUnmaskElement.prototype.constructor = PasswordUnmaskElement;

    /**
     * Returns a string describing this object.
     * @returns {string}
     */
    PasswordUnmaskElement.prototype.toString = function() {
        return '[object PasswordUnmaskElement]';
    };

    /**
     * Initialises a new instance of this element.
     * @param {Function} done
     */
    PasswordUnmaskElement.prototype.init = function(done) {
        var id = this.id,
            input = $('#' + id);

        this.input = input;
        this.unmaskinput = $('#' + id + 'unmask');
        this.wrap = this.input.parent('.wrap');

        // Watch the unmask changes.
        this.unmaskinput.change($.proxy(this.unmask, this));

        // Update the astrix with each key up.
        this.input.keyup($.proxy(this.updateMask, this));
        this.input.mouseup();

        // Call the changed method when this element is changed.
        this.input.change($.proxy(this.changed, this));

        done();
    };

    /**
     * Unmasks the password.
     */
    PasswordUnmaskElement.prototype.unmask = function() {
        this.input.attr('type', this.unmaskinput.is(":checked") ? 'text' : 'password');
    };

    /**
     * Returns the value of the password field.
     * @returns {String}
     */
    PasswordUnmaskElement.prototype.getValue = function() {
        if (this.input) {
            return this.input.val();
        }
        return null;
    };

    return PasswordUnmaskElement;

});