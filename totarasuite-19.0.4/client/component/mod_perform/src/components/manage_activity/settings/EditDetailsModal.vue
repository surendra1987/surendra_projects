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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @module totara_perform
-->

<template>
  <Modal
    :aria-labelledby="$id('title')"
    class="tui-performPASettingEditDetails"
  >
    <ModalContent
      :title="$str('edit_activity', 'mod_perform')"
      :title-id="$id('title')"
      @dismiss="$emit('request-close')"
    >
      <Loader :loading="loading">
        <Uniform
          ref="form"
          :initial-values="initialValues"
          @submit="updateSetting"
        >
          <FormRowStack spacing="small">
            <!-- Activity name -->
            <FormRow
              :label="$str('general_info_label_activity_title', 'mod_perform')"
              required
            >
              <FormText
                char-length="full"
                :maxlength="data.name.max_length"
                name="name"
                :validations="v => [v.required()]"
              />
            </FormRow>

            <!-- Activity description -->
            <FormRow
              :label="
                $str('general_info_label_activity_description', 'mod_perform')
              "
            >
              <FormTextarea char-length="full" name="description" :rows="4" />
            </FormRow>

            <!-- Activity type -->
            <FormRow
              :label="$str('general_info_label_activity_type', 'mod_perform')"
              :required="!activityActive"
            >
              <div>
                <span
                  v-if="activityActive"
                  class="tui-performPASettingEditDetails__type-static"
                >
                  {{ data.type.display_name }}
                </span>

                <FormSelect
                  v-else
                  char-length="20"
                  name="type_id"
                  :options="data.types"
                  :validations="v => [v.required()]"
                />
              </div>
            </FormRow>
          </FormRowStack>
        </Uniform>
      </Loader>

      <template v-slot:buttons>
        <Button
          :disabled="loading"
          :loading="saving"
          :styleclass="{ primary: true }"
          :text="$str('button_save', 'mod_perform')"
          @click="$refs.form.submit()"
        />

        <CancelButton
          :disabled="saving || loading"
          @click="$emit('request-close')"
        />
      </template>
    </ModalContent>
  </Modal>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import CancelButton from 'tui/components/buttons/Cancel';
import {
  FormRow,
  FormRowStack,
  FormSelect,
  FormText,
  FormTextarea,
  Uniform,
} from 'tui/components/uniform';
import Loader from 'tui/components/loading/Loader';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import { notify } from 'tui/notifications';

// Query
import updateActivity from 'mod_perform/graphql/update_activity_basic_settings';

export default {
  components: {
    Button,
    CancelButton,
    Uniform,
    FormRow,
    FormRowStack,
    FormText,
    FormSelect,
    FormTextarea,
    Loader,
    Modal,
    ModalContent,
  },

  props: {
    activityId: { required: true, type: [Number, String] },
    data: { required: true, type: Object },
    loading: { type: Boolean },
  },

  emits: ['request-close', 'updated'],

  data() {
    return {
      initialValues: {
        description: this.data.description.edit_value,
        name: this.data.name.edit_value,
        type_id: this.data.type.id,
      },
      saving: false,
    };
  },

  computed: {
    /**
     * Is the activity active
     *
     */
    activityActive() {
      return this.data.state_details.name === 'ACTIVE';
    },
  },

  methods: {
    /**
     * Handle the submission of setting value changes
     *
     * @param {Object} values form inputs
     */
    async updateSetting(values) {
      try {
        this.saving = true;
        const result = await this.$apollo.mutate({
          mutation: updateActivity,
          variables: {
            input: {
              activity_id: this.activityId,
              description: values.description,
              name: values.name,
              type_id: values.type_id,
            },
          },
        });

        if (result) {
          this.$emit('updated');
        }
      } catch (e) {
        this.saving = false;
        // Error notification
        notify({
          message: this.$str('toast_error_generic_update', 'mod_perform'),
          type: 'error',
        });
      }
    },
  },
};
</script>
