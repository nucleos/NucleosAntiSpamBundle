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

namespace Nucleos\AntiSpamBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Nucleos\AntiSpamBundle\DependencyInjection\NucleosAntiSpamExtension;

final class NucleosAntiSpamExtensionTest extends AbstractExtensionTestCase
{
    public function testLoadDefault(): void
    {
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('nucleos_antispam.form.extension.type.time', 2, [
            'min'        => 5,
            'max'        => 3600,
            'global'     => false,
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('nucleos_antispam.form.extension.type.honeypot', 1, [
            'field'      => 'email_address',
            'class'      => 'hidden',
            'global'     => false,
            'provider'   => 'nucleos_antispam.provider.session',
        ]);
    }

    public function testLoadCustom(): void
    {
        $this->load([
            'twig' => [
                'mail' => [
                    'css_class' => 'spamme',
                    'dot_text'  => ['[DOT]'],
                    'at_text'   => ['[AT'],
                ],
            ],
            'time' => [
                'min'        => 0,
                'max'        => 600,
                'global'     => true,
            ],
            'honeypot' => [
                'field'      => 'custom',
                'class'      => 'hide',
                'global'     => true,
                'provider'   => 'nucleos_antispam.provider.custom',
            ],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('nucleos_antispam.form.extension.type.time', 2, [
            'min'        => 0,
            'max'        => 600,
            'global'     => true,
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('nucleos_antispam.form.extension.type.honeypot', 1, [
            'field'      => 'custom',
            'class'      => 'hide',
            'global'     => true,
            'provider'   => 'nucleos_antispam.provider.custom',
        ]);
    }

    protected function getContainerExtensions(): array
    {
        return [
            new NucleosAntiSpamExtension(),
        ];
    }
}
