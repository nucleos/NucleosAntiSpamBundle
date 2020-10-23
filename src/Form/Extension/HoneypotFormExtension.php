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

namespace Nucleos\AntiSpamBundle\Form\Extension;

use Nucleos\AntiSpamBundle\Form\EventListener\AntiSpamHoneypotListener;
use RuntimeException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HoneypotFormExtension extends AbstractTypeExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array<string, mixed>
     */
    private $defaults;

    public function __construct(TranslatorInterface $translator, array $defaults)
    {
        $this->translator        = $translator;
        $this->defaults          = $defaults;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (true !== $options['antispam_honeypot']) {
            return;
        }

        $builder
            ->setAttribute('antispam_honeypot_factory', $builder->getFormFactory())
            ->addEventSubscriber(new AntiSpamHoneypotListener($this->translator, $options['antispam_honeypot_field']))
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (null !== $view->parent || true !== $options['antispam_honeypot'] || true !== $options['compound']) {
            return;
        }

        if ($form->has($options['antispam_honeypot_field'])) {
            throw new RuntimeException(sprintf('Honeypot field "%s" is already used.', $options['antispam_honeypot_field']));
        }
        $factory = $form->getConfig()->getAttribute('antispam_honeypot_factory');

        if (!$factory instanceof FormFactoryInterface) {
            throw new RuntimeException('Invalid form factory to create a honeyput.');
        }

        $formOptions = $this->createViewOptions($options);

        $formView = $factory
            ->createNamed($options['antispam_honeypot_field'], TextType::class, null, $formOptions)
            ->createView($view)
        ;

        $view->children[$options['antispam_honeypot_field']] = $formView;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'antispam_honeypot'        => $this->defaults['global'],
                'antispam_honeypot_class'  => $this->defaults['class'],
                'antispam_honeypot_field'  => $this->defaults['field'],
            ])
            ->setAllowedTypes('antispam_honeypot', 'bool')
            ->setAllowedTypes('antispam_honeypot_class', ['string', 'null'])
            ->setAllowedTypes('antispam_honeypot_field', 'string')
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            FormType::class,
        ];
    }

    private function createViewOptions(array $options): array
    {
        $formOptions = [
            'mapped'   => false,
            'label'    => false,
            'required' => false,
        ];

        if (!\array_key_exists('antispam_honeypot_class', $options) || null === $options['antispam_honeypot_class']) {
            $formOptions['attr'] = [
                'style' => 'display:none',
            ];
        } else {
            $formOptions['attr'] = [
                'class' => $options['antispam_honeypot_class'],
            ];
        }

        return $formOptions;
    }
}
