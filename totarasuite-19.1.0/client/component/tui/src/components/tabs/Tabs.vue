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

<script>
import { h } from 'vue';
import { addProps, eachChild, COMPONENTS_AND_ELEMENTS } from 'tui/vue/vnode';
import TabBar from 'tui/components/tabs/TabBar';

export default {
  props: {
    controlled: Boolean,
    selected: [String, Number],
    direction: {
      type: String,
      default: 'horizontal',
      validator: x => ['horizontal', 'vertical'].includes(x),
    },
    smallTabs: {
      type: Boolean,
    },
    contentSpacing: {
      type: String,
      validator: x => !x || x == 'large',
    },
    /** Fill all available space? */
    fill: Boolean,
  },

  emits: ['input', 'update:selected'],

  data() {
    return {
      tabs: [],
      currentSelected: this.selected,
    };
  },

  watch: {
    selected(value) {
      this.currentSelected = value;
    },
  },

  mounted() {
    if (this.currentSelected == null && this.tabs.length > 0) {
      this.currentSelected = this.tabs[0].id;
    }
  },

  methods: {
    $_setSelectedTab(tabId) {
      if (!this.controlled) {
        this.currentSelected = tabId;
      }

      this.$emit('update:selected', tabId);
      this.$emit('input', tabId);
    },
  },

  render() {
    const vnodes = addProps(
      this.$slots.default ? this.$slots.default() : [],
      vnode => ({
        active:
          this.currentSelected != null &&
          this.currentSelected === vnode.props.id,
        htmlId: this.$id(vnode.props.id),
      })
    );

    let tabs = [];
    eachChild(
      vnodes,
      vnode => {
        const props = vnode.props;

        const labelExtra =
          vnode.children &&
          typeof vnode.children === 'object' &&
          vnode.children['label-extra'];

        tabs.push({
          id: props.id,
          htmlId: props.htmlId,
          name: props.name,
          disabled: props.disabled,
          hidden: props.hidden,
          renderLabelExtra: labelExtra,
          labelExtraText: props.labelExtraText,
        });
      },
      COMPONENTS_AND_ELEMENTS
    );

    // assign so we can read it in mounted
    this.tabs = tabs;

    return h(
      'div',
      {
        class: [
          'tui-tabs',
          'tui-tabs--' + this.direction,
          this.contentSpacing
            ? 'tui-tabs--contentSpacing-' + this.contentSpacing
            : null,
          this.fill ? 'tui-tabs--fill' : null,
        ],
      },
      [
        h(TabBar, {
          tabs,
          small: this.smallTabs,
          direction: this.direction,
          value: this.currentSelected,
          'onUpdate:value': this.$_setSelectedTab,
        }),
        h('div', { class: 'tui-tabs__panels' }, vnodes),
      ]
    );
  },
};
</script>

<style lang="scss">
.tui-tabs {
  $mod-fill: #{&}--fill;
  $mod-horizontal: #{&}--horizontal;
  $mod-vertical: #{&}--vertical;

  &--fill {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    height: 100%;
  }

  &--vertical {
    display: flex;
    flex-direction: row;
  }

  #{$mod-fill} > &__panels {
    flex-grow: 1;
    min-height: 0;

    > .tui-tabContent {
      height: 100%;
    }
  }
}

.tui-tabContent {
  .tui-tabs--horizontal & {
    padding-top: var(--gap-4);
  }

  .tui-tabs--horizontal.tui-tabs--contentSpacing-large & {
    padding-top: var(--gap-8);
  }

  .tui-tabs--vertical & {
    padding-left: var(--gap-4);
  }

  .tui-tabs--vertical.tui-tabs--contentSpacing-large & {
    padding-left: var(--gap-8);
  }
}
</style>
