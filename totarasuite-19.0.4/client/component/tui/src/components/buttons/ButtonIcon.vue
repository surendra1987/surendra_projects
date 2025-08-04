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

  @author Simon Chester <simon.chester@totara.com>
  @module tui
-->

<script setup>
import { computed } from 'vue';
import Button from 'tui/components/buttons/Button';

const props = defineProps({
  variant: {
    type: String,
    validator: x => ['default', 'primary', 'stealth', 'link'].includes(x),
  },
  size: {
    type: String,
    validator: x => ['default', 'sm', 'xs'].includes(x),
  },
  color: {
    type: String,
    validator: x => ['default', 'danger'].includes(x),
  },
  type: {
    type: String,
    default: 'button',
  },
  shape: {
    type: String,
    validator: x => ['default', 'pill'].includes(x),
  },
  ariaDisabled: Boolean,
  ariaLabel: {
    type: [Boolean, String],
    required: true,
  },
  autofocus: Boolean,
  caret: Boolean,
  /** @deprecated since Totara 19 */
  styleclass: Object,
  disabled: Boolean,
  loading: Boolean,
  text: String,
  textFirst: Boolean,
  title: {
    type: [String, Boolean],
    default: null,
  },
});

const styleclassComputed = computed(() => {
  if (!props.styleclass) {
    return props.styleclass;
  }
  const orig = props.styleclass || {};
  const val = { ...orig };
  if (orig.transparentNoPadding) {
    val.transparent = true;
  }
  if (orig.transparent && !orig.transparentNoPadding) {
    val.transparentWithPadding = true;
  }
  return val;
});

const titleText = computed(() => {
  if (props.title) {
    return props.title;
  }
  if (props.title === false) {
    // if title is specifically set to false, it means we don't want a
    // title (tooltip)
    return null;
  }

  return props.ariaLabel && props.text !== props.ariaLabel
    ? props.ariaLabel
    : null;
});
</script>

<template>
  <Button v-bind="props" :styleclass="styleclassComputed" :title="titleText">
    <template v-if="$slots.default && !textFirst" v-slot:icon>
      <slot />
    </template>
    <template v-if="$slots.default && textFirst" v-slot:icon-after>
      <slot />
    </template>
  </Button>
</template>
