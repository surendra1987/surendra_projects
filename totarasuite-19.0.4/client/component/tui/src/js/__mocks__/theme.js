const original = jest.requireActual('../theme');

const vars = {
  'char-length-scale': '2, 3, 4, 5, 10, 15, 20, 25, 30, 50, 75, 100',
};

export default {
  getVar(name) {
    return vars[name];
  },

  getVarUsage(name) {
    return `var(--${name})`;
  },

  getArrayVar() {
    return [];
  },

  adjustHexValueBrightness: original.adjustHexValueBrightness,
};
