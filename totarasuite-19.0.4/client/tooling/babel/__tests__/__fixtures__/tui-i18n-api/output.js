import { getString, getString as stringyMcStringface, hasString, langString } from 'tui/i18n';
import * as i18nNS from 'tui/i18n';

// plain call
console.log("##str:get:success,core##");
console.log("##str:get:success,core##");
console.log("##str:get:success,core##");

// invalid calls
console.log(getString('success', 9));
console.log(getString({ foo: bar }, "hi"));

// dynamic
console.log(someVar ? "##str:get:success,core##" : "##str:get:failure,core##");

// arguments
console.log(getString.__r("##str:get:hello,core##", { name: 'Jim' }));
console.log(getString.__r(someVar ? "##str:get:hello,core##" : "##str:get:goodbye,core##", { name: 'Fred' }));

// hasString
console.log("##str:has:success,core##");

// namespace import
console.log("##str:get:success,core##");

// langString
console.log(langString.__p("##str:get:abc,def##", 'abc', 'def'));
console.log(langString.__p("##str:get:abc,def##", 'abc', 'def', 'hji'));
console.log(i18nNS.langString.__p("##str:get:abc,def##", 'abc', 'def'));
