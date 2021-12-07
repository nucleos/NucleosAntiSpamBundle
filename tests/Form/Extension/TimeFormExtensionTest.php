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

namespace Nucleos\AntiSpamBundle\Tests\Form\Extension;

use Nucleos\AntiSpamBundle\Form\EventListener\AntiSpamTimeListener;
use Nucleos\AntiSpamBundle\Form\Extension\TimeFormExtension;
use Nucleos\AntiSpamBundle\Provider\TimeProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TimeFormExtensionTest extends TestCase
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
        $this->translator   = $this->createMock(TranslatorInterface::class);
    }

    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(static::once())->method('addEventSubscriber')->with(static::isInstanceOf(AntiSpamTimeListener::class));

        $extension = new TimeFormExtension(
            $this->timeProvider,
            $this->translator,
            []
        );
        $extension->buildForm($builder, [
            'antispam_time'     => true,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);

        $builder->method('addEventSubscriber')->with(static::isInstanceOf(AntiSpamTimeListener::class));
    }

    public function testBuildFormWithDisabledAntispam(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(static::never())->method('addEventSubscriber');

        $extension = new TimeFormExtension(
            $this->timeProvider,
            $this->translator,
            []
        );
        $extension->buildForm($builder, [
            'antispam_time'     => false,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);
    }

    public function testFinishView(): void
    {
        $view = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->method('getName')
            ->willReturn('my_form')
        ;

        $this->timeProvider->expects(static::once())->method('createFormProtection')->with('my_form');

        $extension = new TimeFormExtension(
            $this->timeProvider,
            $this->translator,
            []
        );
        $extension->finishView($view, $form, [
            'compound'          => true,
            'antispam_time'     => true,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);
    }

    public function testFinishViewForChildForm(): void
    {
        $view         = $this->createMock(FormView::class);
        $view->parent = $this->createMock(FormView::class);
        $form         = $this->createMock(FormInterface::class);
        $form->method('getName')
            ->willReturn('my_form')
        ;

        $extension = new TimeFormExtension(
            $this->timeProvider,
            $this->translator,
            []
        );
        $extension->finishView($view, $form, [
            'compound'          => true,
            'antispam_time'     => true,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);

        $this->timeProvider->expects(static::never())->method('createFormProtection');
    }

    public function testFinishViewWithDisbaledAntispam(): void
    {
        $view = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->method('getName')
            ->willReturn('my_form')
        ;

        $extension = new TimeFormExtension(
            $this->timeProvider,
            $this->translator,
            []
        );
        $extension->finishView($view, $form, [
            'compound'          => true,
            'antispam_time'     => false,
            'antispam_time_min' => 10,
            'antispam_time_max' => 30,
        ]);

        $this->timeProvider->expects(static::never())->method('createFormProtection');
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $extension = new TimeFormExtension(
            $this->timeProvider,
            $this->translator,
            [
                'global' => true,
                'min'    => 10,
                'max'    => 30,
            ]
        );
        $extension->configureOptions($resolver);

        $result = $resolver->resolve();

        static::assertTrue($result['antispam_time']);
        static::assertSame(10, $result['antispam_time_min']);
        static::assertSame(30, $result['antispam_time_max']);
    }

    public function testExtendedTypes(): void
    {
        static::assertSame([FormType::class], TimeFormExtension::getExtendedTypes());
    }
}
