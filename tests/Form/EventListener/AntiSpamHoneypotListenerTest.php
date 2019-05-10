<?php

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Tests\Form\EventListener;

use Core23\AntiSpamBundle\Form\EventListener\AntiSpamHoneypotListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AntiSpamHoneypotListenerTest extends TestCase
{
    private $translator;

    protected function setUp()
    {
        $this->translator  =  $this->prophesize(TranslatorInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame([
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ], AntiSpamHoneypotListener::getSubscribedEvents());
    }

    public function testPreSubmit(): void
    {
        $config = $this->prophesize(FormConfigInterface::class);
        $config->getOption('compound')
            ->willReturn(true)
        ;

        $form = $this->prophesize(FormInterface::class);
        $form->isRoot()
            ->willReturn(true)
        ;
        $form->getConfig()
            ->willReturn($config)
        ;

        $event = $this->prophesize(FormEvent::class);
        $event->getForm()
            ->willReturn($form)
        ;
        $event->getData()
            ->willReturn([
                'foo'      => 'bar',
                'my-field' => '',
            ])
        ;
        $event->setData([
            'foo' => 'bar',
        ])
        ->shouldBeCalled()
        ;

        $listener = new AntiSpamHoneypotListener(
            $this->translator->reveal(),
            'my-field'
        );
        $listener->preSubmit($event->reveal());

        static::assertTrue(true);
    }

    public function testPreSubmitWithFilledHoneypot(): void
    {
        $this->translator->trans('honeypot_error', [], 'Core23AntiSpamBundle')
            ->willReturn('There is an error')
        ;

        $config = $this->prophesize(FormConfigInterface::class);
        $config->getOption('compound')
            ->willReturn(true)
        ;

        $form = $this->prophesize(FormInterface::class);
        $form->isRoot()
            ->willReturn(true)
        ;
        $form->getConfig()
            ->willReturn($config)
        ;
        $form->addError(Argument::type(FormError::class))
            ->shouldBeCalled()
        ;

        $event = $this->prophesize(FormEvent::class);
        $event->getForm()
            ->willReturn($form)
        ;
        $event->getData()
            ->willReturn([
                'foo'      => 'bar',
                'my-field' => 'def',
            ])
        ;
        $event->setData([
            'foo' => 'bar',
        ])
        ->shouldBeCalled()
        ;

        $listener = new AntiSpamHoneypotListener(
            $this->translator->reveal(),
            'my-field'
        );
        $listener->preSubmit($event->reveal());
    }

    public function testPreSubmitChildForm(): void
    {
        $config = $this->prophesize(FormConfigInterface::class);
        $config->getOption('compound')
            ->willReturn(false)
        ;

        $form = $this->prophesize(FormInterface::class);
        $form->isRoot()
            ->willReturn(false)
        ;
        $form->getConfig()
            ->willReturn($config)
        ;

        $event = $this->prophesize(FormEvent::class);
        $event->getForm()
            ->willReturn($form)
        ;

        $listener = new AntiSpamHoneypotListener(
            $this->translator->reveal(),
            'my-field'
        );
        $listener->preSubmit($event->reveal());

        static::assertTrue(true);
    }

    public function testPreSubmitCompoundForm(): void
    {
        $config = $this->prophesize(FormConfigInterface::class);
        $config->getOption('compound')
            ->willReturn(true)
        ;

        $form = $this->prophesize(FormInterface::class);
        $form->isRoot()
            ->willReturn(false)
        ;
        $form->getConfig()
            ->willReturn($config)
        ;

        $event = $this->prophesize(FormEvent::class);
        $event->getForm()
            ->willReturn($form)
        ;

        $listener = new AntiSpamHoneypotListener(
            $this->translator->reveal(),
            'my-field'
        );
        $listener->preSubmit($event->reveal());

        static::assertTrue(true);
    }
}
