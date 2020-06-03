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
compression options (it not configurable more). From this version
changelog will be started.

