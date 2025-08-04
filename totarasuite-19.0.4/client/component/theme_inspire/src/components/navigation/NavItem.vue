<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Brian Barnes <brian.barnes@totara.com>
  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module theme_inspire
-->
<template>
  <Tooltip :hidden="navExpanded" position="right">
    <template v-slot:trigger>
      <!-- No children -->
      <div
        v-if="!hasChildren"
        class="tui-theme_inspire-navItem tui-theme_inspire-navItem--link"
        :class="{
          'tui-theme_inspire-navItem--selected': isSelected,
          'tui-theme_inspire-navItem--navExpanded': navExpanded,
        }"
      >
        <a
          :href="url"
          class="tui-theme_inspire-navItem__heading tui-theme_inspire-navItem__heading--link"
          :class="{
            'tui-theme_inspire-navItem__heading--selected': isSelected,
            'tui-theme_inspire-navItem__heading--navExpanded': navExpanded,
            ['tui-theme_inspire-navItem__heading--depth-' + depth]: true,
          }"
          :aria-label="headingText"
          :target="hasTarget ? '_blank' : null"
        >
          <div
            v-if="showIcons && iconContent"
            class="tui-theme_inspire-navItem__icon"
            :class="{
              'tui-theme_inspire-navItem__icon--navExpanded': navExpanded,
            }"
            :aria-hidden="true"
            v-html="iconContent"
          />

          <span
            v-if="navExpanded"
            class="tui-theme_inspire-navItem__headingText"
            :class="{
              ['tui-theme_inspire-navItem__headingText--depth-' + depth]: true,
            }"
          >
            {{ headingText }}
          </span>
          <ExternalLink
            v-if="hasTarget && navExpanded"
            class="tui-theme_inspire-navItem__headingAction"
          />
        </a>
      </div>

      <!-- With children -->
      <div
        v-else
        class="tui-theme_inspire-navItem tui-theme_inspire-navItem--parent"
        :class="{
          'tui-theme_inspire-navItem--selected': isSelected,
          'tui-theme_inspire-navItem--navExpanded': navExpanded,
        }"
      >
        <button
          class="tui-theme_inspire-navItem__heading"
          :class="{
            'tui-theme_inspire-navItem__heading--childSelected':
              item.childSelected,
            'tui-theme_inspire-navItem__heading--navExpanded': navExpanded,
            ['tui-theme_inspire-navItem__heading--depth-' + depth]: true,
          }"
          :aria-controls="$id('subitems')"
          :aria-expanded="itemExpanded"
          :aria-label="$str('a11y_shownavitems', 'theme_inspire', headingText)"
          @click="toggleItemExpanded"
        >
          <div
            v-if="showIcons && iconContent"
            class="tui-theme_inspire-navItem__icon"
            :class="{
              'tui-theme_inspire-navItem__icon--navExpanded': navExpanded,
            }"
            :aria-hidden="true"
            v-html="iconContent"
          />
          <span
            v-if="navExpanded"
            class="tui-theme_inspire-navItem__headingText"
            :class="{
              ['tui-theme_inspire-navItem__headingText--depth-' + depth]: true,
            }"
          >
            {{ headingText }}
          </span>
          <component
            :is="itemExpanded ? 'Collapse' : 'Expand'"
            v-if="navExpanded"
            class="tui-theme_inspire-navItem__headingAction"
          />
        </button>

        <!-- Children of this item -->
        <ul
          v-if="showChildren"
          :id="$id('subitems')"
          class="tui-theme_inspire-navItem__children"
        >
          <li
            v-for="child in childItems"
            :key="child.name"
            :class="child.customclass"
          >
            <NavItem :item="child" :depth="depth + 1" />
          </li>
        </ul>
      </div>
    </template>
    <template v-slot:content>
      {{ headingText }}
      <ExternalLink v-if="hasTarget" />
    </template>
  </Tooltip>
</template>
<script>
import Button from 'tui/components/buttons/Button';
import Collapse from 'tui/components/icons/Collapse';
import Expand from 'tui/components/icons/Expand';
import ExternalLink from 'tui/components/icons/ExternalLink';
import Tooltip from 'tui/components/popover/Tooltip';

export default {
  name: 'NavItem',

  components: {
    Button,
    Collapse,
    Expand,
    ExternalLink,
    Tooltip,
  },

  props: {
    showIcons: Boolean,
    navExpanded: { type: Boolean, default: true },
    depth: { type: Number, default: 0 },
    item: Object,
  },

  emits: ['expand-nav'],

  data() {
    return {
      /**
       * The item will be expanded initially if any of its children are selected
       */
      itemExpanded: this.item.childSelected,
    };
  },

  computed: {
    /**
     * The children of this item
     *
     * @return {Object}
     */
    childItems() {
      return this.item.children;
    },

    /**
     * Does this item have children?
     *
     * @return {Boolean}
     */
    hasChildren() {
      return this.childItems && this.childItems.length > 0;
    },

    /**
     * Does this item have a target value set?
     *
     * @return {Boolean}
     */
    hasTarget() {
      return this.item.target != '';
    },

    /**
     * The text data for the heading of this item
     *
     * @return {String}
     */
    headingText() {
      return this.item.linktext;
    },

    /**
     * The raw icon data to be output with v-html
     *
     * @return {String}
     */
    iconContent() {
      return this.item.icon_content;
    },

    /**
     * Is this item currently selected?
     *
     * @return {Boolean}
     */
    isSelected() {
      return this.item.is_selected;
    },

    /**
     * Should we be showing the children for this item?
     *
     * @return {Boolean}
     */
    showChildren() {
      return this.hasChildren && this.navExpanded && this.itemExpanded;
    },

    /**
     * The url of the page this item links to
     *
     * @return {Boolean}
     */
    url() {
      return this.item.url == null || this.item.url == '#'
        ? null
        : this.$url(this.item.url);
    },
  },

  watch: {
    navExpanded(expanded) {
      // When the nav is collapsed, collapse the items unless this item is selected
      if (!expanded && !this.item.childSelected) {
        this.itemExpanded = false;
      }
    },
  },

  methods: {
    /**
     * If the navigation is expanded, then show/hide the item children
     *
     * If the navigation is collapsed then expand it first
     */
    toggleItemExpanded() {
      if (this.navExpanded) {
        this.itemExpanded = !this.itemExpanded;
      } else {
        this.$emit('expand-nav');
        this.itemExpanded = true;
      }
    },
  },
};
</script>
<style lang="scss">
.tui-theme_inspire-navItem {
  --tui-theme_inspire-navItem-height: #{rem-px(44)};
  --tui-theme_inspire-navItem-indicator-height: #{rem-px(32)};
  --tui-theme_inspire-navItem-top-level-nav-item-padding: 10px;
  --tui-theme_inspire-navItem-nav-item-padding: 6px;

  display: flex;
  flex-grow: 1;
  flex-wrap: wrap;
  height: var(--tui-theme_inspire-navItem-height);
  padding-top: gap(1);
  color: var(--nav-tab-text-color);

  &--navExpanded {
    height: 100%;
  }

  &__icon {
    width: 1.2em;
    height: 1.2em;
    margin: auto;
    &--navExpanded {
      margin: gap(3) auto auto gap(3);
    }
  }

  .tui-theme_inspire-navItem {
    padding-top: 0;
  }

  &--selected {
    color: var(--nav-selected-color);

    &:before {
      height: var(--tui-theme_inspire-navItem-indicator-height);
      margin-top: gap(1);
      border-right: gap(1) solid var(--nav-selected-color);
      border-top-right-radius: gap(1);
      border-bottom-right-radius: gap(1);
      content: '';

      .tui-theme_inspire-navItem & {
        margin-top: 0;
      }
    }
  }

  &--link {
    flex-wrap: nowrap;
  }

  &__heading {
    display: flex;
    flex-grow: 1;
    align-items: flex-start;
    padding: 0;
    background-color: inherit;
    border: none;
    border-radius: var(--btn-radius);

    &--childSelected {
      color: var(--nav-selected-color);
    }

    &--depth-0 {
      margin-left: gap(5);
      font-weight: 600;

      &.tui-theme_inspire-navItem__heading--navExpanded {
        margin-left: gap(3);
      }

      &.tui-theme_inspire-navItem__heading--selected {
        margin-left: gap(4);

        &.tui-theme_inspire-navItem__heading--navExpanded {
          margin-left: gap(2);
        }
      }

      .tui-theme_inspire-navItem__headingAction {
        margin: gap(2);
      }
    }

    &--depth-1 {
      margin-left: gap(10);
      font-weight: 400;

      &.tui-theme_inspire-navItem__heading--selected {
        margin-left: gap(9);
      }
    }

    &--depth-2 {
      margin-left: gap(13);

      &.tui-theme_inspire-navItem__heading--selected {
        margin-left: gap(12);
      }
    }

    &--link {
      color: var(--nav-tab-text-color);

      &:hover,
      &:focus {
        color: var(--nav-tab-text-color);
        text-decoration: none;
      }

      &.tui-theme_inspire-navItem__heading--selected {
        color: var(--nav-selected-color);
      }
    }

    @media (hover: hover) {
      &:hover {
        background-color: color-mix(
          in srgb,
          var(--nav-tab-text-color) 10%,
          transparent
        );
      }
    }

    &:focus-visible {
      outline: 2px solid var(--nav-tab-text-color);
    }
  }

  &__headingText {
    flex-grow: 1;
    margin: auto;
    padding: var(--tui-theme_inspire-navItem-nav-item-padding) gap(1)
      var(--tui-theme_inspire-navItem-nav-item-padding) gap(3);
    text-align: left;
    overflow-wrap: anywhere;
    hyphens: auto;

    &--depth-0 {
      padding: var(--tui-theme_inspire-navItem-top-level-nav-item-padding)
        gap(1) var(--tui-theme_inspire-navItem-top-level-nav-item-padding)
        gap(3);
    }
  }

  &__headingAction {
    flex-basis: rem-px(24);
    flex-shrink: 0;
    height: rem-px(24);
    margin: gap(1) gap(2);
    padding: var(--tui-theme_inspire-navItem-nav-item-padding);
    color: var(--nav-tab-text-color);
  }

  &__children {
    flex-basis: 100%;
    margin-bottom: gap(2);
    margin-left: 0;
    list-style: none;
  }
}
</style>
