<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Provider;

use DateTime;
use Symfony\Component\HttpFoundation\Session\Session;

final class SessionTimeProvider implements TimeProviderInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function createFormProtection(string $name): void
    {
        $startTime = new DateTime();
        $key       = $this->getSessionKey($name);
        $this->session->set($key, $startTime);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(string $name, array $options): bool
    {
        $startTime = $this->getFormTime($name);

        if (null === $startTime) {
            return false;
        }

        $currentTime = new DateTime();

        if (\array_key_exists('min', $options) && null !== $options['min']) {
            $minTime = clone $startTime;
            $minTime->modify(sprintf('+%d seconds', $options['min']));

            if ($minTime > $currentTime) {
                return false;
            }
        }

        if (\array_key_exists('max', $options) && null !== $options['max']) {
            $maxTime = clone $startTime;
            $maxTime->modify(sprintf('+%d seconds', $options['max']));

            if ($maxTime < $currentTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFormProtection(string $name): void
    {
        $key = $this->getSessionKey($name);
        $this->session->remove($key);
    }

    /**
     * Check if a form has a time protection.
     *
     * @param string $name
     *
     * @return bool
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
     *
     * @return DateTime|null
     */
    private function getFormTime(string $name): ?DateTime
    {
        $key = $this->getSessionKey($name);

        if ($this->hasFormProtection($name)) {
            return $this->session->get($key);
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getSessionKey(string $name): string
    {
        return 'antispam_'.$name;
    }
}
