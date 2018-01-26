<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Provider;

interface TimeProviderInterface
{
    /**
     * Creates a new form time protection.
     *
     * @param string $name
     */
    public function createFormProtection(string $name): void;

    /**
     * Clears the form time protection.
     *
     * @param string $name
     */
    public function removeFormProtection(string $name): void;

    /**
     * Check if the form is valid.
     *
     * @param string $name
     * @param array  $options
     *
     * @return bool $valid
     */
    public function isValid(string $name, array $options): bool;
}
