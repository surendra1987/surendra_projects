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

  @author Brian Barnes <brian.barnes@totara.com>
  @module totara_program
-->
<template>
  <Responsive
    class="tui-totara_program-courseSet"
    :breakpoints="[
      { name: 1, boundaries: [0, 360] },
      { name: 2, boundaries: [361, 540] },
      { name: 3, boundaries: [541, 720] },
      { name: 4, boundaries: [721, 900] },
      { name: 5, boundaries: [901, 1080] },
      { name: 6, boundaries: [1081, 1260] },
    ]"
    @responsive-resize="resize"
  >
    <Grid
      v-if="courses.length > 0"
      grid-tag="ol"
      :max-units="gridUnits"
      gutter-size="var(--gap-card-grid)"
    >
      <GridItem v-for="course in courses" :key="course.id" grid-item-tag="li">
        <CourseCard
          class="tui-totara_program-courseSet__courseCard"
          :image="course.image"
          :title="course.fullname"
          :limit-height="true"
        >
          <div v-if="course.score">
            {{ $str('score', 'totara_program', course.score) }}
          </div>
          <div class="tui-totara_program-courseSet__courseCardProgress">
            <div
              v-if="course.no_criteria"
              class="tui-totara_program-courseSet__courseCardProgressLozenge"
            >
              <Lozenge
                type="neutral"
                :text="$str('statusnocriteria', 'core_completion')"
              />
            </div>
            <Progress
              v-else
              class="tui-totara_program-courseSet__courseCardProgressIndicator"
              :value="course.progress"
            />
            <Dropdown
              v-if="course.can_mark_complete && !course.is_complete"
              :separator="false"
              :close-on-click="false"
            >
              <template v-slot:trigger="{ toggle, isOpen }">
                <ButtonIcon
                  :styleclass="{
                    stealth: true,
                    transparent: true,
                  }"
                  class="tui-totara_program-courseSet__courseCardProgressMenu"
                  :aria-label="
                    $str('showxoptions', 'totara_program', course.fullname)
                  "
                  :aria-expanded="isOpen"
                  @click="toggle"
                >
                  <MoreIcon />
                </ButtonIcon>
              </template>
              <DropdownItem
                :href="course.completeURL"
                :aria-label="course.fullname"
              >
                {{ $str('markcomplete', 'totara_program') }}
              </DropdownItem>
            </Dropdown>
          </div>

          <Button
            :disabled="!course.launchURL"
            class="tui-totara_program-courseSet__courseCardLaunch"
            :styleclass="{
              stealth: true,
            }"
            :text="
              !course.launchURL
                ? $str('notavailable', 'totara_program')
                : $str('launchcourse', 'totara_program')
            "
            :aria-label="
              course.launchURL
                ? $str('launchcoursex', 'totara_program', course.fullname)
                : $str('courseanotavailable', 'totara_program', course.fullname)
            "
            @click="launchCourse(course.launchURL)"
          />
        </CourseCard>
      </GridItem>
    </Grid>
  </Responsive>
</template>
<script>
import Responsive from 'tui/components/responsive/Responsive';
import Grid from 'tui/components/grid/Grid';
import GridItem from 'tui/components/grid/GridItem';
import Progress from 'tui/components/progress/Progress';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import MoreIcon from 'tui/components/icons/More';
import CourseCard from 'core_course/components/cards/CourseCard';
import Lozenge from 'tui/components/lozenge/Lozenge';

export default {
  components: {
    Responsive,
    Progress,
    Grid,
    GridItem,
    Dropdown,
    DropdownItem,
    Button,
    ButtonIcon,
    MoreIcon,
    CourseCard,
    Lozenge,
  },

  props: {
    courses: {
      type: Array,
      default() {
        return [];
      },
    },

    courseSetName: String,
  },

  data() {
    return {
      gridUnits: 6,
    };
  },

  methods: {
    resize(units) {
      this.gridUnits = units;
    },

    launchCourse(url) {
      window.location = url;
    },
  },
};
</script>
<style lang="scss">
.tui-totara_program-courseSet {
  &__course {
    position: relative;
  }

  &__courseCardProgress {
    display: flex;
    align-items: center;
    height: rem-px(20);

    > * + * {
      margin-left: var(--gap-2);
    }
  }

  &__courseCardProgressLozenge,
  &__courseCardProgressIndicator {
    flex-grow: 1;
  }

  &__courseCardProgressMenu {
    display: block;
  }

  &__courseCardLaunch {
    align-self: flex-end;
  }
}
</style>
