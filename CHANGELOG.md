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
- The home block no longer breaks the whole page on PrestaShop 9: the template called
  `Tools::displayPrice()`, which was removed in 9.0. Prices are now formatted with the shop locale.
- The order button is shown again: it only appeared when hiding prices from guests was enabled, so a
  shop without `m4ploginaccess` had a form with no way to submit it.
- The configuration page no longer returns a 500 on PrestaShop 9: it called `addJquery()`, which the
  back-office controller no longer exposes. jQuery is always present in the back office anyway, and
  the remaining asset calls are guarded so the module still works on 1.7 and 8.x.

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
