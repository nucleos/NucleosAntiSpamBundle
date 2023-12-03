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

namespace Nucleos\AntiSpamBundle\Tests;

use Nucleos\AntiSpamBundle\DependencyInjection\NucleosAntiSpamExtension;
use Nucleos\AntiSpamBundle\NucleosAntiSpamBundle;
use PHPUnit\Framework\TestCase;

final class NucleosAntiSpamBundleTest extends TestCase
{
    public function testGetContainerExtension(): void
    {
        $bundle = new NucleosAntiSpamBundle();

        self::assertInstanceOf(NucleosAntiSpamExtension::class, $bundle->getContainerExtension());
    }
}
