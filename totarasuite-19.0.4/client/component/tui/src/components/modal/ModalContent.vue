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
    class="tui-modalContent"
    :class="{ 'tui-modalContent--noContentPadding': !contentPadding }"
  >
    <div
      class="tui-modalContent__header"
      :class="{
        'tui-modalContent__header--noBottomPadding':
          !closeButton && !titleVisible && !$slots['custom-title'],
      }"
    >
      <div
        :id="titleId"
        ref="modalTitle"
        class="tui-modalContent__header-title"
        :class="{
          'tui-modalContent__header-title--small': titleSmall,
          'tui-modalContent__header-title--sronly': !titleVisible,
        }"
      >
        {{ title || '' }}
        <slot name="title" />
      </div>

      <slot name="custom-title" />

      <div class="tui-modalContent__header-buttons">
        <slot name="header-buttons" />
        <ModalHeaderButton v-if="closeButton" @click="dismiss()">
          <CloseIcon :size="400" />
        </ModalHeaderButton>
      </div>
    </div>
    <div
      class="tui-modalContent__content"
      :class="{
        'tui-modalContent__content--noTopPadding':
          !closeButton && !titleVisible && !$slots['custom-title'],
      }"
    >
      <slot />
    </div>
    <div
      v-if="$slots['footer-content'] || $slots.buttons"
      class="tui-modalContent__footer"
    >
      <slot name="footer-content">
        <div class="tui-modalContent__footer-buttons">
          <ButtonGroup>
            <slot name="buttons" />
          </ButtonGroup>
        </div>
      </slot>
    </div>
  </div>
</template>

<script>
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import ModalHeaderButton from 'tui/components/modal/ModalHeaderButton';
import CloseIcon from 'tui/components/icons/Close';

export default {
  components: {
    ButtonGroup,
    ModalHeaderButton,
    CloseIcon,
  },

  props: {
    title: {
      type: String,
      required: false,
      validator: x => !x || x.slice(0).trim().length > 0,
    },
    titleId: String,
    titleSmall: {
      type: Boolean,
    },
    titleVisible: {
      type: Boolean,
      default: true,
    },
    closeButton: Boolean,
    contentPadding: {
      type: Boolean,
      default: true,
    },
  },

  emits: ['dismiss'],

  mounted() {
    this.$_checkTitle();
  },

  updated() {
    this.$_checkTitle();
  },

  methods: {
    dismiss() {
      this.$emit('dismiss');
    },

    $_checkTitle() {
      if (!this.title && !this.$slots.title) {
        console.error(
          '[ModalContent] You must pass either a title prop or define a title slot.'
        );
      }
    },
  },
};
</script>

<style lang="scss">
.tui-modalContent {
  @include font(body);
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  min-height: 0;

  &__header {
    display: flex;
    flex-shrink: 0;
    align-items: flex-start;
    padding: var(--modal-content-outer-padding);
    padding-bottom: var(--gap-4);
    &--noBottomPadding {
      padding-bottom: 0;
    }

    &-title {
      @include font(h3);
      flex-grow: 1;
      overflow: hidden;
      font-weight: 500;

      &--sronly {
        @include sr-only();
      }

      &--small {
        @include font(h4);
      }
    }

    &-buttons {
      display: flex;
      margin-left: auto;
    }
  }

  &__content {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    min-height: 0;
    padding: var(--gap-2) var(--modal-content-outer-padding);
    overflow-y: auto;
    &--noTopPadding {
      padding-top: 0;
    }
  }

  &--noContentPadding &__content {
    padding: 0;
  }

  &__footer {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    padding: var(--modal-content-outer-padding);
    padding-top: var(--gap-4);
    &-buttons {
      margin-left: auto;
    }
  }

  & > :last-child {
    padding-bottom: var(--modal-content-outer-padding);
  }

  &--noContentPadding {
    & > :last-child {
      padding-bottom: 0;
    }
  }
}
</style>
