<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Qingyang Liu <gary.liu@totara.com>
  @module totara_engage
-->

<template>
  <Responsive
    :breakpoints="[
      { name: 'small', boundaries: [0, 768] },
      { name: null, boundaries: [768, 1672] },
    ]"
    @responsive-resize="resize"
  >
    <div class="tui-totara_engage-yourLibrary">
      <ContributionBaseContent
        :loading="$apollo.loading"
        :loading-more="loadingMore"
        :cards="contribution.cards"
        :total-cards="contribution.cursor.total"
        :show-footnotes="includeFootnotes"
        :is-load-more-visible="isLoadMoreVisible"
        :show-empty-content="true"
        :from-library="true"
        :current-boundary-name="currentBoundaryName"
        @scrolled-to-bottom="scrolledToBottom"
        @load-more="loadMore"
      >
        <template v-slot:heading>
          <h1 class="tui-totara_engage-yourLibrary__title">
            {{ $str('yourlibrary', 'totara_engage') }}
          </h1>
        </template>

        <template
          v-if="pageProps.showCreateResource || pageProps.showCreatePlaylist"
          v-slot:creationButtons
        >
          <CreationDropDown
            :show-create-resource="pageProps.showCreateResource"
            :show-create-playlist="pageProps.showCreatePlaylist"
            @create-resource="createResourceHandler"
            @create-playlist="createPlaylistHandler"
          />
        </template>

        <template v-slot:navigation>
          <nav
            class="tui-totara_engage-yourLibrary__navigation"
            :aria-label="$str('yourlibrary', 'totara_engage')"
          >
            <NavigationPill
              v-for="(option, index) in getOptions"
              :key="index"
              class="tui-totara_engage-yourLibrary__navigation-button"
              :text="option.label"
              :selected="option.selected"
              @click="switchPills(option)"
            />
          </nav>
        </template>

        <template v-slot:filters>
          <ContributionFilter
            v-model:value="innerFilters"
            component="totara_engage"
            :area="contributionArea"
            :show-type="showTypeOption"
            :sort-value="filterValue.sort"
            @sort="filterSort"
            @input="updateFilters"
          />
        </template>
      </ContributionBaseContent>
      <ModalPresenter :open="modalOpen" @request-close="modalRequestClose">
        <ContributeModal
          :modal-title="modalTitle"
          :exclude-modals="excludeModals"
          :show-tab="showTab"
          :show-notification="pageProps.showNotification"
          @request-close="modalRequestClose"
        />
      </ModalPresenter>
    </div>
  </Responsive>
</template>

<script>
import ContributionBaseContent from 'totara_engage/components/contribution/BaseContent';
import ContributionFilter from 'totara_engage/components/contribution/FilterBarArea';
import { UrlSourceType } from 'totara_engage/index';
import CreationDropDown from 'totara_engage/components/dropdown/CreationDropDown';
import ContributeModal from 'totara_engage/components/modal/ContributeModal';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import NavigationPill from 'totara_engage/components/navigation/NavigationPill';
import Responsive from 'tui/components/responsive/Responsive';

// Mixins
import ContributionMixin, {
  contributionMixinData,
} from 'totara_engage/mixins/contribution_mixin';
import LibraryMixin from 'totara_engage/mixins/library_mixin';

export default {
  components: {
    ContributionBaseContent,
    ContributionFilter,
    CreationDropDown,
    ContributeModal,
    ModalPresenter,
    NavigationPill,
    Responsive,
  },

  mixins: [ContributionMixin, LibraryMixin],

  data() {
    return {
      ...contributionMixinData(),
      search: this.pageProps.search,
      options: this.pageProps.options,
      showTypeOption: true,
      typeOption: null,
      modalOpen: false,
      excludeModals: [],
      modalTitle: '',
      showTab: true,
      innerFilters: {
        bar: {
          search: null,
        },
        extra: {
          access: null,
          type: null,
          topic: null,
        },
      },
      currentBoundaryName: null,
    };
  },

  computed: {
    getOptions() {
      let options = JSON.parse(JSON.stringify(this.options));
      options.forEach(option => {
        Object.assign(option, {
          selected: this.filterValue.section == option.id,
        });
      });
      return options;
    },
  },

  created() {
    this.filterValue = Object.assign({}, this.filterValue, {
      search: '',
    });
    this.contributionComponent = 'totara_engage';
    this.contributionArea = 'search';
    this.contributionSource = UrlSourceType.libraryYourLibrary();
    this.includeFootnotes = true;
    this.footnotes.footnotes_type = 'search';
  },

  mounted() {
    if (this.creation === 'cr') {
      this.createResourceHandler();
    } else if (this.creation === 'cp') {
      this.createPlaylistHandler();
    }
  },

  methods: {
    /**
     * @param {Object} option
     */
    switchPills(option) {
      if (option.id) {
        // Only switch different pill, trigger reset values.
        if (this.filterValue.section !== option.id) {
          this.resetFilters();
        }
        this.filterValue = Object.assign({}, this.filterValue, {
          section: option.id,
        });
      } else {
        if (this.filterValue.section) {
          this.resetFilters();
        }
        this.filterValue = Object.assign({}, this.filterValue, {
          section: null,
          search: '',
        });
      }

      this.setContributionToDefault();
      this.changeContributionprops(option);
    },

    setContributionToDefault() {
      this.filterValue = Object.assign({}, this.filterValue, {
        sort: 1,
      });
      this.includeFootnotes = false;
      this.footnotes.footnotes_type = null;
      this.showTypeOption = true;
      if (this.typeOption) {
        this.filterValue = Object.assign({}, this.filterValue, {
          type: this.typeOption,
        });
      }
    },

    /**
     * @param {Object} option
     */
    changeContributionprops(option) {
      switch (option.value) {
        case 'YOURRESOURCES':
          this.contributionArea = 'owned';
          break;
        case 'SHAREDWITHYOU':
          this.contributionArea = 'shared';
          this.includeFootnotes = true;
          this.footnotes.footnotes_type = 'shared';
          this.filterValue = Object.assign({}, this.filterValue, {
            sort: 5,
          });
          break;
        case 'SAVEDRESOURCES':
          this.contributionArea = 'saved';
          break;
        case 'YOURPLAYLISTS':
        case 'SAVEDPLAYLISTS':
          this.showTypeOption = false;
          this.contributionArea = 'search';
          if (this.filterValue.type) {
            this.typeOption = this.filterValue.type;
          }
          // Do not pass type option for playlist sections.
          this.filterValue = Object.assign({}, this.filterValue, {
            type: null,
          });
          break;
        default:
          this.contributionArea = 'search';
          this.includeFootnotes = true;
          this.footnotes.footnotes_type = 'search';
      }
    },

    createResourceHandler() {
      this.modalOpen = true;
      this.excludeModals = ['totara_playlist'];
      this.modalTitle = this.$str('contribute', 'totara_engage');
      this.showTab = true;
    },

    createPlaylistHandler() {
      this.modalOpen = true;
      this.excludeModals = ['engage_article', 'engage_survey'];
      this.modalTitle = this.$str('playlist:create', 'totara_playlist');
      this.showTab = false;
    },

    modalRequestClose() {
      this.modalOpen = false;
    },

    /**
     * Update filters
     */
    updateFilters() {
      const { extra, bar } = this.innerFilters;
      this.filterValue = Object.assign({}, this.filterValue, {
        access: extra.access,
        type: extra.type ?? '',
        topic: extra.topic ?? '',
        search: bar.search ?? '',
      });
    },

    resetFilters() {
      this.innerFilters.bar.search = null;
      this.innerFilters.extra.access = null;
      this.innerFilters.extra.type = null;
      this.innerFilters.extra.topic = null;
      this.updateFilters();
    },

    resize(boundaryName) {
      this.currentBoundaryName = boundaryName;
    },
  },
};
</script>

<style lang="scss">
.tui-totara_engage-yourLibrary {
  &__navigation {
    display: flex;
    flex-wrap: wrap;
    gap: var(--gap-3);
    margin: var(--gap-4) 0;
  }

  &__title {
    margin: 0;
  }
}
</style>
