# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[semantic versioning](https://semver.org/).

## [Unreleased]

### Added
- MIT license, `LICENSE`, `composer.json` and the `config.xml` manifest (only the Polish one existed).
- English and Polish translation catalogues (`Modules.M4pquickorder.Admin` and `.Shop`).
- `index.php` guards in every directory.

### Fixed
- Runs on PrestaShop 9: prices are formatted with the shop locale and the back-office asset calls
  are guarded, so the module works on 1.7, 8.x and 9.
- The order button is shown whatever the price-visibility setting, so the form can always be
  submitted without `m4ploginaccess`.

### Changed
- Back-office and template strings now go through the new translation system instead of
  `$this->l()` and `{l s=… mod=…}`.
- The order form is protected with PrestaShop's own customer token instead of a value derived from
  the shop host, which was identical for every visitor and could be reproduced by anyone.
- Declared PrestaShop compatibility from 1.7.6.

## [1.0.0] — 2025-10-08

### Added
- First release: quick order block on the home page with selected products, combinations and stock
  checks.
