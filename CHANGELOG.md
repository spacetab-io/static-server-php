## [4.1.0] - 2022-09-05

### Added

None.

### Changed

* Upgrades composer packages.
* Upgrades PHP version to `8.1`

### Removed

None. 

### Fixed

* Fixes prerender host proxy (add nginx option `proxy_ssl_server_name on`).
* User a human-readable header parser error message when user wants to use a `:` character in header values.

## [4.0.0] - 2020-01-03

### Added

None.

### Changed

* Upgrades Composer to `2.0` version.
* Upgrades NGINX to `1.19.6` version.
* Upgrades NGINX Brotli Module to `1.0.9` version.
* Upgrades PHP to `8.0` version.
* Upgrades PHPUNIT configuration file to new schema.
* Update LICENSE and README year from 2020 to 2021 🎇
* Completely rewritten sources code and now it became more simplified.
* PHP performs the role of "template engine" to generate NGINX configuration 
  and read/forward the `Spacetab` configuration. Does not control the NGINX process.
* 99% backward compatibility.
* Using asynchronous non-blocking i/o.
* Web-server configuration files separated to easily configuring.
* Improved error handling.

### Removed

* Removed `box-project/box` from project dependencies. Now uses separated docker image to build a phar-archive.
* Removed code which is responsible for dist-code modification. Because is too hard for understanding and this project 
  is too small for such an abstraction layer. Now dist-code modification is enabled by default and no option to 
  disable it.
* A lot of many composer packages that no need anymore... 

### Fixed

* A potential fix of bug when web-server spawn zombie processes.

## [3.2.1] - 2020-06-03

### Added

None.

### Changed

None.

### Removed

None.

### Fixed

* Nginx `daemon off;` option as default.

## [3.2.0] - 2020-05-22

### Added

* Prerender cdn file postfix that is adding through config param.

### Changed

None.

### Removed

None.

## [3.1.0] - 2020-05-21

### Added

* Server preserves query params in prerender proxy pass.

### Changed

None.

### Removed

None.

## [3.0.0] - 2020-05-15

### Added

None.

### Changed

* For now server does not supports `prerender.io` (because it unstable) and moving to own software: https://github.com/spacetab-io/prerender-go
* Changed all snake_case config keys to camelCase and renamed a little.
* Caches for `index.html` and `__config.js` reduced to `2m` and other caches reduced to `30m`.
* Restructured and simplified documentation.

### Removed

None.

## [2.0.3] - 2020-01-27

### Added

- Added new before start check, when prerender host (option `server.prerender.host`) is fill and has a valid url address.

### Changed

None.

### Removed

None.

## [2.0.2] - 2020-01-27

### Added

- Option `server.modify.sha1_in_config`. Enable of disable `__config` name with passed VCS SHA1 from CI.

### Changed

None.

### Removed

None.

## [2.0.1] - 2020-01-27

Prerender option now more configurable and supports many of sets
cloud-services or local instances.

### Added

- Option `server.prerender.resolver`. This is a nginx "resolver" option to force DNS resolution and prevent caching of IPs.
- Option `server.prerender.headers` (array of headers). This is an authorization headers (or others), format: key - $headerName, value - $headerValue.

### Changed

- Option `server.prerender.url` accepts url with schema and will be passed to `proxy_pass` option as is.

### Removed

- Option `server.prerender.token`. Use `server.prerender.headers` option.

## [2.0.0] - 2020-01-09

Released new version of SPA-webserver.
Full backward compatibility with previous 1.0.0 version except
compression options (it is not configurable more). From this version
changelog will be started.

