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
  <!-- Expand button (hidden on mobile) -->
  <template v-if="!overlaid">
    <Button
      class="tui-theme_inspire-navigation__toggle tui-theme_inspire-navigation__toggle--expand"
      :class="{
        'tui-theme_inspire-navigation__toggle--navHidden': hidden,
        'tui-theme_inspire-navigation__toggle--navExpanded': expanded,
      }"
      variant="stealth"
      :aria-label="
        expanded
          ? $str('a11y_nav_collapse', 'theme_inspire')
          : $str('a11y_nav_expand', 'theme_inspire')
      "
      :aria-expanded="expanded"
      :aria-controls="$id('items')"
      @click="toggleExpanded"
    >
      <slot name="icon">
        <MobileMenuIcon :size="400" />
      </slot>
    </Button>

    <!-- Overlay button (hidden on desktop) -->
    <Button
      class="tui-theme_inspire-navigation__toggle tui-theme_inspire-navigation__toggle--overlay"
      :class="{
        'tui-theme_inspire-navigation__overlay--navHidden': hidden,
      }"
      variant="stealth"
      :aria-label="$str('a11y_nav_overlay', 'theme_inspire')"
      :aria-expanded="false"
      :aria-controls="$id('items')"
      @click="toggleOverlaid"
    >
      <slot name="icon">
        <MobileMenuIcon :size="400" />
      </slot>
    </Button>
  </template>

  <nav
    ref="nav"
    class="tui-theme_inspire-navigation"
    :class="{
      'tui-theme_inspire-navigation--hidden': hidden,
      'tui-theme_inspire-navigation--overlaid': overlaid,
      'tui-theme_inspire-navigation--desktopExpanded': expanded,
    }"
    :aria-hidden="hidden"
  >
    <div class="tui-theme_inspire-navigation__nav">
      <div class="tui-theme_inspire-navigation__heading">
        <a :href="siteUrl">
          <img
            :src="logoSrc"
            :alt="logoAlt"
            class="tui-theme_inspire-navigation__headingLogo"
          />
        </a>
        <Button
          v-if="overlaid"
          class="tui-theme_inspire-navigation__collapse"
          variant="stealth"
          :aria-label="$str('a11y_nav_close_overlay', 'theme_inspire')"
          :aria-expanded="true"
          :aria-controls="$id('items')"
          @click="toggleOverlaid"
        >
          <slot name="icon">
            <Close :size="400" />
          </slot>
        </Button>
      </div>

      <ul :id="$id('items')" class="tui-theme_inspire-navigation__items">
        <li
          v-for="item in navItems"
          :key="item.name"
          :class="item.customclass"
          :aria-current="item.is_selected ? 'page' : null"
        >
          <NavItem
            :item="item"
            :nav-expanded="overlaid || expanded"
            :show-icons="iconsEnabled"
            @expand-nav="toggleOverlaid"
          />
        </li>
      </ul>
    </div>
  </nav>
</template>
<script>
import Button from 'tui/components/buttons/Button';
import Close from 'tui/components/icons/Close';
import MobileMenuIcon from 'tui/components/icons/MobileMenu';
import NavItem from 'theme_inspire/components/navigation/NavItem';

// Utils
import { debounce } from 'tui/util';

// GraphQL
import setNavigationState from 'theme_inspire/graphql/set_navigation_state';

export default {
  components: {
    Button,
    Close,
    MobileMenuIcon,
    NavItem,
  },

  props: {
    iconsEnabled: Boolean,
    initialState: String,
    logoAlt: String,
    logoMark: String,
    logoUrl: String,
    menuData: Array,
    placeholderId: String,
    siteUrl: String,
  },

  data() {
    return {
      expanded: this.initialState == 'expanded',
      overlaid: false,
      setNavigationStateDebounced: null,
    };
  },

  computed: {
    /**
     * Should the nav be hidden?
     *
     * @return {Boolean}
     */
    hidden() {
      return !this.expanded && !this.overlaid && !this.iconsEnabled;
    },

    /**
     * The url for the logo depending on whether the nav is expanded/collapsed or not
     *
     * @return {String}
     */
    logoSrc() {
      if (this.overlaid || this.expanded) {
        return this.logoUrl;
      } else {
        return this.logoMark;
      }
    },

    /**
     * Generates an array of root-level nav items from `menuData` with each item including its nested children
     *
     * @return {Array}
     */
    navItems() {
      const menuData = JSON.parse(JSON.stringify(this.menuData));
      return menuData
        .filter(item => item.parent === '')
        .map(item => {
          item.children = this.getChildren(item.name, menuData);

          item.children = item.children.map(child => {
            child.children = this.getChildren(child.name, menuData);

            child.childSelected = child.children.some(
              child => child.is_selected
            );
            return child;
          });

          item.childSelected = item.children.some(
            child => child.is_selected || child.childSelected
          );

          return item;
        });
    },
  },

  watch: {
    /**
     * Add and remove the click-to-close event listen when in overlaid mode
     *
     * @param {Boolean} value
     */
    overlaid(value) {
      if (value) {
        document.addEventListener('click', this.handleClick);
      } else {
        document.removeEventListener('click', this.handleClick);
      }
    },
  },

  unmounted() {
    document.removeEventListener('click', this.handleClick);
  },

  mounted() {
    // Remove the placeholder nav that is used to avoid page jumps
    if (this.placeholderId) {
      const el = document.getElementById(this.placeholderId);
      if (el) {
        el.remove();
      }
    }

    this.setNavigationStateDebounced = debounce(this.setNavigationState, 200, {
      leading: true,
    });
  },

  methods: {
    /**
     * Gets the children of the provided nav item from the menu data
     *
     * @param {String} itemName Name of the nav item
     * @param {Array} menuData
     * @return {Array} Cloned items
     */
    getChildren(itemName, menuData) {
      return menuData.filter(item => item.parent == itemName);
    },

    /**
     * Handle the click event to close the nav when the page is clicked
     *
     * @param {Object} clickEvent
     */
    handleClick(clickEvent) {
      // If the click is not inside the nav or the overlay button
      if (
        this.overlaid &&
        !this.$refs.nav.contains(clickEvent.target) &&
        document.contains(clickEvent.target)
      ) {
        this.toggleOverlaid();
      }
    },

    /**
     * Sends the mutation to save the navigation state
     *
     * Either expanded or collapsed
     */
    async setNavigationState() {
      try {
        await this.$apollo.mutate({
          mutation: setNavigationState,
          variables: {
            input: {
              state: this.expanded ? 'expanded' : 'collapsed',
            },
          },
        });
      } catch (e) {
        // fall through
      }
    },

    /**
     * Toggles the overlaid state
     */
    toggleOverlaid() {
      if (!this.overlaid) {
        this.overlaid = true;
        this.expanded = false;
      } else {
        this.overlaid = false;
      }
    },

    /**
     * Toggles the expanded state
     */
    toggleExpanded() {
      if (!this.expanded) {
        this.expanded = true;
        this.overlaid = false;
      } else {
        this.expanded = false;
      }

      if (this.setNavigationStateDebounced) {
        this.setNavigationStateDebounced();
      }
    },
  },
};
</script>
<style lang="scss">
// Expand/overlay toggle button
//
// We use a different button for expanding and overlaying as they execute different functions, even though they look identical
// Ovelay is hidden on mobile, while expand is hidden on desktop
.tui-theme_inspire-navigation__toggle {
  &--overlay {
    display: block;
  }
  &--expand {
    display: none;
  }

  // Desktop width
  @media (min-width: $tui-screen-md) {
    &--overlay {
      display: none;
    }
    &--expand {
      display: block;
    }

    left: var(--nav-collapsed-width);
    &--navExpanded {
      left: var(--nav-width);
    }
    margin-left: gap(5);
  }

  position: absolute;
  z-index: 1;
  width: var(--nav-button-size);
  height: var(--nav-button-size);
  margin: gap(4);
  color: var(--color-text);

  &--navHidden {
    left: 0;
  }
}

/*
  The approach to the styling here is to provide a modifier to the entire
  nav depending on the current state. This means a bit of repetition in
  favour of clarity and easier modification in the future around the different states.
  No modifier means the nav is in the "collapsed" state.

  The different states for the navigation are:

    - Hidden (desktop and mobile)
    - Collapsed (desktop only)
    - Overlaid (desktop and mobile)
    - Expanded (desktop only)

  The default "-navigation" rule set sets various properties shared across all states,
  which are then overridden in subsequent states where needed.
*/

/*
  COLLAPSED (base styles)

  Hidden on mobile
*/
.tui-theme_inspire-navigation {
  position: relative;
  z-index: var(--zindex-navbar);
  display: none;
  flex-direction: column;
  overflow: visible;

  // Desktop width
  @media (min-width: $tui-screen-md) {
    display: flex;
    width: var(--nav-collapsed-width);
  }

  .tui-theme_inspire-navigation__nav {
    position: fixed;
    display: flex;
    flex-direction: column;
    width: var(--nav-collapsed-width);
    height: 100%;
    background-color: var(--nav-bg-color);
    border-right: 1px inset var(--nav-border-colour);
  }

  .tui-theme_inspire-navigation__heading {
    margin: auto;
    border-bottom: 1px inset var(--nav-border-colour);
  }

  .tui-theme_inspire-navigation__headingLogo {
    max-width: var(--nav-logomark-width);
    max-height: var(--nav-logomark-height);
    margin: gap(8) 0;
  }

  .tui-theme_inspire-navigation__collapse {
    float: right;
    width: var(--nav-button-size);
    height: var(--nav-button-size);
    margin-top: gap(4);
    margin-right: gap(-3);
    color: var(--nav-tab-text-color);

    &:focus-visible {
      outline: 2px solid var(--nav-tab-text-color);
    }
  }

  .tui-theme_inspire-navigation__items {
    display: flex;
    flex-direction: column;
    gap: gap(1);
    height: 100%;
    margin: 0;
    padding: gap(5) gap(5) gap(5) 0;
    overflow-y: auto;
    list-style: none;
  }
}

/*
  OVERLAID
*/
.tui-theme_inspire-navigation--overlaid {
  position: fixed;
  z-index: var(--zindex-nav-overlay);
  display: flex;
  width: var(--nav-width);

  // Desktop width
  @media (min-width: $tui-screen-md) {
    position: relative;
    width: var(--nav-collapsed-width);
  }

  .tui-theme_inspire-navigation__nav {
    width: var(--nav-width);
  }

  .tui-theme_inspire-navigation__heading {
    margin: 0 gap(6);
  }

  .tui-theme_inspire-navigation__headingLogo {
    max-width: var(--nav-logo-width);
    max-height: var(--nav-logo-height);
  }

  .tui-theme_inspire-navigation__items {
    padding: gap(5) gap(3) gap(5) 0;
  }
}

/*
  EXPANDED

  Hidden on mobile
*/
.tui-theme_inspire-navigation--desktopExpanded {
  display: none;

  // Desktop width
  @media (min-width: $tui-screen-md) {
    display: flex;
    width: var(--nav-width);

    .tui-theme_inspire-navigation__nav {
      width: var(--nav-width);
    }

    .tui-theme_inspire-navigation__heading {
      margin: 0 gap(6);
    }

    .tui-theme_inspire-navigation__headingLogo {
      max-width: var(--nav-logo-width);
      max-height: var(--nav-logo-height);
    }

    .tui-theme_inspire-navigation__items {
      padding: gap(5) gap(3) gap(5) 0;
    }
  }
}

/*
  HIDDEN
*/
.tui-theme_inspire-navigation--hidden {
  display: none;
}
</style>
