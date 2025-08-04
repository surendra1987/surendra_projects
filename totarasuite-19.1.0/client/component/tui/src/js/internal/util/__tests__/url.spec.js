/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @module tui
 */

import { formatParams, parseParams } from '../url';

describe('formatParams', () => {
  it.each([
    ['a=1&b=2', { a: '1', b: '2' }],
    ['a[foo]=1&b=2', { a: { foo: '1' }, b: '2' }],
    ['a[foo][bar]=1&b=2', { a: { foo: { bar: '1' } }, b: '2' }],
    ['a[foo][0]=1&a[foo][1]=2', { a: { foo: ['1', '2'] } }],
  ])('parses url params (%j)', (str, obj) => {
    expect(formatParams(obj)).toEqual(str);
  });
});

describe('parseParams', () => {
  it.each([
    ['a=1&b=2', { a: '1', b: '2' }],
    ['a[foo]=1&b=2', { a: { foo: '1' }, b: '2' }],
    ['a[foo][bar]=1&b=2', { a: { foo: { bar: '1' } }, b: '2' }],
    ['a[foo][0]=1&a[foo][1]=2', { a: { foo: ['1', '2'] } }],
    ['a[foo][]=1&a[foo][]=2', { a: { foo: ['1', '2'] } }],
  ])('parses url params (%j)', (str, obj) => {
    expect(parseParams(str)).toEqual(obj);
  });

  it('handles malformed params', () => {
    expect(parseParams('a[foo]ab[]a=1')).toEqual({ 'a[foo]ab[]a': '1' });
  });

  it('supports URLSearchParams', () => {
    expect(parseParams('a=1&b=2')).toEqual({ a: '1', b: '2' });
  });

  it.each([
    { a: '1', b: '2' },
    { a: { foo: '1' }, b: '2' },
    { a: { foo: ['1', '2'] } },
  ])('successfully roundtrips formatParams (%j)', obj => {
    expect(parseParams(formatParams(obj))).toEqual(obj);
  });
});
