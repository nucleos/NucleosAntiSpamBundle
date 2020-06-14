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

namespace Nucleos\AntiSpamBundle\Provider;

interface TimeProviderInterface
{
    /**
     * Creates a new form time protection.
     */
    public function createFormProtection(string $name): void;

    /**
     * Clears the form time protection.
     */
    public function removeFormProtection(string $name): void;

    /**
     * Check if the form is valid.
     *
     * @return bool $valid
     */
    public function isValid(string $name, array $options): bool;
}
