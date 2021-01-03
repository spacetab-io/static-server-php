Static server
-------------

[![CircleCI](https://circleci.com/gh/spacetab-io/static-server-php/tree/master.svg?style=svg)](https://circleci.com/gh/spacetab-io/static-server-php/tree/master)
[![codecov](https://codecov.io/gh/spacetab-io/static-server-php/branch/master/graph/badge.svg)](https://codecov.io/gh/spacetab-io/static-server-php)

Server dynamically configures web application that requires a static configuration without needs to rebuild it.
This server is a simple wrapper for nginx or an any web server.

Compatible with: Vue, React, Angular, etc.

## Table of contents

* [Features](#features)
* [Usage](#usage)
    + [Dockerfile sample](#dockerfile-sample)
    + [Local integration with modern web apps](#local-integration-with-modern-web-apps)
    + [Command line interface](#command-line-interface)
    + [Prerender & SEO](#prerender--seo)
    + [Headers](#headers)
    + [Link header](#link-header)
* [How it works](#how-it-works)
* [Compression](#compression)
* [Environment variables](#environment-variables)
* [Default files in the root directory](#default-files-in-the-root-directory)
* [Tests](#tests)
* [License](#license)

## Features

* Special created for modern web app's.
* Secure headers by default.
* If backend app will be hacked, the hacker may write a letter to us, because email address injected to head section of index (console message) :)
* Corporate config standard supported by default and injected too.
* Brotli-compression (Gzip used as fallback for outdated browsers). Enabled by default. [More](#Compression).
* Deny all `robots.txt` by default.
* Hot reload

## Usage

### Dockerfile sample
```Dockerfile
FROM spacetabio/static-server-php:4.0.0

ARG VCS_SHA1
ARG STAGE

# dist & frontend yaml configuration 
COPY dist/ /app
COPY ./configuration /app/configuration
```

### Local integration with modern web apps

Full example can be founded [here](https://github.com/spacetab-io/configuration-js#how-to-usage-library-with-spa-apps).

### Command line interface

CLI usage implies 2 commands for usage:

1) Start server:
```bash
server run
```

2) Reload

After editing files or configuration you can reload the server without restart master process.

```bash
server reload
```

3) Dump loaded configuration:
```bash
server dump
```

Comments about server configuration can be found [here](./configuration/defaults).

### Prerender & SEO

If you would like to optimize web-site for search-indexing bots, 
server supports integration with this prerender in the box:
  
https://github.com/spacetab-io/prerender-go

### Headers

By default, will be added following headers to response:

```http
Pragma: public
X-XSS-Protection: 1; mode=block
X-Frame-Options: SAMEORIGIN
X-Content-Type: nosniff
X-Content-Type-Options: nosniff
X-Ua-Compatible: IE=edge
Referrer-Policy: no-referrer
```

### Link header

As new feature since `1.1.4` version you able to use `Link` header
for server configuration.

* How it use for `<link rel=preload>` requirements (lighthouse), – https://w3c.github.io/preload/#example-3 , https://w3c.github.io/preload/#example-6
* Specification https://tools.ietf.org/html/rfc5988#section-5

Example:

```yaml
dev:
  server:
    headers:
      link:
        - value: </app/style.css>; rel=preload; as=style; nopush
        - value:
            - <https://example.com/app/script.js>
            - rel=preload
            - as=script
```

## How it works?

Server reads files from `dist`, then modifying `index.html` on the fly 
and append a configuration before first `<script>` tag will be founded.
Also, available [insert config before first tag](./configuration/defaults/___server.yaml#L8) to `<head>` 
section (but it blocks page painting).

Injected config file (`__config.js`) has following content:

```js
window.__stage = 'local';
window.__config = JSON.parse('{}' /* frontend config from yaml here */);
window.__vcs = '%s';
```

Also, will be injected `<link>` tag with `rel=preload`. [More](https://developers.google.com/web/tools/lighthouse/audits/preload).

Then, starts the nginx server.

## Compression

By default, server use Brotli-compression algorithm developed by [Google Inc](https://en.wikipedia.org/wiki/Brotli). <br>
If a more [effective](https://medium.com/oyotech/how-brotli-compression-gave-us-37-latency-improvement-14d41e50fee4) 
(up to 21%) lossless compression algorithm than gzip and deflate.<br>
<br>
For the present, his support all modern browsers:
https://caniuse.com/#search=Brotli

## Environment variables

Server read the following environment variables:

```bash
CONFIG_PATH – server and frontend configuration.
STAGE – server and frontend mode to start: prod/dev/local
VCS_SHA1 – build commit sha1 for debug
```

## Default files in the root directory

By default, root directory is `/app`. It special for container-based usage. <br>
Root directory contains following files from scratch:
```
.
├── favicon.ico
├── index.html
└── robots.txt
```

* `favicon.ico` – is a transparent `.ico` file (for prevent error logs).
* `index.html` – simple index file with hello message.
* `robots.txt` – the file which blocks all robots by default.
* `/.well-known/security.txt` – https://securitytxt.org/

Each file can be replaced.

## Tests

Install packages for development using composer and just run following command:

```
vendor/bin/phpunit
```

## License

The MIT License

Copyright © 2021 spacetab.io, Inc. https://spacetab.io

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

