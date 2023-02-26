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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionTimeProviderTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $session = $this->createMock(Session::class);
        $session->expects(static::once())->method('set')->with('antispam_foobar', static::isInstanceOf(DateTime::class));

        $stack    = $this->createStack($session);
        $provider = new SessionTimeProvider($stack);
        $provider->createFormProtection('foobar');
    }

    public function testIsValid(): void
    {
        $session  = $this->prepareValidSessionKey();

        $stack    = $this->createStack($session);
        $provider = new SessionTimeProvider($stack);

        static::assertTrue($provider->isValid('foobar', []));
    }

    public function testIsValidWithMinTime(): void
    {
        $session  = $this->prepareValidSessionKey();

        $stack    = $this->createStack($session);
        $provider = new SessionTimeProvider($stack);

        static::assertTrue($provider->isValid('foobar', [
            'min' => 10,
        ]));
    }

    public function testIsValidWithMaxTime(): void
    {
        $session  = $this->prepareValidSessionKey();

        /** @var RequestStack $stack */
        $stack    = $this->createStack($session);
        $provider = new SessionTimeProvider($stack);

        static::assertTrue($provider->isValid('foobar', [
            'max' => 60,
        ]));
    }

    public function testIsInvalid(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('has')->with('antispam_foobar')
            ->willReturn(false)
        ;

        $stack    = $this->createStack($session);
        $provider = new SessionTimeProvider($stack);

        static::assertFalse($provider->isValid('foobar', []));
    }

    public function testIsInvalidBecauseOfMinTime(): void
    {
        $session  = $this->prepareValidSessionKey();

        $stack    = $this->createStack($session);
        $provider = new SessionTimeProvider($stack);
        static::assertFalse($provider->isValid('foobar', [
            'min' => 60,
        ]));
    }

    public function testIsInvalidBecauseOfMaxTime(): void
    {
        $session  = $this->prepareValidSessionKey();

        /** @var RequestStack $stack */
        $stack    = $this->createStack($session);
        $provider = new SessionTimeProvider($stack);

        static::assertFalse($provider->isValid('foobar', [
            'max' => 10,
        ]));
    }

    public function testRemoveFormProtection(): void
    {
        $session = $this->createMock(Session::class);
        $session->expects(static::once())->method('remove')->with('antispam_foobar');
        $stack    = $this->createStack($session);
        $provider = new SessionTimeProvider($stack);
        $provider->removeFormProtection('foobar');
    }

    private function prepareValidSessionKey(): SessionInterface
    {
        $session = $this->createMock(SessionInterface::class);
        $session->method('has')->with('antispam_foobar')
            ->willReturn(true)
        ;
        $session->method('get')->with('antispam_foobar')
            ->willReturn(new DateTime('- 15 seconds'))
        ;

        return $session;
    }

    private function createStack(SessionInterface $session): RequestStack
    {
        $request = new Request();
        $request->setSession($session);

        $stack = new RequestStack();
        $stack->push($request);

        return $stack;
    }
}
