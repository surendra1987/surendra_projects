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
  @module totara_playlist
-->

<template>
  <div class="tui-addNewPlaylistCard">
    <Card class="tui-addNewPlaylistCard__card">
      <Contribute
        :adder-text="$str('contribute_adder_text', 'totara_playlist')"
        :show-text="false"
        :show-icon="true"
        :styleclass="{ circle: true, primary: true }"
        :size="500"
        :container="container"
        @done="$emit('contribute', $event)"
        @open-adder="showAdder = true"
        @open-course-adder="showCourseAdder = true"
      />
    </Card>

    <Adder
      grid-direction="horizontal"
      :playlist-id="playlistId"
      :show-adder="showAdder"
      :show-course-adder="showCourseAdder"
      :units="12"
      @close="closeAdders"
    />
  </div>
</template>

<script>
import Card from 'tui/components/card/Card';

import Contribute from 'totara_engage/components/contribution/Contribute';
import { AccessManager } from 'totara_engage/index';

import Adder from 'totara_playlist/components/contribution/Adder';

export default {
  components: {
    Adder,
    Card,
    Contribute,
  },

  props: {
    playlistId: {
      type: [Number, String],
      required: true,
    },

    access: {
      type: String,
      required: true,
      validator(prop) {
        return AccessManager.isValid(prop);
      },
    },
  },

  emits: ['contribute'],

  data() {
    return {
      showAdder: false,
      showCourseAdder: false,
    };
  },

  computed: {
    container() {
      return {
        instanceId: this.playlistId,
        component: 'totara_playlist',
        access: this.access,
        showModal: false,
      };
    },
  },

  methods: {
    closeAdders() {
      this.showAdder = false;
      this.showCourseAdder = false;
    },
  },
};
</script>

<style lang="scss">
.tui-addNewPlaylistCard {
  width: 100%;
  height: calc(var(--engage-card-height) + 11px);

  .tui-card {
    // Overiding cards border
    border: 2px dashed var(--color-primary);
  }

  &__card {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
  }

  &__icon {
    color: var(--color-primary);
    cursor: pointer;
  }
}
</style>
