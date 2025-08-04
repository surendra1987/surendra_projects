// Shim for Jest

/* Switch eslint config environment to 'node' to prevent 'module' definition error */
/* eslint-env node */

const babelConfigs = require('./client/tooling/configs/babel');

module.exports = api => {
    const isTest = api.env('test');

    if (isTest) {
        return babelConfigs.test;
    }

    return {};
};
