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

namespace Nucleos\AntiSpamBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class StringTwigExtension extends AbstractExtension
{
    private const MAIL_HTML_PATTERN = '/\<a(?:[^>]+)href\=\"mailto\:([^">]+)\"(?:[^>]*)\>(.*?)\<\/a\>/ism';
    private const MAIL_TEXT_PATTERN = '/(([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,4})(\((.+?)\))?)/i';

    private ?string $mailCssClass;

    /**
     * @var string[]
     */
    private array $mailAtText;

    /**
     * @var string[]
     */
    private array $mailDotText;

    /**
     * @param string[] $mailAtText
     * @param string[] $mailDotText
     */
    public function __construct(?string $mailCssClass, array $mailAtText, array $mailDotText)
    {
        $this->mailCssClass = $mailCssClass;
        $this->mailAtText   = $mailAtText;
        $this->mailDotText  = $mailDotText;
    }

    public function getFilters(): array
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
            return preg_replace_callback(self::MAIL_HTML_PATTERN, [$this, 'encryptMail'], $string) ?? '';
        }

        return preg_replace_callback(self::MAIL_TEXT_PATTERN, [$this, 'encryptMailText'], $string) ?? '';
    }

    /**
     * @param string[] $matches
     */
    private function encryptMailText(array $matches): string
    {
        [$original, $email] = $matches;

        return $this->getSecuredName($email).
            $this->hashedArrayValue($this->mailAtText, $original).
            $this->getSecuredName($email, true);
    }

    /**
     * @param string[] $matches
     */
    private function encryptMail(array $matches): string
    {
        [$original, $email, $text] = $matches;

        if ($text === $email) {
            $text = '';
        }

        return
            '<span'.(null !== $this->mailCssClass ? ' class="'.$this->mailCssClass.'"' : '').'>'.
            '<span>'.$this->getSecuredName($email).'</span>'.
                $this->hashedArrayValue($this->mailAtText, $original).
            '<span>'.$this->getSecuredName($email, true).'</span>'.
            ('' !== $text ? ' (<span>'.$text.'</span>)' : '').
            '</span>';
    }

    private function getSecuredName(string $name, bool $isDomain = false): string
    {
        $index = strpos($name, '@');

        \assert(false !== $index);

        if ($isDomain) {
            $name = substr($name, $index + 1);
        } else {
            $name = substr($name, 0, $index);
        }

        return str_replace('.', $this->hashedArrayValue($this->mailDotText, $name), $name);
    }

    /**
     * @param string[] $list
     */
    private function hashedArrayValue(array $list, string $name): string
    {
        $count = \count($list);
        $index = $this->numericHash($name) % $count;

        return $list[$index];
    }

    private function numericHash(string $name): int
    {
        $hash = hash('sha256', $name);

        return \intval(substr($hash, 0, 6), 16);
    }
}
