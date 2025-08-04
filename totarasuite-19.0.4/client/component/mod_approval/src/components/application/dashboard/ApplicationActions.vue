<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kunle Odusan <kunle.odusan@totaralearning.com>
  @module mod_approval
-->
<template>
  <div>
    <Dropdown position="bottom-left">
      <template v-slot:trigger="{ toggle, isOpen }">
        <MoreButton
          :no-padding="true"
          :aria-label="$str('more_actions', 'mod_approval')"
          :aria-expanded="isOpen"
          @click="toggle"
        />
      </template>
      <DropdownItem
        v-if="application.interactor.can_edit"
        :href="applicationEditUrl"
      >
        {{ $str('edit', 'mod_approval') }}
      </DropdownItem>
      <DropdownItem :href="application.page_urls.preview" target="_blank">
        {{ $str('print_preview', 'mod_approval') }}
      </DropdownItem>
      <DropdownItem
        v-if="application.interactor.can_clone"
        @click="handleCloneClick"
      >
        {{ $str('clone', 'mod_approval') }}
      </DropdownItem>
      <DropdownItem
        v-if="application.interactor.can_delete"
        @click="
          $send({
            type: $e.CONFIRM_DELETE_APPLICATION,
            applicationToDeleteId: application.id,
          })
        "
      >
        {{ $str('delete', 'core') }}
      </DropdownItem>
    </Dropdown>

    <ModalPresenter :open="isCloneOpen" @request-close="isCloneOpen = false">
      <CloneApplicationModal
        :create-new-application-menu="createNewApplicationMenu"
        @clone="handleCloneChoice"
      />
    </ModalPresenter>
  </div>
</template>

<script>
import { totaraUrl } from 'tui/util';
import { MOD_APPROVAL__DASHBOARD_TABLE } from 'mod_approval/constants';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import MoreButton from 'tui/components/buttons/MoreIcon';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import CloneApplicationModal from 'mod_approval/components/application/dashboard/CloneApplicationModal';
import createNewApplicationMenuQuery from 'mod_approval/graphql/create_new_application_menu';
import applicationCloneMutation from 'mod_approval/graphql/application_clone';

export default {
  components: {
    CloneApplicationModal,
    ModalPresenter,
    Dropdown,
    DropdownItem,
    MoreButton,
  },

  props: {
    application: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      isCloneOpen: false,
      createNewApplicationMenu: [],
    };
  },

  xState: {
    machineId: MOD_APPROVAL__DASHBOARD_TABLE,
  },

  computed: {
    applicationEditUrl() {
      return this.$url(
        this.application.page_urls.edit,
        this.$selectors.getParsedParams(this.$context)
      );
    },
  },

  methods: {
    async handleCloneClick() {
      const { application } = this;
      const result = await this.$apollo.query({
        query: createNewApplicationMenuQuery,
        variables: {
          query: {
            workflow_type_id: application.workflow_type_id,
            applicant_id: application.user.id,
          },
        },
      });

      const items = result.data.mod_approval_create_new_application_menu;
      this.createNewApplicationMenu = items;

      if (items.length === 0) {
        await this.clone(null);
      } else if (items.length === 1) {
        await this.clone(items[0].job_assignment_id);
      } else {
        this.isCloneOpen = true;
      }
    },

    handleCloneChoice(id) {
      this.isCloneOpen = false;
      this.clone(id);
    },

    async clone(jobAssignmentId) {
      const result = await this.$apollo.mutate({
        mutation: applicationCloneMutation,
        variables: {
          input: {
            application_id: this.application.id,
            job_assignment_id: jobAssignmentId,
          },
        },
      });

      const edit_url =
        result.data.mod_approval_application_clone.application.page_urls.edit;

      window.location.href = totaraUrl(edit_url, {
        notify_type: 'success',
        notify: 'clone_application',
      });
    },
  },
};
</script>
