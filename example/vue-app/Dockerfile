FROM node:12-stretch
WORKDIR /usr/app
COPY . /usr/app

ARG STAGE

RUN npm ci --silent \
    && npm run build

FROM spacetabio/static-server-php:3.0.0

COPY --from=0 /usr/app/dist /app
COPY --from=0 /usr/app/configuration /app/configuration

ARG VCS_SHA1
ARG STAGE
ENV STAGE $STAGE
ENV VCS_SHA1 $VCS_SHA1
