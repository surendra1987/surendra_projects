<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Brian Barnes <brian.barnes@totara.com>
  @module core_tag
-->

<template>
  <div class="core_tag--addCollection">
    <Button :text="$str('addtagcoll', 'tag')" @click="open = true" />
    <ModalPresenter :open="open" @request-close="open = false">
      <Modal size="small" :aria-labelledby="id">
        <ModalContent
          :close-button="true"
          :title="$str('addtagcoll', 'tag')"
          :title-id="id"
          @dismiss="open = false"
        >
          <Uniform
            ref="createTagColl"
            :initial-values="{ searchable: true }"
            @submit="addTagCollection"
          >
            <FormRow :label="$str('name', 'core')" :required="true">
              <FormText name="name" :validations="v => [v.required()]" />
            </FormRow>

            <FormRow>
              <FormCheckbox name="searchable">
                {{ $str('searchable', 'tag') }}
              </FormCheckbox>
            </FormRow>
            <input v-show="false" type="submit" />
          </Uniform>
          <template v-slot:buttons>
            <ButtonGroup>
              <Button
                :styleclass="{ primary: 'true' }"
                :text="$str('create', 'core')"
                @click="$refs.createTagColl.submit()"
              />
              <ButtonCancel @click="open = false" />
            </ButtonGroup>
          </template>
        </ModalContent>
      </Modal>
    </ModalPresenter>
  </div>
</template>
<script>
import Button from 'tui/components/buttons/Button';
import ButtonCancel from 'tui/components/buttons/Cancel';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import {
  FormRow,
  FormCheckbox,
  FormText,
  Uniform,
} from 'tui/components/uniform';

import { redirectWithPost } from 'tui/dom/form';
import { config } from 'tui/config';

export default {
  components: {
    Button,
    ButtonCancel,
    ButtonGroup,
    FormCheckbox,
    FormRow,
    FormText,
    Modal,
    ModalContent,
    ModalPresenter,
    Uniform,
  },

  data() {
    return {
      open: false,
      id: this.$id('addCollection'),
      formValues: null,
    };
  },

  methods: {
    /**
     * Submit the form adding a tag collection
     *
     * @param {Object} values the form values to submit
     */
    addTagCollection(values) {
      redirectWithPost(this.$url('/tag/manage.php'), {
        action: 'colladd',
        sesskey: config.sesskey,
        name: values.name,
        searchable: values.searchable ? '1' : '0',
      });
    },
  },
};
</script>
