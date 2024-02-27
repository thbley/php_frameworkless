declare var PROD: boolean;

interface Performance {
    memory?: {
        readonly totalJSHeapSize: number;
        readonly usedJSHeapSize: number;
    };
}

interface PerformanceEntry {
    readonly transferSize: number;
    readonly decodedBodySize: number;
}
