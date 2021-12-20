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

namespace Nucleos\AntiSpamBundle\Tests\Form\EventListener;

use Nucleos\AntiSpamBundle\Form\EventListener\AntiSpamTimeListener;
use Nucleos\AntiSpamBundle\Provider\TimeProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AntiSpamTimeListenerTest extends TestCase
{
    /**
     * @var MockObject&TimeProviderInterface
     */
    private $timeProvider;

    /**
     * @var MockObject&TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        $this->timeProvider = $this->createMock(TimeProviderInterface::class);
        $this->translator   =  $this->createMock(TranslatorInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame([
            FormEvents::PRE_SUBMIT  => 'preSubmit',
            FormEvents::POST_SUBMIT => 'postSubmit',
        ], AntiSpamTimeListener::getSubscribedEvents());
    }

    public function testPreSubmit(): void
    {
        $this->timeProvider->method('isValid')->with('my-form', ['foo' => 'bar'])
            ->willReturn(true)
        ;
        $this->timeProvider->expects(static::once())->method('removeFormProtection')->with('my-form')
        ;

        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(true)
        ;

        $form = $this->prepareForm($config, true);

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider,
            $this->translator,
            ['foo' => 'bar']
        );
        $listener->preSubmit($event);
    }

    public function testPreSubmitInvalidForm(): void
    {
        $this->translator->method('trans')->with('time_error', [], 'NucleosAntiSpamBundle')
            ->willReturn('There is an error')
        ;

        $this->timeProvider->method('isValid')->with('my-form', ['foo' => 'bar'])
            ->willReturn(false)
        ;
        $this->timeProvider->expects(static::once())->method('removeFormProtection')->with('my-form');

        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(true)
        ;

        $form = $this->prepareForm($config, true);
        $form->expects(static::once())->method('addError')->with(static::isInstanceOf(FormError::class));

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider,
            $this->translator,
            ['foo' => 'bar']
        );
        $listener->preSubmit($event);
    }

    public function testPreSubmitChildForm(): void
    {
        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(false)
        ;

        $form = $this->prepareForm($config);

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider,
            $this->translator,
            []
        );
        $listener->preSubmit($event);

        $this->timeProvider->expects(static::never())->method('removeFormProtection');
    }

    public function testPreSubmitCompoundForm(): void
    {
        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(true)
        ;

        $form = $this->prepareForm($config);

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider,
            $this->translator,
            []
        );
        $listener->preSubmit($event);

        $this->timeProvider->expects(static::never())->method('removeFormProtection');
    }

    public function testPostSubmit(): void
    {
        $this->timeProvider->expects(static::once())->method('createFormProtection')->with('my-form');

        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(true)
        ;

        $form = $this->prepareForm($config, true);
        $form->method('isValid')
            ->willReturn(false)
        ;

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider,
            $this->translator,
            ['foo' => 'bar']
        );
        $listener->postSubmit($event);
    }

    public function testPostSubmitValidForm(): void
    {
        $this->timeProvider->expects(static::once())->method('createFormProtection')->with('my-form');

        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(true)
        ;

        $form = $this->prepareForm($config, true);
        $form->method('isValid')
            ->willReturn(false)
        ;

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider,
            $this->translator,
            ['foo' => 'bar']
        );
        $listener->postSubmit($event);
    }

    public function testPostSubmitFormNotCompound(): void
    {
        $this->timeProvider->expects(static::never())->method('createFormProtection');

        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(false)
        ;

        $form = $this->prepareForm($config, true);
        $form->method('isValid')
            ->willReturn(true)
        ;

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider,
            $this->translator,
            ['foo' => 'bar']
        );
        $listener->postSubmit($event);
    }

    /**
     * @return MockObject&FormInterface
     */
    private function prepareForm(FormConfigInterface $config, bool $root = false): FormInterface
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')
            ->willReturn($root)
        ;
        $form->method('getConfig')
            ->willReturn($config)
        ;
        $form->method('getName')
            ->willReturn('my-form')
        ;

        return $form;
    }
}
