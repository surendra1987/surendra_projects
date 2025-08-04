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

import { defineComponent, h, ref } from 'vue';
import tui from '../tui';
import { memoize } from '../util';

const getModalPresenter = memoize(() =>
  tui.defaultExport(tui.require('tui/components/modal/ModalPresenter'))
);

/**
 * Show a modal.
 *
 * @param {object} opts
 * @param {*} opts.component Vue component for modal.
 * @param {object} opts.props
 * @param {function} opts.onClose Called when modal closes.
 * @param {function} opts.onCloseComplete Called when modal has finished closing.
 * @returns {{ close: Function }}
 */
export function showModal({ component, props, onClose, onCloseComplete }) {
  const el = document.createElement('div');
  document.body.appendChild(el);

  const container = { component, props };

  const compRef = ref(null);

  let mounted = true;

  const app = tui.mount(
    ModalPresenterWrap,
    {
      ref: compRef,
      container,
      onClose: e => {
        if (onClose) onClose(e);
      },
      onCloseComplete: e => {
        mounted = false;
        app.unmount();
        el.remove();
        if (onCloseComplete) onCloseComplete(e);
      },
    },
    el
  );

  compRef.value.show();

  return {
    close() {
      if (mounted) {
        compRef.value.requestClose();
      }
    },
  };
}

const ModalPresenterWrap = defineComponent({
  props: { container: { type: Object } },

  emits: ['close', 'close-complete'],

  data: () => ({ result: null, open: false }),

  methods: {
    show() {
      this.open = true;
    },
    requestClose(e) {
      this.result = e;
      this.open = false;
      this.$emit('close', e);
    },
    closeComplete() {
      this.$emit('close-complete', this.result);
    },
  },

  render() {
    return h(
      getModalPresenter(),
      {
        open: this.open,
        onRequestClose: this.requestClose,
        onCloseComplete: this.closeComplete,
      },
      () => [h(this.container.component, this.container.props)]
    );
  },
});
