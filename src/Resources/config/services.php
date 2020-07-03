<?php

/*
 * This file is part of the NucleosAntiSpamBundle package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Nucleos\AntiSpamBundle\Form\Extension\HoneypotFormExtension;
use Nucleos\AntiSpamBundle\Form\Extension\TimeFormExtension;
use Nucleos\AntiSpamBundle\Provider\SessionTimeProvider;
use Nucleos\AntiSpamBundle\Twig\Extension\StringTwigExtension;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\Extension\Core\Type\FormType;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('nucleos_antispam.form.extension.type.honeypot', HoneypotFormExtension::class)
            ->tag('form.type_extension', [
                'extended-type' => FormType::class,
            ])
            ->args([
                new Reference('translator'),
                [],
            ])

        ->set('nucleos_antispam.provider.session', SessionTimeProvider::class)
            ->args([
                new Reference('session'),
                [],
            ])

        ->set('nucleos_antispam.form.extension.type.time', TimeFormExtension::class)
            ->tag('form.type_extension', [
                'extended-type' => FormType::class,
            ])
            ->args([
                new Reference('nucleos_antispam.provider'),
                new Reference('translator'),
                [],
            ])

        ->set('nucleos_antispam.twig.string_extension', StringTwigExtension::class)
            ->tag('twig.extension')
            ->args([
                new Parameter('nucleos_antispam.twig.mail_css_class'),
                new Parameter('nucleos_antispam.twig.mail_at_text'),
                new Parameter('nucleos_antispam.twig.mail_dot_text'),
            ])

    ;
};
