<?php

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Tests\Form\Extension;

use Core23\AntiSpamBundle\Form\EventListener\AntiSpamTimeListener;
use Core23\AntiSpamBundle\Form\Extension\TimeFormExtension;
use Core23\AntiSpamBundle\Provider\TimeProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class TimeFormExtensionTest extends TestCase
{
    private $timeProvider;

    private $translator;

    protected function setUp()
    {
        $this->timeProvider = $this->prophesize(TimeProviderInterface::class);
        $this->translator   = $this->prophesize(TranslatorInterface::class);
    }

    public function testItIsInstantiable(): void
    {
        $this->assertInstanceOf(FormTypeExtensionInterface::class, new TimeFormExtension(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            [],
            ));
    }

    public function testBuildForm(): void
    {
        $builder = $this->prophesize(FormBuilderInterface::class);
        $builder->addEventSubscriber(Argument::type(AntiSpamTimeListener::class))
        ->shouldBeCalled()
        ;

        $extension = new TimeFormExtension(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            [],
            );
        $extension->buildForm($builder->reveal(), [
            'antispam_time'     => true,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);
    }

    public function testBuildFormWithDisabledAntispam(): void
    {
        $builder = $this->prophesize(FormBuilderInterface::class);

        $extension = new TimeFormExtension(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            [],
            );
        $extension->buildForm($builder->reveal(), [
            'antispam_time'     => false,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);

        $this->assertTrue(true);
    }

    public function testFinishView(): void
    {
        $view = $this->prophesize(FormView::class);
        $form = $this->prophesize(FormInterface::class);
        $form->getName()
            ->willReturn('my_form')
        ;

        $this->timeProvider->createFormProtection('my_form')
            ->shouldBeCalled()
        ;

        $extension = new TimeFormExtension(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            [],
            );
        $extension->finishView($view->reveal(), $form->reveal(), [
            'compound'          => true,
            'antispam_time'     => true,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);
    }

    public function testFinishViewForChildForm(): void
    {
        $view         = $this->prophesize(FormView::class);
        $view->parent = $this->prophesize(FormView::class)->reveal();
        $form         = $this->prophesize(FormInterface::class);

        $extension = new TimeFormExtension(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            [],
            );
        $extension->finishView($view->reveal(), $form->reveal(), [
            'compound'          => true,
            'antispam_time'     => true,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);

        $this->assertTrue(true);
    }

    public function testFinishViewWithDisbaledAntispam(): void
    {
        $view = $this->prophesize(FormView::class);
        $form = $this->prophesize(FormInterface::class);

        $extension = new TimeFormExtension(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            [],
            );
        $extension->finishView($view->reveal(), $form->reveal(), [
            'compound'          => true,
            'antispam_time'     => false,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);

        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $extension = new TimeFormExtension(
            $this->timeProvider->reveal(),
            $this->translator->reveal(),
            [
                'global' => true,
                'min'    => 10,
                'max'    => 30,
            ],
            );
        $extension->configureOptions($resolver);

        $result = $resolver->resolve();

        $this->assertTrue($result['antispam_time']);
        $this->assertSame(10, $result['antispam_time_min']);
        $this->assertSame(30, $result['antispam_time_max']);
    }

    public function testExtendedTypes(): void
    {
        $this->assertSame([FormType::class], TimeFormExtension::getExtendedTypes());
    }
}
