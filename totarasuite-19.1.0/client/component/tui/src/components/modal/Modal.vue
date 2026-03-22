<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Simon Chester <simon.chester@totaralearning.com>
  @module tui
-->

<template>
  <div
    ref="modal"
    role="dialog"
    :aria-labelledby="ariaLabelledby"
    :aria-label="ariaLabel"
    class="tui-modal-wrap tui-modal-wrap--animated"
    :class="[
      shade && 'tui-modal-wrap--shade',
      modalIn && 'tui-modal-wrap--in',
      computedType && 'tui-modal-wrap--type-' + computedType,
    ]"
    tabindex="-1"
    @mousedown="handleModalOuterMousedown"
  >
    <div ref="backdrop" class="tui-modal-wrap__backdrop" />
    <div
      class="tui-modal tui-modal--animated"
      :class="[
        shade && 'tui-modal--shade',
        modalIn && 'tui-modal--in',
        forceScroll && 'tui-modal--always-scroll',
        computedSize && 'tui-modal--size-' + computedSize,
        computedType && 'tui-modal--type-' + computedType,
        errorModal && 'tui-modal--error',
      ]"
    >
      <CloseButton
        v-if="dismissableSources.overlayClose"
        :aria-label="$str('closebuttontitle', 'core')"
        :class="'tui-modal__outsideClose'"
        :size="300"
        @click="dismiss()"
      />

      <div class="tui-modal__pad">
        <div ref="inner" class="tui-modal__inner">
          <CloseButton
            v-if="dismissableSources.overlayClose"
            :aria-label="$str('closebuttontitle', 'core')"
            :class="'tui-modal__close'"
            :size="300"
            @click="dismiss()"
          />
          <PropsProvider :provide="provideSlot">
            <slot />
          </PropsProvider>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { nextTick } from 'vue';
import { waitForTransitionEnd } from 'tui/dom/transitions';
import { trapFocusOnTab } from 'tui/dom/focus';
import { bodySetModalOpen } from '../../js/internal/body_modal';
import { presenterInterfaceName } from 'tui/components/modal/ModalPresenter';
import PropsProvider from 'tui/components/util/PropsProvider';
import CloseButton from 'tui/components/buttons/CloseIcon';
import { addListener, removeListener } from 'tui/dom/keyboard_stack';
import { pull } from 'tui/util';

const modalStack = [];

export default {
  components: {
    CloseButton,
    PropsProvider,
  },

  inject: {
    [presenterInterfaceName]: { default: null },
  },

  provide() {
    // hide presenter interface to avoid things inside modal
    // (including other modals) accidentally accessing it
    return { [presenterInterfaceName]: null };
  },

  props: {
    open: Boolean,
    type: {
      type: String,
      default: 'normal',
      validator: x => x === 'normal' || x === 'sheet' || x === 'drawer',
    },
    size: {
      type: String,
      default: 'normal',
      validator: x => x === 'small' || x === 'normal' || x === 'large',
    },
    dismissable: {
      type: [Boolean, Object],
      default: true,
    },
    // tint the backdrop?
    shade: {
      type: Boolean,
      default: true,
    },
    ariaLabelledby: String,
    ariaLabel: String,
    errorModal: Boolean,
  },

  emits: ['close'],

  data() {
    return {
      isOpen: false,
      closing: false,
      forceScroll: false,
      modalIn: false,
    };
  },

  computed: {
    presenterOpen() {
      return (
        this[presenterInterfaceName] && this[presenterInterfaceName].data.open
      );
    },

    shouldBeOpen() {
      return this.open || this.presenterOpen;
    },

    dismissableSources() {
      const defaultDismissable = {
        overlayClose: this.computedType == 'sheet',
        esc: true,
        backdropClick: true,
      };
      if (this.dismissable === true) {
        return defaultDismissable;
      } else if (this.dismissable === false) {
        return {};
      } else {
        return Object.assign(defaultDismissable, this.dismissable);
      }
    },

    computedSize() {
      if (this.size === 'sheet' && process.env.NODE_ENV !== 'production') {
        console.warn(
          '[Modal] size="sheet" was deprecated in Totara 18.0, please use type="sheet" instead'
        );
      }
      if (this.type === 'sheet' || this.size === 'sheet') {
        return null;
      }
      return this.size;
    },

    computedType() {
      return !this.type && this.size === 'sheet' ? 'sheet' : this.type;
    },
  },

  watch: {
    shouldBeOpen(open) {
      if (open) {
        this.$_open();
      } else {
        this.$_close();
      }
    },

    isOpen(isOpen) {
      if (this[presenterInterfaceName]) {
        this[presenterInterfaceName].setIsOpen(isOpen);
      }
    },
  },

  created() {
    this.$_openDisposables = [];
  },

  mounted() {
    this.$el.remove();
    this.$_removeElements();
    if (this.shouldBeOpen) {
      this.$_open();
    }
  },

  beforeUnmount() {
    this.$_removeElements();
    removeListener('Escape', this.$_handleEscape);
    removeListener('Tab', this.$_trapFocus);
    this.$_openDisposables.forEach(x => x());
    this.$_openDisposables = [];
    if (this.$_bodyModalHandle) {
      this.$_bodyModalHandle.close();
    }
  },

  methods: {
    dismiss() {
      this.requestClose();
    },

    requestClose(result) {
      this.$_emitRequestClose(result);
    },

    $_open() {
      if (this.isOpen) {
        return;
      }
      this.isOpen = true;
      this.$_bodyModalHandle = bodySetModalOpen();
      this.forceScroll = this.$_bodyModalHandle.scroll;

      addListener('Escape', this.$_handleEscape);
      addListener('Tab', this.$_trapFocus);

      // accessibility: save previously focused element to restore when the dialog is closed
      this.previousFocus = document.activeElement;

      // accessibility: focus first focusable element within modal
      nextTick(() => {
        this.$refs.modal?.focus();
      });

      this.$_animateOpen().then(() => {
        this.opening = false;
        if (!this.isOpen) {
          // handle modal being closed before it loads
          this.$_close();
        }
      });
    },

    $_animateOpen() {
      return new Promise(resolve => {
        document.body.appendChild(this.$refs.modal);
        modalStack.push(this);

        // force reflow
        this.$refs.modal.offsetHeight;

        this.modalIn = true;
        resolve();
      });
    },

    $_close() {
      if (!this.closing) {
        this.closing = true;
        this.$_animateClose().then(() => {
          if (this.$_bodyModalHandle) {
            this.$_bodyModalHandle.close();
          }
          this.closing = false;
          this.isOpen = false;

          removeListener('Escape', this.$_handleEscape);
          removeListener('Tab', this.$_trapFocus);
          this.$_openDisposables.forEach(x => x());
          this.$_openDisposables = [];

          // accessibility: restore focus to previous element when modal closed
          if (this.previousFocus) {
            this.previousFocus.focus();
          }

          // handle case where open is toggled off and back on rapidly
          if (this.shouldBeOpen) {
            this.$_open();
          }
        });
      }
    },

    async $_animateClose() {
      this.modalIn = false;

      const transitionEls = [
        this.$refs.modal,
        this.$refs.inner,
        this.$refs.backdrop,
      ].filter(Boolean);

      await waitForTransitionEnd(transitionEls);

      this.$_removeElements();
    },

    $_emitRequestClose(result) {
      let ok = true;

      this.$emit('close', {
        result,
        cancel() {
          ok = false;
        },
      });

      if (this[presenterInterfaceName] && ok) {
        this[presenterInterfaceName].requestClose(result);
      }
    },

    /**
     * Traps tab focus to to the modal
     *
     * @param {Object} e The (slightly modified) KeyboardEvent to handle
     */
    $_trapFocus(e) {
      if (this.$_isCurrentModal()) {
        trapFocusOnTab(this.$refs.modal, e);
      }
    },

    /**
     * Handles an escape keypress - closing the modal if specified
     *
     * @param {Object} event A lightly modified keyboard event
     */
    $_handleEscape(event) {
      if (this.$_isCurrentModal() && this.dismissableSources.esc) {
        event.stopPropagation();
        event.preventDefault();
        this.dismiss();
      }
    },

    /**
     * Handle mousedown on outermost modal element.
     *
     * @param {MouseEvent} mousedownEvent
     */
    handleModalOuterMousedown(mousedownEvent) {
      if (
        this.$refs.modal.contains(mousedownEvent.target) &&
        !this.$refs.inner.contains(mousedownEvent.target)
      ) {
        // prevent default (select) for backdrop mousedown
        mousedownEvent.preventDefault();
      }

      // attach temporary mouseup handler
      const handler = mouseupEvent => {
        dispose();

        // to trigger backdrop click, neither mousedown nor mouseup can have
        // been within inner
        if (
          this.$refs.modal &&
          this.$refs.modal.contains(mouseupEvent.target) &&
          this.$refs.inner &&
          !this.$refs.inner.contains(mousedownEvent.target) &&
          !this.$refs.inner.contains(mouseupEvent.target)
        ) {
          if (this.dismissableSources.backdropClick) {
            this.dismiss();
          }
        }
      };

      this.$refs.modal.addEventListener('mouseup', handler);
      const dispose = () => {
        this.$refs.modal.removeEventListener('mouseup', handler);
        pull(this.$_openDisposables, dispose);
      };
      this.$_openDisposables.push(dispose);
    },

    provideSlot() {
      return {
        listeners: {
          dismiss: this.dismiss,
        },
      };
    },

    $_removeElements() {
      this.$refs.modal.remove();
      pull(modalStack, this);
    },

    /**
     * Check to see if this is the modal at the top of the stack.
     *
     * @returns {boolean}
     */
    $_isCurrentModal() {
      return modalStack[modalStack.length - 1] === this;
    },
  },
};
</script>

<style lang="scss">
$tui-modal-smallSize: 400px !default;
$tui-modal-normalSize: 560px !default;
$tui-modal-largeSize: 800px !default;
$tui-modal-sheetBreakpoint: 768px !default;

.tui-modal-wrap {
  position: fixed;
  top: 0;
  left: 0;
  z-index: var(--zindex-modal);
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  overflow: hidden;
  outline: none;

  &__backdrop {
    position: absolute;
    inset: 0;
    display: none;
  }

  &--shade {
    .tui-modal-wrap__backdrop {
      display: block;
      background-color: var(--color-backdrop-standard);
    }

    &.tui-modal-wrap--type-sheet .tui-modal-wrap__backdrop {
      background-color: var(--color-backdrop-heavy);
    }

    &.tui-modal-wrap--animated {
      .tui-modal-wrap__backdrop {
        opacity: 0;
        transition: opacity var(--transition-modal-function)
          var(--transition-modal-duration);
      }
      &.tui-modal-wrap--in .tui-modal-wrap__backdrop {
        opacity: 1;
      }
    }
  }
}

.tui-modal {
  position: fixed;
  top: 0;
  left: 0;
  z-index: var(--zindex-modal);
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  overflow: hidden;
  outline: none;

  &--animated {
    .tui-modal__inner {
      transform: translateY(100vh);
      transition: transform var(--transition-modal-function)
          var(--transition-modal-duration),
        opacity var(--transition-modal-function)
          var(--transition-modal-duration);
    }

    &.tui-modal--in .tui-modal__inner {
      transform: translateY(0);
    }
  }

  &--error {
    z-index: var(--zindex-error-modal);
  }

  &.tui-modal--type-sheet {
    .tui-modal__inner {
      overflow: auto;
    }
  }

  &--shade {
    &.tui-modal--animated {
      &.tui-modal--in {
        opacity: 1;
      }
    }
  }

  &__pad {
    width: 100%;
    height: 100%;
    padding: 0;
  }

  &__inner {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    margin: auto;
    color: var(--color-text);
    background-color: var(--color-background);
    box-shadow: var(--shadow-4);
  }

  &__header {
    display: flex;
    flex-shrink: 0;
  }

  &__close,
  &__outsideClose {
    position: absolute;
    top: 0;
    right: 0;
    display: flex;
    padding: var(--gap-4);
    font-size: font-size-px(18);
  }

  &__outsideClose {
    display: none;
    color: var(--color-backdrop-contrast);
  }

  &__outsideClose:hover,
  &__outsideClose:focus {
    color: var(--color-backdrop-contrast);
    opacity: 0.8;
  }
}

.has-tui-modal {
  overflow: hidden;
}

@media (min-width: $tui-modal-sheetBreakpoint) {
  .tui-modal--type-sheet {
    &.tui-modal--animated {
      .tui-modal__inner {
        transform: scale(0.9);
        opacity: 0;
      }

      &.tui-modal--in .tui-modal__inner {
        transform: none;
        opacity: 1;
      }

      .tui-modal__outsideClose {
        opacity: 0;
        transition: opacity var(--transition-modal-function)
          var(--transition-modal-duration);
      }

      &.tui-modal--in .tui-modal__outsideClose {
        opacity: 1;
      }

      &.tui-modal--in .tui-modal__outsideClose:hover,
      &.tui-modal--in .tui-modal__outsideClose:focus {
        opacity: 0.8;
      }
    }

    .tui-modal {
      &__pad {
        padding: var(--modal-sheet-padding);
      }

      &__inner {
        border-radius: var(--modal-border-radius);
      }

      &__close {
        display: none;
      }

      &__outsideClose {
        display: flex;
      }
    }
  }
}

@mixin tui-modal-size($name, $width) {
  @media (min-width: ($width + 75px)) {
    .tui-modal--type-normal.tui-modal--size-#{$name} {
      overflow-y: auto;

      &.tui-modal--always-scroll {
        overflow-y: scroll;
      }

      &.tui-modal--animated {
        .tui-modal__inner {
          transform: scale(0.9);
          opacity: 0;
        }

        &.tui-modal--in .tui-modal__inner {
          transform: none;
          opacity: 1;
        }
      }

      // a separate __pad element is required as flexbox centering with
      // `margin-top/bottom: auto;` and padding on the parent are not compatible
      .tui-modal {
        &__pad {
          height: auto;
          margin: auto;
          padding: var(--modal-container-padding) 0;
        }

        &__inner {
          width: $width;
          height: auto;
          border-radius: var(--modal-border-radius);
        }

        &__close {
          display: none;
        }

        &__outsideClose {
          display: flex;
        }
      }
    }

    @media (min-width: ($width / (3/4))) {
      .tui-modal--type-drawer.tui-modal--size-#{$name} {
        &.tui-modal--animated {
          .tui-modal__inner {
            transform: translateX($width);
            .dir-rtl & {
              transform: translateX(-$width);
            }
          }

          &.tui-modal--in .tui-modal__inner {
            transform: none;
          }
        }

        .tui-modal__pad {
          width: 100%;
          height: 100%;
          padding: 0;
        }

        .tui-modal__inner {
          width: $width;
          height: 100%;
          margin: 0;
          margin-left: auto;
          border-left: var(--border-width-thin) solid var(--color-neutral-5);
        }

        &.tui-modal--shade .tui-modal__inner {
          border: none;
          box-shadow: var(--shadow-4);
        }
      }
    }
  }
}

@include tui-modal-size('small', $tui-modal-smallSize);
@include tui-modal-size('normal', $tui-modal-normalSize);
@include tui-modal-size('large', $tui-modal-largeSize);
</style>
