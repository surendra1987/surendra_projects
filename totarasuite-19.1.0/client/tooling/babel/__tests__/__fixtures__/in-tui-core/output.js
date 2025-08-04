import { getString } from 'tui/i18n';
import { langString } from './i18n';

console.log("##str:get:success,core##");
console.log(langString.__p("##str:get:abc,def##", 'abc', 'def'));
