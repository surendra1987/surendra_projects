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

  @author Arshad Anwer <arshad.anwer@totara.com>
  @module tui
-->

<script setup>
import Arrow from 'tui/components/decor/Arrow';
import PopoverTrigger from 'tui/components/popover/PopoverTrigger';
import PopoverPositioner from 'tui/components/popover/PopoverPositioner';
import { ref } from 'vue';
import { langSide } from 'tui/i18n';

defineProps({
  position: {
    type: String,
    default: 'top',
    validator: x =>
      x
        .split('-', 2)
        .every(y => ['top', 'right', 'bottom', 'left'].includes(y)),
  },
  content: {
    type: String,
  },
  contextMode: String,
  hidden: Boolean,
});

const emit = defineEmits(['open-changed']);

const trigger = ref(null);
const body = ref(null);
const isOpen = ref(false);

function setOpen(visible) {
  isOpen.value = visible;
  emit('open-changed', visible);
}

function getReference() {
  let reference = trigger.value;
  if (reference) {
    reference = reference.$el;
  }
  if (reference instanceof Element) {
    return reference;
  }
  return null;
}
</script>

<template>
  <PopoverTrigger
    v-if="$slots.trigger"
    ref="trigger"
    :triggers="['hover']"
    :ui-element="body"
    @open-changed="setOpen"
  >
    <slot name="trigger" :is-open="isOpen" />
  </PopoverTrigger>
  <PopoverPositioner
    v-if="!hidden"
    v-slot="{ side, arrowDistance }"
    :context-mode="contextMode"
    :position="position"
    :open="isOpen"
    :reference-element="getReference()"
  >
    <div ref="body" role="tooltip" class="tui-tooltip__body">
      <Arrow
        :relative-side="langSide(side)"
        :distance="arrowDistance"
        variant="inverse"
      />
      <slot name="content">
        {{ content }}
      </slot>
    </div>
  </PopoverPositioner>
</template>

<style lang="scss">
.tui-tooltip {
  &__body {
    position: relative;
    width: max-content;
    max-width: 80vw;
    margin: 10px; // arrow size
    padding: gap(2) gap(3);
    color: var(--color-neutral-1);
    overflow-wrap: break-word;
    background: var(--color-neutral-7);
    border-radius: var(--border-radius-small);

    // switch when 300px would be 80% of the viewport (80vw) to avoid jump
    @media (min-width: rem-px(300 / 0.8)) {
      max-width: rem-px(300);
    }
  }
}
</style>
