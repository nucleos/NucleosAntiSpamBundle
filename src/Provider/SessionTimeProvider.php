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

use DateTime;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Session\Session;

final class SessionTimeProvider implements TimeProviderInterface
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function createFormProtection(string $name): void
    {
        $startTime = new DateTime();
        $key       = $this->getSessionKey($name);
        $this->session->set($key, $startTime);
    }

    public function isValid(string $name, array $options): bool
    {
        $startTime = $this->getFormTime($name);

        if (null === $startTime) {
            return false;
        }

        $currentTime = new DateTimeImmutable();

        if ($this->violatesMin($startTime, $currentTime, $options)) {
            return false;
        }

        if ($this->violatesMax($startTime, $currentTime, $options)) {
            return false;
        }

        return true;
    }

    public function removeFormProtection(string $name): void
    {
        $key = $this->getSessionKey($name);
        $this->session->remove($key);
    }

    /**
     * Check if a form has a time protection.
     */
    private function hasFormProtection(string $name): bool
    {
        $key = $this->getSessionKey($name);

        return $this->session->has($key);
    }

    /**
     * Gets the form time for specified form.
     *
     * @param string $name Name of form to get
     */
    private function getFormTime(string $name): ?DateTime
    {
        $key = $this->getSessionKey($name);

        if ($this->hasFormProtection($name)) {
            return $this->session->get($key);
        }

        return null;
    }

    private function getSessionKey(string $name): string
    {
        return 'antispam_'.$name;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function violatesMin(DateTime $value, DateTimeImmutable $currentTime, array $options): bool
    {
        if (\array_key_exists('min', $options) && null !== $options['min']) {
            $minTime = clone $value;
            $minTime->modify(sprintf('+%d seconds', $options['min']));

            if ($minTime > $currentTime) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function violatesMax(DateTime $value, DateTimeImmutable $currentTime, array $options): bool
    {
        if (\array_key_exists('max', $options) && null !== $options['max']) {
            $maxTime = clone $value;
            $maxTime->modify(sprintf('+%d seconds', $options['max']));

            if ($maxTime < $currentTime) {
                return true;
            }
        }

        return false;
    }
}
