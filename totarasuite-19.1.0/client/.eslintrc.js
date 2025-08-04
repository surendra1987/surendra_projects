module.exports = {
  root: true,
  env: {
    browser: true,
    commonjs: true,
    node: true,
    es2020: true,
  },
  plugins: ['tui'],
  extends: [
    'eslint:recommended',
    'plugin:jest/recommended',
    'plugin:vue/vue3-recommended',
    // disable rules that would conflict with prettier
    'prettier',
    'prettier/vue',
  ],
  globals: {
    // tui global interface
    tui: true,
  },
  rules: {
    // we use console for error reporting
    'no-console': 'off',
    'vue/no-v-html': 'off',
    'vue/require-default-prop': 'off',
    'vue/html-self-closing': ['error', { html: { void: 'any' } }],
    'vue/no-reserved-component-names': 'off',
    'vue/multi-word-component-names': 'off',
    'vue/v-slot-style': 'off',
    'vue/no-template-shadow': 'off',
    'vue/one-component-per-file': 'off',
    'vue/order-in-components': 'warn',
    'vue/no-deprecated-model-definition': 'error',
    'jest/expect-expect': 'off',
    'tui/no-tui-internal': 'error',
    'tui/no-weka-standalone-usage-identifier': 'error',
    'tui/no-direct-testing-library-import': 'error',
    'tui/replaceable-string-references': 'error',
    'tui/replaceable-string-references-vue': 'error',
    'tui/no-v-model-without-prop-name-on-component': 'error',
    'tui/no-emit-input-without-update': 'error',
  },
  overrides: [
    {
      files: ['**/*.vue'],
      rules: {
        'vue/padding-lines-in-component-definition': [
          'warn',
          {
            betweenOptions: 'always',
            withinOption: 'ignore',
          },
        ],
      },
    },
  ],
};
