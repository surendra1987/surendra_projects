<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Anthony van Beek <anthony@pixelfusion.co.nz>
  @module tui
-->
<template>
  <StarsIcon
    v-bind="$attrs"
    :class="[animated ? 'tui-iconStars--animate' : null]"
  />
</template>

<script setup>
import { createIconComponent } from 'tui/lib/svg_icon';

defineProps({
  animated: Boolean,
});

// Define the icon structure directly, to avoid the class names being stripped
const icon = [
  'svgc',
  {
    // SVG attributes
    viewBox: '0 0 20 20',
    fill: 'none',
  },
  // SVG content string
  '<path class="tui-iconStars__1" d="M9.57154 7.80883C9.7088 7.39705 10.2912 7.39706 10.4285 7.80884L11.2353 10.2294C11.595 11.3083 12.4416 12.155 13.5206 12.5146L15.9412 13.3215C16.3529 13.4588 16.3529 14.0412 15.9412 14.1785L13.5206 14.9853C12.4417 15.345 11.595 16.1916 11.2353 17.2706L10.4285 19.6911C10.2912 20.1029 9.7088 20.1029 9.57154 19.6911L8.76466 17.2706C8.40501 16.1916 7.55836 15.345 6.47941 14.9853L4.05884 14.1785C3.64705 14.0412 3.64705 13.4588 4.05884 13.3215L6.47941 12.5146C7.55836 12.155 8.40501 11.3083 8.76467 10.2294L9.57154 7.80883Z" fill="url(#paint0_linear_263_37996)"/><path class="tui-iconStars__2" d="M4.74293 1.4353C4.82529 1.18823 5.17476 1.18823 5.25711 1.4353L5.74122 2.88763C5.957 3.535 6.465 4.043 7.11237 4.25879L8.56471 4.7429C8.81178 4.82526 8.81178 5.17473 8.56471 5.25709L7.11238 5.7412C6.465 5.95699 5.957 6.46499 5.74122 7.11236L5.25711 8.5647C5.17476 8.81177 4.82528 8.81177 4.74293 8.5647L4.2588 7.11235C4.04301 6.46498 3.53502 5.95699 2.88765 5.7412L1.4353 5.25709C1.18823 5.17473 1.18823 4.82526 1.4353 4.7429L2.88765 4.25879C3.53502 4.043 4.04301 3.53501 4.2588 2.88764L4.74293 1.4353Z" fill="url(#paint1_linear_263_37996)"/><path class="tui-iconStars__3" d="M13.5786 0.123533C13.6335 -0.0411787 13.8665 -0.0411773 13.9214 0.123535L14.2441 1.09175C14.388 1.52333 14.7267 1.862 15.1583 2.00586L16.1265 2.3286C16.2912 2.38351 16.2912 2.61649 16.1265 2.67139L15.1583 2.99413C14.7267 3.13799 14.388 3.47666 14.2441 3.90824L13.9214 4.87647C13.8665 5.04118 13.6335 5.04118 13.5786 4.87647L13.2559 3.90823C13.112 3.47665 12.7734 3.13799 12.3418 2.99413L11.3735 2.67139C11.2088 2.61649 11.2088 2.38351 11.3735 2.3286L12.3418 2.00586C12.7734 1.862 13.112 1.52334 13.2559 1.09176L13.5786 0.123533Z" fill="url(#paint2_linear_263_37996)"/><defs><linearGradient id="paint0_linear_263_37996" x1="8.75" y1="0" x2="8.75" y2="20" gradientUnits="userSpaceOnUse"><stop stop-color="#9A2EF8"/><stop offset="1" stop-color="#FF9081"/></linearGradient><linearGradient id="paint1_linear_263_37996" x1="8.75" y1="0" x2="8.75" y2="20" gradientUnits="userSpaceOnUse"><stop stop-color="#9A2EF8"/><stop offset="1" stop-color="#FF9081"/></linearGradient><linearGradient id="paint2_linear_263_37996" x1="8.75" y1="0" x2="8.75" y2="20" gradientUnits="userSpaceOnUse"><stop stop-color="#9A2EF8"/><stop offset="1" stop-color="#FF9081"/></linearGradient></defs>',
];

const StarsIcon = createIconComponent(icon);
</script>

<style lang="scss">
// Cascading animation for individual stars
@keyframes tui-iconStars__cascade {
  0% {
    // Start small and offset top-left
    transform: scale(0) translate(-25%, -25%);
    opacity: 0;
  }
  50% {
    // Full size and centered
    transform: scale(1) translate(0, 0);
    opacity: 1;
  }
  100% {
    // End small and offset bottom-right
    transform: scale(0) translate(25%, 25%);
    opacity: 0;
  }
}

.tui-iconStars--animate {
  // Ensure transform works correctly on SVG
  display: inline-block;
  transform-origin: center center;

  .tui-iconStars__1,
  .tui-iconStars__2,
  .tui-iconStars__3 {
    // Ensure scaling happens from the center
    transform-origin: center center;
    // Common animation properties
    animation-name: tui-iconStars__cascade;
    animation-timing-function: ease-in-out;
    animation-iteration-count: infinite;
  }

  // Apply different durations for a staggered effect
  .tui-iconStars__1 {
    animation-duration: 2s;
  }
  .tui-iconStars__2 {
    animation-duration: 3s;
  }
  .tui-iconStars__3 {
    animation-duration: 4s;
  }
}
</style>
