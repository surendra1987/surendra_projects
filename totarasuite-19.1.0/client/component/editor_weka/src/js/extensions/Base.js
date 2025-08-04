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
 * @module editor_weka
 */

import { defineComponent, h, markRaw } from 'vue';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import PositionedActionDropdown from 'editor_weka/components/editing/PositionedActionDropdown';
// eslint-disable-next-line no-unused-vars
import { Node, Schema } from 'ext_prosemirror/model';
// eslint-disable-next-line no-unused-vars
import Editor from '../Editor';
import { loadLangStrings } from 'tui/i18n';
import pending from 'tui/pending';

const sleep = ms => {
  const done = pending('sleep');
  return new Promise(resolve =>
    setTimeout(() => {
      done();
      resolve();
    }, ms)
  );
};

export default class BaseExtension {
  /**
   *
   * @param {Object} opt
   */
  constructor(opt) {
    /** @type {Editor} */
    this.editor = null;

    // Storing the options for later usage.
    this.options = Object.assign({}, opt);
  }

  setEditor(editor) {
    this.editor = editor;
  }

  /**
   * Get nodes defined by this extension.
   *
   * @returns {Object}
   */
  nodes() {
    return {};
  }

  /**
   * Get marks defined by this extension.
   *
   * @returns {Object}
   */
  marks() {
    return {};
  }

  /**
   * Get plugins added by this extension.
   *
   * @returns {Array}
   */
  plugins() {
    return [];
  }

  /**
   * Get toolbar items added by this extension.
   *
   * @returns {Array}
   */
  toolbarItems() {
    return [];
  }

  /**
   * Get keyboard shortcuts defined by this extension.
   *
   * @param {(key, command) => void} bind
   */
  // eslint-disable-next-line no-unused-vars
  keymap(bind) {}

  /**
   * Get input rules defined by this extension.
   *
   * @returns {Array}
   */
  inputRules() {
    return [];
  }

  /**
   * @returns {Schema}
   */
  getSchema() {
    return this.editor.schema;
  }

  /**
   *
   * @param {EditorState} state
   */
  applyFormatters(state) {
    return state;
  }

  /**
   * @returns {Node}
   */
  get doc() {
    return this.editor.state.doc;
  }

  async showModal(component, props) {
    const container = { component: markRaw(component), props };

    return new Promise(resolve => {
      const handle = this.editor.addExtraComponent(ModalPresenterWrap, {
        container,
        onCloseComplete(e) {
          handle.remove();
          resolve(e);
        },
      });
    });
  }

  async showActionDropdown(pos, { actions }) {
    const coords = this.editor.view.coordsAtPos(pos);
    const parentCoords = this.editor.viewExtrasEl.offsetParent.getBoundingClientRect();

    // wait for click dispatch to end otherwise Dropdown will catch it
    await sleep(0);

    // load lang strings
    await loadLangStrings(actions.map(x => x.label));

    const handle = this.editor.addExtraComponent(PositionedActionDropdown, {
      location: {
        x: coords.left - parentCoords.left,
        y: coords.bottom - parentCoords.top,
      },
      actions,
      onDismiss() {
        handle.remove();
      },
    });
  }

  /**
   *
   * @param {Function} getRange
   */
  removeNode(getRange) {
    this.editor.execute((state, dispatch) => {
      const transaction = state.tr;
      const range = getRange();

      transaction.delete(range.from, range.to);
      dispatch(transaction);
    });
  }
}

const ModalPresenterWrap = defineComponent({
  props: { container: { type: Object } },

  emits: ['close-complete'],

  data: () => ({ result: null, open: false }),

  mounted() {
    this.open = true;
  },

  methods: {
    requestClose(e) {
      this.result = e;
      this.open = false;
    },
    closeComplete() {
      this.$emit('close-complete', this.result);
    },
  },

  render() {
    return h(
      ModalPresenter,
      {
        open: this.open,
        onRequestClose: this.requestClose,
        onCloseComplete: this.closeComplete,
      },
      () => [h(this.container.component, this.container.props)]
    );
  },
});
