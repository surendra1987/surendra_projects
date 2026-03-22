/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

import { h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import ModalPresenter from '../ModalPresenter';

const ModalStub = {
  inject: ['modal-presenter-interface'],
  render() {
    return h('div', ["i'm a modal"]);
  },
};

let wrapper;
let handleRequestClose;

const stubWrapper = () => wrapper.findComponent(ModalStub);
const mpi = () =>
  wrapper.findComponent(ModalStub).vm['modal-presenter-interface'];

describe('ModalPresenter', () => {
  beforeEach(() => {
    handleRequestClose = jest.fn();
    wrapper = mount(ModalPresenter, {
      slots: {
        default: [ModalStub],
      },
      props: {
        onRequestClose: handleRequestClose,
      },
    });
  });

  it('mounts and unmounts slot content', async () => {
    expect(wrapper.findComponent(ModalStub).exists()).toBeFalse();
    wrapper.setProps({ open: true });
    await nextTick();
    expect(wrapper.findComponent(ModalStub).exists()).toBeTrue();
    wrapper.setProps({ open: false });
    await nextTick();
    expect(wrapper.findComponent(ModalStub).exists()).toBeFalse();
  });

  it('waits until modal has closed before unmounting it', async () => {
    wrapper.setProps({ open: true });
    await nextTick();
    expect(wrapper.findComponent(ModalStub).exists()).toBeTrue();
    mpi().setIsOpen(true);
    wrapper.setProps({ open: false });
    await nextTick();
    expect(wrapper.findComponent(ModalStub).exists()).toBeTrue();
    mpi().setIsOpen(false);
    await nextTick();
    expect(wrapper.findComponent(ModalStub).exists()).toBeFalse();
  });

  it('passes request close event from interface through', async () => {
    wrapper.setProps({ open: true });
    expect(handleRequestClose).toHaveBeenCalledTimes(0);

    await nextTick();
    mpi().requestClose();
    expect(handleRequestClose).toHaveBeenCalledTimes(1);

    mpi().requestClose({ result: 3 });
    expect(handleRequestClose).toHaveBeenCalledWith({ result: 3 });
  });

  it('re-emits request close event from slot content', async () => {
    wrapper.setProps({ open: true });
    await nextTick();
    expect(handleRequestClose).toHaveBeenCalledTimes(0);

    stubWrapper().vm.$emit('request-close');

    expect(handleRequestClose).toHaveBeenCalledTimes(1);

    stubWrapper().vm.$emit('request-close', { result: 5 });
    expect(handleRequestClose).toHaveBeenCalledWith({ result: 5 });
  });
});
