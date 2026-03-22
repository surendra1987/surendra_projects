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
 * @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @module editor_weka
 */

import { ResolvedPos } from 'ext_prosemirror/model';
import { defineComponent, h, markRaw, nextTick, reactive } from 'vue';
import { getClosestScrollable } from 'tui/dom/scroll';
import { position } from 'tui/lib/popover';
import { Rect, Size } from 'tui/geometry';
import { getBoundingClientRect } from 'tui/dom/position';

export default class Suggestion {
  /**
   * Create a new suggestion instance.
   *
   * @param {Editor} editor
   */
  constructor(editor) {
    this._size = null;
    this._editor = editor;
    this._updatePosition = this._updatePosition.bind(this);
  }

  /**
   *
   * @param {RegExp} $regex
   * @param {ResolvedPos} $position
   * @return {{range: {from: *, to: *}, text: string}|null}
   */
  matcher($regex, $position) {
    if (!$position || !($position instanceof ResolvedPos)) {
      return null;
    }

    const text = $position.doc.textBetween(
      $position.start(),
      $position.pos,
      '\n',
      '\0'
    );

    // Empty string, no point to return the matcher.
    if (text === '') {
      return null;
    }

    let match = $regex.exec(text);
    if (!match) {
      return null;
    }

    let from = match.index + $position.start(),
      to = from + match[0].length;

    return {
      range: {
        from,
        to,
      },
      text: match[0],
    };
  }

  /**
   * Destroy the instance.
   */
  destroyInstance() {
    if (this._scrollableContainers) {
      this._scrollableContainers.forEach(x =>
        x.removeEventListener('scroll', this._updatePosition)
      );
      this._scrollableContainers = null;
    }

    window.removeEventListener('resize', this._updatePosition);

    if (this._handle) {
      this._handle.remove();
      this._handle = null;
    }
  }

  /**
   * Resets the component object.
   * @returns {{apply: boolean, from: number, text: string, to: number}}
   */
  resetComponent() {
    console.warn(
      '[editor_weka] The function Suggestion.resetComponent() had been deprecated, ',
      'please do not use'
    );

    return { apply: false, text: '', from: 0, to: 0 };
  }

  /**
   * Show popup with the list of matching suggestions.
   *
   * @param {object} opts
   * @param {EditorView} opts.view
   * @param {object} opts.component
   * @param {object} opts.range
   * @param {string} opts.text
   */
  async showList({ view, component, state: { range, text } }) {
    if (this._handle !== null) {
      this.destroyInstance();
    }

    const element = document.createElement('span');
    document.body.appendChild(element);

    this._view = view;
    this._range = range;

    this._location = reactive(this._getLocation());
    const props = reactive(
      component.props
        ? Object.assign({}, component.props, { location: this._location })
        : {
            location: this._location,
            pattern: text,
            area: this._editor.identifier.area || undefined,
            contextId: this._editor.identifier.contextId || undefined,
            component: this._editor.identifier.component || undefined,
            instanceId: this._editor.identifier.instanceId || undefined,
          }
    );

    Object.assign(props, {
      onItemSelected: ({ id, text }) => {
        this.destroyInstance();

        // Add the component node into the editor.
        const node = this.getNode(
          view.state,
          component.name,
          component.attrs(id, text)
        );
        this.convertToNode(range, node);
      },
      onDismiss: () => {
        this.destroyInstance();
        this._editor.view.focus();
      },
    });

    this._handle = this._editor.addExtraComponent(SuggestionComponentWrap, {
      childComponent: markRaw(component.component),
      childProps: props,
      onResize: size => {
        this._size = size;
        this._updatePosition();
      },
    });

    this._scrollableContainers = [];
    let scrollable = getClosestScrollable(this._editor.viewExtrasLiveEl);
    while (scrollable) {
      this._scrollableContainers.push(scrollable);
      scrollable.addEventListener('scroll', this._updatePosition);
      scrollable = getClosestScrollable(scrollable.parentNode);
    }
    this._scrollableContainers.push(document);
    document.addEventListener('scroll', this._updatePosition);

    window.addEventListener('resize', this._updatePosition);

    nextTick(this._updatePosition);
  }

  /**
   * Update position of suggestion menu
   */
  _updatePosition() {
    Object.assign(this._location, this._getLocation());
  }

  /**
   * Work out location to place suggestion menu
   *
   * @returns {{x: number, y: number}}
   */
  _getLocation() {
    const html = document.documentElement;
    const parentCoords = getBoundingClientRect(document.body);
    const refCoords = this._view.coordsAtPos(this._range.from);
    const refRect = Rect.fromPositions({
      left: refCoords.left,
      top: refCoords.top,
      right: refCoords.right + 1,
      bottom: refCoords.bottom,
    }).sub(parentCoords.getPosition());
    const viewport = new Rect(0, 0, html.clientWidth, html.clientHeight).sub(
      parentCoords.getPosition()
    );

    const pos = position({
      position: ['bottom', 'left'],
      ref: refRect,
      viewport,
      size: this._size ?? new Size(50, 50),
      padding: 0,
    });

    return {
      x: pos.location.x,
      y: pos.location.y,
    };
  }

  /**
   * Gets the node to replace text.
   * @param {EditorState} state
   * @param {String} name
   * @param {Object} props
   * @returns {Node}
   */
  getNode(state, name, props) {
    return state.schema.node(name, props);
  }

  /**
   * Convert text to component node.
   * @param {Object} range
   * @param {Object} node
   */
  convertToNode(range, node) {
    this._editor.execute((state, dispatch) => {
      let tr = this.createTransaction(state, range, node);

      // Dispatch the transaction with the above changes.
      dispatch(tr);

      // Bring back the focus to the editor.
      this._editor.view.focus();
    });
  }

  /**
   * Create transaction to replace text with node.
   *
   * @param {EditorState} state
   * @param {Object} range
   * @param {Node} node
   * @returns {Transaction}
   */
  createTransaction(state, range, node) {
    let tr = state.tr;

    // Replace the typed text with the selected node component.
    tr = tr.replaceWith(range.from, range.to, node);

    return tr;
  }

  /**
   *
   * @param {Transaction} transaction
   * @param {{
   *  active: Boolean,
   *  range: Object,
   *  text: String|null
   * }} oldState
   * @param {RegExp} regex
   * @param {Object|null} cache - deprecated
   * @return {Object}
   */
  apply(transaction, oldState, regex, cache) {
    if (cache) {
      console.warn(
        '[editor_weka] The fourth parameter of function Suggestion.apply() had been deprecated ',
        'and no longer needed. Please update all calls'
      );
    }

    const { selection } = transaction;
    let next = Object.assign({}, oldState);

    if (selection.from === selection.to) {
      const match = this.matcher(regex, selection.$from);

      if (match) {
        next.active = true;
        next.range = match.range;
        next.text = match.text;

        return next;
      }
    }

    next.active = false;
    next.range = {};
    next.text = null;

    return next;
  }
}

const SuggestionComponentWrap = defineComponent({
  props: {
    childComponent: Object,
    childProps: Object,
  },

  emits: ['resize', 'instance'],

  mounted() {
    this.$emit('instance', this);
    this.$_el = this.$refs.foo.$el;
    document.body.appendChild(this.$_el);
    this._resizeObserver = new ResizeObserver(this.measure);
    this._resizeObserver.observe(this.$el);
    this.measure();
  },

  beforeUnmount() {
    this._resizeObserver.disconnect();
    this.$_el.remove();
  },

  methods: {
    measure() {
      this.$emit(
        'resize',
        new Size(this.$_el.offsetWidth, this.$_el.offsetHeight)
      );
    },
  },

  render() {
    return h(
      'div',
      {},
      h(this.childComponent, {
        ...this.childProps,
        ref: 'foo',
      })
    );
  },
});
