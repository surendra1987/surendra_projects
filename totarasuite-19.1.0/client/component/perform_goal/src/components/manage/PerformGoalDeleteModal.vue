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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @package perform_goal
-->

<template>
  <ConfirmationModal
    :confirm-button-text="$str('delete', 'core')"
    :loading="deleting"
    :open="open"
    :title="$str('goal_delete_modal_title', 'perform_goal')"
    @cancel="$emit('cancel')"
    @confirm="handleDelete"
  >
    <p>
      {{ $str('goal_delete_modal_message_1', 'perform_goal') }}
    </p>
    <p>
      {{ $str('goal_delete_modal_message_2', 'perform_goal') }}
    </p>
  </ConfirmationModal>
</template>

<script>
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';

// GraphQL
import DeleteGoalQuery from 'perform_goal/graphql/delete_goal';

export default {
  components: {
    ConfirmationModal,
  },

  props: {
    // The ID for this goal
    id: { type: [Number, String] },
    // Is the modal open
    open: { type: Boolean, required: true },
  },

  emits: ['cancel', 'handleDeleted'],

  data() {
    return {
      deleting: false,
    };
  },

  methods: {
    async handleDelete() {
      try {
        this.deleting = true;
        const result = await this.$apollo.mutate({
          mutation: DeleteGoalQuery,
          variables: {
            goal_reference: {
              id: parseInt(this.id),
            },
          },
        });

        this.$emit('handleDeleted', result);
      } finally {
        this.deleting = false;
      }
    },
  },
};
</script>
