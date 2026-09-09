FROM node:20-slim

WORKDIR /tests

# Playwright Chromium system deps
RUN apt-get update -qq && apt-get install -y --no-install-recommends \
    wget curl gnupg ca-certificates \
    fonts-liberation libasound2 libatk-bridge2.0-0 libatk1.0-0 libc6 libcairo2 \
    libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc-s1 libglib2.0-0 \
    libgtk-3-0 libnspr4 libnss3 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 \
    libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 \
    libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 lsb-release xdg-utils \
    && rm -rf /var/lib/apt/lists/*

# Install project deps (assumes node_modules already populated by host setup)
COPY package.json ./
COPY node_modules ./node_modules
COPY cucumber.js ./
COPY tsconfig.json ./
COPY features ./features
COPY step_definitions ./step_definitions
COPY support ./support

# Reuse host's Playwright browser cache if mounted
ENV PLAYWRIGHT_BROWSERS_PATH=/root/.cache/ms-playwright

CMD ["node_modules/.bin/cucumber-js", "--format", "@cucumber/pretty-formatter"]
