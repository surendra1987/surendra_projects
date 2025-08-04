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

  @author Kian Nguyen <kian.nguyen@totaralearning.com>
  @module totara_engage
-->

<template>
  <div class="tui-engageTopicsSelector">
    <TopicsSelector
      :id="generatedId"
      :selected-topics="selectedTopics"
      :input-placeholder="$str('enter_topics', 'totara_engage')"
      :disabled="disabled"
      @change="$emit('change', $event)"
    />
  </div>
</template>

<script>
import TopicsSelector from 'totara_topic/components/form/TopicsSelector';

const has = Object.prototype.hasOwnProperty;

export default {
  components: {
    TopicsSelector,
  },

  props: {
    selectedTopics: {
      type: [Array, Object],
      default() {
        return [];
      },

      validator(prop) {
        let items = Array.prototype.slice.call(prop);
        for (let i in items) {
          if (!has.call(items, i)) {
            continue;
          }

          let item = items[i];
          if (!has.call(item, 'value') || !has.call(item, 'id')) {
            return false;
          }
        }

        return true;
      },
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['change'],

  computed: {
    generatedId() {
      return this.$id();
    },
  },
};
</script>
