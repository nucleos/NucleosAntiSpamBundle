<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class StringTwigExtension extends AbstractExtension
{
    private const MAIL_HTML_PATTERN = '/\<a(?:[^>]+)href\=\"mailto\:([^">]+)\"(?:[^>]*)\>(.*?)\<\/a\>/ism';
    private const MAIL_TEXT_PATTERN = '/(([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,4})(\((.+?)\))?)/i';

    /**
     * @var string|null
     */
    private $mailCssClass;

    /**
     * @var string[]
     */
    private $mailAtText;

    /**
     * @var string[]
     */
    private $mailDotText;

    /**
     * StringTwigExtension constructor.
     *
     * @param string|null $mailCssClass
     * @param string[]    $mailAtText
     * @param string[]    $mailDotText
     */
    public function __construct(?string $mailCssClass, array $mailAtText, array $mailDotText)
    {
        $this->mailCssClass = $mailCssClass;
        $this->mailAtText   = $mailAtText;
        $this->mailDotText  = $mailDotText;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('antispam', [$this, 'antispam'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * Replaces E-Mail addresses with an alternative text representation.
     *
     * @param string $string input string
     * @param bool   $html   Secure html or text
     *
     * @return string with replaced links
     */
    public function antispam(string $string, bool $html = true): string
    {
        if ($html) {
            return (string) preg_replace_callback(self::MAIL_HTML_PATTERN, [$this, 'encryptMail'], $string);
        }

        return (string) preg_replace_callback(self::MAIL_TEXT_PATTERN, [$this, 'encryptMailText'], $string);
    }

    /**
     * @param string[] $matches
     *
     * @return string
     */
    private function encryptMailText(array $matches): string
    {
        $email = $matches[1];

        return $this->getSecuredName($email).
            $this->mailAtText[array_rand($this->mailAtText)].
            $this->getSecuredName($email, true);
    }

    /**
     * @param string[] $matches
     *
     * @return string
     */
    private function encryptMail(array $matches): string
    {
        [, $email, $text] = $matches;

        if ($text === $email) {
            $text = '';
        }

        return
            '<span'.(!empty($this->mailCssClass) ? ' class="'.$this->mailCssClass.'"' : '').'>'.
            '<span>'.$this->getSecuredName($email).'</span>'.
                $this->mailAtText[array_rand($this->mailAtText)].
            '<span>'.$this->getSecuredName($email, true).'</span>'.
            ($text ? ' (<span>'.$text.'</span>)' : '').
            '</span>';
    }

    /**
     * @param string $name
     * @param bool   $isDomain
     *
     * @return string
     */
    private function getSecuredName(string $name, bool $isDomain = false): string
    {
        $index = strpos($name, '@');

        if ($index === -1) {
            return '';
        }

        if ($isDomain) {
            $name = (string) substr($name, $index + 1);
        } else {
            $name = (string) substr($name, 0, $index);
        }

        return str_replace('.', $this->mailDotText[array_rand($this->mailDotText)], $name);
    }
}
