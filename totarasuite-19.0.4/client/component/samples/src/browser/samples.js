import { defineAsyncComponent, markRaw } from 'vue';
import { memoize } from 'tui/util';

const prefix = 'samples/components/samples/';

export const wrapSampleComponent = memoize(sample => {
  return markRaw(
    defineAsyncComponent({
      loader: () =>
        tui
          // eslint-disable-next-line tui/no-tui-internal
          ._loadTuiComponent(sample.tuiComponent)
          .then(() => tui.loadComponent(sample.component)),
      errorComponent: tui.defaultExport(
        tui.require('tui/components/errors/ErrorPageRender')
      ),
    })
  );
});

export function getSamples() {
  return (
    tui
      // eslint-disable-next-line tui/no-tui-internal
      ._getLoadedComponentModules('samples')
      .filter(x => x.startsWith(prefix))
      .map(x => {
        const i = x.indexOf('/', prefix.length);
        const tuiComponent = x.slice(prefix.length, i);
        return {
          component: x,
          tuiComponent,
          text: x.slice(i + 1),
          key: x.slice(prefix.length),
        };
      })
  );
}
