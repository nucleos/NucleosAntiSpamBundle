<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Tests\Twig\Extension;

use Core23\AntiSpamBundle\Twig\Extension\StringTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

final class StringTwigExtensionTest extends TestCase
{
    public function testGetFilters(): void
    {
        $extension = new StringTwigExtension('spam', ['[AT]'], ['[DOT]']);

        $filters = $extension->getFilters();

        static::assertNotCount(0, $filters);

        foreach ($filters as $filter) {
            static::assertInstanceOf(TwigFilter::class, $filter);
            static::assertIsCallable($filter->getCallable());
        }
    }

    /**
     * @dataProvider getMailHtml
     */
    public function testAntispam(string $input, string $output): void
    {
        $extension = new StringTwigExtension('spam', ['[AT]'], ['[DOT]']);

        static::assertSame($output, $extension->antispam($input));
    }

    /**
     * @dataProvider getMailText
     */
    public function testAntispamText(string $input, string $output): void
    {
        $extension = new StringTwigExtension('spam', ['[AT]'], ['[DOT]']);

        static::assertSame($output, $extension->antispam($input, false));
    }

    public function getMailHtml(): array
    {
        // @noinspection JSUnusedLocalSymbols
        return [
            [
                'Lorem Ipsum <script>const link = "foo@bar.baz"; </script> Sit Amet',
                'Lorem Ipsum <script>const link = "foo@bar.baz"; </script> Sit Amet',
            ],
            // TODO: Replace plain mails text in html
            //            [
            //                'Lorem Ipsum foo.sub@bar.baz.tld Sit Amet',
            //                'Lorem Ipsum <span class="spam"><span>foo[DOT]sub</span>[AT]<span>bar[DOT]baz[DOT]tld</span></span> Sit Amet',
            //            ],
            [
                'Lorem Ipsum <a href="mailto:john@smith.cool">John Smith</a> Sit Amet',
                'Lorem Ipsum <span class="spam"><span>john</span>[AT]<span>smith[DOT]cool</span> (<span>John Smith</span>)</span> Sit Amet',
            ],
            [
                'Lorem Ipsum <a href="mailto:foo.sub@bar.baz.tld">foo.sub@bar.baz.tld</a> Sit Amet',
                'Lorem Ipsum <span class="spam"><span>foo[DOT]sub</span>[AT]<span>bar[DOT]baz[DOT]tld</span></span> Sit Amet',
            ],
            [
                'Lorem Ipsum <span class="spam"><span>foo[DOT]sub</span>[AT]<span>bar[DOT]baz[DOT]tld</span></span> Sit Amet',
                'Lorem Ipsum <span class="spam"><span>foo[DOT]sub</span>[AT]<span>bar[DOT]baz[DOT]tld</span></span> Sit Amet',
            ],
        ];
    }

    public function getMailText(): array
    {
        return [
            [
                'Lorem Ipsum foo.sub@bar.baz.tld Sit Amet',
                'Lorem Ipsum foo[DOT]sub[AT]bar[DOT]baz[DOT]tld Sit Amet',
            ],
            [
                'Lorem Ipsum foo[DOT]sub[AT]bar[DOT]baz[DOT]tld Sit Amet',
                'Lorem Ipsum foo[DOT]sub[AT]bar[DOT]baz[DOT]tld Sit Amet',
            ],
        ];
    }
}
