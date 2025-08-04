/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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

/* eslint-disable jest/expect-expect */

const fs = require('fs');
const path = require('path');
const babel = require('@babel/core');
const plugin = require('../transform-lang-strings');

function transform(input, opts) {
  const { code } = babel.transformSync(input, {
    configFile: false,
    plugins: [plugin],
    ...opts,
  });
  return code;
}

function expectFixture(fixture) {
  const dir = path.join(__dirname, '__fixtures__', fixture.name);
  const source = fs.readFileSync(path.join(dir, 'code.js'), 'utf-8');
  const transformedSource = transform(source, {
    filename: fixture.filename,
  });
  const output = fs.readFileSync(path.join(dir, 'output.js'), 'utf-8');
  // normalise formatting for check
  const formattedOutput = babel.transformSync(output, { configFile: false })
    .code;
  expect(transformedSource).toEqual(formattedOutput);
}

function generateFixtureTests(fixtures) {
  fixtures.forEach(fixture => {
    fixture = typeof fixture === 'object' ? fixture : { name: fixture };
    test(fixture.name, () => {
      expectFixture(fixture);
    });
  });
}

generateFixtureTests([
  'tui-i18n-api',
  { name: 'vue-instance-methods', filename: 'foo.vue' },
  { name: 'in-tui-core', filename: 'client/components/tui/src/js/foo.js' },
]);
