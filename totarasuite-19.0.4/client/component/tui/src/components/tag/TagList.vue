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

  @author Alvin Smith <alvin.smith@totaralearning.com>
  @module tui
-->

<template>
  <Dropdown
    :close-on-click="closeOnClick"
    :separator="separator"
    match-width
    :fixed-height="!!virtualScrollOptions"
    :restore-focus-to="$refs.input"
    @open="$emit('open')"
  >
    <template v-slot:trigger="{ toggle, isOpen }">
      <div
        class="tui-tagList"
        :class="{
          'tui-tagList--hasFocus': inputHasFocus,
        }"
        @click="handleClick(toggle, isOpen)"
      >
        <div class="tui-tagList__tags">
          <OverflowDetector v-slot="{ measuring }" @change="overflowChanged">
            <div
              class="tui-tagList__tagItems"
              :class="{
                'tui-tagList__tagItems--open': isOpen || inputHasFocus,
              }"
              aria-live="polite"
              role="status"
              aria-atomic="false"
              :aria-label="
                labelName
                  ? $str('selected_tag_list_name', 'totara_core', labelName)
                  : $str('tags_selected', 'totara_core')
              "
              aria-relevant="additions"
            >
              <template v-for="(tag, index) in tags" :key="index">
                <div class="tui-tagList__tagItem">
                  <slot
                    v-if="isOpen || measuring || index < visible"
                    name="tag"
                    :tag="tag"
                  >
                    <Tag :text="tag.text">
                      <template v-slot:button>
                        <ButtonIcon
                          ref="tagIcon"
                          :disabled="disabled"
                          :styleclass="{
                            transparent: true,
                            primary: true,
                            small: true,
                          }"
                          :aria-label="
                            labelName
                              ? $str('tag_remove_from', 'totara_core', {
                                  name: tag.text,
                                  taglist: labelName,
                                })
                              : $str('tag_remove', 'totara_core', tag.text)
                          "
                          @click.stop.prevent="handleRemove(tag, index)"
                        >
                          <Close size="100" />
                        </ButtonIcon>
                      </template>
                    </Tag>
                  </slot>
                </div>
              </template>
              <input
                ref="input"
                v-model="itemName"
                class="tui-tagList__input"
                :disabled="disabled"
                :placeholder="placeholderText"
                :aria-label="
                  $str(
                    'filter_x_taglist',
                    'totara_core',
                    labelName || $str('tag_list', 'totara_core')
                  )
                "
                @focus="e => handleInputFocus(e, toggle, isOpen)"
                @blur="handleInputBlur"
                @keydown="e => handleInputKeydown(e, toggle, isOpen)"
              />
            </div>
          </OverflowDetector>
          <span
            v-show="!isOpen && tags.length > visible"
            class="tui-tagList__suffix"
            >{{ $str('n_more', 'totara_core', tags.length - visible) }}</span
          >
        </div>
        <button
          class="tui-tagList__expandArrow"
          :aria-expanded="isOpen.toString()"
          :aria-label="
            labelName
              ? $str('tag_list_x', 'totara_core', labelName)
              : $str('tag_list', 'totara_core')
          "
          aria-haspopup="menu"
          :disabled="disabled"
          @click.stop.prevent="expandList(toggle, isOpen)"
        />
      </div>
    </template>
    <Loader v-if="loading" :loading="true" class="tui-tagList__loading" />
    <template v-else-if="virtualScrollOptions">
      <VirtualScroll
        :data-key="virtualScrollOptions.dataKey"
        :data-list="items"
        :aria-label="virtualScrollOptions.ariaLabel"
        :is-loading="virtualScrollOptions.isLoading || false"
        :start="virtualScrollOptions.start"
        :offset="virtualScrollOptions.offset"
        :top-threshold="virtualScrollOptions.topThreshold"
        :bottom-threshold="virtualScrollOptions.bottomThreshold"
        :use-role="false"
        @scrolltop="onScrollToTop"
        @scrollbottom="onScrollToBottom"
      >
        <template v-slot:item="{ item, index }">
          <DropdownItem :key="index" @click="dropdownItemClicked(item, index)">
            <slot name="item" :item="item" :index="index" />
          </DropdownItem>
        </template>
        <template v-slot:footer>
          <div class="loader-wrapper">
            <Loader :loading="virtualScrollOptions.isLoading" />
          </div>
        </template>
      </VirtualScroll>
    </template>
    <template v-for="(item, index) in items" v-else :key="index">
      <DropdownItem @click="dropdownItemClicked(item, index)">
        <slot name="item" :item="item" :index="index" />
      </DropdownItem>
    </template>
  </Dropdown>
</template>

<script>
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Close from 'tui/components/icons/Close';
import Tag from 'tui/components/tag/Tag';
import OverflowDetector from 'tui/components/util/OverflowDetector';
import VirtualScroll from 'tui/components/virtualscroll/VirtualScroll';
import { validatePropObject } from 'tui/vue_util';
import Loader from 'tui/components/loading/Loader';
import { debounce } from '../../js/util';
import { getString } from 'tui/i18n';

export default {
  components: {
    ButtonIcon,
    Dropdown,
    DropdownItem,
    Close,
    Tag,
    OverflowDetector,
    VirtualScroll,
    Loader,
  },

  props: {
    labelName: String,
    disabled: {
      type: Boolean,
      default: false,
    },
    tags: Array,
    items: Array,
    filter: String,
    loading: Boolean,
    separator: {
      type: Boolean,
      default: false,
    },
    closeOnClick: {
      type: Boolean,
      default: false,
    },

    // Virtual scroll will be enabled when minimum options
    // are passed (dataKey, ariaLabel)
    virtualScrollOptions: {
      type: Object,
      validator: options => {
        if (!options) {
          return true;
        }

        const required = ['dataKey', 'ariaLabel'];
        const properties = {
          dataKey: 'string',
          ariaLabel: 'string',
          start: 'number',
          offset: 'number',
          topThreshold: 'number',
          bottomThreshold: 'number',
          isLoading: 'boolean',
        };

        return validatePropObject({ options, properties, required });
      },
    },
    inputPlaceholder: {
      type: String,
      default: getString('tag_list_placeholder', 'totara_core'),
    },

    debounceFilter: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['open', 'filter', 'remove', 'select', 'scrolltop', 'scrollbottom'],

  data() {
    return {
      clickedLabelName: '',
      itemName: this.filter || '',
      visible: Infinity,
      inputHasFocus: false,
    };
  },

  computed: {
    placeholderText() {
      return this.tags.length === 0 ? this.inputPlaceholder : null;
    },
  },

  watch: {
    /**
     * Triggers things that need to happen when the taglist is updated
     */
    itemName() {
      if (this.debounceFilter) {
        this.emitFilter(this);
      } else {
        this.$emit('filter', this.itemName);
      }
    },
    filter(value) {
      if (this.itemName != value) {
        this.itemName = value;
      }
    },
  },

  methods: {
    overflowChanged({ visible }) {
      this.visible = visible;
    },
    handleRemove(tag, index) {
      if (this.tags.length === 1) {
        this.focusInput();
      } else if (index == this.tags.length - 1) {
        this.$refs.tagIcon[index - 1].$el.focus();
      }

      this.$emit('remove', tag, index);
    },
    dropdownItemClicked(item, index) {
      this.$emit('select', item, index);
      this.focusInput();
    },
    handleClick(toggle, isOpen) {
      // Open the dropdown when it's closed
      if (!isOpen) {
        // Reset the input value when opening the menu
        this.itemName = '';
        toggle();
      }

      this.focusInput();
    },
    expandList(toggle, isOpen) {
      toggle();

      // Focus on input after dropdown get opened
      if (!isOpen) {
        // Reset the input value when opening the menu
        this.itemName = '';
        this.focusInput();
      }
    },
    focusInput() {
      /**
       * 2 nextTick are required here as the focusInput should be triggered after the menu opened.
       * And detect the menu open status used one nextTicks already.
       */
      this.$nextTick(() => {
        this.$nextTick(() => {
          this.$refs.input.focus();
        });
      });
    },

    onScrollToTop() {
      this.$emit('scrolltop');
    },

    onScrollToBottom() {
      this.$emit('scrollbottom');
    },

    /**
     * Emits the filter event after a time delay to prevent excessive execution
     *
     * @param {Vue} that this Vue object
     */
    emitFilter: debounce(that => {
      that.$emit('filter', that.itemName);
    }, 500),

    /**
     * Sets the focus back to the input (and lets the browser determine where focus should go next)
     */
    setFocus() {
      this.$refs.input.focus();
    },

    handleInputFocus(e, toggle, isOpen) {
      this.inputHasFocus = true;
      if (!isOpen) {
        toggle();
      }
    },

    handleInputBlur() {
      this.inputHasFocus = false;
    },

    handleInputKeydown(e, toggle, isOpen) {
      if (
        e.key === 'Backspace' &&
        this.tags.length > 0 &&
        this.$refs.input.value.length <= 0
      ) {
        const index = this.tags.length - 1;
        this.$emit('remove', this.tags[index], index);
      }
      if (e.key === 'ArrowDown') {
        if (!isOpen) {
          toggle();
        }
      }
    },
  },
};
</script>

<style lang="scss">
.tui-tagList {
  $inner-height: calc(
    var(--form-input-height) - (var(--form-input-border-size) * 2)
  );
  display: flex;
  min-width: rem-px(230);
  min-height: var(--form-input-height);
  color: var(--form-input-text-color);
  font-size: var(--form-input-font-size);
  background: var(--form-input-bg-color);
  border: var(--form-input-border-size) solid var(--form-input-border-color);
  border-radius: var(--form-input-border-radius);

  .tui-contextInvalid & {
    border-color: var(--form-input-border-color-invalid);
    box-shadow: var(--form-input-shadow-invalid);
  }

  &--hasFocus {
    @include tui-focus;

    .tui-contextInvalid & {
      background: var(--form-input-bg-color-invalid-focus);
      border-color: var(--form-input-border-color-invalid);
      outline-color: var(--form-input-border-color-invalid);
      box-shadow: var(--form-input-shadow-invalid-focus);
    }
  }

  &__tags {
    display: flex;
    flex: auto;
    align-items: center;
    min-width: 0;
  }

  &__tagItems {
    display: flex;
    flex-grow: 1;
    gap: var(--gap-1);
    align-items: center;
    min-width: 0;
    padding: calc((#{$inner-height} - var(--tag-height)) / 2) var(--gap-2);

    &--open {
      flex-wrap: wrap;
    }
  }

  &__tagItem {
    display: flex;
    flex-shrink: 0;
    align-items: center;
  }

  &__suffix {
    @include font(body-sm);
    flex-shrink: 0;
    padding-right: var(--gap-2);
    padding-left: var(--gap-1);
    color: var(--color-state);
    white-space: nowrap;
    &:hover {
      cursor: pointer;
    }
  }

  &__input {
    flex-grow: 1;
    height: var(--tag-height);
    background: transparent;
    border: none;
    &:focus {
      outline: none;
    }
  }

  &__expandArrow {
    position: relative;
    width: $inner-height;
    height: $inner-height;
    background: none;
    border: none;

    &::after {
      position: absolute;
      top: calc((#{$inner-height} - var(--select-icon-size)) / 2);
      right: calc((#{$inner-height} - var(--select-icon-size) * 2) / 2);
      display: block;
      width: 0;
      height: 0;
      border: var(--select-icon-size) solid transparent;
      border-top-color: var(--form-input-text-color);
      content: '';
      pointer-events: none;
    }
  }

  &__caret {
    fill: var(--color-neutral-7);
  }

  &__loading {
    margin: var(--gap-4);
  }
}
</style>
