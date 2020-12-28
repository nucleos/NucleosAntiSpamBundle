# 2.1.0

## Changes

## 🚀 Features

- Add combined assets [@core23]

# 2.0.0

## Changed

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

# 1.3.0

## Changes

- Add missing strict file header [@core23] ([#34])

## 📦 Dependencies

- Add support for symfony 5 [@core23] ([#25])
- Drop support for symfony < 4.2 [@core23] ([#31])

[#34]: https://github.com/nucleos/NucleosAntiSpamBundle/pull/34
[#31]: https://github.com/nucleos/NucleosAntiSpamBundle/pull/31
[#25]: https://github.com/nucleos/NucleosAntiSpamBundle/pull/25
[@nucleos]: https://github.com/nucleos
[@core23]: https://github.com/core23
