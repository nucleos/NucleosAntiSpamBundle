# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.3.1 - 2021-12-08


-----

### Release Notes for [2.3.1](https://github.com/nucleos/NucleosAntiSpamBundle/milestone/5)

2.3.x bugfix release (patch)

### 2.3.1

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug

 - [412: Fix deprecation warning](https://github.com/nucleos/NucleosAntiSpamBundle/pull/412) thanks to @core23

## 2.3.0 - 2021-12-07


-----

### Release Notes for [2.3.0](https://github.com/nucleos/NucleosAntiSpamBundle/milestone/3)

Feature release (minor)

### 2.3.0

- Total issues resolved: **0**
- Total pull requests resolved: **6**
- Total contributors: **1**

#### dependency

 - [400: Add symfony 6 support](https://github.com/nucleos/NucleosAntiSpamBundle/pull/400) thanks to @core23
 - [397: Drop symfony 4 support](https://github.com/nucleos/NucleosAntiSpamBundle/pull/397) thanks to @core23
 - [390: Drop PHP 7 support](https://github.com/nucleos/NucleosAntiSpamBundle/pull/390) thanks to @core23

#### Enhancement

 - [399: Fix session deprecations](https://github.com/nucleos/NucleosAntiSpamBundle/pull/399) thanks to @core23
 - [398: Drop node-sass](https://github.com/nucleos/NucleosAntiSpamBundle/pull/398) thanks to @core23
 - [396: Update tools and use make to run them](https://github.com/nucleos/NucleosAntiSpamBundle/pull/396) thanks to @core23

## 2.2.0 - 2021-02-07

-----


-----

### Release Notes for [2.2.0](https://github.com/nucleos/NucleosAntiSpamBundle/milestone/1)



### 2.2.0

- Total issues resolved: **0**
- Total pull requests resolved: **4**
- Total contributors: **2**

#### dependency

 - [137: Add support for PHP 8](https://github.com/nucleos/NucleosAntiSpamBundle/pull/137) thanks to @core23
 - [66: Drop support for PHP 7.2](https://github.com/nucleos/NucleosAntiSpamBundle/pull/66) thanks to @core23

#### Documentation

 - [91: Rework README](https://github.com/nucleos/NucleosAntiSpamBundle/pull/91) thanks to @ThomasLandauer

#### Enhancement

 - [60: Move configuration to PHP](https://github.com/nucleos/NucleosAntiSpamBundle/pull/60) thanks to @core23

## 2.1.0

### Changes

### 🚀 Features

- Add combined assets [@core23]

## 2.0.0

### Changed

* Renamed namespace `Core23\AntiSpamBundle` to `Nucleos\AntiSpamBundle` after move to [@nucleos]

  Run

  ```
  $ composer remove core23/antiSpam-bundle
  ```

  and

  ```
  $ composer require nucleos/antiSpam-bundle
  ```

  to update.

  Run

  ```
  $ find . -type f -exec sed -i '.bak' 's/Core23\\AntiSpamBundle/Nucleos\\AntiSpamBundle/g' {} \;
  ```

  to replace occurrences of `Core23\AntiSpamBundle` with `Nucleos\AntiSpamBundle`.

  Run

  ```
  $ find -type f -name '*.bak' -delete
  ```

  to delete backup files created in the previous step.

## 1.3.0

### Changes

- Add missing strict file header [@core23] ([#34])

### 📦 Dependencies

- Add support for symfony 5 [@core23] ([#25])
- Drop support for symfony < 4.2 [@core23] ([#31])

[#34]: https://github.com/nucleos/NucleosAntiSpamBundle/pull/34
[#31]: https://github.com/nucleos/NucleosAntiSpamBundle/pull/31
[#25]: https://github.com/nucleos/NucleosAntiSpamBundle/pull/25
[@nucleos]: https://github.com/nucleos
[@core23]: https://github.com/core23
