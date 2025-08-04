import { getString, getString as stringyMcStringface, hasString, langString } from 'tui/i18n';
import * as i18nNS from 'tui/i18n';

// plain call
console.log(getString('success', 'core'));
console.log(stringyMcStringface('success', 'core'));
console.log(getString('success'));

// invalid calls
console.log(getString('success', 9));
console.log(getString({ foo: bar }, "hi"));

// dynamic
console.log(getString(someVar ? 'success' : 'failure', 'core'));

// arguments
console.log(getString('hello', 'core', { name: 'Jim' }));
console.log(getString(someVar ? 'hello' : 'goodbye', 'core', { name: 'Fred' }));

// hasString
console.log(hasString('success', 'core'));

// namespace import
console.log(i18nNS.getString('success', 'core'));

// langString
console.log(langString('abc', 'def'));
console.log(langString('abc', 'def', 'hji'));
console.log(i18nNS.langString('abc', 'def'));
