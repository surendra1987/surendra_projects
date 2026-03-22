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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package core_my
-->

<template>
  <Modal :aria-labelledby="$id('title')" size="large">
    <ModalContent
      :close-button="true"
      :title="title"
      :title-id="$id('title')"
      :title-small="stackedPage"
      @dismiss="$emit('cancel')"
    >
      <div class="tui-overviewViewAllModal">
        <div class="tui-overviewViewAllModal__content">
          <slot name="view-all-content" />

          <div class="tui-overviewViewAllModal__content-loadMore">
            <Button
              v-if="!lastViewAllPage"
              :disabled="loading"
              :styleclass="{ transparent: true }"
              :text="$str('overview_view_all_load_more', 'core_my')"
              @click="$emit('view-all-load-more')"
            />
          </div>
        </div>

        <div class="tui-overviewViewAllModal__footer">
          <Button
            :text="$str('overview_view_all_close', 'core_my')"
            @click="$emit('request-close')"
          />
        </div>
      </div>
    </ModalContent>
  </Modal>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';

export default {
  components: {
    Button,
    Modal,
    ModalContent,
  },

  props: {
    // Last page of content
    lastViewAllPage: {
      type: Boolean,
    },
    // Loading more content
    loading: {
      type: Boolean,
    },
    // Outer layout is stacked
    stackedPage: {
      type: Boolean,
    },
    // Modal title text
    title: {
      type: String,
    },
  },

  emits: ['cancel', 'view-all-load-more', 'request-close'],
};
</script>

<style lang="scss">
.tui-overviewViewAllModal {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  height: rem-px(500);
  margin-top: var(--gap-3);

  & > * + * {
    margin-top: var(--gap-4);
  }

  &__content {
    flex-grow: 1;
    padding-bottom: var(--gap-2);
    overflow: scroll;

    & > * + * {
      margin-top: var(--gap-2);
    }

    &-loadMore {
      text-align: center;
    }
  }

  &__footer {
    display: flex;
    justify-content: flex-end;
  }
}
</style>
