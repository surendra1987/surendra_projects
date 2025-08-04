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

/***/ "./client/component/totara_dashboard/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!******************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/totara_dashboard/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \******************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/report/manage_dashboards/Actions\": \"./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue\",\n\t\"./components/report/manage_dashboards/Actions.vue\": \"./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/totara_dashboard/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/totara_dashboard/src/_sync_^(?");

/***/ }),

/***/ "./client/component/totara_dashboard/tui.json":
/*!****************************************************!*\
  !*** ./client/component/totara_dashboard/tui.json ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"totara_dashboard\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"totara_dashboard\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"totara_dashboard\")\ntui._bundle.addModulesFromContext(\"totara_dashboard\", __webpack_require__(\"./client/component/totara_dashboard/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/totara_dashboard/tui.json?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=script&setup=true&lang=js":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=script&setup=true&lang=js ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_buttons_ButtonIcon__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/buttons/ButtonIcon */ \"tui/components/buttons/ButtonIcon\");\n/* harmony import */ var tui_components_buttons_ButtonIcon__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_buttons_ButtonIcon__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/dropdown/Dropdown */ \"tui/components/dropdown/Dropdown\");\n/* harmony import */ var tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tui_components_dropdown_DropdownButton__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tui/components/dropdown/DropdownButton */ \"tui/components/dropdown/DropdownButton\");\n/* harmony import */ var tui_components_dropdown_DropdownButton__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tui_components_dropdown_DropdownButton__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var tui_components_icons_More__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! tui/components/icons/More */ \"tui/components/icons/More\");\n/* harmony import */ var tui_components_icons_More__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(tui_components_icons_More__WEBPACK_IMPORTED_MODULE_3__);\n/* harmony import */ var tui_components_icons_Settings__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! tui/components/icons/Settings */ \"tui/components/icons/Settings\");\n/* harmony import */ var tui_components_icons_Settings__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(tui_components_icons_Settings__WEBPACK_IMPORTED_MODULE_4__);\n\n\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  __name: 'Actions',\n  props: {\n    cloneUrl: String,\n    deleteUrl: String,\n    editUrl: String,\n    moveDownUrl: String,\n    moveTopUrl: String,\n    moveUpUrl: String\n  },\n  setup(__props, {\n    expose: __expose\n  }) {\n    __expose();\n    function handleActionClick(url) {\n      window.location.href = url;\n    }\n    const __returned__ = {\n      handleActionClick,\n      get ButtonIcon() {\n        return (tui_components_buttons_ButtonIcon__WEBPACK_IMPORTED_MODULE_0___default());\n      },\n      get Dropdown() {\n        return (tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_1___default());\n      },\n      get DropdownButton() {\n        return (tui_components_dropdown_DropdownButton__WEBPACK_IMPORTED_MODULE_2___default());\n      },\n      get MoreIcon() {\n        return (tui_components_icons_More__WEBPACK_IMPORTED_MODULE_3___default());\n      },\n      get SettingsIcon() {\n        return (tui_components_icons_Settings__WEBPACK_IMPORTED_MODULE_4___default());\n      }\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n});\n\n//# sourceURL=webpack:///./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=template&id=3d15f36f":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=template&id=3d15f36f ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-totara_dashboard-actions\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Settings button \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"ButtonIcon\"], {\n    \"aria-label\": \"##str:get:editdashboard,totara_dashboard##\",\n    styleclass: {\n      stealth: true\n    },\n    size: \"sm\",\n    href: $props.editUrl\n  }, {\n    default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"SettingsIcon\"])]),\n    _: 1 /* STABLE */\n  }, 8 /* PROPS */, [\"aria-label\", \"href\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Actions dropdown \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"Dropdown\"], {\n    \"context-mode\": \"uncontained\",\n    position: \"bottom-right\",\n    separator: false\n  }, {\n    trigger: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n      toggle,\n      isOpen\n    }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"ButtonIcon\"], {\n      \"aria-label\": \"##str:get:actions,core##\",\n      \"aria-expanded\": isOpen,\n      styleclass: {\n        stealth: true\n      },\n      size: \"sm\",\n      onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(toggle, [\"prevent\"])\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"MoreIcon\"])]),\n      _: 2 /* DYNAMIC */\n    }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"aria-label\", \"aria-expanded\", \"onClick\"])]),\n    default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [$props.cloneUrl ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)($setup[\"DropdownButton\"], {\n      key: 0,\n      onClick: _cache[0] || (_cache[0] = $event => $setup.handleActionClick($props.cloneUrl))\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:manage_action_clone,totara_dashboard##\"), 1 /* TEXT */)]),\n      _: 1 /* STABLE */\n    })) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), $props.moveUpUrl ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)($setup[\"DropdownButton\"], {\n      key: 1,\n      onClick: _cache[1] || (_cache[1] = $event => $setup.handleActionClick($props.moveUpUrl))\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:manage_action_up,totara_dashboard##\"), 1 /* TEXT */)]),\n      _: 1 /* STABLE */\n    })) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), $props.moveDownUrl ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)($setup[\"DropdownButton\"], {\n      key: 2,\n      onClick: _cache[2] || (_cache[2] = $event => $setup.handleActionClick($props.moveDownUrl))\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:manage_action_down,totara_dashboard##\"), 1 /* TEXT */)]),\n      _: 1 /* STABLE */\n    })) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), $props.moveTopUrl ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)($setup[\"DropdownButton\"], {\n      key: 3,\n      onClick: _cache[3] || (_cache[3] = $event => $setup.handleActionClick($props.moveTopUrl))\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:manage_action_top,totara_dashboard##\"), 1 /* TEXT */)]),\n      _: 1 /* STABLE */\n    })) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), $props.deleteUrl ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)($setup[\"DropdownButton\"], {\n      key: 4,\n      onClick: _cache[4] || (_cache[4] = $event => $setup.handleActionClick($props.deleteUrl))\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:manage_action_delete,totara_dashboard##\"), 1 /* TEXT */)]),\n      _: 1 /* STABLE */\n    })) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true)]),\n    _: 1 /* STABLE */\n  })]);\n}\n\n//# sourceURL=webpack:///./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=template&id=3d15f36f":
/*!*****************************************************************************************************************************!*\
  !*** ./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=template&id=3d15f36f ***!
  \*****************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1448_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Actions_vue_vue_type_template_id_3d15f36f__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1448_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Actions_vue_vue_type_template_id_3d15f36f__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use[0]!../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./Actions.vue?vue&type=template&id=3d15f36f */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=template&id=3d15f36f\");\n\n\n//# sourceURL=webpack:///./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1451.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1451.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1451.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=style&index=0&id=3d15f36f&lang=scss":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1451.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1451.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1451.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=style&index=0&id=3d15f36f&lang=scss ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1451.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1451.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1451.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue":
/*!***********************************************************************************************!*\
  !*** ./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue ***!
  \***********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _Actions_vue_vue_type_template_id_3d15f36f__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Actions.vue?vue&type=template&id=3d15f36f */ \"./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=template&id=3d15f36f\");\n/* harmony import */ var _Actions_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Actions.vue?vue&type=script&setup=true&lang=js */ \"./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _Actions_vue_vue_type_style_index_0_id_3d15f36f_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Actions.vue?vue&type=style&index=0&id=3d15f36f&lang=scss */ \"./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=style&index=0&id=3d15f36f&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_Actions_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_Actions_vue_vue_type_template_id_3d15f36f__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?");

/***/ }),

/***/ "./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=script&setup=true&lang=js":
/*!**********************************************************************************************************************************!*\
  !*** ./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=script&setup=true&lang=js ***!
  \**********************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1448_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Actions_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1448_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Actions_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use[0]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./Actions.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1448.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?");

/***/ }),

/***/ "./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=style&index=0&id=3d15f36f&lang=scss":
/*!********************************************************************************************************************************************!*\
  !*** ./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=style&index=0&id=3d15f36f&lang=scss ***!
  \********************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1451_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1451_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1451_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Actions_vue_vue_type_style_index_0_id_3d15f36f_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1451_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1451_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1451_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Actions_vue_vue_type_style_index_0_id_3d15f36f_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1451.use[0]!../../../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-1451.use[1]!../../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1451.use[2]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Actions.vue?vue&type=style&index=0&id=3d15f36f&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1451.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1451.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1451.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?vue&type=style&index=0&id=3d15f36f&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1451_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1451_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1451_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Actions_vue_vue_type_style_index_0_id_3d15f36f_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1451_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1451_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1451_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Actions_vue_vue_type_style_index_0_id_3d15f36f_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1451_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1451_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1451_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Actions_vue_vue_type_style_index_0_id_3d15f36f_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1451_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1451_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1451_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Actions_vue_vue_type_style_index_0_id_3d15f36f_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/totara_dashboard/src/components/report/manage_dashboards/Actions.vue?");

/***/ }),

/***/ "tui/components/buttons/ButtonIcon":
/*!*********************************************************************!*\
  !*** external "tui.require(\"tui/components/buttons/ButtonIcon\")" ***!
  \*********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/buttons/ButtonIcon");

/***/ }),

/***/ "tui/components/dropdown/DropdownButton":
/*!**************************************************************************!*\
  !*** external "tui.require(\"tui/components/dropdown/DropdownButton\")" ***!
  \**************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/dropdown/DropdownButton");

/***/ }),

/***/ "tui/components/dropdown/Dropdown":
/*!********************************************************************!*\
  !*** external "tui.require(\"tui/components/dropdown/Dropdown\")" ***!
  \********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/dropdown/Dropdown");

/***/ }),

/***/ "tui/components/icons/More":
/*!*************************************************************!*\
  !*** external "tui.require(\"tui/components/icons/More\")" ***!
  \*************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/icons/More");

/***/ }),

/***/ "tui/components/icons/Settings":
/*!*****************************************************************!*\
  !*** external "tui.require(\"tui/components/icons/Settings\")" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/icons/Settings");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/totara_dashboard/tui.json");
/******/ 	
/******/ })()
;