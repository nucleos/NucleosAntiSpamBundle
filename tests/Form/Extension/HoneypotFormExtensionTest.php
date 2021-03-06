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

use Nucleos\AntiSpamBundle\Form\EventListener\AntiSpamHoneypotListener;
use Nucleos\AntiSpamBundle\Form\Extension\HoneypotFormExtension;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HoneypotFormExtensionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<TranslatorInterface>
     */
    private $translator;

    protected function setUp(): void
    {
        $this->translator   = $this->prophesize(TranslatorInterface::class);
    }

    public function testBuildForm(): void
    {
        $factory = $this->prophesize(FormFactoryInterface::class);

        $builder = $this->prophesize(FormBuilderInterface::class);
        $builder->getFormFactory()
            ->willReturn($factory)
        ;
        $builder->setAttribute('antispam_honeypot_factory', $factory->reveal())
            ->willReturn($builder)
            ->shouldBeCalled()
        ;
        $builder->addEventSubscriber(Argument::type(AntiSpamHoneypotListener::class))
            ->willReturn($builder)
            ->shouldBeCalled()
        ;

        $extension = new HoneypotFormExtension(
            $this->translator->reveal(),
            []
        );
        $extension->buildForm($builder->reveal(), [
            'antispam_honeypot'       => true,
            'antispam_honeypot_class' => 'spamclass',
            'antispam_honeypot_field' => 'hidden-field',
        ]);
    }

    public function testBuildFormWithDisabledAntispam(): void
    {
        $builder = $this->prophesize(FormBuilderInterface::class);

        $extension = new HoneypotFormExtension(
            $this->translator->reveal(),
            []
        );
        $extension->buildForm($builder->reveal(), [
            'antispam_honeypot'       => false,
            'antispam_honeypot_class' => 'spamclass',
            'antispam_honeypot_field' => 'hidden-field',
        ]);

        $builder->addEventSubscriber(Argument::type(AntiSpamHoneypotListener::class))
            ->shouldNotHaveBeenCalled()
        ;
    }

    public function testFinishView(): void
    {
        $parenView = $this->prophesize(FormView::class);

        $view = $this->prophesize(FormView::class);

        $parentForm = $this->prophesize(FormInterface::class);
        $parentForm->createView($view)
            ->willReturn($parenView)
        ;

        $formFactory = $this->prophesize(FormFactoryInterface::class);
        $formFactory
            ->createNamed('hidden-field', TextType::class, null, [
                'mapped'   => false,
                'label'    => false,
                'required' => false,
                'attr'     => [
                    'class' => 'spamclass',
                ],
            ])
            ->willReturn($parentForm)
        ;

        $config = $this->prophesize(FormConfigInterface::class);
        $config->getAttribute('antispam_honeypot_factory')
            ->willReturn($formFactory)
        ;

        $form = $this->prophesize(FormInterface::class);
        $form->has('hidden-field')
            ->willReturn(false)
        ;
        $form->getConfig()
            ->willReturn($config)
        ;

        $extension = new HoneypotFormExtension(
            $this->translator->reveal(),
            []
        );
        $extension->finishView($view->reveal(), $form->reveal(), [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_class' => 'spamclass',
            'antispam_honeypot_field' => 'hidden-field',
        ]);

        static::assertSame($parenView->reveal(), $view->children['hidden-field']);
    }

    public function testFinishWithEmptyClass(): void
    {
        $parenView = $this->prophesize(FormView::class);

        $view = $this->prophesize(FormView::class);

        $parentForm = $this->prophesize(FormInterface::class);
        $parentForm->createView($view)
            ->willReturn($parenView)
        ;

        $formFactory = $this->prophesize(FormFactoryInterface::class);
        $formFactory
            ->createNamed('hidden-field', TextType::class, null, [
                'mapped'   => false,
                'label'    => false,
                'required' => false,
                'attr'     => [
                    'style' => 'display:none',
                ],
            ])
            ->willReturn($parentForm)
        ;

        $config = $this->prophesize(FormConfigInterface::class);
        $config->getAttribute('antispam_honeypot_factory')
            ->willReturn($formFactory)
        ;

        $form = $this->prophesize(FormInterface::class);
        $form->has('hidden-field')
            ->willReturn(false)
        ;
        $form->getConfig()
            ->willReturn($config)
        ;

        $extension = new HoneypotFormExtension(
            $this->translator->reveal(),
            []
        );
        $extension->finishView($view->reveal(), $form->reveal(), [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_field' => 'hidden-field',
        ]);

        static::assertSame($parenView->reveal(), $view->children['hidden-field']);
    }

    public function testFinishWithExistingField(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Honeypot field "hidden-field" is already used.');

        $view = $this->prophesize(FormView::class);

        $form = $this->prophesize(FormInterface::class);
        $form->has('hidden-field')
            ->willReturn(true)
        ;

        $extension = new HoneypotFormExtension(
            $this->translator->reveal(),
            []
        );
        $extension->finishView($view->reveal(), $form->reveal(), [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_field' => 'hidden-field',
        ]);
    }

    public function testFinishWithEmptyFormFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid form factory to create a honeyput.');

        $view = $this->prophesize(FormView::class);

        $config = $this->prophesize(FormConfigInterface::class);
        $config->getAttribute('antispam_honeypot_factory')
            ->willReturn(null)
        ;

        $form = $this->prophesize(FormInterface::class);
        $form->has('hidden-field')
            ->willReturn(false)
        ;
        $form->getConfig()
            ->willReturn($config)
        ;

        $extension = new HoneypotFormExtension(
            $this->translator->reveal(),
            []
        );
        $extension->finishView($view->reveal(), $form->reveal(), [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_field' => 'hidden-field',
        ]);
    }

    public function testFinishViewForChildForm(): void
    {
        $view         = $this->prophesize(FormView::class);
        $view->parent = $this->prophesize(FormView::class)->reveal();
        $form         = $this->prophesize(FormInterface::class);
        $form->getConfig()
            ->shouldNotBeCalled()
        ;

        $extension = new HoneypotFormExtension(
            $this->translator->reveal(),
            []
        );
        $extension->finishView($view->reveal(), $form->reveal(), [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_class' => 'spamclass',
            'antispam_honeypot_field' => 'hidden-field',
        ]);
    }

    public function testFinishViewWithDisbaledAntispam(): void
    {
        $view = $this->prophesize(FormView::class);
        $form = $this->prophesize(FormInterface::class);
        $form->getConfig()
            ->shouldNotBeCalled()
        ;

        $extension = new HoneypotFormExtension(
            $this->translator->reveal(),
            []
        );
        $extension->finishView($view->reveal(), $form->reveal(), [
            'compound'                => true,
            'antispam_honeypot'       => false,
            'antispam_honeypot_class' => 'spamclass',
            'antispam_honeypot_field' => 'hidden-field',
        ]);
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $extension = new HoneypotFormExtension(
            $this->translator->reveal(),
            [
                'global' => true,
                'class'  => 'my-class',
                'field'  => 'a-field',
            ]
        );
        $extension->configureOptions($resolver);

        $result = $resolver->resolve();

        static::assertTrue($result['antispam_honeypot']);
        static::assertSame('my-class', $result['antispam_honeypot_class']);
        static::assertSame('a-field', $result['antispam_honeypot_field']);
    }

    public function testExtendedTypes(): void
    {
        static::assertSame([FormType::class], HoneypotFormExtension::getExtendedTypes());
    }
}
