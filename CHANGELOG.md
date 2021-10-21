# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.1.14]

### Fixed

Support `psr/log` v2/v3 [#379](https://github.com/atymic/twitter/pull/379)

## [3.1.13]

### Fixed

Guzzle Oauth Subscriber PSR7 2.x compat [#373](https://github.com/atymic/twitter/pull/373)

## [3.1.12]

### Fixed

Re-add account activity trait [#372](https://github.com/atymic/twitter/pull/372)

## [3.1.11]

### Fixed

Require guzzlehttp/psr7 v1.x [#370](https://github.com/atymic/twitter/pull/370)

## [3.1.10]

### Added

Expose new tweet count endpoints [#366](https://github.com/atymic/twitter/pull/366)

## [3.1.9]

### Added

Add ability to get last response (for checking headers such as rate limits) [#359](https://github.com/atymic/twitter/pull/359)

## [3.1.8]

### Fixed

Prior to this, hot-swapping methods (`forApiV1()` and `forApiV2()`) did not actually swap service implementations. #357

## [3.1.6]

### Fixed

Unable to switch API version on service (#356)

## [3.1.5]

### Fixed

Ensure `Twitter` service acts as singleton [#352](https://github.com/atymic/twitter/pull/352)

## [3.1.4]

### Fixed

Fixed incorrect import in `AuthTrait` [#347](https://github.com/atymic/twitter/pull/347)


## [3.1.3]

### Fixed

Fixed `getOembed` querying the wrong hostname/endpoint [#345](https://github.com/atymic/twitter/pull/345)


## [3.1.2]

### Fixed

Fixed `directQuery` param order [#343](https://github.com/atymic/twitter/pull/343)


## [3.1.1]

### Fixed

Fixed `getOembed` url containing invalid full stop [8d9b15](https://github.com/atymic/twitter/commit/8d9b15dcdb88e21fc66c8d7bc582e4839d814dc0)

## [3.1.0]

### Added

- Twitter API v2 Support [#337](https://github.com/atymic/twitter/pull/337)

## [3.0.0]

See [UPGRADE.md](./UPGRADE.md) for the upgrade guide.
