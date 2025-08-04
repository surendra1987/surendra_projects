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
  <div class="core_tag-addStandardTags">
    <Button
      class="core_tag-addStandardTags__trigger"
      :text="$str('addotags', 'tag')"
      @click="open = true"
    />
    <ModalPresenter :open="open" @request-close="open = false">
      <Modal size="small" :aria-labelledby="uid">
        <ModalContent
          :close-button="true"
          :title="$str('addotags', 'tag')"
          :title-id="uid"
          @dismiss="open = false"
        >
          <Uniform ref="standardTags" @submit="addStandardTags">
            <FormRow :label="$str('inputstandardtags', 'tag')" :required="true">
              <FormTextarea name="name" :validations="v => [v.required()]" />
            </FormRow>
            <input v-show="false" type="submit" />
          </Uniform>
          <template v-slot:buttons>
            <ButtonGroup>
              <Button
                :styleclass="{ primary: 'true' }"
                :text="$str('continue', 'core')"
                @click="$refs.standardTags.submit()"
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
import { FormRow, FormTextarea, Uniform } from 'tui/components/uniform';

import { redirectWithPost } from 'tui/dom/form';
import { config } from 'tui/config';

export default {
  components: {
    Button,
    ButtonCancel,
    ButtonGroup,
    FormRow,
    FormTextarea,
    Modal,
    ModalContent,
    ModalPresenter,
    Uniform,
  },

  props: {
    id: Number,
  },

  data() {
    return {
      open: false,
      uid: this.$id('addCollection'),
      isValid: false,
    };
  },

  methods: {
    /**
     * Adds new standard tags to the collection
     *
     * @param {Object} values The Uniform form values
     */
    addStandardTags(values) {
      redirectWithPost(this.$url('/tag/manage.php'), {
        action: 'addstandardtag',
        sesskey: config.sesskey,
        tagslist: values.name,
        tc: this.id,
      });
    },
  },
};
</script>
<style lang="scss">
.core_tag-addStandardTags {
  &__trigger {
    margin: var(--gap-2) 0;
  }
}
</style>
