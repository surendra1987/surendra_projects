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
  @module totara_core
-->

<template>
  <div class="tui-attachmentNodeCollection">
    <slot>
      <template v-if="hasFiles">
        <AttachmentNode
          v-for="(file, index) in files"
          :key="index"
          :filename="file.filename"
          :file-size="file.size"
          :download-url="file.download_url"
        />
      </template>
    </slot>
  </div>
</template>

<script>
import AttachmentNode from 'tui/components/json_editor/nodes/AttachmentNode';
const has = Object.prototype.hasOwnProperty;

export default {
  components: {
    AttachmentNode,
  },

  props: {
    files: {
      type: [Object, Array],
      default() {
        return [];
      },

      validator(prop) {
        prop = Array.prototype.slice.call(prop);

        const keys = ['filename', 'size', 'download_url'];

        // Finding the items that only have enuf keys above.
        const items = prop.filter(item => {
          for (let i in keys) {
            if (!has.call(keys, i)) {
              continue;
            }

            if (!has.call(item, keys[i])) {
              return false;
            }
          }

          return true;
        });

        return items.length === prop.length;
      },
    },
  },

  computed: {
    hasFiles() {
      const files = Array.prototype.slice.call(this.files);
      return files.length !== 0;
    },
  },
};
</script>

<style lang="scss">
.tui-attachmentNodeCollection {
  display: grid;
  grid-template-columns: #{unquote(
      'repeat(auto-fill, minmax(min(15.625rem, 100%), 1fr))'
    )};
  gap: var(--gap-2);
  padding: var(--gap-1) 0;
  white-space: normal;
}
</style>
