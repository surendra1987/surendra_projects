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
  <div class="tui-playlistResourcesGrid">
    <template v-if="!$apollo.loading">
      <Droppable
        v-slot="{ attrs, events }"
        :source-id="$id('playlist-grid')"
        source-name="Playlist Resources Grid"
        :accept-drop="() => updateAble"
        layout-interaction="grid-line"
        axis="horizontal"
        @drop="handleDrop"
      >
        <div
          v-bind="attrs"
          class="tui-playlistResourcesGrid__container"
          v-on="events || {}"
        >
          <template v-for="(card, index) in allCards" :key="index">
            <template v-if="card.component === 'AddNewPlaylistCard'">
              <AddNewPlaylistCard
                :playlist-id="playlistId"
                :access="access"
                @contribute="addResource"
              />
            </template>
            <template v-else-if="card.component !== 'FillSlot'">
              <Draggable
                v-slot="{ dragging, anyDragging, attrs, events }"
                :index="index"
                type="playlist-grid-item"
                :value="card"
                :aria-label="$str('move_element', 'totara_playlist', card.name)"
              >
                <div
                  v-bind="attrs"
                  class="tui-playlistResourcesGrid__card-item"
                  :class="{
                    'tui-playlistResourcesGrid__card-item--dragging':
                      updateAble && dragging,
                  }"
                  v-on="events"
                >
                  <EngageCard
                    :card-attribute="card"
                    :item-draggable="updateAble && (!anyDragging || dragging)"
                    :aria-labelledby="$id(`row-${index}-label`)"
                    :label-id="$id(`row-${index}-label`)"
                    :aria-posinset="index"
                    :aria-setsize="cards.length"
                    :show-footnotes="updateAble"
                    @refetch="$emit('refetch', $event)"
                  />
                </div>
              </Draggable>
            </template>
            <template v-else>
              <AddNewPlaylistCard
                :style="{ visibility: 'hidden' }"
                :playlist-id="playlistId"
                :access="access"
                @contribute="addResource"
              />
            </template>
          </template>
        </div>
      </Droppable>
    </template>
  </div>
</template>

<script>
import Draggable from 'tui/components/drag_drop/Draggable';
import Droppable from 'tui/components/drag_drop/Droppable';
import EngageCard from 'totara_engage/components/card/compute/EngageCard';
import AddNewPlaylistCard from 'totara_playlist/components/card/AddNewPlaylistCard';

// GraphQL queries
import addResources from 'totara_playlist/graphql/add_resources';

export default {
  components: {
    AddNewPlaylistCard,
    Draggable,
    Droppable,
    EngageCard,
  },

  props: {
    access: {
      type: String,
      required: true,
    },
    cards: {
      type: Array,
      required: true,
    },
    contributable: {
      type: Boolean,
      default: true,
    },
    playlistId: {
      type: [Number, String],
      required: true,
    },
    updateAble: Boolean,
    interactor: {
      type: Object,
      default: () => ({
        user_id: 0,
        can_bookmark: false,
        can_comment: false,
        can_rate: false,
        can_react: false,
        can_share: false,
        can_add_resource: false,
      }),
    },
  },

  emits: ['refetch', 'resource-added', 'resource-reordered'],

  data() {
    return {
      loadingComponents: false,
    };
  },

  computed: {
    allCards() {
      if (!this.contributable || !this.interactor.can_add_resource) {
        return this.cards;
      }

      return [
        {
          component: 'AddNewPlaylistCard',
        },
      ].concat(this.cards);
    },
  },

  methods: {
    /**
     *
     * @param {Number} resourceId
     */
    addResource({ resourceId }) {
      this.$apollo
        .mutate({
          mutation: addResources,
          refetchAll: false,
          refetchQueries: [
            'totara_playlist_cards',
            'totara_playlist_get_playlist',
          ],
          variables: {
            playlistid: this.playlistId,
            resources: [resourceId],
          },
        })
        .then(() => {
          this.$emit('resource-added');
        });
    },
    handleDrop(info) {
      if (info.destination.sourceId == info.source.sourceId) {
        const list = this.cards.slice();

        // -1 since card index from 1. The index 0 is the AddResourceCard
        const item = list.splice(info.source.index - 1, 1)[0];

        // Same reason, -1 to access the real index
        list.splice(info.destination.index - 1, 0, item);

        const { instanceid } = item;
        const destinationIndex = info.destination.index - 1;
        const playlistId = this.playlistId;

        this.$emit('resource-reordered', {
          list,
          instanceid,
          destinationIndex,
          playlistId,
        });
      }
    },
  },
};
</script>

<style lang="scss">
.tui-playlistResourcesGrid {
  &__container {
    position: relative;
    display: grid;
    grid-template-columns: repeat(
      auto-fill,
      minmax(min(var(--tui-card-default-width), 100%), 1fr)
    );
    gap: var(--gap-4);
  }

  &__card {
    &-item {
      position: relative;
      height: 100%;

      &-moveIcon {
        position: absolute;
        top: var(--gap-2);
        left: var(--gap-2);
        display: none;
      }

      &:hover &-moveIcon,
      &--dragging &-moveIcon {
        z-index: 1;
        display: block;
      }
    }
  }
}
</style>
