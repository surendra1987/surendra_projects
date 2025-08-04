<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Simon Chester <simon.chester@totara.com>
  @module auth_ssosaml
-->

<script>
import { h } from 'vue';
import SimpleTableRow from 'auth_ssosaml/components/ui/simple_table/SimpleTableRow';

export default {
  components: {
    SimpleTableRow,
  },

  provide() {
    return {
      ssosamlSimpleTable: this.contextData,
    };
  },

  props: {
    columns: { type: Array, required: true },
    stackAt: Number,
  },

  data() {
    return {
      width: Infinity,
      contextData: {
        columns: this.columns,
        isStacked: false,
      },
    };
  },

  computed: {
    gridTemplateColumns() {
      const sizes = this.columns.map(x => {
        if (typeof x.size === 'number') {
          return `${x.size}fr`;
        } else if (typeof x.size === 'string') {
          return x.size;
        }
        return '1fr';
      });
      return sizes.join(' ');
    },

    isStacked() {
      return this.stackAt != null && this.width <= this.stackAt;
    },
  },

  watch: {
    isStacked() {
      this.contextData.isStacked = this.isStacked;
    },
  },

  mounted() {
    this.resizeObserver = new ResizeObserver(this.handleResize);
    this.resizeObserver.observe(this.$el);
    this.handleResize();
  },

  beforeUnmount() {
    this.resizeObserver.disconnect();
  },

  methods: {
    handleResize() {
      this.width = this.$el.offsetWidth;
    },
  },

  render() {
    return h(
      'div',
      {
        class: [
          'tui-auth_ssosaml-simpleTable',
          this.isStacked && 'tui-auth_ssosaml-simpleTable--stacked',
        ],
        style: {
          '--tui-auth_ssosaml-simple-table-grid-template-columns': this
            .gridTemplateColumns,
        },
      },
      [
        this.$slots.header
          ? this.$slots.header()
          : h(SimpleTableRow, { header: true }),
        this.$slots.default && this.$slots.default(),
      ]
    );
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-simpleTable {
  $block: #{&};

  &__row {
    display: grid;
    grid-template-columns: var(
      --tui-auth_ssosaml-simple-table-grid-template-columns
    );
    gap: var(--gap-2);
    padding: var(--gap-2) var(--gap-1);
    border-bottom: 1px solid var(--datatable-row-border-color);

    &--header {
      border-bottom: var(--border-width-normal) solid
        var(--datatable-row-first-border-color);
    }
  }

  &--stacked &__row {
    grid-template-columns: none;
  }

  &--stacked &__row--header {
    padding: 0;
  }

  &__cell {
    display: flex;
    flex-direction: row;
  }

  &--stacked &__cell {
    flex-direction: column;
  }

  &--stacked &__row--header &__cell {
    @include sr-only();
  }

  &__cell-label {
    @include font(body-sm, var(--label-weight));
    display: none;
    padding-bottom: var(--gap-1);
  }

  &--stacked &__cell-label {
    display: block;
  }

  &__cell-content {
    display: flex;
    flex-grow: 1;
    align-items: flex-start;
    min-width: 0;

    #{$block}__cell--valign-start & {
      align-items: flex-start;
    }

    #{$block}__cell--valign-center & {
      align-items: center;
    }

    #{$block}__cell--valign-end & {
      align-items: flex-end;
    }

    #{$block}__cell--valign-stretch & {
      align-items: stretch;
    }
  }

  &__row--header &__cell {
    font-weight: var(--label-weight);
  }
}
</style>
