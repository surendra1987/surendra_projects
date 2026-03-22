/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totaralearning.com] for more information.
 *
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

/* eslint-disable vue/component-definition-name-casing */
/* eslint-disable vue/one-component-per-file */

import { uniqueId, totaraUrl } from './util';
import { trapFocusOnTab } from './dom/focus';

const hasOwnProperty = Object.prototype.hasOwnProperty;

export default {
  install(app) {
    const componentUids = new WeakMap();

    function getUid(comp) {
      let uid = componentUids.get(comp);
      if (!uid) {
        uid = 'uid-' + uniqueId();
        componentUids.set(comp, uid);
      }
      return uid;
    }

    app.mixin({
      computed: {
        uid() {
          return getUid(this);
        },
      },
    });

    const gp = app.config.globalProperties;

    /**
     * Get a globally unique ID string scoped to within this component.
     *
     * Equivalent to this.uid + '-' + id.
     *
     * @param {string=} id
     */
    gp.$id = function(id) {
      return id ? getUid(this) + '-' + id : getUid(this);
    };

    /**
     * Get a reference (#) to an ID string.
     *
     * Prepends # to the result of $id().
     *
     * @param {string=} id
     */
    gp.$idRef = function(id) {
      return '#' + this.$id(id);
    };

    gp.$url = totaraUrl;

    // expose window as $window to avoid using a global
    /* istanbul ignore else */
    if (typeof window !== 'undefined') {
      gp.$window = window;
    }

    // trap focus inside the specified element
    const trapFocusHandlers = new WeakMap();
    app.directive('trap-focus', {
      mounted(el) {
        const handler = e => {
          if (e.key == 'Tab') {
            trapFocusOnTab(el, e);
          }
        };
        document.addEventListener('keydown', handler);
        trapFocusHandlers.set(el, handler);
      },

      unmounted(el) {
        const handler = trapFocusHandlers.get(el);
        document.removeEventListener('keydown', handler);
        trapFocusHandlers.delete(el);
      },
    });

    // toggle class if inner node has focus
    const focusHandlers = new WeakMap();
    const focusHandlerClass = 'tui-focusWithin';
    app.directive('focus-within', {
      mounted(el, binding) {
        if (hasOwnProperty.call(binding, 'value') && !binding.value) {
          return;
        }
        let hasFocusWithin = false;
        const inHandler = () => {
          let olds = document.getElementsByClassName(focusHandlerClass);

          Array.prototype.forEach.call(olds, old => {
            if (!el.contains(old)) {
              focusHandlers.get(old).hasFocusWithin = false;
              old.classList.remove(focusHandlerClass);
            }
          });
          el.classList.add(focusHandlerClass);
          focusHandlers.get(el).hasFocusWithin = true;
        };
        const outHandler = () => {
          el.classList.remove(focusHandlerClass);
          focusHandlers.get(el).hasFocusWithin = false;
        };

        el.addEventListener('focusin', inHandler);
        el.addEventListener('focusout', outHandler);
        focusHandlers.set(el, { inHandler, outHandler, hasFocusWithin });
      },

      // Class is removed when updating component, readd if it was active
      updated: function(el) {
        if (!focusHandlers.has(el)) {
          return;
        }
        if (focusHandlers.get(el).hasFocusWithin) {
          el.classList.add(focusHandlerClass);
        }
      },

      unmounted(el) {
        if (!focusHandlers.has(el)) {
          return;
        }
        const elEvents = focusHandlers.get(el);
        el.removeEventListener('focusin', elEvents.inHandler);
        el.removeEventListener('focusout', elEvents.outHandler);
        focusHandlers.delete(el);
      },
    });

    // Passthrough component, renders its default slot without any wrapping
    // element.
    // Useful for e.g. <component :is="var ? 'MyWrapper' : 'passthrough'">
    app.component('passthrough', function(props, { slots }) {
      return slots.default && slots.default({});
    });

    app.component('render', function(props) {
      return props.vnode;
    });
  },
};
