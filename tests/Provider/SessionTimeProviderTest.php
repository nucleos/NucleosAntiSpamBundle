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

namespace Nucleos\AntiSpamBundle\Tests\Provider;

use DateTime;
use Nucleos\AntiSpamBundle\Provider\SessionTimeProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

final class SessionTimeProviderTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateFromString(): void
    {
        $session = $this->prophesize(Session::class);
        $session->set('antispam_foobar', Argument::type(DateTime::class))
            ->shouldBeCalled()
        ;
        /** @var RequestStack $stack */
        $stack    = $this->createStack($session)->reveal();
        $provider = new SessionTimeProvider($stack);
        $provider->createFormProtection('foobar');
    }

    public function testIsValid(): void
    {
        $session  = $this->prepareValidSessionKey();
        /** @var RequestStack $stack */
        $stack    = $this->createStack($session)->reveal();
        $provider = new SessionTimeProvider($stack);

        static::assertTrue($provider->isValid('foobar', []));
    }

    public function testIsValidWithMinTime(): void
    {
        $session  = $this->prepareValidSessionKey();
        /** @var RequestStack $stack */
        $stack    = $this->createStack($session)->reveal();
        $provider = new SessionTimeProvider($stack);

        static::assertTrue($provider->isValid('foobar', [
            'min' => 10,
        ]));
    }

    public function testIsValidWithMaxTime(): void
    {
        $session  = $this->prepareValidSessionKey();
        /** @var RequestStack $stack */
        $stack    = $this->createStack($session)->reveal();
        $provider = new SessionTimeProvider($stack);

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
        /** @var RequestStack $stack */
        $stack    = $this->createStack($session)->reveal();
        $provider = new SessionTimeProvider($stack);

        static::assertFalse($provider->isValid('foobar', []));
    }

    public function testIsInvalidBecauseOfMinTime(): void
    {
        $session  = $this->prepareValidSessionKey();
        /** @var RequestStack $stack */
        $stack    = $this->createStack($session)->reveal();
        $provider = new SessionTimeProvider($stack);
        static::assertFalse($provider->isValid('foobar', [
            'min' => 60,
        ]));
    }

    public function testIsInvalidBecauseOfMaxTime(): void
    {
        $session  = $this->prepareValidSessionKey();
        /** @var RequestStack $stack */
        $stack    = $this->createStack($session)->reveal();
        $provider = new SessionTimeProvider($stack);

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
        /** @var RequestStack $stack */
        $stack    = $this->createStack($session)->reveal();
        $provider = new SessionTimeProvider($stack);
        $provider->removeFormProtection('foobar');
    }

    /**
     * @return ObjectProphecy<Session>
     */
    private function prepareValidSessionKey(): ObjectProphecy
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

    /**
     * @param ObjectProphecy<Session> $session
     *
     * @return ObjectProphecy<RequestStack>
     */
    private function createStack(ObjectProphecy $session): ObjectProphecy
    {
        $stack = $this->prophesize(RequestStack::class);
        $stack->getSession()->willReturn($session->reveal());

        return $stack;
    }
}
