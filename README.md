NucleosAntiSpamBundle
=====================
[![Latest Stable Version](https://poser.pugx.org/nucleos/antispam-bundle/v/stable)](https://packagist.org/packages/nucleos/antispam-bundle)
[![Latest Unstable Version](https://poser.pugx.org/nucleos/antispam-bundle/v/unstable)](https://packagist.org/packages/nucleos/antispam-bundle)
[![License](https://poser.pugx.org/nucleos/antispam-bundle/license)](LICENSE.md)

[![Total Downloads](https://poser.pugx.org/nucleos/antispam-bundle/downloads)](https://packagist.org/packages/nucleos/antispam-bundle)
[![Monthly Downloads](https://poser.pugx.org/nucleos/antispam-bundle/d/monthly)](https://packagist.org/packages/nucleos/antispam-bundle)
[![Daily Downloads](https://poser.pugx.org/nucleos/antispam-bundle/d/daily)](https://packagist.org/packages/nucleos/antispam-bundle)

[![Continuous Integration](https://github.com/nucleos/NucleosAntiSpamBundle/workflows/Continuous%20Integration/badge.svg)](https://github.com/nucleos/NucleosAntiSpamBundle/actions)
[![Code Coverage](https://codecov.io/gh/nucleos/NucleosAntiSpamBundle/branch/main/graph/badge.svg)](https://codecov.io/gh/nucleos/NucleosAntiSpamBundle)
[![Type Coverage](https://shepherd.dev/github/nucleos/NucleosAntiSpamBundle/coverage.svg)](https://shepherd.dev/github/nucleos/NucleosAntiSpamBundle)

This bundle provides some basic features to reduce spam in symfony.

## Installation

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```
composer require nucleos/antispam-bundle
```

### Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Nucleos\AntiSpamBundle\NucleosAntiSpamBundle::class => ['all' => true],
];
```

## Usage

### Form based protection

Create a form on the fly:

```php
$this->createForm(CustomFormType:class, null, array(
    // Time protection
    'antispam_time'     => true,
    'antispam_time_min' => 10,
    'antispam_time_max' => 60,

    // Honeypot protection
    'antispam_honeypot'       => true,
    'antispam_honeypot_class' => 'hide-me',
    'antispam_honeypot_field' => 'email-repeat',
))
```

### Twig text protection

```twig
{# Replace plain text #}
{{ text|antispam }}

{# Replace rich text mails #}
{{ htmlText|antispam(true) }}

```

If you want a JavaScript decoding for the encoded mails, you should use the `AntiSpam.js` library:

```javascript
document.addEventListener('DOMContentLoaded', () => {
  new AntiSpam('.custom_class');
});

```

It is recommended to use [webpack](https://webpack.js.org/) / [webpack-encore](https://github.com/symfony/webpack-encore)
to include the JavaScript library in your page. This file is located in the `assets` folder.

### Global protection

Add protection to all forms using the configuration:

```yaml
# config/packages/nucleos_antispam.yaml

nucleos_antispam:
    # Time protection
    time:
        global: true

    # Honeypot protection
    honeypot:
        global: true
```

### Configure the Bundle

Create a configuration file called `nucleos_antispam.yaml`:

```yaml
# config/packages/nucleos_antispam.yaml

nucleos_antispam:
    # Twig mail filter
    twig:
        mail:
            css_class: 'custom_class'
            at_text:   [ '[AT]', '(AT)', '[ÄT]' ]
            dot_text:  [ '[DOT]', '(DOT)', '[.]' ]

    # Time protection
    time:
        min: 5
        max: 3600
        global: false

    # Honeypot protection
    honeypot:
        field: 'email_address'
        class: 'hidden'
        global: false
        provider: 'nucleos_antispam.provider.session'
```

### Assets

It is recommended to use [webpack](https://webpack.js.org/) / [webpack-encore](https://github.com/symfony/webpack-encore)
to include the `AntiSpam.js` file in your page. This file is located in the `assets` folder.

## License

This bundle is under the [MIT license](LICENSE.md).
