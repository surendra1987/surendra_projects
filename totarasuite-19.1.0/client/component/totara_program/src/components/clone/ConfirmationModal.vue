<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2022 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Brian Barnes <brian.barnes@totara.com>
  @module totara_program
-->
<template>
  <ModalPresenter :open="open">
    <Modal>
      <ModalContent
        class="tui-totaraProgramCloneConfirmationModal"
        :close-button="true"
        :title="$str('cloneprogramx', 'totara_program', fullname)"
        @dismiss="$emit('dismiss')"
      >
        <Uniform
          ref="form"
          class="tui-totaraProgramCloneConfirmationModal__form"
          :initial-values="initialValues"
          @change="selectionChanged"
          @submit="clone"
        >
          <FormRow>
            <FormCheckboxGroup
              name="options"
              :validations="v => [v.required()]"
            >
              <Checkbox value="DETAILS">
                {{ $str('copydetails', 'totara_program') }}
              </Checkbox>
              <Checkbox value="CONTENT">
                {{ $str('copycontent', 'totara_program') }}
              </Checkbox>
              <Checkbox value="NOTIFICATION_PREFERENCES">
                {{ $str('copynotifications', 'totara_program') }}
              </Checkbox>
            </FormCheckboxGroup>
          </FormRow>
          <input v-show="false" type="submit" />
        </Uniform>
        <p v-if="isCertif">
          {{ $str('clonecertificationwarning', 'totara_certification') }}
        </p>
        <p v-else>
          {{ $str('cloneprogramwarning', 'totara_program') }}
        </p>
        <template v-slot:buttons>
          <ButtonGroup>
            <Button
              :styleclass="{ primary: 'true' }"
              :loading="false"
              :text="$str('clone', 'totara_program')"
              :disabled="!cloneEnabled"
              @click="$refs.form.submit()"
            />
            <ButtonCancel @click="$emit('dismiss')" />
          </ButtonGroup>
        </template>
      </ModalContent>
    </Modal>
  </ModalPresenter>
</template>
<script>
import Button from 'tui/components/buttons/Button';
import ButtonCancel from 'tui/components/buttons/Cancel';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import { Uniform, FormRow, FormCheckboxGroup } from 'tui/components/uniform';
import Checkbox from 'tui/components/form/Checkbox';

import Clone from 'totara_program/graphql/clone_program';

export default {
  components: {
    Modal,
    ModalContent,
    ModalPresenter,
    Button,
    ButtonCancel,
    ButtonGroup,

    Uniform,
    FormRow,
    FormCheckboxGroup,
    Checkbox,
  },

  props: {
    id: Number,
    fullname: String,
    open: Boolean,
    isCertif: {
      type: Boolean,
      required: true,
    },
  },

  emits: ['dismiss'],

  data() {
    return {
      initialValues: {
        options: ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES'],
      },
      cloneEnabled: true,
    };
  },

  methods: {
    /**
     * Creates and submits a form to start the clone process.
     *
     * @param options the options from Uniform
     */
    async clone({ options }) {
      let newId;
      newId = await this.$apollo.mutate({
        mutation: Clone,
        variables: {
          input: {
            program_id: this.id,
            clone_sections: options,
          },
        },
      });
      newId = newId.data.totara_program_clone_program.id;
      window.location = this.$url('/totara/program/edit.php', {
        id: newId,
        action: 'edit',
      });
    },

    /**
     * Handles changes to the form
     */
    selectionChanged(formData) {
      if (formData.options.length === 0) {
        this.cloneEnabled = false;
      } else {
        this.cloneEnabled = true;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-totaraProgramCloneConfirmationModal {
  &__form {
    margin-bottom: var(--gap-2);
  }
}
</style>
