/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totaralearning.com] for more information.
 *
 * @author Tatsuhiro Kirihara <tatsuhiro.kirihara@totaralearning.com>
 * @module mod_approval
 */

import { shallowMount } from '@vue/test-utils';
import { structuralDeepClone } from 'tui/util';
import MicroProfileCard from '../MicroProfileCard';

const propsTemplate = {
  user: {
    id: 42,
    fullname: 'Totara User',
    email: 'totara.user@example.com',
    profileimageurl: 'https://example.com/user.jpg',
  },
};

describe('mod_approval/components/cards/MicroProfileCard', () => {
  let props;

  beforeEach(() => {
    props = structuralDeepClone(propsTemplate);
  });

  it('Check snapshot with default', () => {
    const wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.element).toMatchSnapshot();
  });

  it('Check snapshot with large size', () => {
    props.size = 'large';
    const wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.element).toMatchSnapshot();
  });

  it('Check snapshot with readonly', () => {
    props.readOnly = true;
    const wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.element).toMatchSnapshot();
  });

  it('Check snapshot with custom name', () => {
    props.name = 'Cus Tom Name';
    const wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.element).toMatchSnapshot();
  });

  it('Check snapshot with email', () => {
    props.showEmail = true;
    const wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.element).toMatchSnapshot();
  });

  it('Check snapshot with emphasised', () => {
    props.emphasiseName = true;
    const wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.element).toMatchSnapshot();
  });

  it('Check avatarUrl', () => {
    let wrapper;

    delete props.user.profileimageurlsmall;
    delete props.user.profileimageurl;
    delete props.user.card_display;

    props.user.profileimageurlsmall = 'https://example.com/small.jpg';
    wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.vm.avatarUrl).toEqual('https://example.com/small.jpg');

    props.user.profileimageurl = 'https://example.com/medium.jpg';
    wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.vm.avatarUrl).toEqual('https://example.com/medium.jpg');

    props.user.card_display = {
      profile_picture_url: 'https://example.com/large.jpg',
    };
    wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.vm.avatarUrl).toEqual('https://example.com/large.jpg');
  });

  it('Check avatarAlt', () => {
    let wrapper;

    wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.vm.avatarAlt).toEqual('');

    props.user.card_display = { profile_picture_alt: 'DJ ToT@rA' };
    wrapper = shallowMount(MicroProfileCard, { props });
    expect(wrapper.vm.avatarAlt).toEqual('DJ ToT@rA');
  });
});
