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

  @author Simon Chester <simon.chester@totara.com>
  @module totara_reportbuilder
-->

<template>
  <Modal :aria-labelledby="$id('title')" size="small">
    <ModalContent
      :title="$str('new_tenant_report', 'totara_reportbuilder')"
      :title-id="$id('title')"
    >
      <Uniform ref="form" input-width="full" @submit="handleSubmit">
        <FormRow :label="$str('select_tenant', 'totara_tenant')">
          <FormSelect
            name="tenantId"
            :options="tenantOptions"
            :validations="v => [v.required()]"
          />
        </FormRow>

        <input v-show="false" type="submit" />
      </Uniform>

      <template v-slot:buttons>
        <Button
          :text="$str('createreport', 'totara_reportbuilder')"
          :styleclass="{ primary: true }"
          @click="$refs.form.submit()"
        />
        <Button
          :text="$str('cancel', 'core')"
          @click="$emit('request-close')"
        />
      </template>
    </ModalContent>
  </Modal>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import { Uniform, FormRow, FormSelect } from 'tui/components/uniform';

export default {
  components: {
    Button,
    Modal,
    ModalContent,
    Uniform,
    FormRow,
    FormSelect,
  },

  props: {
    tenants: { type: Array, required: false },
  },

  emits: ['request-close', 'submit'],

  computed: {
    tenantOptions() {
      return this.tenants.map(x => ({ id: x.id, label: x.name }));
    },
  },

  methods: {
    handleSubmit(values) {
      this.$emit('submit', values);
    },
  },
};
</script>
