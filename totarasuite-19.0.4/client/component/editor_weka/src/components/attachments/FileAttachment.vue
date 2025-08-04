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
  @module editor_weka
-->

<template>
  <!-- Component for displaying draft file as attachment -->
  <div>
    <AttachmentNode
      :filename="filename"
      :file-size="fileSize"
      :item-id="draftId"
      component="user"
      area="draft"
    >
      <template v-if="!disabled" v-slot:actions>
        <AttachmentActions :actions="actions" :filename="filename" />
      </template>
    </AttachmentNode>
  </div>
</template>

<script>
import AttachmentNode from 'tui/components/json_editor/nodes/AttachmentNode';
import AttachmentActions from 'editor_weka/components/attachments/AttachmentActions';
import AttachmentMixin from 'editor_weka/mixins/attachment_mixin';

export default {
  components: {
    AttachmentNode,
    AttachmentActions,
  },

  mixins: [AttachmentMixin],

  emits: ['delete', 'download'],

  computed: {
    actions() {
      let actions = [
        {
          label: this.$str('remove', 'core'),
          action: () => {
            this.$emit('delete');
          },
        },
      ];

      if (this.hasDownloadUrl) {
        actions.push({
          label: this.$str('download', 'core'),
          action: () => {
            this.$emit('download');
          },
        });
      }

      return actions;
    },
  },
};
</script>
