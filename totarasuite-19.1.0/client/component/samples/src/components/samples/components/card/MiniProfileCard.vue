<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD’s customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kian Nguyen <kian.nguyen@totaralearning.com>
  @author Alvin Smith <alvin.smith@totaralearning.com>
  @module samples
-->
<template>
  <SamplesExample>
    <template v-if="includeDropdown">
      <MiniProfileCard
        :drop-down-position="dropDownPosition"
        :display="cardData"
        :no-border="noBorder"
        :no-padding="noPadding"
        :horizontal="horizontal"
        :has-shadow="hasShadow"
        :read-only="readOnly"
      >
        <template v-slot:drop-down-items>
          <DropdownItem>Dropdown item</DropdownItem>
        </template>
      </MiniProfileCard>
    </template>
    <template v-else>
      <MiniProfileCard
        :drop-down-position="dropDownPosition"
        :display="cardData"
        :no-border="noBorder"
        :no-padding="noPadding"
        :horizontal="horizontal"
        :has-shadow="hasShadow"
        :read-only="readOnly"
      />
    </template>
  </SamplesExample>

  <SamplesCtl>
    <FormRow v-slot="{ id }" label="No border">
      <RadioGroup :id="id" v-model:value="noBorder" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        noBorder
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="No padding">
      <RadioGroup :id="id" v-model:value="noPadding" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        noPadding
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Horizontal">
      <RadioGroup :id="id" v-model:value="horizontal" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        horizontal
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Has shadow">
      <RadioGroup :id="id" v-model:value="hasShadow" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        hasShadow
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Read only">
      <RadioGroup :id="id" v-model:value="readOnly" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        readOnly
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Dropdown position">
      <RadioGroup :id="id" v-model:value="dropDownPosition" :horizontal="true">
        <Radio :value="undefined">Unspecified</Radio>
        <Radio value="top-left">top-left</Radio>
        <Radio value="top-right">top-right</Radio>
        <Radio value="bottom-left">bottom-left</Radio>
        <Radio value="bottom-right">bottom-right</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        dropDownPosition
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Description rows">
      <InputText :id="id" v-model:value="descriptionRow1" />
      <InputText :id="id" v-model:value="descriptionRow2" />
      <InputText :id="id" v-model:value="descriptionRow3" />
      <InputText :id="id" v-model:value="descriptionRow4" />
      <FormRowDetails :id="id">
        display
      </FormRowDetails>
    </FormRow>
    <FormRow v-slot="{ id, label }" label="Profile picture alt">
      <InputText
        :id="id"
        v-model:value="profilePictureAlt"
        :placeholder="label"
      />
      <FormRowDetails :id="id">
        display
      </FormRowDetails>
    </FormRow>
    <FormRow v-slot="{ id, label }" label="Profile picture source">
      <InputText
        :id="id"
        v-model:value="profilePictureUrl"
        :placeholder="label"
      />
      <FormRowDetails :id="id">
        display
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>

  <SamplesCtl label="Slot options">
    <FormRow v-slot="{ id }" label="Include dropdown">
      <RadioGroup v-model:value="includeDropdown" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        drop-down-options
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<script>
import { createSilhouetteImage } from 'samples/components/sample_parts/misc/placeholder_generator';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import MiniProfileCard from 'tui/components/profile/MiniProfileCard';
import InputText from 'tui/components/form/InputText';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    Radio,
    RadioGroup,
    FormRow,
    FormRowDetails,
    DropdownItem,
    MiniProfileCard,
    InputText,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      includeDropdown: true,
      dropDownPosition: 'bottom-right',
      noBorder: false,
      noPadding: false,
      horizontal: false,
      hasShadow: false,
      readOnly: false,
      descriptionRow1: 'Charles F. Oliver',
      descriptionRow2: '@herecomescharlie',
      descriptionRow3:
        '1621 Frum Street, Nashville, TN, Tennessee, United States',
      descriptionRow4: 'charles.f.oliver@example.com',
      profilePictureAlt: 'Charles F. Oliver picture',
      profilePictureUrl: createSilhouetteImage('#3c9'),
    };
  },

  computed: {
    cardData() {
      return {
        profile_picture_alt: this.profilePictureAlt,
        profile_picture_url: this.profilePictureUrl,
        profile_url: this.$url('/user/profile.php'),
        display_fields: [
          {
            label: 1,
            value: this.descriptionRow1,
            associate_url: this.$url('/user/profile.php'),
          },
          {
            label: 2,
            value: this.descriptionRow2,
            associate_url: null,
          },
          {
            label: 3,
            value: this.descriptionRow3,
            associate_url: null,
          },
          {
            label: 4,
            value: this.descriptionRow4,
            associate_url: null,
          },
        ],
      };
    },
  },
};
</script>
