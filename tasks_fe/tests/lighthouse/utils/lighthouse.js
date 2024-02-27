// @ts-check

import Lighthouse from 'lighthouse';
import { screenEmulationMetrics, userAgents } from 'lighthouse/core/config/constants.js';
import fs from 'node:fs';

/**
 * @param {import('puppeteer-core').Page} page
 * @param {string} url
 * @param {string} filename
 * @param {boolean} mobile
 * @returns {Promise<Map<string, [number, string]>>}
 */
export const lightHouse = async (page, url, filename, mobile) => {
    const result = await Lighthouse(
        url,
        {
            output: 'html',
            logLevel: 'silent',
            disableFullPageScreenshot: true,
            onlyCategories: ['performance', 'accessibility', 'best-practices', 'seo'],
            maxWaitForLoad: 3000,
            skipAudits: []
        },
        {
            extends: 'lighthouse:default',
            settings: {
                throttlingMethod: 'simulate',
                throttling: {
                    rttMs: 1,
                    throughputKbps: 100 * 1024,
                    requestLatencyMs: 1,
                    downloadThroughputKbps: 100 * 1024,
                    uploadThroughputKbps: 50 * 1024,
                    cpuSlowdownMultiplier: 0
                },
                formFactor: mobile ? 'mobile' : 'desktop',
                screenEmulation: mobile ? screenEmulationMetrics.mobile : screenEmulationMetrics.desktop,
                emulatedUserAgent: mobile ? userAgents.mobile : userAgents.desktop
            }
        },
        page
    );
    fs.writeFileSync(filename, String(result?.report));

    const output = new Map();
    for (const category of Object.values(result?.lhr.categories ?? {})) {
        output.set(category.id, [category.score, String((category.score ?? 0) * 100)]);
    }

    for (const audit of Object.values(result?.lhr.audits ?? {})) {
        if (audit.displayValue !== undefined && audit.displayValue !== '') {
            output.set(audit.id, [audit.score, audit.displayValue]);
        }
    }

    return output;
};

/**
 * @param {Map<string, [number, string]>} desktop
 * @param {Map<string, [number, string]>} mobile
 * @returns string[]
 */
export const formatOutput = (desktop, mobile) => {
    const output = [];
    for (const key in Object.fromEntries(desktop)) {
        const desktopValue = desktop.get(key) ?? [0, ''];
        const mobileValue = mobile.get(key) ?? [0, ''];
        const id = key.padEnd(35);

        output.push(
            `${id} ${getEmoji(desktopValue[0])} ${desktopValue[1]} ${getEmoji(mobileValue[0])} ${mobileValue[1]}`
        );
    }

    return output;
};

/**
 * @param {number} score 0 to 1
 * @returns {string}
 */
const getEmoji = (score) => {
    if (score >= 0.9) {
        return '🟢';
    }
    if (score >= 0.5) {
        return '🟧';
    }
    return '🔺';
};
