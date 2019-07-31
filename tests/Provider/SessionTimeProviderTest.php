<?php

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Tests\Provider;

use Core23\AntiSpamBundle\Provider\SessionTimeProvider;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Session\Session;

final class SessionTimeProviderTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $session = $this->prophesize(Session::class);
        $session->set('antispam_foobar', Argument::type(DateTime::class))
            ->shouldBeCalled()
        ;

        $provider = new SessionTimeProvider($session->reveal());
        $provider->createFormProtection('foobar');
    }

    public function testIsValid(): void
    {
        $session  = $this->prepareValidSessionKey();

        $provider = new SessionTimeProvider($session->reveal());

        static::assertTrue($provider->isValid('foobar', []));
    }

    public function testIsValidWithMinTime(): void
    {
        $session  = $this->prepareValidSessionKey();

        $provider = new SessionTimeProvider($session->reveal());

        static::assertTrue($provider->isValid('foobar', [
            'min' => 10,
        ]));
    }

    public function testIsValidWithMaxTime(): void
    {
        $session  = $this->prepareValidSessionKey();

        $provider = new SessionTimeProvider($session->reveal());

        static::assertTrue($provider->isValid('foobar', [
            'max' => 60,
        ]));
    }

    public function testIsInvalid(): void
    {
        $session = $this->prophesize(Session::class);
        $session->has('antispam_foobar')
            ->willReturn(false)
        ;

        $provider = new SessionTimeProvider($session->reveal());

        static::assertFalse($provider->isValid('foobar', []));
    }

    public function testIsInvalidBecauseOfMinTime(): void
    {
        $session  = $this->prepareValidSessionKey();

        $provider = new SessionTimeProvider($session->reveal());
        static::assertFalse($provider->isValid('foobar', [
            'min' => 60,
        ]));
    }

    public function testIsInvalidBecauseOfMaxTime(): void
    {
        $session  = $this->prepareValidSessionKey();
        $provider = new SessionTimeProvider($session->reveal());

        static::assertFalse($provider->isValid('foobar', [
            'max' => 10,
        ]));
    }

    public function testRemoveFormProtection(): void
    {
        $session = $this->prophesize(Session::class);
        $session->remove('antispam_foobar')
            ->shouldBeCalled()
        ;

        $provider = new SessionTimeProvider($session->reveal());
        $provider->removeFormProtection('foobar');
    }

    /**
     * @return ObjectProphecy|Session
     */
    private function prepareValidSessionKey()
    {
        $session = $this->prophesize(Session::class);
        $session->has('antispam_foobar')
            ->willReturn(true)
        ;
        $session->get('antispam_foobar')
            ->willReturn(new DateTime('- 15 seconds'))
        ;

        return $session;
    }
}
