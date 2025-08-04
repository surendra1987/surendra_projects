/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./client/component/criteria_onactivate/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!*********************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/criteria_onactivate/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \*********************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/achievements/AchievementDisplay\": \"./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue\",\n\t\"./components/achievements/AchievementDisplay.vue\": \"./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/criteria_onactivate/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/criteria_onactivate/src/_sync_^(?");

/***/ }),

/***/ "./client/component/criteria_onactivate/tui.json":
/*!*******************************************************!*\
  !*** ./client/component/criteria_onactivate/tui.json ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"criteria_onactivate\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"criteria_onactivate\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"criteria_onactivate\")\ntui._bundle.addModulesFromContext(\"criteria_onactivate\", __webpack_require__(\"./client/component/criteria_onactivate/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/criteria_onactivate/tui.json?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var totara_competency_components_achievements_AchievementLayout__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! totara_competency/components/achievements/AchievementLayout */ \"totara_competency/components/achievements/AchievementLayout\");\n/* harmony import */ var totara_competency_components_achievements_AchievementLayout__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(totara_competency_components_achievements_AchievementLayout__WEBPACK_IMPORTED_MODULE_0__);\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    AchievementLayout: (totara_competency_components_achievements_AchievementLayout__WEBPACK_IMPORTED_MODULE_0___default())\n  },\n  props: {\n    assignmentId: {\n      required: true,\n      type: Number\n    },\n    dateAchieved: {\n      type: String\n    },\n    instanceId: {\n      required: true,\n      type: Number\n    },\n    userId: {\n      required: true,\n      type: Number\n    }\n  },\n  emits: ['loaded'],\n  mounted() {\n    this.$emit('loaded');\n  }\n});\n\n//# sourceURL=webpack:///./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=053009f6":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=053009f6 ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-criteriaOnActiveAchievement\"\n};\nconst _hoisted_2 = {\n  class: \"tui-criteriaOnActiveAchievement__title\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_AchievementLayout = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"AchievementLayout\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_AchievementLayout, null, {\n    left: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"h4\", _hoisted_2, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:achievement_display,criteria_onactivate##\"), 1 /* TEXT */)]),\n    right: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$str.__r(\"##str:get:achieved_on,totara_competency##\", {\n      date: $props.dateAchieved\n    })), 1 /* TEXT */)]),\n    _: 1 /* STABLE */\n  })]);\n}\n\n//# sourceURL=webpack:///./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=053009f6":
/*!*******************************************************************************************************************************!*\
  !*** ./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=053009f6 ***!
  \*******************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_965_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_AchievementDisplay_vue_vue_type_template_id_053009f6__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_965_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_AchievementDisplay_vue_vue_type_template_id_053009f6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./AchievementDisplay.vue?vue&type=template&id=053009f6 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=053009f6\");\n\n\n//# sourceURL=webpack:///./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-968.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-968.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-968.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=053009f6&lang=scss":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-968.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-968.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-968.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=053009f6&lang=scss ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-968.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-968.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-968.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue":
/*!*************************************************************************************************!*\
  !*** ./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue ***!
  \*************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _AchievementDisplay_vue_vue_type_template_id_053009f6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AchievementDisplay.vue?vue&type=template&id=053009f6 */ \"./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=053009f6\");\n/* harmony import */ var _AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AchievementDisplay.vue?vue&type=script&lang=js */ \"./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js\");\n/* harmony import */ var _AchievementDisplay_vue_vue_type_style_index_0_id_053009f6_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AchievementDisplay.vue?vue&type=style&index=0&id=053009f6&lang=scss */ \"./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=053009f6&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_AchievementDisplay_vue_vue_type_template_id_053009f6__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************************!*\
  !*** ./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_965_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_965_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./AchievementDisplay.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-965.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=053009f6&lang=scss":
/*!**********************************************************************************************************************************************!*\
  !*** ./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=053009f6&lang=scss ***!
  \**********************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_968_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_968_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_968_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_053009f6_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_968_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_968_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_968_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_053009f6_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-968.use[0]!../../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-968.use[1]!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-968.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AchievementDisplay.vue?vue&type=style&index=0&id=053009f6&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-968.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-968.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-968.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=053009f6&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_968_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_968_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_968_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_053009f6_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_968_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_968_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_968_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_053009f6_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_968_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_968_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_968_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_053009f6_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_968_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_968_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_968_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_053009f6_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/criteria_onactivate/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "totara_competency/components/achievements/AchievementLayout":
/*!***********************************************************************************************!*\
  !*** external "tui.require(\"totara_competency/components/achievements/AchievementLayout\")" ***!
  \***********************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("totara_competency/components/achievements/AchievementLayout");

/***/ }),

/***/ "vue":
/*!***************************************!*\
  !*** external "tui.require(\"vue\")" ***!
  \***************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("vue");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/criteria_onactivate/tui.json");
/******/ 	
/******/ })()
;