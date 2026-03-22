export default {
  render() {
    return h('div', {}, [
      this.$str('success', 'core'),
      this.$tryStr('success', 'core'),
      this.$hasStr('success', 'core'),
      _ctx.$str('success', 'core'),
      _ctx.$tryStr('success', 'core'),
      _ctx.$hasStr('success', 'core'),
      this.$str(someVar ? 'success' : 'failure', 'core'),
    ]);
  },
};
