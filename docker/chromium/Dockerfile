FROM alpine:3.19

RUN apk --no-cache upgrade \
    && apk --no-cache add chromium-swiftshader \
    && adduser -u 1000 -h /var/www -D -S -G www-data www-data

USER www-data:www-data

WORKDIR /var/www

EXPOSE 9222

HEALTHCHECK none

# see https://github.com/puppeteer/puppeteer/blob/a87a03587949406dfda22377c48a22f915c760ef/packages/puppeteer-core/src/node/ChromeLauncher.ts
# see https://github.com/GoogleChrome/chrome-launcher/blob/main/docs/chrome-flags-for-tools.md
CMD chromium-browser --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --disable-gpu \
    --no-sandbox --disable-setuid-sandbox --disable-extensions --disable-dev-shm-usage \
    --disable-sync --no-first-run --disable-font-subpixel-positioning --enable-font-antialiasing=false \
    --force-color-profile=generic-rgb --use-gl=swiftshader --password-store=basic --use-mock-keychain \
    --no-default-browser-check --mute-audio --hide-scrollbars \
    --disable-features=Translate,AcceptCHFrame,MediaRouter,OptimizationHints,ProcessPerSiteUpToMainFrameThreshold,AutofillServerCommunication,OptimizationHints \
    --allow-pre-commit-input --disable-background-networking --disable-background-timer-throttling \
    --disable-backgrounding-occluded-windows --disable-breakpad --disable-client-side-phishing-detection \
    --disable-component-extensions-with-background-pages --disable-component-update --disable-default-apps \
    --disable-field-trial-config --disable-hang-monitor --disable-ipc-flooding-protection \
    --disable-renderer-backgrounding --disable-search-engine-choice-screen --enable-automation \
    --metrics-recording-only --disable-domain-reliability --no-pings --ignore-certificate-errors
