AntiSpamBundle
==============
[![Latest Stable Version](https://poser.pugx.org/core23/antispam-bundle/v/stable)](https://packagist.org/packages/core23/antispam-bundle)
[![Latest Unstable Version](https://poser.pugx.org/core23/antispam-bundle/v/unstable)](https://packagist.org/packages/core23/antispam-bundle)
[![License](https://poser.pugx.org/core23/antispam-bundle/license)](LICENSE.md)

[![Total Downloads](https://poser.pugx.org/core23/antispam-bundle/downloads)](https://packagist.org/packages/core23/antispam-bundle)
[![Monthly Downloads](https://poser.pugx.org/core23/antispam-bundle/d/monthly)](https://packagist.org/packages/core23/antispam-bundle)
[![Daily Downloads](https://poser.pugx.org/core23/antispam-bundle/d/daily)](https://packagist.org/packages/core23/antispam-bundle)

[![Continuous Integration](https://github.com/core23/AntiSpamBundle/workflows/Continuous%20Integration/badge.svg)](https://github.com/core23/AntiSpamBundle/actions)
[![Code Coverage](https://codecov.io/gh/core23/AntiSpamBundle/branch/master/graph/badge.svg)](https://codecov.io/gh/core23/AntiSpamBundle)

This bundle provides some basic features to reduce spam in symfony.

## Installation

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```
composer require core23/antispam-bundle
```

### Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Core23\AntiSpamBundle\Core23AntiSpamBundle::class => ['all' => true],
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
));
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
to include the JavaScript library in your page. These file is located in the `assets` folder.

### Global protection

Add protection to all forms using the configuration:

```yaml
# config/packages/core23_antispam.yaml

core23_antispam:
    # Time protection
    time:
        global: true

    # Honeypot protection
    honeypot:
        global: true
```

### Configure the Bundle

Create a configuration file called `core23_antispam.yaml`:

```yaml
# config/packages/core23_antispam.yaml

core23_antispam:
    # Twig mail filter
    twig:
        mail:
            css_class: 'custom_class'
            at_text:   [ '[DOT]', '(DOT)', '[.]' ]
            dot_text:  [ '[AT]', '(AT)', '[ÄT]' ]

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
        provider: 'core23_antispam.provider.session'
```

### Assets

It is recommended to use [webpack](https://webpack.js.org/) / [webpack-encore](https://github.com/symfony/webpack-encore)
to include the `AntiSpam.js` file in your page. These file is located in the `assets` folder.

## License

This bundle is under the [MIT license](LICENSE.md).
