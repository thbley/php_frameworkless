FROM mcr.microsoft.com/playwright:jammy

# default playwright image does not contain ms-edge
RUN npx -y playwright install msedge \
    && apt-get clean \
    && rm -rf /tmp/* /var/lib/apt/lists/* /var/cache/apt/archives/* ~/.npm/ \
    && ln -s /ms-playwright/chromium-*/chrome-linux/chrome /usr/local/sbin/chrome

USER pwuser:pwuser

HEALTHCHECK none

ENV HOME=/tmp
