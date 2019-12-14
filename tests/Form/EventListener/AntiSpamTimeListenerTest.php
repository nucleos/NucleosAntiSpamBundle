<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Tests\Form\EventListener;

use Core23\AntiSpamBundle\Form\EventListener\AntiSpamTimeListener;
use Core23\AntiSpamBundle\Provider\TimeProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class AntiSpamTimeListenerTest extends TestCase
{
    private $timeProvider;

    private $translator;

    protected function setUp(): void
    {
        $this->timeProvider = $this->prophesize(TimeProviderInterface::class);
        $this->translator   =  $this->prophesize(TranslatorInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame([
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ], AntiSpamTimeListener::getSubscribedEvents());
    }

    public function testPreSubmit(): void
    {
        $this->timeProvider->isValid('my-form', ['foo' => 'bar'])
            ->willReturn(true)
        ;
        $this->timeProvider->removeFormProtection('my-form')
            ->shouldBeCalled()
        ;

        $config = $this->prophesize(FormConfigInterface::class);
        $config->getOption('compound')
            ->willReturn(true)
        ;

        $form = $this->prepareForm($config, true);

        $event = $this->prophesize(FormEvent::class);
        $event->getForm()
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            ['foo' => 'bar']
        );
        $listener->preSubmit($event->reveal());
    }

    public function testPreSubmitInvalidForm(): void
    {
        $this->translator->trans('time_error', [], 'Core23AntiSpamBundle')
            ->willReturn('There is an error')
        ;

        $this->timeProvider->isValid('my-form', ['foo' => 'bar'])
            ->willReturn(false)
        ;
        $this->timeProvider->removeFormProtection('my-form')
            ->shouldBeCalled()
        ;

        $config = $this->prophesize(FormConfigInterface::class);
        $config->getOption('compound')
            ->willReturn(true)
        ;

        $form = $this->prepareForm($config, true);
        $form->addError(Argument::type(FormError::class))
            ->shouldBeCalled()
        ;

        $event = $this->prophesize(FormEvent::class);
        $event->getForm()
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            ['foo' => 'bar']
        );
        $listener->preSubmit($event->reveal());
    }

    public function testPreSubmitChildForm(): void
    {
        $config = $this->prophesize(FormConfigInterface::class);
        $config->getOption('compound')
            ->willReturn(false)
        ;

        $form = $this->prepareForm($config);

        $event = $this->prophesize(FormEvent::class);
        $event->getForm()
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            []
        );
        $listener->preSubmit($event->reveal());

        $this->timeProvider->removeFormProtection('my-form')
            ->shouldNotHaveBeenCalled()
        ;
    }

    public function testPreSubmitCompoundForm(): void
    {
        $config = $this->prophesize(FormConfigInterface::class);
        $config->getOption('compound')
            ->willReturn(true)
        ;

        $form = $this->prepareForm($config);

        $event = $this->prophesize(FormEvent::class);
        $event->getForm()
            ->willReturn($form)
        ;

        $listener = new AntiSpamTimeListener(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            []
        );
        $listener->preSubmit($event->reveal());

        $this->timeProvider->removeFormProtection('my-form')
            ->shouldNotHaveBeenCalled()
        ;
    }

    /**
     * @param FormConfigInterface|ObjectProphecy $config
     *
     * @return FormInterface|ObjectProphecy
     */
    private function prepareForm($config, bool $root = false)
    {
        $form = $this->prophesize(FormInterface::class);
        $form->isRoot()
            ->willReturn($root)
        ;
        $form->getConfig()
            ->willReturn($config)
        ;
        $form->getName()
            ->willReturn('my-form')
        ;

        return $form;
    }
}
