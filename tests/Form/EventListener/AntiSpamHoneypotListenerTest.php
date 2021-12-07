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

use Nucleos\AntiSpamBundle\Form\EventListener\AntiSpamHoneypotListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AntiSpamHoneypotListenerTest extends TestCase
{
    /**
     * @var MockObject&TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame([
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ], AntiSpamHoneypotListener::getSubscribedEvents());
    }

    public function testPreSubmit(): void
    {
        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(true)
        ;

        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')
            ->willReturn(true)
        ;
        $form->method('getConfig')
            ->willReturn($config)
        ;

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;
        $event->method('getData')
            ->willReturn([
                'foo'      => 'bar',
                'my-field' => '',
            ])
        ;
        $event->expects(static::once())->method('setData')->with([
            'foo' => 'bar',
        ]);

        $listener = new AntiSpamHoneypotListener(
            $this->translator,
            'my-field'
        );
        $listener->preSubmit($event);
    }

    public function testPreSubmitWithFilledHoneypot(): void
    {
        $this->translator->method('trans')->with('honeypot_error', [], 'NucleosAntiSpamBundle')
            ->willReturn('There is an error')
        ;

        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(true)
        ;

        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')
            ->willReturn(true)
        ;
        $form->method('getConfig')
            ->willReturn($config)
        ;
        $form->expects(static::once())->method('addError')
            ->with(static::isInstanceOf(FormError::class))
        ;

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;
        $event->method('getData')
            ->willReturn([
                'foo'      => 'bar',
                'my-field' => 'def',
            ])
        ;
        $event->expects(static::once())->method('setData')->with([
            'foo' => 'bar',
        ]);

        $listener = new AntiSpamHoneypotListener(
            $this->translator,
            'my-field'
        );
        $listener->preSubmit($event);
    }

    public function testPreSubmitChildForm(): void
    {
        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(false)
        ;

        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')
            ->willReturn(false)
        ;
        $form->method('getConfig')
            ->willReturn($config)
        ;

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;

        $listener = new AntiSpamHoneypotListener(
            $this->translator,
            'my-field'
        );
        $listener->preSubmit($event);

        $form->expects(static::never())->method('addError');
    }

    public function testPreSubmitCompoundForm(): void
    {
        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getOption')->with('compound')
            ->willReturn(true)
        ;

        $form = $this->createMock(FormInterface::class);
        $form->method('isRoot')
            ->willReturn(false)
        ;
        $form->method('getConfig')
            ->willReturn($config)
        ;

        $event = $this->createMock(FormEvent::class);
        $event->method('getForm')
            ->willReturn($form)
        ;

        $listener = new AntiSpamHoneypotListener(
            $this->translator,
            'my-field'
        );
        $listener->preSubmit($event);

        $form->expects(static::never())->method('addError');
    }
}
