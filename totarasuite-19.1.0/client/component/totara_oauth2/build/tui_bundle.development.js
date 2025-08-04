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

/***/ "./client/component/totara_oauth2/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!***************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \***************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/Oauth2ProviderContent\": \"./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue\",\n\t\"./components/Oauth2ProviderContent.vue\": \"./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue\",\n\t\"./components/action/Oauth2ProviderAction\": \"./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue\",\n\t\"./components/action/Oauth2ProviderAction.vue\": \"./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue\",\n\t\"./components/modal/Oauth2ProviderModal\": \"./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue\",\n\t\"./components/modal/Oauth2ProviderModal.vue\": \"./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue\",\n\t\"./pages/Oauth2Provider\": \"./client/component/totara_oauth2/src/pages/Oauth2Provider.vue\",\n\t\"./pages/Oauth2Provider.vue\": \"./client/component/totara_oauth2/src/pages/Oauth2Provider.vue\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/totara_oauth2/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/_sync_^(?");

/***/ }),

/***/ "./server/totara/oauth2/webapi/ajax/client_providers.graphql":
/*!*******************************************************************!*\
  !*** ./server/totara/oauth2/webapi/ajax/client_providers.graphql ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"query\",\"name\":{\"kind\":\"Name\",\"value\":\"totara_oauth2_client_providers\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"input\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"totara_oauth2_client_providers_input\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"providers\"},\"name\":{\"kind\":\"Name\",\"value\":\"totara_oauth2_client_providers\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"input\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"input\"}}}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"items\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"client_id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"client_secret\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"name\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"format\"},\"value\":{\"kind\":\"EnumValue\",\"value\":\"PLAIN\"}}],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"description\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"format\"},\"value\":{\"kind\":\"EnumValue\",\"value\":\"HTML\"}}],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"scope\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"detail_scope\"},\"arguments\":[],\"directives\":[]}]}}]}}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/totara/oauth2/webapi/ajax/client_providers.graphql?");

/***/ }),

/***/ "./server/totara/oauth2/webapi/ajax/create_provider.graphql":
/*!******************************************************************!*\
  !*** ./server/totara/oauth2/webapi/ajax/create_provider.graphql ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"mutation\",\"name\":{\"kind\":\"Name\",\"value\":\"totara_oauth2_create_provider\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"input\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"totara_oauth2_provider_input\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"provider\"},\"name\":{\"kind\":\"Name\",\"value\":\"totara_oauth2_create_provider\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"input\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"input\"}}}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"client_id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"client_secret\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"name\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"format\"},\"value\":{\"kind\":\"EnumValue\",\"value\":\"PLAIN\"}}],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"description\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"format\"},\"value\":{\"kind\":\"EnumValue\",\"value\":\"HTML\"}}],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"scope\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"detail_scope\"},\"arguments\":[],\"directives\":[]}]}}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/totara/oauth2/webapi/ajax/create_provider.graphql?");

/***/ }),

/***/ "./server/totara/oauth2/webapi/ajax/delete_provider.graphql":
/*!******************************************************************!*\
  !*** ./server/totara/oauth2/webapi/ajax/delete_provider.graphql ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"mutation\",\"name\":{\"kind\":\"Name\",\"value\":\"totara_oauth2_delete_provider\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"result\"},\"name\":{\"kind\":\"Name\",\"value\":\"totara_oauth2_delete_provider\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"}}}],\"directives\":[]}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/totara/oauth2/webapi/ajax/delete_provider.graphql?");

/***/ }),

/***/ "./client/component/totara_oauth2/tui.json":
/*!*************************************************!*\
  !*** ./client/component/totara_oauth2/tui.json ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"totara_oauth2\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"totara_oauth2\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"totara_oauth2\")\ntui._bundle.addModulesFromContext(\"totara_oauth2\", __webpack_require__(\"./client/component/totara_oauth2/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/tui.json?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_form_Form__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/form/Form */ \"tui/components/form/Form\");\n/* harmony import */ var tui_components_form_Form__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_Form__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_form_InputSizedText__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/form/InputSizedText */ \"tui/components/form/InputSizedText\");\n/* harmony import */ var tui_components_form_InputSizedText__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_InputSizedText__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tui_config__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tui/config */ \"tui/config\");\n/* harmony import */ var tui_config__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tui_config__WEBPACK_IMPORTED_MODULE_2__);\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    Form: (tui_components_form_Form__WEBPACK_IMPORTED_MODULE_0___default()),\n    InputSizedText: (tui_components_form_InputSizedText__WEBPACK_IMPORTED_MODULE_1___default())\n  },\n  methods: {\n    getOauthUrl() {\n      return tui_config__WEBPACK_IMPORTED_MODULE_2__.config.wwwroot + '/totara/oauth2/token.php';\n    },\n    getXapiUrl() {\n      return tui_config__WEBPACK_IMPORTED_MODULE_2__.config.wwwroot + '/totara/xapi/receiver.php';\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/dropdown/Dropdown */ \"tui/components/dropdown/Dropdown\");\n/* harmony import */ var tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_buttons_MoreIcon__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/buttons/MoreIcon */ \"tui/components/buttons/MoreIcon\");\n/* harmony import */ var tui_components_buttons_MoreIcon__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_buttons_MoreIcon__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tui_components_dropdown_DropdownItem__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tui/components/dropdown/DropdownItem */ \"tui/components/dropdown/DropdownItem\");\n/* harmony import */ var tui_components_dropdown_DropdownItem__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tui_components_dropdown_DropdownItem__WEBPACK_IMPORTED_MODULE_2__);\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    Dropdown: (tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_0___default()),\n    MoreIcon: (tui_components_buttons_MoreIcon__WEBPACK_IMPORTED_MODULE_1___default()),\n    DropdownItem: (tui_components_dropdown_DropdownItem__WEBPACK_IMPORTED_MODULE_2___default())\n  },\n  props: {\n    providerName: {\n      type: String,\n      required: true\n    }\n  },\n  emits: ['delete-provider']\n});\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=script&lang=js":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=script&lang=js ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_buttons_ButtonGroup__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/buttons/ButtonGroup */ \"tui/components/buttons/ButtonGroup\");\n/* harmony import */ var tui_components_buttons_ButtonGroup__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_buttons_ButtonGroup__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/buttons/Button */ \"tui/components/buttons/Button\");\n/* harmony import */ var tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tui/components/form/FormRow */ \"tui/components/form/FormRow\");\n/* harmony import */ var tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var tui_components_buttons_Cancel__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! tui/components/buttons/Cancel */ \"tui/components/buttons/Cancel\");\n/* harmony import */ var tui_components_buttons_Cancel__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(tui_components_buttons_Cancel__WEBPACK_IMPORTED_MODULE_3__);\n/* harmony import */ var tui_components_form_Checkbox__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! tui/components/form/Checkbox */ \"tui/components/form/Checkbox\");\n/* harmony import */ var tui_components_form_Checkbox__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_Checkbox__WEBPACK_IMPORTED_MODULE_4__);\n/* harmony import */ var tui_components_uniform__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! tui/components/uniform */ \"tui/components/uniform\");\n/* harmony import */ var tui_components_uniform__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(tui_components_uniform__WEBPACK_IMPORTED_MODULE_5__);\n/* harmony import */ var tui_components_modal_Modal__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! tui/components/modal/Modal */ \"tui/components/modal/Modal\");\n/* harmony import */ var tui_components_modal_Modal__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(tui_components_modal_Modal__WEBPACK_IMPORTED_MODULE_6__);\n/* harmony import */ var tui_components_modal_ModalContent__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! tui/components/modal/ModalContent */ \"tui/components/modal/ModalContent\");\n/* harmony import */ var tui_components_modal_ModalContent__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(tui_components_modal_ModalContent__WEBPACK_IMPORTED_MODULE_7__);\n\n\n\n\n\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    ButtonGroup: (tui_components_buttons_ButtonGroup__WEBPACK_IMPORTED_MODULE_0___default()),\n    Button: (tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_1___default()),\n    Checkbox: (tui_components_form_Checkbox__WEBPACK_IMPORTED_MODULE_4___default()),\n    Cancel: (tui_components_buttons_Cancel__WEBPACK_IMPORTED_MODULE_3___default()),\n    FormRow: (tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_2___default()),\n    FormText: tui_components_uniform__WEBPACK_IMPORTED_MODULE_5__.FormText,\n    Modal: (tui_components_modal_Modal__WEBPACK_IMPORTED_MODULE_6___default()),\n    ModalContent: (tui_components_modal_ModalContent__WEBPACK_IMPORTED_MODULE_7___default()),\n    Uniform: tui_components_uniform__WEBPACK_IMPORTED_MODULE_5__.Uniform,\n    FormTextarea: tui_components_uniform__WEBPACK_IMPORTED_MODULE_5__.FormTextarea\n  },\n  props: {\n    title: {\n      type: String,\n      required: true\n    },\n    showCloseButton: {\n      type: Boolean,\n      default: true\n    },\n    isSaving: {\n      type: Boolean,\n      default: false\n    }\n  },\n  emits: ['submit', 'request-close'],\n  data() {\n    return {\n      initialValues: {\n        name: '',\n        xapi_write: 'XAPI_WRITE',\n        description: ''\n      },\n      formValues: null\n    };\n  },\n  methods: {\n    /**\n     *\n     * @param {String} field\n     * @param {Int} defaultRow\n     * @param {Int} maxRow\n     *\n     **/\n    setRows(field, defaultRow, maxRow) {\n      let text = '';\n      if (this.formValues && field in this.formValues) {\n        text = this.formValues[field];\n      } else if (this.initialValues && field in this.initialValues) {\n        text = this.initialValues[field];\n      }\n      let row = (text.match(/\\n/g) || []).length + 1;\n      if (row < defaultRow) {\n        return defaultRow;\n      }\n      if (row > maxRow) {\n        return maxRow;\n      }\n      return row;\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/buttons/Button */ \"tui/components/buttons/Button\");\n/* harmony import */ var tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_collapsible_Collapsible__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/collapsible/Collapsible */ \"tui/components/collapsible/Collapsible\");\n/* harmony import */ var tui_components_collapsible_Collapsible__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_collapsible_Collapsible__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tui_components_modal_ConfirmationModal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tui/components/modal/ConfirmationModal */ \"tui/components/modal/ConfirmationModal\");\n/* harmony import */ var tui_components_modal_ConfirmationModal__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tui_components_modal_ConfirmationModal__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var tui_components_form_Form__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! tui/components/form/Form */ \"tui/components/form/Form\");\n/* harmony import */ var tui_components_form_Form__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_Form__WEBPACK_IMPORTED_MODULE_3__);\n/* harmony import */ var tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! tui/components/form/FormRow */ \"tui/components/form/FormRow\");\n/* harmony import */ var tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_4__);\n/* harmony import */ var tui_components_layouts_LayoutOneColumn__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! tui/components/layouts/LayoutOneColumn */ \"tui/components/layouts/LayoutOneColumn\");\n/* harmony import */ var tui_components_layouts_LayoutOneColumn__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(tui_components_layouts_LayoutOneColumn__WEBPACK_IMPORTED_MODULE_5__);\n/* harmony import */ var tui_components_modal_ModalPresenter__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! tui/components/modal/ModalPresenter */ \"tui/components/modal/ModalPresenter\");\n/* harmony import */ var tui_components_modal_ModalPresenter__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(tui_components_modal_ModalPresenter__WEBPACK_IMPORTED_MODULE_6__);\n/* harmony import */ var totara_oauth2_components_Oauth2ProviderContent__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! totara_oauth2/components/Oauth2ProviderContent */ \"totara_oauth2/components/Oauth2ProviderContent\");\n/* harmony import */ var totara_oauth2_components_Oauth2ProviderContent__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(totara_oauth2_components_Oauth2ProviderContent__WEBPACK_IMPORTED_MODULE_7__);\n/* harmony import */ var totara_oauth2_components_modal_Oauth2ProviderModal__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! totara_oauth2/components/modal/Oauth2ProviderModal */ \"totara_oauth2/components/modal/Oauth2ProviderModal\");\n/* harmony import */ var totara_oauth2_components_modal_Oauth2ProviderModal__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(totara_oauth2_components_modal_Oauth2ProviderModal__WEBPACK_IMPORTED_MODULE_8__);\n/* harmony import */ var totara_oauth2_components_action_Oauth2ProviderAction__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! totara_oauth2/components/action/Oauth2ProviderAction */ \"totara_oauth2/components/action/Oauth2ProviderAction\");\n/* harmony import */ var totara_oauth2_components_action_Oauth2ProviderAction__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(totara_oauth2_components_action_Oauth2ProviderAction__WEBPACK_IMPORTED_MODULE_9__);\n/* harmony import */ var tui_components_form_InputGroup__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! tui/components/form/InputGroup */ \"tui/components/form/InputGroup\");\n/* harmony import */ var tui_components_form_InputGroup__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_InputGroup__WEBPACK_IMPORTED_MODULE_10__);\n/* harmony import */ var tui_components_form_InputGroupInput__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! tui/components/form/InputGroupInput */ \"tui/components/form/InputGroupInput\");\n/* harmony import */ var tui_components_form_InputGroupInput__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_InputGroupInput__WEBPACK_IMPORTED_MODULE_11__);\n/* harmony import */ var tui_components_form_InputGroupButton__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! tui/components/form/InputGroupButton */ \"tui/components/form/InputGroupButton\");\n/* harmony import */ var tui_components_form_InputGroupButton__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_InputGroupButton__WEBPACK_IMPORTED_MODULE_12__);\n/* harmony import */ var tui_notifications__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! tui/notifications */ \"tui/notifications\");\n/* harmony import */ var tui_notifications__WEBPACK_IMPORTED_MODULE_13___default = /*#__PURE__*/__webpack_require__.n(tui_notifications__WEBPACK_IMPORTED_MODULE_13__);\n/* harmony import */ var totara_oauth2_graphql_client_providers__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! totara_oauth2/graphql/client_providers */ \"./server/totara/oauth2/webapi/ajax/client_providers.graphql\");\n/* harmony import */ var totara_oauth2_graphql_create_provider__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! totara_oauth2/graphql/create_provider */ \"./server/totara/oauth2/webapi/ajax/create_provider.graphql\");\n/* harmony import */ var totara_oauth2_graphql_delete_provider__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! totara_oauth2/graphql/delete_provider */ \"./server/totara/oauth2/webapi/ajax/delete_provider.graphql\");\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n// GraphQL\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    Button: (tui_components_buttons_Button__WEBPACK_IMPORTED_MODULE_0___default()),\n    Collapsible: (tui_components_collapsible_Collapsible__WEBPACK_IMPORTED_MODULE_1___default()),\n    DeleteConfirmationModal: (tui_components_modal_ConfirmationModal__WEBPACK_IMPORTED_MODULE_2___default()),\n    FormRow: (tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_4___default()),\n    Form: (tui_components_form_Form__WEBPACK_IMPORTED_MODULE_3___default()),\n    Layout: (tui_components_layouts_LayoutOneColumn__WEBPACK_IMPORTED_MODULE_5___default()),\n    ModalPresenter: (tui_components_modal_ModalPresenter__WEBPACK_IMPORTED_MODULE_6___default()),\n    Oauth2ProviderModal: (totara_oauth2_components_modal_Oauth2ProviderModal__WEBPACK_IMPORTED_MODULE_8___default()),\n    Oauth2ProviderContent: (totara_oauth2_components_Oauth2ProviderContent__WEBPACK_IMPORTED_MODULE_7___default()),\n    Oauth2ProviderAction: (totara_oauth2_components_action_Oauth2ProviderAction__WEBPACK_IMPORTED_MODULE_9___default()),\n    InputGroup: (tui_components_form_InputGroup__WEBPACK_IMPORTED_MODULE_10___default()),\n    InputGroupInput: (tui_components_form_InputGroupInput__WEBPACK_IMPORTED_MODULE_11___default()),\n    InputGroupButton: (tui_components_form_InputGroupButton__WEBPACK_IMPORTED_MODULE_12___default())\n  },\n  data() {\n    return {\n      providers: [],\n      modalOpen: false,\n      deleteModalOpen: false,\n      deleting: false,\n      isSaving: false,\n      expanded: {},\n      targetProvider: {},\n      showSecret: {}\n    };\n  },\n  computed: {\n    hasNoRecordError() {\n      return this.providers.length === 0;\n    }\n  },\n  apollo: {\n    providers: {\n      query: totara_oauth2_graphql_client_providers__WEBPACK_IMPORTED_MODULE_14__[\"default\"],\n      variables() {\n        return {\n          input: {}\n        };\n      },\n      update({\n        providers: {\n          items\n        }\n      }) {\n        return items;\n      }\n    }\n  },\n  created() {\n    this.providers.forEach(provider => this.expanded[provider.id] = false);\n  },\n  methods: {\n    /**\n     *\n     * @param {Object} formValue\n     */\n    async createProvider(formValue) {\n      this.isSaving = true;\n      try {\n        const {\n          data: {\n            provider\n          }\n        } = await this.$apollo.mutate({\n          mutation: totara_oauth2_graphql_create_provider__WEBPACK_IMPORTED_MODULE_15__[\"default\"],\n          variables: {\n            input: {\n              name: formValue.name,\n              description: formValue.description,\n              scope_type: formValue.xapi_write\n            }\n          },\n          update: (proxy, {\n            data: {\n              provider\n            }\n          }) => {\n            const variables = {\n              input: {}\n            };\n            const {\n              providers: {\n                items\n              }\n            } = proxy.readQuery({\n              query: totara_oauth2_graphql_client_providers__WEBPACK_IMPORTED_MODULE_14__[\"default\"],\n              variables\n            });\n            const innerProviders = [...items];\n            if (provider) {\n              innerProviders.push(provider);\n            }\n            proxy.writeQuery({\n              query: totara_oauth2_graphql_client_providers__WEBPACK_IMPORTED_MODULE_14__[\"default\"],\n              variables,\n              data: {\n                providers: {\n                  items: innerProviders.sort((p1, p2) => p1.name.localeCompare(p2.name))\n                }\n              }\n            });\n          }\n        });\n        if (provider) {\n          this.providers.forEach(p => this.expanded[p.id] = false);\n          this.modalOpen = false;\n          this.expanded[provider.id] = true;\n          await (0,tui_notifications__WEBPACK_IMPORTED_MODULE_13__.notify)({\n            message: \"##str:get:provider_added,totara_oauth2##\",\n            type: 'success'\n          });\n        }\n      } finally {\n        this.isSaving = false;\n      }\n    },\n    /**\n     * @param {Int} id\n     * @param {Boolean} value\n     */\n    handleCollapsibleChange(value, id) {\n      this.expanded = Object.assign({}, this.expanded, {\n        [id]: value\n      });\n    },\n    /**\n     *\n     * @param {Object} provider\n     */\n    openDeleteModal(provider) {\n      this.targetProvider = provider;\n      this.deleteModalOpen = true;\n    },\n    async deleteProvider() {\n      if (!this.deleteModalOpen || !this.targetProvider) {\n        return;\n      }\n      try {\n        this.deleting = true;\n        const {\n          data: {\n            result\n          }\n        } = await this.$apollo.mutate({\n          mutation: totara_oauth2_graphql_delete_provider__WEBPACK_IMPORTED_MODULE_16__[\"default\"],\n          variables: {\n            id: this.targetProvider.id\n          },\n          update: proxy => {\n            const variables = {\n              input: {}\n            };\n            const {\n              providers: {\n                items\n              }\n            } = proxy.readQuery({\n              query: totara_oauth2_graphql_client_providers__WEBPACK_IMPORTED_MODULE_14__[\"default\"],\n              variables\n            });\n            const innerProviders = [...items];\n            proxy.writeQuery({\n              query: totara_oauth2_graphql_client_providers__WEBPACK_IMPORTED_MODULE_14__[\"default\"],\n              variables,\n              data: {\n                providers: {\n                  items: innerProviders.filter(p => p.id !== this.targetProvider.id)\n                }\n              }\n            });\n          }\n        });\n        if (result) {\n          (0,tui_notifications__WEBPACK_IMPORTED_MODULE_13__.notify)({\n            type: 'success',\n            message: \"##str:get:delete_success,totara_oauth2##\"\n          });\n        }\n      } finally {\n        this.deleteModalOpen = false;\n        this.deleting = false;\n      }\n    },\n    /**\n     * @param {number} id\n     */\n    toggleSecretVisibility(id) {\n      this.showSecret[id] = !this.showSecret[id];\n    },\n    /**\n     * @param {number} id\n     */\n    showHideText(id) {\n      return this.showSecret[id] ? \"##str:get:hide,totara_oauth2##\" : \"##str:get:show,totara_oauth2##\";\n    },\n    /**\n     * @param {object} provider\n     */\n    showHideAriaLabel(provider) {\n      return this.showSecret[provider.id] ? this.$str.__r(\"##str:get:hide_client_secret_aria,totara_oauth2##\", provider.name) : this.$str.__r(\"##str:get:show_client_secret_aria,totara_oauth2##\", provider.name);\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=template&id=037c9a63":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=template&id=037c9a63 ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = [\"innerHTML\"];\nconst _hoisted_2 = [\"innerHTML\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_InputSizedText = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"InputSizedText\");\n  const _component_Form = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Form\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_Form, {\n    class: \"tui-oauth2ProviderContent\"\n  }, {\n    default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_InputSizedText, null, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:oauth_url_title,totara_oauth2##\"), 1 /* TEXT */)]),\n      _: 1 /* STABLE */\n    }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_InputSizedText, null, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", {\n        innerHTML: \"##str:get:oauth_url_desc,totara_oauth2##\"\n      }, null, 8 /* PROPS */, _hoisted_1)]),\n      _: 1 /* STABLE */\n    }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_InputSizedText, {\n      class: \"tui-oauth2ProviderContent__url\"\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.getOauthUrl()), 1 /* TEXT */)]),\n      _: 1 /* STABLE */\n    }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_InputSizedText, null, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", {\n        innerHTML: \"##str:get:xapi_url_desc,totara_oauth2##\"\n      }, null, 8 /* PROPS */, _hoisted_2)]),\n      _: 1 /* STABLE */\n    }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_InputSizedText, {\n      class: \"tui-oauth2ProviderContent__url\"\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.getXapiUrl()), 1 /* TEXT */)]),\n      _: 1 /* STABLE */\n    })]),\n    _: 1 /* STABLE */\n  });\n}\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=template&id=3f76ccf1":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=template&id=3f76ccf1 ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-oauth2ProviderAction\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_MoreIcon = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"MoreIcon\");\n  const _component_DropdownItem = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"DropdownItem\");\n  const _component_Dropdown = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Dropdown\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Dropdown, null, {\n    trigger: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n      toggle,\n      isOpen\n    }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_MoreIcon, {\n      \"aria-expanded\": isOpen ? 'true' : 'false',\n      \"aria-label\": _ctx.$str.__r(\"##str:get:actions_for,totara_oauth2##\", $props.providerName),\n      size: 300,\n      onClick: toggle\n    }, null, 8 /* PROPS */, [\"aria-expanded\", \"aria-label\", \"onClick\"])]),\n    default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_DropdownItem, {\n      title: _ctx.$str.__r(\"##str:get:delete_provider_name,totara_oauth2##\", $props.providerName),\n      \"aria-label\": _ctx.$str.__r(\"##str:get:delete_provider_name,totara_oauth2##\", $props.providerName),\n      onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('delete-provider'))\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:delete,core##\"), 1 /* TEXT */)]),\n      _: 1 /* STABLE */\n    }, 8 /* PROPS */, [\"title\", \"aria-label\"])]),\n    _: 1 /* STABLE */\n  })]);\n}\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=template&id=42694335":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=template&id=42694335 ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = [\"disabled\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_FormRow = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"FormRow\");\n  const _component_FormText = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"FormText\");\n  const _component_FormTextarea = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"FormTextarea\");\n  const _component_Checkbox = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Checkbox\");\n  const _component_Uniform = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Uniform\");\n  const _component_Button = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Button\");\n  const _component_Cancel = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Cancel\");\n  const _component_ButtonGroup = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"ButtonGroup\");\n  const _component_ModalContent = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"ModalContent\");\n  const _component_Modal = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Modal\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_Modal, {\n    \"aria-labelledby\": _ctx.$id('title'),\n    class: \"tui-oauth2ProviderForm\"\n  }, {\n    default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ModalContent, {\n      title: $props.title,\n      \"title-id\": _ctx.$id('title'),\n      \"close-button\": $props.showCloseButton\n    }, {\n      buttons: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ButtonGroup, {\n        class: \"tui-oauth2ProviderForm__buttonGroup\"\n      }, {\n        default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Button, {\n          styleclass: {\n            primary: true\n          },\n          text: \"##str:get:add_provider,totara_oauth2##\",\n          \"aria-label\": \"##str:get:add_provider,totara_oauth2##\",\n          type: \"submit\",\n          disabled: $props.isSaving,\n          onClick: _cache[2] || (_cache[2] = $event => _ctx.$refs.form.submit())\n        }, null, 8 /* PROPS */, [\"text\", \"aria-label\", \"disabled\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Cancel, {\n          disabled: $props.isSaving,\n          onClick: _cache[3] || (_cache[3] = $event => _ctx.$emit('request-close'))\n        }, null, 8 /* PROPS */, [\"disabled\"])]),\n        _: 1 /* STABLE */\n      })]),\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Uniform, {\n        ref: \"form\",\n        \"initial-values\": $data.initialValues,\n        \"input-width\": \"full\",\n        onChange: _cache[0] || (_cache[0] = $event => $data.formValues = $event),\n        onSubmit: _cache[1] || (_cache[1] = $event => _ctx.$emit('submit', $event))\n      }, {\n        default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormRow, {\n          \"aria-hidden\": \"true\",\n          class: \"tui-oauth2ProviderForm__required\"\n        }, {\n          default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [_cache[4] || (_cache[4] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", {\n            class: \"tui-oauth2ProviderForm__requiredStar\"\n          }, \" * \", -1 /* HOISTED */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(\" \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:required_fields,totara_oauth2##\"), 1 /* TEXT */)]),\n          _: 1 /* STABLE */\n        }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormRow, {\n          label: \"##str:get:name,core##\",\n          required: \"\",\n          vertical: true\n        }, {\n          default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n            id\n          }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormText, {\n            id: id,\n            name: \"name\",\n            validations: v => [v.required()],\n            maxlength: 75\n          }, null, 8 /* PROPS */, [\"id\", \"validations\"])]),\n          _: 1 /* STABLE */\n        }, 8 /* PROPS */, [\"label\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormRow, {\n          label: \"##str:get:description,totara_oauth2##\",\n          vertical: true\n        }, {\n          default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormTextarea, {\n            name: \"description\",\n            maxlength: 1024,\n            \"aria-describedby\": _ctx.$id('desc-desc'),\n            rows: $options.setRows('description', 8, 25)\n          }, null, 8 /* PROPS */, [\"aria-describedby\", \"rows\"])]),\n          _: 1 /* STABLE */\n        }, 8 /* PROPS */, [\"label\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormRow, {\n          label: \"##str:get:scopes,totara_oauth2##\",\n          vertical: true\n        }, {\n          default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Checkbox, {\n            name: \"xapi_write\",\n            disabled: \"\",\n            checked: \"\",\n            class: \"tui-oauth2ProviderForm__checkBox\"\n          }, {\n            default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:xapi_write,totara_oauth2##\"), 1 /* TEXT */)]),\n            _: 1 /* STABLE */\n          })]),\n          _: 1 /* STABLE */\n        }, 8 /* PROPS */, [\"label\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"input\", {\n          type: \"submit\",\n          disabled: $props.isSaving\n        }, null, 8 /* PROPS */, _hoisted_1), [[vue__WEBPACK_IMPORTED_MODULE_0__.vShow, false]])]),\n        _: 1 /* STABLE */\n      }, 8 /* PROPS */, [\"initial-values\"])]),\n      _: 1 /* STABLE */\n    }, 8 /* PROPS */, [\"title\", \"title-id\", \"close-button\"])]),\n    _: 1 /* STABLE */\n  }, 8 /* PROPS */, [\"aria-labelledby\"]);\n}\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=template&id=0ed067c6":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=template&id=0ed067c6 ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = [\"innerHTML\"];\nconst _hoisted_2 = {\n  key: 0,\n  class: \"tui-oauth2ProviderPage__errorTitle\"\n};\nconst _hoisted_3 = [\"innerHTML\"];\nconst _hoisted_4 = {\n  class: \"tui-oauth2ProviderPage__monospaceFont\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_Button = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Button\");\n  const _component_Oauth2ProviderModal = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Oauth2ProviderModal\");\n  const _component_ModalPresenter = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"ModalPresenter\");\n  const _component_DeleteConfirmationModal = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"DeleteConfirmationModal\");\n  const _component_Oauth2ProviderAction = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Oauth2ProviderAction\");\n  const _component_FormRow = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"FormRow\");\n  const _component_InputGroupInput = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"InputGroupInput\");\n  const _component_InputGroupButton = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"InputGroupButton\");\n  const _component_InputGroup = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"InputGroup\");\n  const _component_Form = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Form\");\n  const _component_Collapsible = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Collapsible\");\n  const _component_Oauth2ProviderContent = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Oauth2ProviderContent\");\n  const _component_Layout = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Layout\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_Layout, {\n    class: \"tui-oauth2ProviderPage\",\n    title: \"##str:get:oauth2providerdetails,totara_oauth2##\",\n    loading: _ctx.$apollo.loading\n  }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createSlots)({\n    \"header-buttons\": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Button, {\n      text: \"##str:get:add_provider,totara_oauth2##\",\n      onClick: _cache[0] || (_cache[0] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)($event => $data.modalOpen = true, [\"prevent\"]))\n    }, null, 8 /* PROPS */, [\"text\"])]),\n    modals: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ModalPresenter, {\n      open: $data.modalOpen,\n      onRequestClose: _cache[1] || (_cache[1] = $event => $data.modalOpen = false)\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Oauth2ProviderModal, {\n        title: \"##str:get:add_oauth2_provider,totara_oauth2##\",\n        \"is-saving\": $data.isSaving,\n        onSubmit: $options.createProvider\n      }, null, 8 /* PROPS */, [\"title\", \"is-saving\", \"onSubmit\"])]),\n      _: 1 /* STABLE */\n    }, 8 /* PROPS */, [\"open\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Deletion modal  \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_DeleteConfirmationModal, {\n      open: $data.deleteModalOpen,\n      title: \"##str:get:delete_modal_title,totara_oauth2##\",\n      \"confirm-button-text\": \"##str:get:continue,totara_oauth2##\",\n      loading: $data.deleting,\n      \"close-button\": true,\n      onConfirm: $options.deleteProvider,\n      onCancel: _cache[2] || (_cache[2] = $event => $data.deleteModalOpen = false)\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"p\", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:delete_confirm_title,totara_oauth2##\"), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"p\", {\n        class: \"tui-oauth2ProviderPage__deleteBody\",\n        innerHTML: _ctx.$str.__r(\"##str:get:delete_confirm_body,totara_oauth2##\", $data.targetProvider.name)\n      }, null, 8 /* PROPS */, _hoisted_1)]),\n      _: 1 /* STABLE */\n    }, 8 /* PROPS */, [\"open\", \"title\", \"confirm-button-text\", \"loading\", \"onConfirm\"])]),\n    _: 2 /* DYNAMIC */\n  }, [!_ctx.$apollo.loading ? {\n    name: \"content\",\n    fn: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [$options.hasNoRecordError ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"p\", _hoisted_2, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:no_record_found,totara_oauth2##\"), 1 /* TEXT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {\n      key: 1\n    }, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.providers, provider => {\n      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_Collapsible, {\n        key: provider.id,\n        label: provider.name,\n        class: \"tui-oauth2ProviderPage__provider\",\n        value: $data.expanded[provider.id],\n        onInput: $event => $options.handleCollapsibleChange($event, provider.id)\n      }, {\n        \"collapsible-side-content\": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Oauth2ProviderAction, {\n          \"provider-name\": provider.name,\n          onDeleteProvider: $event => $options.openDeleteModal(provider)\n        }, null, 8 /* PROPS */, [\"provider-name\", \"onDeleteProvider\"])]),\n        default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Form, {\n          class: \"tui-oauth2ProviderPage__form\"\n        }, {\n          default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [provider.description ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_FormRow, {\n            key: 0,\n            vertical: true,\n            class: \"tui-oauth2ProviderPage__formDesc\"\n          }, {\n            default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", {\n              innerHTML: provider.description\n            }, null, 8 /* PROPS */, _hoisted_3)]),\n            _: 2 /* DYNAMIC */\n          }, 1024 /* DYNAMIC_SLOTS */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormRow, {\n            label: \"##str:get:client_id,totara_oauth2##\",\n            class: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)({\n              'tui-oauth2ProviderPage__clientId': !provider.description\n            })\n          }, {\n            default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_4, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(provider.client_id), 1 /* TEXT */)]),\n            _: 2 /* DYNAMIC */\n          }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"label\", \"class\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormRow, {\n            label: \"##str:get:client_secret,totara_oauth2##\"\n          }, {\n            default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n              id,\n              labelId\n            }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_InputGroup, {\n              \"aria-labelledby\": labelId\n            }, {\n              default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_InputGroupInput, {\n                id: id,\n                monospace: true,\n                readonly: \"\",\n                type: $data.showSecret[provider.id] ? 'text' : 'password',\n                value: provider.client_secret\n              }, null, 8 /* PROPS */, [\"id\", \"type\", \"value\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_InputGroupButton, {\n                class: \"tui-oauth2ProviderPage__inputGroupBtn\",\n                \"aria-controls\": id,\n                \"aria-label\": $options.showHideAriaLabel(provider),\n                text: $options.showHideText(provider.id),\n                onClick: $event => $options.toggleSecretVisibility(provider.id)\n              }, null, 8 /* PROPS */, [\"aria-controls\", \"aria-label\", \"text\", \"onClick\"])]),\n              _: 2 /* DYNAMIC */\n            }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"aria-labelledby\"])]),\n            _: 2 /* DYNAMIC */\n          }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"label\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormRow, {\n            label: \"##str:get:scopes,totara_oauth2##\"\n          }, {\n            default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(provider.detail_scope), 1 /* TEXT */)]),\n            _: 2 /* DYNAMIC */\n          }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"label\"])]),\n          _: 2 /* DYNAMIC */\n        }, 1024 /* DYNAMIC_SLOTS */)]),\n        _: 2 /* DYNAMIC */\n      }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"label\", \"value\", \"onInput\"]);\n    }), 128 /* KEYED_FRAGMENT */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Oauth2ProviderContent)], 64 /* STABLE_FRAGMENT */))]),\n    key: \"0\"\n  } : undefined]), 1032 /* PROPS, DYNAMIC_SLOTS */, [\"title\", \"loading\"]);\n}\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=template&id=037c9a63":
/*!***************************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=template&id=037c9a63 ***!
  \***************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Oauth2ProviderContent_vue_vue_type_template_id_037c9a63__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Oauth2ProviderContent_vue_vue_type_template_id_037c9a63__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./Oauth2ProviderContent.vue?vue&type=template&id=037c9a63 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=template&id=037c9a63\");\n\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=template&id=3f76ccf1":
/*!*********************************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=template&id=3f76ccf1 ***!
  \*********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Oauth2ProviderAction_vue_vue_type_template_id_3f76ccf1__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Oauth2ProviderAction_vue_vue_type_template_id_3f76ccf1__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./Oauth2ProviderAction.vue?vue&type=template&id=3f76ccf1 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=template&id=3f76ccf1\");\n\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=template&id=42694335":
/*!*******************************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=template&id=42694335 ***!
  \*******************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Oauth2ProviderModal_vue_vue_type_template_id_42694335__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Oauth2ProviderModal_vue_vue_type_template_id_42694335__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./Oauth2ProviderModal.vue?vue&type=template&id=42694335 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=template&id=42694335\");\n\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=template&id=0ed067c6":
/*!***************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=template&id=0ed067c6 ***!
  \***************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Oauth2Provider_vue_vue_type_template_id_0ed067c6__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Oauth2Provider_vue_vue_type_template_id_0ed067c6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./Oauth2Provider.vue?vue&type=template&id=0ed067c6 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=template&id=0ed067c6\");\n\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=style&index=0&id=037c9a63&lang=scss":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=style&index=0&id=037c9a63&lang=scss ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=style&index=0&id=42694335&lang=scss":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=style&index=0&id=42694335&lang=scss ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=style&index=0&id=0ed067c6&lang=scss":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=style&index=0&id=0ed067c6&lang=scss ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue":
/*!*********************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _Oauth2ProviderContent_vue_vue_type_template_id_037c9a63__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Oauth2ProviderContent.vue?vue&type=template&id=037c9a63 */ \"./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=template&id=037c9a63\");\n/* harmony import */ var _Oauth2ProviderContent_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Oauth2ProviderContent.vue?vue&type=script&lang=js */ \"./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=script&lang=js\");\n/* harmony import */ var _Oauth2ProviderContent_vue_vue_type_style_index_0_id_037c9a63_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Oauth2ProviderContent.vue?vue&type=style&index=0&id=037c9a63&lang=scss */ \"./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=style&index=0&id=037c9a63&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_Oauth2ProviderContent_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_Oauth2ProviderContent_vue_vue_type_template_id_037c9a63__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue":
/*!***************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue ***!
  \***************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _Oauth2ProviderAction_vue_vue_type_template_id_3f76ccf1__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Oauth2ProviderAction.vue?vue&type=template&id=3f76ccf1 */ \"./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=template&id=3f76ccf1\");\n/* harmony import */ var _Oauth2ProviderAction_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Oauth2ProviderAction.vue?vue&type=script&lang=js */ \"./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_Oauth2ProviderAction_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_Oauth2ProviderAction_vue_vue_type_template_id_3f76ccf1__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue":
/*!*************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _Oauth2ProviderModal_vue_vue_type_template_id_42694335__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Oauth2ProviderModal.vue?vue&type=template&id=42694335 */ \"./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=template&id=42694335\");\n/* harmony import */ var _Oauth2ProviderModal_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Oauth2ProviderModal.vue?vue&type=script&lang=js */ \"./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=script&lang=js\");\n/* harmony import */ var _Oauth2ProviderModal_vue_vue_type_style_index_0_id_42694335_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Oauth2ProviderModal.vue?vue&type=style&index=0&id=42694335&lang=scss */ \"./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=style&index=0&id=42694335&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_Oauth2ProviderModal_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_Oauth2ProviderModal_vue_vue_type_template_id_42694335__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/pages/Oauth2Provider.vue":
/*!*********************************************************************!*\
  !*** ./client/component/totara_oauth2/src/pages/Oauth2Provider.vue ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _Oauth2Provider_vue_vue_type_template_id_0ed067c6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Oauth2Provider.vue?vue&type=template&id=0ed067c6 */ \"./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=template&id=0ed067c6\");\n/* harmony import */ var _Oauth2Provider_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Oauth2Provider.vue?vue&type=script&lang=js */ \"./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=script&lang=js\");\n/* harmony import */ var _Oauth2Provider_vue_vue_type_style_index_0_id_0ed067c6_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Oauth2Provider.vue?vue&type=style&index=0&id=0ed067c6&lang=scss */ \"./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=style&index=0&id=0ed067c6&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_Oauth2Provider_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_Oauth2Provider_vue_vue_type_template_id_0ed067c6__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/totara_oauth2/src/pages/Oauth2Provider.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=script&lang=js":
/*!*********************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=script&lang=js ***!
  \*********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Oauth2ProviderContent_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Oauth2ProviderContent_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./Oauth2ProviderContent.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=script&lang=js":
/*!***************************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=script&lang=js ***!
  \***************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Oauth2ProviderAction_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Oauth2ProviderAction_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./Oauth2ProviderAction.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/action/Oauth2ProviderAction.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Oauth2ProviderModal_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Oauth2ProviderModal_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./Oauth2ProviderModal.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=script&lang=js":
/*!*********************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=script&lang=js ***!
  \*********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Oauth2Provider_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1508_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Oauth2Provider_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./Oauth2Provider.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1508.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=style&index=0&id=037c9a63&lang=scss":
/*!******************************************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=style&index=0&id=037c9a63&lang=scss ***!
  \******************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderContent_vue_vue_type_style_index_0_id_037c9a63_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderContent_vue_vue_type_style_index_0_id_037c9a63_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Oauth2ProviderContent.vue?vue&type=style&index=0&id=037c9a63&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?vue&type=style&index=0&id=037c9a63&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderContent_vue_vue_type_style_index_0_id_037c9a63_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderContent_vue_vue_type_style_index_0_id_037c9a63_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderContent_vue_vue_type_style_index_0_id_037c9a63_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderContent_vue_vue_type_style_index_0_id_037c9a63_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/Oauth2ProviderContent.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=style&index=0&id=42694335&lang=scss":
/*!**********************************************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=style&index=0&id=42694335&lang=scss ***!
  \**********************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderModal_vue_vue_type_style_index_0_id_42694335_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderModal_vue_vue_type_style_index_0_id_42694335_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!../../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Oauth2ProviderModal.vue?vue&type=style&index=0&id=42694335&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?vue&type=style&index=0&id=42694335&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderModal_vue_vue_type_style_index_0_id_42694335_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderModal_vue_vue_type_style_index_0_id_42694335_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderModal_vue_vue_type_style_index_0_id_42694335_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2ProviderModal_vue_vue_type_style_index_0_id_42694335_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/components/modal/Oauth2ProviderModal.vue?");

/***/ }),

/***/ "./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=style&index=0&id=0ed067c6&lang=scss":
/*!******************************************************************************************************************!*\
  !*** ./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=style&index=0&id=0ed067c6&lang=scss ***!
  \******************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2Provider_vue_vue_type_style_index_0_id_0ed067c6_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2Provider_vue_vue_type_style_index_0_id_0ed067c6_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Oauth2Provider.vue?vue&type=style&index=0&id=0ed067c6&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1511.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1511.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1511.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?vue&type=style&index=0&id=0ed067c6&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2Provider_vue_vue_type_style_index_0_id_0ed067c6_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2Provider_vue_vue_type_style_index_0_id_0ed067c6_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2Provider_vue_vue_type_style_index_0_id_0ed067c6_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1511_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1511_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1511_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Oauth2Provider_vue_vue_type_style_index_0_id_0ed067c6_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/totara_oauth2/src/pages/Oauth2Provider.vue?");

/***/ }),

/***/ "totara_oauth2/components/Oauth2ProviderContent":
/*!**********************************************************************************!*\
  !*** external "tui.require(\"totara_oauth2/components/Oauth2ProviderContent\")" ***!
  \**********************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("totara_oauth2/components/Oauth2ProviderContent");

/***/ }),

/***/ "totara_oauth2/components/action/Oauth2ProviderAction":
/*!****************************************************************************************!*\
  !*** external "tui.require(\"totara_oauth2/components/action/Oauth2ProviderAction\")" ***!
  \****************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("totara_oauth2/components/action/Oauth2ProviderAction");

/***/ }),

/***/ "totara_oauth2/components/modal/Oauth2ProviderModal":
/*!**************************************************************************************!*\
  !*** external "tui.require(\"totara_oauth2/components/modal/Oauth2ProviderModal\")" ***!
  \**************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("totara_oauth2/components/modal/Oauth2ProviderModal");

/***/ }),

/***/ "tui/components/buttons/ButtonGroup":
/*!**********************************************************************!*\
  !*** external "tui.require(\"tui/components/buttons/ButtonGroup\")" ***!
  \**********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/buttons/ButtonGroup");

/***/ }),

/***/ "tui/components/buttons/Button":
/*!*****************************************************************!*\
  !*** external "tui.require(\"tui/components/buttons/Button\")" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/buttons/Button");

/***/ }),

/***/ "tui/components/buttons/Cancel":
/*!*****************************************************************!*\
  !*** external "tui.require(\"tui/components/buttons/Cancel\")" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/buttons/Cancel");

/***/ }),

/***/ "tui/components/buttons/MoreIcon":
/*!*******************************************************************!*\
  !*** external "tui.require(\"tui/components/buttons/MoreIcon\")" ***!
  \*******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/buttons/MoreIcon");

/***/ }),

/***/ "tui/components/collapsible/Collapsible":
/*!**************************************************************************!*\
  !*** external "tui.require(\"tui/components/collapsible/Collapsible\")" ***!
  \**************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/collapsible/Collapsible");

/***/ }),

/***/ "tui/components/dropdown/DropdownItem":
/*!************************************************************************!*\
  !*** external "tui.require(\"tui/components/dropdown/DropdownItem\")" ***!
  \************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/dropdown/DropdownItem");

/***/ }),

/***/ "tui/components/dropdown/Dropdown":
/*!********************************************************************!*\
  !*** external "tui.require(\"tui/components/dropdown/Dropdown\")" ***!
  \********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/dropdown/Dropdown");

/***/ }),

/***/ "tui/components/form/Checkbox":
/*!****************************************************************!*\
  !*** external "tui.require(\"tui/components/form/Checkbox\")" ***!
  \****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/Checkbox");

/***/ }),

/***/ "tui/components/form/FormRow":
/*!***************************************************************!*\
  !*** external "tui.require(\"tui/components/form/FormRow\")" ***!
  \***************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/FormRow");

/***/ }),

/***/ "tui/components/form/Form":
/*!************************************************************!*\
  !*** external "tui.require(\"tui/components/form/Form\")" ***!
  \************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/Form");

/***/ }),

/***/ "tui/components/form/InputGroupButton":
/*!************************************************************************!*\
  !*** external "tui.require(\"tui/components/form/InputGroupButton\")" ***!
  \************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/InputGroupButton");

/***/ }),

/***/ "tui/components/form/InputGroupInput":
/*!***********************************************************************!*\
  !*** external "tui.require(\"tui/components/form/InputGroupInput\")" ***!
  \***********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/InputGroupInput");

/***/ }),

/***/ "tui/components/form/InputGroup":
/*!******************************************************************!*\
  !*** external "tui.require(\"tui/components/form/InputGroup\")" ***!
  \******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/InputGroup");

/***/ }),

/***/ "tui/components/form/InputSizedText":
/*!**********************************************************************!*\
  !*** external "tui.require(\"tui/components/form/InputSizedText\")" ***!
  \**********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/InputSizedText");

/***/ }),

/***/ "tui/components/layouts/LayoutOneColumn":
/*!**************************************************************************!*\
  !*** external "tui.require(\"tui/components/layouts/LayoutOneColumn\")" ***!
  \**************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/layouts/LayoutOneColumn");

/***/ }),

/***/ "tui/components/modal/ConfirmationModal":
/*!**************************************************************************!*\
  !*** external "tui.require(\"tui/components/modal/ConfirmationModal\")" ***!
  \**************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/modal/ConfirmationModal");

/***/ }),

/***/ "tui/components/modal/ModalContent":
/*!*********************************************************************!*\
  !*** external "tui.require(\"tui/components/modal/ModalContent\")" ***!
  \*********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/modal/ModalContent");

/***/ }),

/***/ "tui/components/modal/ModalPresenter":
/*!***********************************************************************!*\
  !*** external "tui.require(\"tui/components/modal/ModalPresenter\")" ***!
  \***********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/modal/ModalPresenter");

/***/ }),

/***/ "tui/components/modal/Modal":
/*!**************************************************************!*\
  !*** external "tui.require(\"tui/components/modal/Modal\")" ***!
  \**************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/modal/Modal");

/***/ }),

/***/ "tui/components/uniform":
/*!**********************************************************!*\
  !*** external "tui.require(\"tui/components/uniform\")" ***!
  \**********************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/uniform");

/***/ }),

/***/ "tui/config":
/*!**********************************************!*\
  !*** external "tui.require(\"tui/config\")" ***!
  \**********************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/config");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/totara_oauth2/tui.json");
/******/ 	
/******/ })()
;