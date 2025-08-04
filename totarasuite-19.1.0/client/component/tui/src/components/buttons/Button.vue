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
import { computed, onMounted, ref } from 'vue';
import Caret from 'tui/components/decor/Caret';
import Loading from 'tui/components/icons/Loading';

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
  shape: {
    type: String,
    validator: x => ['default', 'pill', 'circle'].includes(x),
  },
  type: {
    type: String,
    default: 'button',
  },
  loading: Boolean,
  disabled: Boolean,
  ariaLabel: {
    type: [Boolean, String],
    default: null,
  },
  ariaDisabled: {
    type: [Boolean, String],
    default: null,
  },
  autofocus: Boolean,
  /** @deprecated since Totara 19 */
  styleclass: Object,
  text: String,
  href: String,
  caret: Boolean,
});

const emit = defineEmits(['click']);

const ariaDisabled = computed(() => {
  if (
    props.ariaDisabled === true ||
    (props.ariaDisabled === null && props.loading)
  ) {
    return 'true';
  }
  return props.ariaDisabled == null ? null : String(props.ariaDisabled);
});

function styleclassCompat(prop, fallback, styleclassMap) {
  return computed(() => {
    if (props[prop]) {
      return props[prop];
    }
    if (props.styleclass) {
      for (const key in styleclassMap) {
        if (props.styleclass[key]) {
          return styleclassMap[key];
        }
      }
    }
    return fallback;
  });
}

const variantComputed = styleclassCompat('variant', 'default', {
  transparent: 'link',
  stealth: 'stealth',
  primary: 'primary',
});

const sizeComputed = styleclassCompat('size', null, {
  xsmall: 'xs',
  small: 'sm',
});

const colorComputed = styleclassCompat('color', null, {
  alert: 'danger',
});

const shapeComputed = styleclassCompat('shape', null, {
  circle: 'circle',
});

const root = ref(null);

onMounted(() => {
  if (props.autofocus && root.value) {
    root.value.focus();
  }
});

function handleClick(e) {
  if (props.disabled || props.loading) {
    // prevent from acting as a submit button
    e.preventDefault();
    return;
  }
  emit('click', e);
}
</script>

<template>
  <component
    :is="href ? 'a' : 'button'"
    ref="root"
    :href="disabled ? null : href"
    :class="[
      'tui-btn',
      variantComputed && 'tui-btn--variant-' + variantComputed,
      sizeComputed && 'tui-btn--size-' + sizeComputed,
      colorComputed && 'tui-btn--color-' + colorComputed,
      shapeComputed && 'tui-btn--shape-' + shapeComputed,
      (disabled || loading) && 'tui-btn--disabled',
      loading && 'tui-btn--loading',
      (text || $slots.default) && 'tui-btn--hasContent',
      styleclass?.transparentWithPadding && 'tui-btn--legacyTransparentPadding',
    ]"
    :type="type"
    :disabled="disabled"
    :aria-label="ariaLabel"
    :aria-disabled="ariaDisabled"
    @click="handleClick"
  >
    <div class="tui-btn__wrap">
      <div v-if="$slots.icon" class="tui-btn__iconBefore">
        <slot name="icon" />
      </div>
      <div v-if="text || $slots.default" class="tui-btn__content">
        {{ text }}
        <slot />
      </div>
      <div v-if="$slots['icon-after'] || caret" class="tui-btn__iconAfter">
        <slot name="icon-after">
          <Caret v-if="caret" />
        </slot>
      </div>
    </div>
    <div aria-live="assertive">
      <div v-if="loading" class="tui-btn__loading">
        <Loading
          :alt="$str('button_loading_text', 'totara_core', text)"
          :size="sizeComputed === 'xs' ? null : 200"
        />
      </div>
    </div>
  </component>
</template>

<style lang="scss">
.tui-btn {
  $block: #{&};
  // local CSS variables
  --tui-btn-color: var(--btn-accent-color);
  --tui-btn-color-hover: var(--btn-accent-color-hover);
  --tui-btn-color-active: var(--btn-accent-color-active);
  --tui-btn-color-contrast: var(--btn-accent-color-contrast);
  --tui-btn-shadow: none;
  --tui-btn-shadow-hover: var(--btn-shadow-hover);
  --tui-btn-shadow-active: var(--btn-shadow-active);
  --tui-btn-content-height: var(--btn-line-height);
  --tui-btn-padding-h: calc(var(--btn-padding-h) - var(--btn-border-width));
  --tui-btn-padding-v: calc(var(--btn-padding-v) - var(--btn-border-width));
  --tui-btn-font-size: var(--btn-font-size);
  --tui-btn-line-height: var(--btn-line-height);
  --tui-btn-min-height: calc(
    var(--tui-btn-line-height) +
      (var(--tui-btn-padding-v) + var(--btn-border-width)) * 2
  );

  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: var(--tui-btn-min-height);
  max-width: 100%;
  min-height: var(--tui-btn-min-height);
  padding: var(--tui-btn-padding-v) var(--tui-btn-padding-h);
  color: var(--tui-btn-color);
  font-size: var(--tui-btn-font-size);
  line-height: var(--tui-btn-line-height);
  overflow-wrap: break-word;
  background: var(--btn-bg-color);
  border: var(--btn-border-width) solid;
  border-color: var(--tui-btn-color);
  border-radius: var(--btn-radius);
  cursor: pointer;
  transition: tui-transitions(
    'button',
    background-color border-color box-shadow
  );

  &:is(a) {
    text-decoration: none;
    &:hover,
    &:focus {
      color: var(--tui-btn-color);
    }
  }

  &__wrap {
    @include flex-center;
    gap: gap(2);
  }

  &__content {
    @include flex-center;
  }

  &:focus-visible {
    @include tui-focus();
  }

  &--color-danger {
    --tui-btn-color: var(--btn-danger-color);
    --tui-btn-color-hover: var(--btn-danger-color);
    --tui-btn-color-active: var(--btn-danger-color);
    --tui-btn-color-contrast: var(--btn-danger-color-contrast);
  }

  &:hover {
    --tui-btn-color: var(--tui-btn-color-hover);
    box-shadow: var(--btn-shadow-hover);
  }

  &:active {
    --tui-btn-color: var(--tui-btn-color-active);
    box-shadow: var(--btn-shadow-active);
  }

  &--size-sm {
    --tui-btn-font-size: var(--btn-sm-font-size);
    --tui-btn-line-height: var(--btn-sm-line-height);
    --tui-btn-padding-h: calc(
      var(--btn-sm-padding-h) - var(--btn-border-width)
    );
    --tui-btn-padding-v: calc(
      var(--btn-sm-padding-v) - var(--btn-border-width)
    );
    border-radius: var(--btn-sm-radius);
  }

  &--size-sm &__wrap {
    gap: gap(1);
  }

  &--size-xs {
    --tui-btn-font-size: var(--btn-xs-font-size);
    --tui-btn-line-height: var(--btn-xs-line-height);
    --tui-btn-padding-h: calc(
      var(--btn-xs-padding-h) - var(--btn-border-width)
    );
    --tui-btn-padding-v: calc(
      var(--btn-xs-padding-v) - var(--btn-border-width)
    );
    border-radius: var(--btn-xs-radius);
  }

  &--size-xs &__wrap {
    gap: gap(0.75);
  }

  &--variant-default {
    &#{$block}--disabled {
      background-color: var(--btn-bg-color-disabled);
    }
  }

  &--variant-primary {
    color: var(--tui-btn-color-contrast);
    background-color: var(--tui-btn-color);

    &:is(a):hover,
    &:is(a):focus {
      color: var(--tui-btn-color-contrast);
    }
  }

  &--variant-stealth {
    background-color: transparent;
    border-color: transparent;
    box-shadow: none;
    &:hover {
      background-color: rgba(0, 0, 0, 0.05);
      box-shadow: none;
    }
    &:active {
      background-color: rgba(0, 0, 0, 0.07);
      box-shadow: none;
    }
    &#{$block}--disabled {
      background-color: transparent;
    }
  }

  &--variant-link {
    --tui-btn-color: var(--link-color);
    --tui-btn-color-hover: var(--link-color);
    --tui-btn-color-active: var(--link-color);
    --tui-btn-color-contrast: var(--color-neutral-7);
    --tui-btn-padding-v: 0;
    padding: 0;
    line-height: 1;
    background: transparent;
    border: none;
    border-radius: 0;

    &:hover,
    &:active {
      box-shadow: none;
    }
  }

  // compat for old ButtonIcon "transparent" style
  &--legacyTransparentPadding {
    padding: 0 var(--gap-1);
  }

  &--shape-pill {
    border-radius: var(--tui-btn-min-height);
  }

  &--shape-circle {
    width: var(--tui-btn-min-height);
    height: var(--tui-btn-min-height);
    padding: var(--tui-btn-padding-v) 0;
    border-radius: var(--tui-btn-min-height);
  }

  &--disabled {
    &,
    &:hover,
    &:active {
      --tui-btn-color: var(--btn-accent-color-disabled);
      box-shadow: none;
    }
  }

  &--loading {
    #{$block}__content,
    #{$block}__iconBefore,
    #{$block}__iconAfter {
      visibility: hidden;
    }
  }

  &__loading {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--btn-loader-color-disabled);
  }

  &__iconBefore {
    @include flex-center;
  }

  &__iconAfter {
    @include flex-center;
  }
}
</style>
