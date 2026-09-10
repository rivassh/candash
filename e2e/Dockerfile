FROM node:20-slim

WORKDIR /tests

# Install project deps (use host node_modules which has all packages)
COPY package.json package-lock.json ./
COPY node_modules ./node_modules
COPY cucumber.js ./
COPY tsconfig.json ./
COPY features ./features
COPY step_definitions ./step_definitions
COPY support ./support

# Reuse host's Playwright browser cache if mounted
ENV PLAYWRIGHT_BROWSERS_PATH=/root/.cache/ms-playwright

CMD ["node_modules/.bin/cucumber-js", "--format", "@cucumber/pretty-formatter"]
