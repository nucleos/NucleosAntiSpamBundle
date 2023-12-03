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

use Nucleos\AntiSpamBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testDefaultOptions(): void
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), [[
        ]]);

        $expected = [
            'twig' => [
                'mail' => [
                    'css_class' => null,
                    'dot_text'  => ['[DOT]', '(DOT)', '[.]'],
                    'at_text'   => ['[AT]', '(AT)', '[ÄT]'],
                ],
            ],
            'time' => [
                'min'        => 5,
                'max'        => 3600,
                'global'     => false,
            ],
            'honeypot' => [
                'field'      => 'email_address',
                'class'      => 'hidden',
                'global'     => false,
                'provider'   => 'nucleos_antispam.provider.session',
            ],
        ];

        self::assertSame($expected, $config);
    }

    public function testOptions(): void
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), [[
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
        ]]);

        $expected = [
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
        ];

        self::assertSame($expected, $config);
    }
}
