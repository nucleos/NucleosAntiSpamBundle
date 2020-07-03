<?php

declare(strict_types=1);

/*
 * This file is part of the NucleosAntiSpamBundle package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\AntiSpamBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class NucleosAntiSpamExtension extends Extension
{
    public function getAlias()
    {
        return 'nucleos_antispam';
    }

    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $this->configureTwig($config, $container);
        $this->configureTime($container, $config);
        $this->configureHoneypot($container, $config);
    }

    /**
     * @param array $config
     */
    private function configureTwig($config, ContainerBuilder $container): void
    {
        $container->setParameter('nucleos_antispam.twig.mail_css_class', $config['twig']['mail']['css_class']);
        $container->setParameter('nucleos_antispam.twig.mail_at_text', $config['twig']['mail']['at_text']);
        $container->setParameter('nucleos_antispam.twig.mail_dot_text', $config['twig']['mail']['dot_text']);
    }

    /**
     * @param array<mixed> $config
     */
    private function configureTime(ContainerBuilder $container, array $config): void
    {
        $container
            ->getDefinition('nucleos_antispam.form.extension.type.time')
            ->replaceArgument(2, $config['time'])
        ;
    }

    /**
     * @param array<mixed> $config
     */
    private function configureHoneypot(ContainerBuilder $container, array $config): void
    {
        $container
            ->getDefinition('nucleos_antispam.form.extension.type.honeypot')
            ->replaceArgument(1, $config['honeypot'])
        ;

        $container->setAlias('nucleos_antispam.provider', $config['honeypot']['provider']);
    }
}
