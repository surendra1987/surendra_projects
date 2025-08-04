export default {
  render() {
    return h('div', {}, [
      "##str:get:success,core##",
      "##str:try:success,core##",
      "##str:has:success,core##",
      "##str:get:success,core##",
      "##str:try:success,core##",
      "##str:has:success,core##",
      someVar ? "##str:get:success,core##" : "##str:get:failure,core##",
    ]);
  },
};
