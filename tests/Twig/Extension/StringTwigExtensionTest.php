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

final class StringTwigExtensionTest extends TestCase
{
    /**
     * @dataProvider getMailHtml
     *
     * @param string $input
     * @param string $output
     */
    public function testAntispam(string $input, string $output): void
    {
        $extension = new StringTwigExtension('spam', ['[AT]'], ['[DOT]']);

        $this->assertSame($output, $extension->antispam($input));
    }

    /**
     * @dataProvider getMailText
     *
     * @param string $input
     * @param string $output
     */
    public function testAntispamText(string $input, string $output): void
    {
        $extension = new StringTwigExtension('spam', ['[AT]'], ['[DOT]']);

        $this->assertSame($output, $extension->antispam($input, false));
    }

    /**
     * @return array
     */
    public function getMailHtml()
    {
        return [
            [
                'Lorem Ipsum <script>var link = "foo@bar.baz"; </script> Sit Amet',
                'Lorem Ipsum <script>var link = "foo@bar.baz"; </script> Sit Amet',
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

    /**
     * @return array
     */
    public function getMailText()
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
