// @ts-check

export class DebugController {
    /**
     * @param {import('../framework/App.js').App} app
     */
    constructor(app) {
        this.app = app;
    }

    /**
     * @param {{totalJSHeapSize: number, usedJSHeapSize: number} | undefined} memory
     */
    updateMemory(memory) {
        if (memory === undefined) {
            return;
        }

        const total = (memory.totalJSHeapSize / 1048576).toFixed(2);
        const used = (memory.usedJSHeapSize / 1048576).toFixed(2);

        const pageStore = this.app.pageStore;
        pageStore.debugJsHeap = `JSHeapSize used ${used} mb, total ${total} mb`;
    }

    /**
     * @param {{name: string, delta: number, value: number}} metric
     */
    updateWebVitals(metric) {
        const pageStore = this.app.pageStore;
        pageStore.debugWebVitals += `${metric.name} ${metric.value.toFixed(1)} Δ${metric.delta.toFixed(0)}, `;
    }
}
