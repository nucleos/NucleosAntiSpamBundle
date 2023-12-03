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

use Nucleos\AntiSpamBundle\Twig\Extension\StringTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

final class StringTwigExtensionTest extends TestCase
{
    public function testGetFilters(): void
    {
        $extension = new StringTwigExtension('spam', ['[AT]', '[ÄT]', '(AT)', '|AT|'], ['[DOT]', ' PUNKT ', '[.]']);

        $filters = $extension->getFilters();

        self::assertNotCount(0, $filters);

        foreach ($filters as $filter) {
            self::assertInstanceOf(TwigFilter::class, $filter);
            self::assertIsCallable($filter->getCallable());
        }
    }

    /**
     * @dataProvider provideAntispamCases
     */
    public function testAntispam(string $input, string $output): void
    {
        $extension = new StringTwigExtension('spam', ['[AT]', '[ÄT]', '(AT)', '|AT|'], ['[DOT]', ' PUNKT ', '[.]']);

        self::assertSame($output, $extension->antispam($input));
    }

    /**
     * @dataProvider provideAntispamTextCases
     */
    public function testAntispamText(string $input, string $output): void
    {
        $extension = new StringTwigExtension('spam', ['[AT]', '[ÄT]', '(AT)', '|AT|'], ['[DOT]', ' PUNKT ', '[.]']);

        self::assertSame($output, $extension->antispam($input, false));
    }

    public function provideAntispamCases(): iterable
    {
        // @noinspection JSUnusedLocalSymbols
        return [
            [
                'Lorem Ipsum <script>const link = "foo@bar.baz"; </script> Sit Amet',
                'Lorem Ipsum <script>const link = "foo@bar.baz"; </script> Sit Amet',
            ],
            [
                'Lorem Ipsum <a href="mailto:john@smith.cool">John Smith</a> Sit Amet',
                'Lorem Ipsum <span class="spam"><span>john</span>[AT]<span>smith[.]cool</span> (<span>John Smith</span>)</span> Sit Amet',
            ],
            [
                'Lorem Ipsum <a href="mailto:foo.sub@bar.baz.tld">foo.sub@bar.baz.tld</a> Sit Amet',
                'Lorem Ipsum <span class="spam"><span>foo[DOT]sub</span>(AT)<span>bar PUNKT baz PUNKT tld</span></span> Sit Amet',
            ],
            [
                'Lorem Ipsum <span class="spam"><span>foo[DOT]sub</span>(AT)<span>bar PUNKT baz PUNKT tld</span></span> Sit Amet',
                'Lorem Ipsum <span class="spam"><span>foo[DOT]sub</span>(AT)<span>bar PUNKT baz PUNKT tld</span></span> Sit Amet',
            ],
        ];
    }

    /**
     * @return string[][]
     */
    public function provideAntispamTextCases(): iterable
    {
        return [
            [
                'Lorem Ipsum foo.sub@bar.baz.tld Sit Amet',
                'Lorem Ipsum foo[DOT]sub[AT]bar PUNKT baz PUNKT tld Sit Amet',
            ],
            [
                'Lorem Ipsum foo[DOT]sub[AT]bar PUNKT baz PUNKT tld Sit Amet',
                'Lorem Ipsum foo[DOT]sub[AT]bar PUNKT baz PUNKT tld Sit Amet',
            ],
        ];
    }
}
