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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
    /**
     * @var MockObject&TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        $this->translator   = $this->createMock(TranslatorInterface::class);
    }

    public function testBuildForm(): void
    {
        $factory = $this->createMock(FormFactoryInterface::class);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('getFormFactory')
            ->willReturn($factory)
        ;
        $builder->expects(self::once())->method('setAttribute')->with('antispam_honeypot_factory', $factory)
            ->willReturn($builder)
        ;
        $builder->expects(self::once())->method('addEventSubscriber')->with(self::isInstanceOf(AntiSpamHoneypotListener::class))
            ->willReturn($builder)
        ;

        $extension = new HoneypotFormExtension(
            $this->translator,
            []
        );
        $extension->buildForm($builder, [
            'antispam_honeypot'       => true,
            'antispam_honeypot_class' => 'spamclass',
            'antispam_honeypot_field' => 'hidden-field',
        ]);
    }

    public function testBuildFormWithDisabledAntispam(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $extension = new HoneypotFormExtension(
            $this->translator,
            []
        );
        $extension->buildForm($builder, [
            'antispam_honeypot'       => false,
            'antispam_honeypot_class' => 'spamclass',
            'antispam_honeypot_field' => 'hidden-field',
        ]);

        $builder->expects(self::never())->method('addEventSubscriber');
    }

    public function testFinishView(): void
    {
        $parenView = $this->createMock(FormView::class);

        $view = $this->createMock(FormView::class);

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm->method('createView')->with($view)
            ->willReturn($parenView)
        ;

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->method('createNamed')->with('hidden-field', TextType::class, null, [
                'mapped'   => false,
                'label'    => false,
                'required' => false,
                'attr'     => [
                    'class' => 'spamclass',
                ],
            ])
            ->willReturn($parentForm)
        ;

        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getAttribute')->with('antispam_honeypot_factory')
            ->willReturn($formFactory)
        ;

        $form = $this->createMock(FormInterface::class);
        $form->method('has')->with('hidden-field')
            ->willReturn(false)
        ;
        $form->method('getConfig')
            ->willReturn($config)
        ;

        $extension = new HoneypotFormExtension(
            $this->translator,
            []
        );
        $extension->finishView($view, $form, [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_class' => 'spamclass',
            'antispam_honeypot_field' => 'hidden-field',
        ]);

        self::assertSame($parenView, $view->children['hidden-field']);
    }

    public function testFinishWithEmptyClass(): void
    {
        $parenView = $this->createMock(FormView::class);

        $view = $this->createMock(FormView::class);

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm->method('createView')->with($view)
            ->willReturn($parenView)
        ;

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->method('createNamed')->with('hidden-field', TextType::class, null, [
                'mapped'   => false,
                'label'    => false,
                'required' => false,
                'attr'     => [
                    'style' => 'display:none',
                ],
            ])
            ->willReturn($parentForm)
        ;

        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getAttribute')->with('antispam_honeypot_factory')
            ->willReturn($formFactory)
        ;

        $form = $this->createMock(FormInterface::class);
        $form->method('has')->with('hidden-field')
            ->willReturn(false)
        ;
        $form->method('getConfig')
            ->willReturn($config)
        ;

        $extension = new HoneypotFormExtension(
            $this->translator,
            []
        );
        $extension->finishView($view, $form, [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_field' => 'hidden-field',
        ]);

        self::assertSame($parenView, $view->children['hidden-field']);
    }

    public function testFinishWithExistingField(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Honeypot field "hidden-field" is already used.');

        $view = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form->method('has')->with('hidden-field')
            ->willReturn(true)
        ;

        $extension = new HoneypotFormExtension(
            $this->translator,
            []
        );
        $extension->finishView($view, $form, [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_field' => 'hidden-field',
        ]);
    }

    public function testFinishWithEmptyFormFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid form factory to create a honeyput.');

        $view = $this->createMock(FormView::class);

        $config = $this->createMock(FormConfigInterface::class);
        $config->method('getAttribute')->with('antispam_honeypot_factory')
            ->willReturn(null)
        ;

        $form = $this->createMock(FormInterface::class);
        $form->method('has')->with('hidden-field')
            ->willReturn(false)
        ;
        $form->method('getConfig')
            ->willReturn($config)
        ;

        $extension = new HoneypotFormExtension(
            $this->translator,
            []
        );
        $extension->finishView($view, $form, [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_field' => 'hidden-field',
        ]);
    }

    public function testFinishViewForChildForm(): void
    {
        $view         = $this->createMock(FormView::class);
        $view->parent = $this->createMock(FormView::class);
        $form         = $this->createMock(FormInterface::class);
        $form->expects(self::never())->method('getConfig');

        $extension = new HoneypotFormExtension(
            $this->translator,
            []
        );
        $extension->finishView($view, $form, [
            'compound'                => true,
            'antispam_honeypot'       => true,
            'antispam_honeypot_class' => 'spamclass',
            'antispam_honeypot_field' => 'hidden-field',
        ]);
    }

    public function testFinishViewWithDisbaledAntispam(): void
    {
        $view = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->expects(self::never())->method('getConfig');

        $extension = new HoneypotFormExtension(
            $this->translator,
            []
        );
        $extension->finishView($view, $form, [
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
            $this->translator,
            [
                'global' => true,
                'class'  => 'my-class',
                'field'  => 'a-field',
            ]
        );
        $extension->configureOptions($resolver);

        $result = $resolver->resolve();

        self::assertTrue($result['antispam_honeypot']);
        self::assertSame('my-class', $result['antispam_honeypot_class']);
        self::assertSame('a-field', $result['antispam_honeypot_field']);
    }

    public function testExtendedTypes(): void
    {
        self::assertSame([FormType::class], HoneypotFormExtension::getExtendedTypes());
    }
}
