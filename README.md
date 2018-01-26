What is AntiSpamBundle?
=======================
[![Latest Stable Version](https://poser.pugx.org/core23/antispam-bundle/v/stable)](https://packagist.org/packages/core23/antispam-bundle)
[![Latest Unstable Version](https://poser.pugx.org/core23/antispam-bundle/v/unstable)](https://packagist.org/packages/core23/antispam-bundle)
[![License](https://poser.pugx.org/core23/antispam-bundle/license)](https://packagist.org/packages/core23/antispam-bundle)

[![Build Status](https://travis-ci.org/core23/AntiSpamBundle.svg)](https://travis-ci.org/core23/AntiSpamBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/core23/AntiSpamBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/core23/AntiSpamBundle)
[![Coverage Status](https://coveralls.io/repos/core23/AntiSpamBundle/badge.svg)](https://coveralls.io/r/core23/AntiSpamBundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cf5f2ca9-1126-4086-8443-a0351c307d6d/mini.png)](https://insight.sensiolabs.com/projects/cf5f2ca9-1126-4086-8443-a0351c307d6d)

[![Donate to this project using Flattr](https://img.shields.io/badge/flattr-donate-yellow.svg)](https://flattr.com/profile/core23)
[![Donate to this project using PayPal](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://paypal.me/gripp)

This bundle provides some basic features to reduce spam in forms.

### Installation

```
composer require core23/antispam-bundle
```

### Enabling the bundle

```php
    // config/bundles.php

    return [
        // ...
        Core23\AntiSpamBundle\Core23AntiSpamBundle::class => ['all' => true],
    ];
```

Define the API credentials in your configuration.

```yml
    # config.yml

    core23_antispam:
        # Timed protection
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
