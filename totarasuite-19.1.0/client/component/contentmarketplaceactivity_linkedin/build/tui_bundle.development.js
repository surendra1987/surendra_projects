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

/***/ "./client/component/contentmarketplaceactivity_linkedin/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!*************************************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \*************************************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/side-panel/LinkedInActivityContentsTree\": \"./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue\",\n\t\"./components/side-panel/LinkedInActivityContentsTree.vue\": \"./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue\",\n\t\"./pages/ActivityView\": \"./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue\",\n\t\"./pages/ActivityView.vue\": \"./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/contentmarketplaceactivity_linkedin/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/_sync_^(?");

/***/ }),

/***/ "./server/lib/webapi/ajax/non_interactive_enrol_pending_approval_info.graphql":
/*!************************************************************************************!*\
  !*** ./server/lib/webapi/ajax/non_interactive_enrol_pending_approval_info.graphql ***!
  \************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"query\",\"name\":{\"kind\":\"Name\",\"value\":\"core_non_interactive_enrol_pending_approval_info\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"course_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"result\"},\"name\":{\"kind\":\"Name\",\"value\":\"core_non_interactive_enrol_pending_approval_info\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"course_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"course_id\"}}}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"button_name\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"pending\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"redirect_url\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"needs_create_new_application\"},\"arguments\":[],\"directives\":[]}]}}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/lib/webapi/ajax/non_interactive_enrol_pending_approval_info.graphql?");

/***/ }),

/***/ "./server/mod/contentmarketplace/contentmarketplaces/linkedin/webapi/ajax/linkedin_activity.graphql":
/*!**********************************************************************************************************!*\
  !*** ./server/mod/contentmarketplace/contentmarketplaces/linkedin/webapi/ajax/linkedin_activity.graphql ***!
  \**********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"query\",\"name\":{\"kind\":\"Name\",\"value\":\"contentmarketplaceactivity_linkedin_linkedin_activity\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"cm_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"instance\"},\"name\":{\"kind\":\"Name\",\"value\":\"contentmarketplaceactivity_linkedin_linkedin_activity\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"cm_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"cm_id\"}}}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"module\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"course_module\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"completion\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"completionenabled\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"completionstatus\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"rpl\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"progress\"},\"arguments\":[],\"directives\":[]}]}},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"course\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"fullname\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"image\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"url\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"course_format\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"has_course_view_page\"},\"arguments\":[],\"directives\":[]}]}},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"format\"},\"arguments\":[],\"directives\":[]}]}},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"name\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"intro\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"format\"},\"value\":{\"kind\":\"EnumValue\",\"value\":\"HTML\"}}],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"completion_condition\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"interactor\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"has_view_capability\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"can_enrol\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"can_launch\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"is_site_guest\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"is_enrolled\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"non_interactive_enrol_instance_enabled\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"supports_non_interactive_enrol\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"non_interactive_enrol_requires_approval\"},\"arguments\":[],\"directives\":[]}]}}]}},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"learning_object\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"asset_type\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"display_level\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"time_to_complete\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"last_updated_at\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"web_launch_url\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"sso_launch_url\"},\"arguments\":[],\"directives\":[]}]}}]}}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/mod/contentmarketplace/contentmarketplaces/linkedin/webapi/ajax/linkedin_activity.graphql?");

/***/ }),

/***/ "./server/mod/contentmarketplace/webapi/ajax/request_non_interactive_enrol_v2.graphql":
/*!********************************************************************************************!*\
  !*** ./server/mod/contentmarketplace/webapi/ajax/request_non_interactive_enrol_v2.graphql ***!
  \********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"mutation\",\"name\":{\"kind\":\"Name\",\"value\":\"mod_contentmarketplace_request_non_interactive_enrol_v2\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"cm_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"result\"},\"name\":{\"kind\":\"Name\",\"value\":\"mod_contentmarketplace_request_non_interactive_enrol_v2\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"cm_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"cm_id\"}}}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"success\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"redirect_url\"},\"arguments\":[],\"directives\":[]}]}}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/mod/contentmarketplace/webapi/ajax/request_non_interactive_enrol_v2.graphql?");

/***/ }),

/***/ "./server/mod/contentmarketplace/webapi/ajax/set_self_completion.graphql":
/*!*******************************************************************************!*\
  !*** ./server/mod/contentmarketplace/webapi/ajax/set_self_completion.graphql ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"mutation\",\"name\":{\"kind\":\"Name\",\"value\":\"mod_contentmarketplace_set_self_completion\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"cm_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]},{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"status\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"param_boolean\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"result\"},\"name\":{\"kind\":\"Name\",\"value\":\"mod_contentmarketplace_set_self_completion\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"cm_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"cm_id\"}}},{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"status\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"status\"}}}],\"directives\":[]}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/mod/contentmarketplace/webapi/ajax/set_self_completion.graphql?");

/***/ }),

/***/ "./client/component/contentmarketplaceactivity_linkedin/tui.json":
/*!***********************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/tui.json ***!
  \***********************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"contentmarketplaceactivity_linkedin\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"contentmarketplaceactivity_linkedin\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"contentmarketplaceactivity_linkedin\")\ntui._bundle.addModulesFromContext(\"contentmarketplaceactivity_linkedin\", __webpack_require__(\"./client/component/contentmarketplaceactivity_linkedin/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/tui.json?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=script&lang=js":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=script&lang=js ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_tree_Tree__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/tree/Tree */ \"tui/components/tree/Tree\");\n/* harmony import */ var tui_components_tree_Tree__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_tree_Tree__WEBPACK_IMPORTED_MODULE_0__);\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    Tree: (tui_components_tree_Tree__WEBPACK_IMPORTED_MODULE_0___default())\n  },\n  props: {\n    /**\n     * Tree data for contents\n     */\n    treeData: {\n      type: Array,\n      required: true\n    },\n    /**\n     * List of open branches\n     */\n    value: {\n      type: Array,\n      required: true\n    }\n  },\n  emits: ['input', 'update:value'],\n  data() {\n    return {\n      open: this.value\n    };\n  }\n});\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=script&lang=js":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=script&lang=js ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_card_ActionCard__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/card/ActionCard */ \"tui/components/card/ActionCard\");\n/* harmony import */ var tui_components_card_ActionCard__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_card_ActionCard__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_settings_navigation_SettingsNavigation__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/settings_navigation/SettingsNavigation */ \"tui/components/settings_navigation/SettingsNavigation\");\n/* harmony import */ var tui_components_settings_navigation_SettingsNavigation__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_settings_navigation_SettingsNavigation__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tui/components/buttons/Button */ \"tui/components/buttons/Button\");\n/* harmony import */ var tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var mod_contentmarketplace_components_layouts_LayoutBannerTwoColumn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! mod_contentmarketplace/components/layouts/LayoutBannerTwoColumn */ \"mod_contentmarketplace/components/layouts/LayoutBannerTwoColumn\");\n/* harmony import */ var mod_contentmarketplace_components_layouts_LayoutBannerTwoColumn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(mod_contentmarketplace_components_layouts_LayoutBannerTwoColumn__WEBPACK_IMPORTED_MODULE_3__);\n/* harmony import */ var tui_components_lozenge_Lozenge__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! tui/components/lozenge/Lozenge */ \"tui/components/lozenge/Lozenge\");\n/* harmony import */ var tui_components_lozenge_Lozenge__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(tui_components_lozenge_Lozenge__WEBPACK_IMPORTED_MODULE_4__);\n/* harmony import */ var tui_components_notifications_NotificationBanner__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! tui/components/notifications/NotificationBanner */ \"tui/components/notifications/NotificationBanner\");\n/* harmony import */ var tui_components_notifications_NotificationBanner__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(tui_components_notifications_NotificationBanner__WEBPACK_IMPORTED_MODULE_5__);\n/* harmony import */ var tui_components_layouts_PageBackLink__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! tui/components/layouts/PageBackLink */ \"tui/components/layouts/PageBackLink\");\n/* harmony import */ var tui_components_layouts_PageBackLink__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(tui_components_layouts_PageBackLink__WEBPACK_IMPORTED_MODULE_6__);\n/* harmony import */ var tui_components_progress_Progress__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! tui/components/progress/Progress */ \"tui/components/progress/Progress\");\n/* harmony import */ var tui_components_progress_Progress__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(tui_components_progress_Progress__WEBPACK_IMPORTED_MODULE_7__);\n/* harmony import */ var tui_components_toggle_ToggleSwitch__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! tui/components/toggle/ToggleSwitch */ \"tui/components/toggle/ToggleSwitch\");\n/* harmony import */ var tui_components_toggle_ToggleSwitch__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(tui_components_toggle_ToggleSwitch__WEBPACK_IMPORTED_MODULE_8__);\n/* harmony import */ var tui_notifications__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! tui/notifications */ \"tui/notifications\");\n/* harmony import */ var tui_notifications__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(tui_notifications__WEBPACK_IMPORTED_MODULE_9__);\n/* harmony import */ var mod_contentmarketplace_constants__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! mod_contentmarketplace/constants */ \"mod_contentmarketplace/constants\");\n/* harmony import */ var mod_contentmarketplace_constants__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(mod_contentmarketplace_constants__WEBPACK_IMPORTED_MODULE_10__);\n/* harmony import */ var contentmarketplaceactivity_linkedin_graphql_linkedin_activity__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! contentmarketplaceactivity_linkedin/graphql/linkedin_activity */ \"./server/mod/contentmarketplace/contentmarketplaces/linkedin/webapi/ajax/linkedin_activity.graphql\");\n/* harmony import */ var mod_contentmarketplace_graphql_set_self_completion__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! mod_contentmarketplace/graphql/set_self_completion */ \"./server/mod/contentmarketplace/webapi/ajax/set_self_completion.graphql\");\n/* harmony import */ var mod_contentmarketplace_graphql_request_non_interactive_enrol_v2__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! mod_contentmarketplace/graphql/request_non_interactive_enrol_v2 */ \"./server/mod/contentmarketplace/webapi/ajax/request_non_interactive_enrol_v2.graphql\");\n/* harmony import */ var core_graphql_non_interactive_enrol_pending_approval_info__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! core/graphql/non_interactive_enrol_pending_approval_info */ \"./server/lib/webapi/ajax/non_interactive_enrol_pending_approval_info.graphql\");\n\n\n\n\n\n\n\n\n\n// Utils\n\n\n\n// GraphQL\n\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    ActionCard: (tui_components_card_ActionCard__WEBPACK_IMPORTED_MODULE_0___default()),\n    AdminMenu: (tui_components_settings_navigation_SettingsNavigation__WEBPACK_IMPORTED_MODULE_1___default()),\n    Button: (tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_2___default()),\n    Layout: (mod_contentmarketplace_components_layouts_LayoutBannerTwoColumn__WEBPACK_IMPORTED_MODULE_3___default()),\n    Lozenge: (tui_components_lozenge_Lozenge__WEBPACK_IMPORTED_MODULE_4___default()),\n    NotificationBanner: (tui_components_notifications_NotificationBanner__WEBPACK_IMPORTED_MODULE_5___default()),\n    PageBackLink: (tui_components_layouts_PageBackLink__WEBPACK_IMPORTED_MODULE_6___default()),\n    Progress: (tui_components_progress_Progress__WEBPACK_IMPORTED_MODULE_7___default()),\n    ToggleSwitch: (tui_components_toggle_ToggleSwitch__WEBPACK_IMPORTED_MODULE_8___default())\n  },\n  props: {\n    /**\n     * The course's module id, not the content marketplace id.\n     */\n    cmId: {\n      type: Number,\n      required: true\n    },\n    /**\n     * Check it has notification or not.\n     */\n    hasNotification: {\n      type: Boolean,\n      required: true\n    }\n  },\n  data() {\n    return {\n      setCompletion: false,\n      // We need to store the initial states of the query data state here to ensure\n      // Vue watches them and updates the DOM accordingly when the query gets updated.\n      interactor: {\n        can_enrol: false,\n        can_launch: false,\n        has_view_capability: false,\n        is_enrolled: false,\n        is_site_guest: false,\n        non_interactive_enrol_instance_enabled: false,\n        supports_non_interactive_enrol: false,\n        non_interactive_enrol_requires_approval: false\n      },\n      module: {\n        completionstatus: mod_contentmarketplace_constants__WEBPACK_IMPORTED_MODULE_10__.COMPLETION_STATUS_UNKNOWN,\n        rpl: false\n      },\n      nonInteractiveEnrolPendingApprovalInfo: {}\n    };\n  },\n  computed: {\n    isProgressBarEnabled() {\n      return this.completionMarketplace && !this.selfCompletionEnabled;\n    },\n    canEnrol() {\n      return this.interactor.can_enrol && !this.interactor.is_site_guest && this.interactor.non_interactive_enrol_instance_enabled;\n    },\n    canLaunch() {\n      if (this.interactor.can_enrol) {\n        return false;\n      }\n      return this.interactor.can_launch || this.interactor.is_site_guest;\n    },\n    displayEnrolText() {\n      if (this.nonInteractiveEnrolPendingApprovalInfo.pending) {\n        return this.nonInteractiveEnrolPendingApprovalInfo.button_name;\n      }\n      return this.interactor.non_interactive_enrol_requires_approval ? \"##str:get:requestapproval,enrol_self##\" : \"##str:get:enrol,core_enrol##\";\n    },\n    enrolBannerText() {\n      if (this.interactor.has_view_capability) {\n        return this.interactor.non_interactive_enrol_instance_enabled ? \"##str:get:viewing_as_enrollable_admin,mod_contentmarketplace##\" : \"##str:get:viewing_as_enrollable_admin_self_enrol_disabled,mod_contentmarketplace##\";\n      }\n      return this.canEnrol ? \"##str:get:viewing_as_enrollable_guest,mod_contentmarketplace##\" : \"##str:get:viewing_as_guest,mod_contentmarketplace##\";\n    },\n    isActivityCompleted() {\n      return this.module.completionstatus !== mod_contentmarketplace_constants__WEBPACK_IMPORTED_MODULE_10__.COMPLETION_STATUS_UNKNOWN && this.module.completionstatus !== mod_contentmarketplace_constants__WEBPACK_IMPORTED_MODULE_10__.COMPLETION_STATUS_INCOMPLETE;\n    },\n    completionEnabled() {\n      return this.module.completion !== mod_contentmarketplace_constants__WEBPACK_IMPORTED_MODULE_10__.COMPLETION_TRACKING_NONE;\n    },\n    selfCompletionEnabled() {\n      return this.module.completion === mod_contentmarketplace_constants__WEBPACK_IMPORTED_MODULE_10__.COMPLETION_TRACKING_MANUAL;\n    },\n    completionMarketplace() {\n      return this.activity.completion_condition === mod_contentmarketplace_constants__WEBPACK_IMPORTED_MODULE_10__.COMPLETION_CONDITION_CONTENT_MARKETPLACE;\n    },\n    getProgress() {\n      if (this.module.progress !== 100 && this.isActivityCompleted) {\n        return 100;\n      }\n      return this.module.progress;\n    },\n    isPathwayCourse() {\n      return this.course ? this.course.format === 'pathway' : false;\n    }\n  },\n  mounted() {\n    if (this.hasNotification) {\n      (0,tui_notifications__WEBPACK_IMPORTED_MODULE_9__.notify)({\n        message: \"##str:get:enrol_success_message,mod_contentmarketplace##\",\n        type: 'success'\n      });\n    }\n  },\n  apollo: {\n    activity: {\n      query: contentmarketplaceactivity_linkedin_graphql_linkedin_activity__WEBPACK_IMPORTED_MODULE_11__[\"default\"],\n      variables() {\n        return {\n          cm_id: this.cmId\n        };\n      },\n      update({\n        instance: data\n      }) {\n        const activity = data.module;\n        this.course = activity.course;\n        this.interactor = activity.interactor;\n        this.learningObject = data.learning_object;\n        this.module = activity.course_module;\n        this.setCompletion = this.isActivityCompleted;\n        return activity;\n      }\n    },\n    nonInteractiveEnrolPendingApprovalInfo: {\n      query: core_graphql_non_interactive_enrol_pending_approval_info__WEBPACK_IMPORTED_MODULE_14__[\"default\"],\n      variables() {\n        return {\n          course_id: this.course.id\n        };\n      },\n      update({\n        result: data\n      }) {\n        return data;\n      },\n      skip() {\n        return !this.interactor.non_interactive_enrol_requires_approval;\n      }\n    }\n  },\n  methods: {\n    async launch() {\n      const url = this.learningObject.sso_launch_url ? this.learningObject.sso_launch_url : this.learningObject.web_launch_url;\n      window.open(url, 'linkedIn_course_window');\n    },\n    async setCompletionHandler() {\n      await this.$apollo.mutate({\n        mutation: mod_contentmarketplace_graphql_set_self_completion__WEBPACK_IMPORTED_MODULE_12__[\"default\"],\n        refetchAll: false,\n        variables: {\n          cm_id: this.cmId,\n          status: this.setCompletion\n        }\n      });\n      this.$apollo.queries.activity.refetch();\n    },\n    async enrol() {\n      const {\n        pending,\n        needs_create_new_application,\n        redirect_url\n      } = this.nonInteractiveEnrolPendingApprovalInfo;\n      if (pending && !needs_create_new_application) {\n        window.location.href = redirect_url;\n        return;\n      }\n      if (this.interactor.supports_non_interactive_enrol) {\n        await this.nonInteractiveEnrol();\n      } else {\n        window.location.href = this.$url('/enrol/index.php', {\n          id: this.course.id\n        });\n      }\n    },\n    async nonInteractiveEnrol() {\n      let {\n        data: {\n          result\n        }\n      } = await this.$apollo.mutate({\n        mutation: mod_contentmarketplace_graphql_request_non_interactive_enrol_v2__WEBPACK_IMPORTED_MODULE_13__[\"default\"],\n        variables: {\n          cm_id: this.cmId\n        },\n        refetchAll: true\n      });\n      if (result.redirect_url) {\n        window.location.href = result.redirect_url;\n        return;\n      }\n      if (result.success) {\n        (0,tui_notifications__WEBPACK_IMPORTED_MODULE_9__.notify)({\n          message: \"##str:get:enrol_success_message,mod_contentmarketplace##\",\n          type: 'success'\n        });\n      }\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=template&id=41b563fa":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=template&id=41b563fa ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-linkedinActivityContentTree__contents\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_Tree = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Tree\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_Tree, {\n    value: $data.open,\n    \"onUpdate:value\": _cache[0] || (_cache[0] = $event => $data.open = $event),\n    class: \"tui-linkedinActivityContentTree\",\n    \"tree-data\": $props.treeData,\n    onInput: _cache[1] || (_cache[1] = e => {\n      _ctx.$emit('input', e);\n      _ctx.$emit('update:value', e);\n    })\n  }, {\n    \"custom-label\": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n      label\n    }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(label), 1 /* TEXT */)]),\n    content: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n      content\n    }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_1, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(content.items, (item, i) => {\n      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", {\n        key: i,\n        class: \"tui-linkedinActivityContentTree__contents-item\"\n      }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(item), 1 /* TEXT */);\n    }), 128 /* KEYED_FRAGMENT */))])]),\n    _: 1 /* STABLE */\n  }, 8 /* PROPS */, [\"value\", \"tree-data\"]);\n}\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=template&id=1c8c7495":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=template&id=1c8c7495 ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-linkedinActivity__admin\"\n};\nconst _hoisted_2 = {\n  class: \"tui-linkedinActivity__body\"\n};\nconst _hoisted_3 = {\n  class: \"tui-linkedinActivity__status-completion\"\n};\nconst _hoisted_4 = {\n  class: \"tui-linkedinActivity__details\"\n};\nconst _hoisted_5 = {\n  class: \"tui-linkedinActivity__details-header\"\n};\nconst _hoisted_6 = {\n  class: \"tui-linkedinActivity__details-content\"\n};\nconst _hoisted_7 = {\n  class: \"tui-linkedinActivity__details-bar\"\n};\nconst _hoisted_8 = {\n  class: \"sr-only\"\n};\nconst _hoisted_9 = {\n  class: \"sr-only\"\n};\nconst _hoisted_10 = [\"innerHTML\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_PageBackLink = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"PageBackLink\");\n  const _component_AdminMenu = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"AdminMenu\");\n  const _component_Button = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Button\");\n  const _component_ActionCard = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"ActionCard\");\n  const _component_NotificationBanner = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"NotificationBanner\");\n  const _component_Lozenge = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Lozenge\");\n  const _component_Progress = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Progress\");\n  const _component_ToggleSwitch = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"ToggleSwitch\");\n  const _component_Layout = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Layout\");\n  return _ctx.activity ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_Layout, {\n    key: 0,\n    class: \"tui-linkedinActivity\",\n    \"banner-image-url\": _ctx.course.image,\n    \"loading-full-page\": _ctx.$apollo.loading,\n    title: _ctx.activity.name\n  }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createSlots)({\n    \"main-content\": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Button, {\n      disabled: !$options.canLaunch,\n      styleclass: {\n        primary: 'true'\n      },\n      text: \"##str:get:launch,mod_contentmarketplace##\",\n      onClick: $options.launch\n    }, null, 8 /* PROPS */, [\"disabled\", \"text\", \"onClick\"]), _cache[1] || (_cache[1] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"hr\", {\n      class: \"tui-linkedinActivity__divider\"\n    }, null, -1 /* HOISTED */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Current status and self completion \"), $options.completionEnabled && $data.interactor.is_enrolled ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", {\n      key: 0,\n      class: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)([\"tui-linkedinActivity__status\", {\n        'tui-linkedinActivity__progressContainer': $options.isProgressBarEnabled\n      }])\n    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Lozenge, {\n      text: $options.isActivityCompleted ? \"##str:get:activity_status_completed,mod_contentmarketplace##\" : \"##str:get:activity_status_not_completed,mod_contentmarketplace##\"\n    }, null, 8 /* PROPS */, [\"text\"])]), $options.isProgressBarEnabled ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_Progress, {\n      key: 0,\n      value: $options.getProgress,\n      class: \"tui-linkedinActivity__status-progress\"\n    }, null, 8 /* PROPS */, [\"value\"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Display the completion toggle if there is self-completion enabled and user hasn't completed via RPL. \"), $options.selfCompletionEnabled && !$data.module.rpl ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_ToggleSwitch, {\n      key: 1,\n      value: $data.setCompletion,\n      \"onUpdate:value\": _cache[0] || (_cache[0] = $event => $data.setCompletion = $event),\n      class: \"tui-linkedinActivity__status-toggle\",\n      text: \"##str:get:activity_set_self_completion,mod_contentmarketplace##\",\n      \"toggle-first\": true,\n      onInput: $options.setCompletionHandler\n    }, null, 8 /* PROPS */, [\"value\", \"text\", \"onInput\"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true)], 2 /* CLASS */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_4, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"h2\", _hoisted_5, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:course_details,mod_contentmarketplace##\"), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_6, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_7, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Course completion time \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_8, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:a11y_activity_time_to_complete,mod_contentmarketplace##\"), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(\" \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.learningObject.time_to_complete), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Course level (Beginner, intermediate, advanced) \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_9, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:a11y_activity_difficulty,mod_contentmarketplace##\"), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(\" \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.learningObject.display_level), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Last updated  \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$str.__r(\"##str:get:updated_at,mod_contentmarketplace##\", _ctx.learningObject.last_updated_at)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", {\n      class: \"tui-linkedinActivity__details-desc\",\n      innerHTML: _ctx.activity.intro\n    }, null, 8 /* PROPS */, _hoisted_10)])])]),\n    _: 2 /* DYNAMIC */\n  }, [_ctx.course.course_format.has_course_view_page && !$options.isPathwayCourse ? {\n    name: \"content-nav\",\n    fn: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_PageBackLink, {\n      link: _ctx.course.url,\n      text: _ctx.course.fullname\n    }, null, 8 /* PROPS */, [\"link\", \"text\"])]),\n    key: \"0\"\n  } : undefined, !$options.isPathwayCourse ? {\n    name: \"banner-content\",\n    fn: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n      stacked\n    }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_AdminMenu, {\n      \"stacked-layout\": stacked\n    }, null, 8 /* PROPS */, [\"stacked-layout\"])])]),\n    key: \"1\"\n  } : undefined, !$data.interactor.is_enrolled && !$options.isPathwayCourse ? {\n    name: \"feedback-banner\",\n    fn: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_NotificationBanner, {\n      type: \"info\"\n    }, {\n      body: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ActionCard, {\n        \"no-border\": true\n      }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createSlots)({\n        \"card-body\": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.enrolBannerText), 1 /* TEXT */)]),\n        _: 2 /* DYNAMIC */\n      }, [$options.canEnrol ? {\n        name: \"card-action\",\n        fn: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"\\n                Using title for button, to allow Selenium finding this button.\\n                This is happening for admin user, because admin user can see more\\n                than one enrol button.\\n              \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Button, {\n          styleclass: {\n            primary: 'true'\n          },\n          title: _ctx.$str.__r(\"##str:get:enrol_to_course,mod_contentmarketplace##\", _ctx.course.fullname),\n          text: $options.displayEnrolText,\n          onClick: $options.enrol\n        }, null, 8 /* PROPS */, [\"title\", \"text\", \"onClick\"])]),\n        key: \"0\"\n      } : undefined]), 1024 /* DYNAMIC_SLOTS */)]),\n      _: 1 /* STABLE */\n    })]),\n    key: \"2\"\n  } : undefined]), 1032 /* PROPS, DYNAMIC_SLOTS */, [\"banner-image-url\", \"loading-full-page\", \"title\"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true);\n}\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=template&id=41b563fa":
/*!*******************************************************************************************************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=template&id=41b563fa ***!
  \*******************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_875_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LinkedInActivityContentsTree_vue_vue_type_template_id_41b563fa__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_875_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LinkedInActivityContentsTree_vue_vue_type_template_id_41b563fa__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./LinkedInActivityContentsTree.vue?vue&type=template&id=41b563fa */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=template&id=41b563fa\");\n\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?");

/***/ }),

/***/ "./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=template&id=1c8c7495":
/*!***********************************************************************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=template&id=1c8c7495 ***!
  \***********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_875_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_ActivityView_vue_vue_type_template_id_1c8c7495__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_875_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_ActivityView_vue_vue_type_template_id_1c8c7495__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./ActivityView.vue?vue&type=template&id=1c8c7495 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=template&id=1c8c7495\");\n\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=style&index=0&id=41b563fa&lang=scss":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=style&index=0&id=41b563fa&lang=scss ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=style&index=0&id=1c8c7495&lang=scss":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=style&index=0&id=1c8c7495&lang=scss ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue":
/*!*************************************************************************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue ***!
  \*************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _LinkedInActivityContentsTree_vue_vue_type_template_id_41b563fa__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LinkedInActivityContentsTree.vue?vue&type=template&id=41b563fa */ \"./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=template&id=41b563fa\");\n/* harmony import */ var _LinkedInActivityContentsTree_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LinkedInActivityContentsTree.vue?vue&type=script&lang=js */ \"./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=script&lang=js\");\n/* harmony import */ var _LinkedInActivityContentsTree_vue_vue_type_style_index_0_id_41b563fa_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./LinkedInActivityContentsTree.vue?vue&type=style&index=0&id=41b563fa&lang=scss */ \"./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=style&index=0&id=41b563fa&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_LinkedInActivityContentsTree_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_LinkedInActivityContentsTree_vue_vue_type_template_id_41b563fa__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?");

/***/ }),

/***/ "./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue":
/*!*****************************************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _ActivityView_vue_vue_type_template_id_1c8c7495__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ActivityView.vue?vue&type=template&id=1c8c7495 */ \"./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=template&id=1c8c7495\");\n/* harmony import */ var _ActivityView_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActivityView.vue?vue&type=script&lang=js */ \"./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=script&lang=js\");\n/* harmony import */ var _ActivityView_vue_vue_type_style_index_0_id_1c8c7495_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ActivityView.vue?vue&type=style&index=0&id=1c8c7495&lang=scss */ \"./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=style&index=0&id=1c8c7495&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_ActivityView_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_ActivityView_vue_vue_type_template_id_1c8c7495__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?");

/***/ }),

/***/ "./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_875_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LinkedInActivityContentsTree_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_875_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LinkedInActivityContentsTree_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./LinkedInActivityContentsTree.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?");

/***/ }),

/***/ "./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_875_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_ActivityView_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_875_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_ActivityView_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./ActivityView.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-875.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?");

/***/ }),

/***/ "./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=style&index=0&id=41b563fa&lang=scss":
/*!**********************************************************************************************************************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=style&index=0&id=41b563fa&lang=scss ***!
  \**********************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LinkedInActivityContentsTree_vue_vue_type_style_index_0_id_41b563fa_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LinkedInActivityContentsTree_vue_vue_type_style_index_0_id_41b563fa_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use[0]!../../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use[1]!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./LinkedInActivityContentsTree.vue?vue&type=style&index=0&id=41b563fa&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?vue&type=style&index=0&id=41b563fa&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LinkedInActivityContentsTree_vue_vue_type_style_index_0_id_41b563fa_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LinkedInActivityContentsTree_vue_vue_type_style_index_0_id_41b563fa_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LinkedInActivityContentsTree_vue_vue_type_style_index_0_id_41b563fa_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LinkedInActivityContentsTree_vue_vue_type_style_index_0_id_41b563fa_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/components/side-panel/LinkedInActivityContentsTree.vue?");

/***/ }),

/***/ "./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=style&index=0&id=1c8c7495&lang=scss":
/*!**************************************************************************************************************************************!*\
  !*** ./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=style&index=0&id=1c8c7495&lang=scss ***!
  \**************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityView_vue_vue_type_style_index_0_id_1c8c7495_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityView_vue_vue_type_style_index_0_id_1c8c7495_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use[0]!../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use[1]!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActivityView.vue?vue&type=style&index=0&id=1c8c7495&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-878.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-878.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-878.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?vue&type=style&index=0&id=1c8c7495&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityView_vue_vue_type_style_index_0_id_1c8c7495_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityView_vue_vue_type_style_index_0_id_1c8c7495_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityView_vue_vue_type_style_index_0_id_1c8c7495_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_878_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_878_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_878_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityView_vue_vue_type_style_index_0_id_1c8c7495_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/contentmarketplaceactivity_linkedin/src/pages/ActivityView.vue?");

/***/ }),

/***/ "mod_contentmarketplace/components/layouts/LayoutBannerTwoColumn":
/*!***************************************************************************************************!*\
  !*** external "tui.require(\"mod_contentmarketplace/components/layouts/LayoutBannerTwoColumn\")" ***!
  \***************************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("mod_contentmarketplace/components/layouts/LayoutBannerTwoColumn");

/***/ }),

/***/ "mod_contentmarketplace/constants":
/*!********************************************************************!*\
  !*** external "tui.require(\"mod_contentmarketplace/constants\")" ***!
  \********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("mod_contentmarketplace/constants");

/***/ }),

/***/ "tui/components/buttons/Button":
/*!*****************************************************************!*\
  !*** external "tui.require(\"tui/components/buttons/Button\")" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/buttons/Button");

/***/ }),

/***/ "tui/components/card/ActionCard":
/*!******************************************************************!*\
  !*** external "tui.require(\"tui/components/card/ActionCard\")" ***!
  \******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/card/ActionCard");

/***/ }),

/***/ "tui/components/layouts/PageBackLink":
/*!***********************************************************************!*\
  !*** external "tui.require(\"tui/components/layouts/PageBackLink\")" ***!
  \***********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/layouts/PageBackLink");

/***/ }),

/***/ "tui/components/lozenge/Lozenge":
/*!******************************************************************!*\
  !*** external "tui.require(\"tui/components/lozenge/Lozenge\")" ***!
  \******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/lozenge/Lozenge");

/***/ }),

/***/ "tui/components/notifications/NotificationBanner":
/*!***********************************************************************************!*\
  !*** external "tui.require(\"tui/components/notifications/NotificationBanner\")" ***!
  \***********************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/notifications/NotificationBanner");

/***/ }),

/***/ "tui/components/progress/Progress":
/*!********************************************************************!*\
  !*** external "tui.require(\"tui/components/progress/Progress\")" ***!
  \********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/progress/Progress");

/***/ }),

/***/ "tui/components/settings_navigation/SettingsNavigation":
/*!*****************************************************************************************!*\
  !*** external "tui.require(\"tui/components/settings_navigation/SettingsNavigation\")" ***!
  \*****************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/settings_navigation/SettingsNavigation");

/***/ }),

/***/ "tui/components/toggle/ToggleSwitch":
/*!**********************************************************************!*\
  !*** external "tui.require(\"tui/components/toggle/ToggleSwitch\")" ***!
  \**********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/toggle/ToggleSwitch");

/***/ }),

/***/ "tui/components/tree/Tree":
/*!************************************************************!*\
  !*** external "tui.require(\"tui/components/tree/Tree\")" ***!
  \************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/tree/Tree");

/***/ }),

/***/ "tui/notifications":
/*!*****************************************************!*\
  !*** external "tui.require(\"tui/notifications\")" ***!
  \*****************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/notifications");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/contentmarketplaceactivity_linkedin/tui.json");
/******/ 	
/******/ })()
;