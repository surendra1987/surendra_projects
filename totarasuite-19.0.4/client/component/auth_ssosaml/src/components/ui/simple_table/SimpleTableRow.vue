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

export default {
  inject: ['ssosamlSimpleTable'],

  props: {
    data: { type: Object },
    header: Boolean,
  },

  computed: {
    columns() {
      return this.ssosamlSimpleTable.columns;
    },
  },

  render() {
    const block = 'tui-auth_ssosaml-simpleTable';
    return h(
      'div',
      {
        class: [`${block}__row`, this.header && `${block}__row--header`],
      },
      [
        this.columns.map((col, index) => {
          const slot = this.$slots[`col-${col.key}`];
          const label = col.label;
          const value = this.header ? label : this.data && this.data[col.key];

          const classes = [`${block}__cell`];

          if (!this.header && col.valign) {
            classes.push(`${block}__cell--valign-` + col.valign);
          } else if (this.header && col.headerValign) {
            classes.push(`${block}__cell--valign-` + col.headerValign);
          }

          return h(
            'div',
            {
              key: col.key || 'idx-' + index,
              class: classes,
            },
            [
              !this.header && label != null
                ? h(
                    'div',
                    {
                      class: `${block}__cell-label`,
                      ariaHidden: 'true',
                    },
                    [label]
                  )
                : null,
              h('div', { class: `${block}__cell-content` }, [
                slot
                  ? slot({
                      value,
                      row: this.data,
                      isStacked: this.ssosamlSimpleTable.isStacked,
                    })
                  : value,
              ]),
            ]
          );
        }),
      ]
    );
  },
};
</script>
