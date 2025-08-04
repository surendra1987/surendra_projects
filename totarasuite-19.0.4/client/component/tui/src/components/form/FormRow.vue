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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @author Simon Chester <simon.chester@totara.com>
  @module tui
-->

<template>
  <div
    class="tui-formRow"
    :class="[
      vertical || actions ? 'tui-formRow--vertical' : null,
      hidden || (!label && !helpmsg) ? 'tui-formRow--emptyDesc' : null,
      fullWidth ? 'tui-formRow--fullWidth' : null,
      contentType ? 'tui-formRow--contentType-' + contentType : null,
    ]"
  >
    <div class="tui-formRow__desc">
      <Label
        v-if="label"
        :id="generatedLabelId"
        :for-id="generatedId"
        :legend="labelLegend"
        :hidden="hidden"
        :accessible-label="accessibleLabel"
        :label="label"
        :required="required"
        :optional="optional"
        :subfield="subfield"
        :inline="true"
      /><HelpIcon
        v-if="helpmsg || $slots['help-message']"
        :desc-id="helpDescId"
        :helpmsg="helpmsg"
        :hidden="hidden"
        :label="label || null"
        :title="helpTitle"
      >
        <slot name="help-message">
          {{ helpmsg }}
        </slot>
      </HelpIcon>
    </div>

    <FieldContextProvider
      :id="generatedId"
      :label-id="generatedLabelId"
      :aria-describedby="ariaDescribedbyId"
    >
      <div
        :class="{
          'tui-formRow__action': true,
          'tui-formRow__action--isStacked': isStacked,
        }"
      >
        <slot
          :id="generatedId"
          :label-id="generatedLabelId"
          :label="label"
          :aria-describedby="ariaDescribedbyId"
          :aria-label="ariaLabel"
        />
      </div>
    </FieldContextProvider>
  </div>
</template>

<script>
import HelpIcon from 'tui/components/form/HelpIcon';
import Label from 'tui/components/form/Label';
import FieldContextProvider from 'tui/components/reform/FieldContextProvider';

export default {
  components: {
    HelpIcon,
    Label,
    FieldContextProvider,
  },

  props: {
    ariaDescribedby: String,
    labelLegend: Boolean,
    helpmsg: String,
    helpTitle: String,
    hidden: Boolean,
    accessibleLabel: String,
    id: String,
    label: String,
    required: Boolean,
    optional: Boolean,
    isStacked: {
      type: Boolean,
      default: true,
    },
    subfield: Boolean,
    vertical: Boolean,
    actions: Boolean,
    fullWidth: Boolean,
    // Tell the FormRow what kind of content it is getting so it can align
    // the label and content correctly.
    // Default is input, pass "other" for text etc.
    contentType: String,
  },

  computed: {
    ariaDescribedbyId() {
      return this.helpmsg
        ? this.helpDescId +
            (this.ariaDescribedby ? ` ${this.ariaDescribedby}` : '')
        : this.ariaDescribedby;
    },
    ariaLabel() {
      return this.hidden ? this.label : null;
    },
    generatedId() {
      return this.id || this.$id();
    },
    generatedLabelId() {
      return this.$id('label');
    },
    helpDescId() {
      return this.generatedId + '-helpDesc';
    },
  },
};
</script>

<style lang="scss">
.tui-formRow {
  display: flex;
  flex-flow: column;

  & > &__desc {
    min-width: 0;
    padding-top: var(--gap-1);
    padding-right: var(--gap-2);
    text-align: left;
    overflow-wrap: break-word;
  }

  & > &__action {
    display: flex;
    max-width: rem-px(712);

    &--isStacked {
      display: block;

      @include tui-stack-vertical(var(--gap-2));
    }
  }

  &--fullWidth > &__action {
    max-width: none;
  }
}

.tui-form--vertical,
.tui-formRow--vertical,
.tui-formRow--emptyDesc {
  & > .tui-formRow__desc {
    padding: 0;
  }
}

.tui-form--vertical,
.tui-formRow--vertical {
  .tui-formRow__action {
    margin-top: var(--gap-1);
  }
}

.tui-formRow--emptyDesc {
  .tui-formRow__action {
    margin-top: 0;
  }
}

.tui-form--horizontal .tui-formRow:not(.tui-formRow--vertical) {
  @include tui-layout-sidebar(
    $side-width: rem-px(220),
    $content-min-width: 60%,
    $gutter: var(--gap-1),
    $sidebar-selector: '.tui-formRow__desc',
    $content-selector: '.tui-formRow__action'
  );

  & > .tui-formRow__desc {
    padding-top: tui-input-v-padding-borderless();
  }

  &.tui-formRow--contentType-other > .tui-formRow__action {
    padding-top: tui-input-v-padding-borderless();
  }
}
</style>
