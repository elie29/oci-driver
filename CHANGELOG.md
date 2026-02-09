# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## [2.0+] - PHP 8.2+ & Modern Tooling Era

### V2.0.1 - 2026-02-09

- [#52](https://github.com/elie29/oci-driver/issues/52) Compact and restructure CHANGELOG for improved readability
- [#53](https://github.com/elie29/oci-driver/issues/53) Update PHP Composer dependencies (no breaking changes)

### V2.0.0 - 2025-12-13

- [#50](https://github.com/elie29/oci-driver/issues/50) Update project to support PHP 8.2+

---

## [1.0.x] - PHP 7.x Foundation Era (2018-2021)

### V1.0.20 - 2021-06-21

- [#47](https://github.com/elie29/oci-driver/issues/47) Accept a SELECT type in Join

### V1.0.19 - 2021-01-18

- [#46](https://github.com/elie29/oci-driver/issues/46) No need to check twice for rollback
- **Fixed:** [#4](https://github.com/elie29/oci-driver/issues/4) implode(): Passing glue string after array is deprecated. Swap the parameters

### V1.0.18 - 2019-11-07

- **Added:** [#45](https://github.com/elie29/oci-driver/issues/45) Add distinct to the Select
- [#44](https://github.com/elie29/oci-driver/issues/44) Enhance error log

### V1.0.17 - 2019-09-29

- [#43](https://github.com/elie29/oci-driver/issues/43) Update composer

### V1.0.16 - 2019-09-17

- **Added:** [#42](https://github.com/elie29/oci-driver/issues/42) Add insert_select

### V1.0.15 - 2019-05-29

- [#40](https://github.com/elie29/oci-driver/issues/40) Change having to be alias for andHaving
- **Fixed:** [#41](https://github.com/elie29/oci-driver/issues/41) Enhance binding when comma is a decimal separator

### V1.0.14 - 2019-05-07

- **Added:** [#39](https://github.com/elie29/oci-driver/issues/39) Add returning into to insert/update

### V1.0.13 - 2019-04-18

- [#38](https://github.com/elie29/oci-driver/issues/38) Update set in query builder accepts only string value

### V1.0.12 - 2019-04-10

- **Added:** [#37](https://github.com/elie29/oci-driver/issues/37) Refactor SessionInit

### V1.0.11 - 2019-02-22

- No changes

### V1.0.10 - 2019-02-05

- [#36](https://github.com/elie29/oci-driver/issues/36) Check connection before commit/rollback

### V1.0.9 - 2019-01-29

- **Added:** [#35](https://github.com/elie29/oci-driver/issues/35) Provide unionWith

### V1.0.8 - 2018-12-18

- **Added:** [#34](https://github.com/elie29/oci-driver/issues/34) Ignore the join when the same table/alias is used

### V1.0.7 - 2018-12-06

- **Added:** [#31](https://github.com/elie29/oci-driver/issues/31) Add fetch by column
- [#32](https://github.com/elie29/oci-driver/issues/32) Oracle and IN limit 1000 units
- [#33](https://github.com/elie29/oci-driver/issues/33) andWhere method enhancement

### V1.0.6 - 2018-11-22

- [#30](https://github.com/elie29/oci-driver/issues/30) Change visibility
- [#29](https://github.com/elie29/oci-driver/issues/29) Comment changes

### V1.0.5 - 2018-11-21

- **Added:** [#28](https://github.com/elie29/oci-driver/issues/28) Change getConnexion
- [#26](https://github.com/elie29/oci-driver/issues/26) Code refactoring
- [#27](https://github.com/elie29/oci-driver/issues/27) Indicate visibility of constants

### V1.0.4 - 2018-10-24

- [#25](https://github.com/elie29/oci-driver/issues/25) Call immediate function with andHaving
- [#24](https://github.com/elie29/oci-driver/issues/24) Call immediate function with andWhere

### V1.0.3 - 2018-10-23

- **Added:** [#23](https://github.com/elie29/oci-driver/issues/23) Create a light query builder

### V1.0.2 - 2018-10-16

- [#22](https://github.com/elie29/oci-driver/issues/22) Delete trigger_error

### V1.0.1 - 2018-10-08

- **Added:** [#21](https://github.com/elie29/oci-driver/issues/21) Include PHP Static Analysis Tool

### V1.0.0 - 2018-10-07

- **Added:**
  - [#1](https://github.com/elie29/oci-driver/issues/1) Add documentation to readme
  - [#17](https://github.com/elie29/oci-driver/issues/17) Create a Factory in Helper folder
- **Changed:**
  - [#5](https://github.com/elie29/oci-driver/issues/5) Parameter in debugger should be nullable
  - [#6](https://github.com/elie29/oci-driver/issues/6) Change Debugger signature
- **Fixed:**
  - [#3](https://github.com/elie29/oci-driver/issues/3) Correct debug interface
  - [#4](https://github.com/elie29/oci-driver/issues/4) Change session init property name
