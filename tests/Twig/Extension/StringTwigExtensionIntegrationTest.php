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

namespace Nucleos\AntiSpamBundle\Tests\Twig\Extension;

use Nucleos\AntiSpamBundle\Tests\App\AppKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class StringTwigExtensionIntegrationTest extends TestCase
{
    public function testRender(): void
    {
        $client = new KernelBrowser(new AppKernel());
        $client->request('GET', '/twig-test');

        self::assertSame(200, $client->getResponse()->getStatusCode());
    }
}
