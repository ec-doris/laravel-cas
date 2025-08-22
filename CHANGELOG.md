# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.0 - 2025-08-22

### Changed

- **BREAKING**: Forked from `ecphp/laravel-cas` to `brownrl/laravel-cas`
- Updated package name in composer.json
- Updated repository URLs and documentation
- Updated copyright attribution
- Fixed missing `jsonSerialize()` method in `Laravel` config class
- Removed PHP conventions and code style dependencies

### Fixed

- Fixed "Premature end of PHP process" test errors by implementing missing `JsonSerializable::jsonSerialize()` method
- Updated `ArrayAccess` methods to work with underlying Properties class structure

### Removed

- Removed `ecphp/php-conventions` and related code style dependencies

## 0.0.2

### Merged

- tests: init 

### Commits

- docs: init `CHANGELOG` file and configuration (auto-changelog) 
- chore: add `phpunit` in Grumphp tasks 
- chore: normalize `composer.json` 
- chore: update LICENSE date 
- chore: autofix code style 
- chore: raise minimum PHP version to 8.1 
- feat: add package auto-discovery 
- chore: make slight modifications 

## 0.0.1 - 2024-04-12

### Merged

- update config for INVALID_TICKET issue 
- Laravel 10 support 
- docs: minor `README` update 
- build(deps): bump cachix/install-nix-action from 18 to 19 
- style: run `prettier` 
- Update README.md 
- fix: update Laravel version 

### Commits

- build(deps): bump cachix/install-nix-action from 19 to 20 
- Apply 1 suggestion(s) to 1 file(s) 
- Initial commit 
