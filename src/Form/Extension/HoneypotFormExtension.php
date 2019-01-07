<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Form\Extension;

use Core23\AntiSpamBundle\Form\EventListener\AntiSpamHoneypotListener;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

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

    /**
     * @param TranslatorInterface $translator
     * @param array               $defaults
     */
    public function __construct(TranslatorInterface $translator, array $defaults)
    {
        $this->translator        = $translator;
        $this->defaults          = $defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['antispam_honeypot']) {
            return;
        }

        $builder
            ->setAttribute('antispam_honeypot_factory', $builder->getFormFactory())
            ->addEventSubscriber(new AntiSpamHoneypotListener($this->translator, $options['antispam_honeypot_field']));
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($view->parent || !$options['antispam_honeypot'] || !$options['compound']) {
            return;
        }

        if ($form->has($options['antispam_honeypot_field'])) {
            throw new \RuntimeException(sprintf('Honeypot field "%s" is already used.', $options['antispam_honeypot_field']));
        }

        $formOptions = [
            'mapped'   => false,
            'label'    => false,
            'required' => false,
        ];

        if (null === $options['antispam_honeypot_class']) {
            $formOptions['attr'] = [
                'style' => 'display:none',
            ];
        } else {
            $formOptions['attr'] = [
                'class' => $options['antispam_honeypot_class'],
            ];
        }

        $factory       = $form->getConfig()->getAttribute('antispam_honeypot_factory');

        if (!$factory instanceof FormFactoryInterface) {
            throw new \RuntimeException('Invalid form factory to create a honeyput.');
        }

        $formView = $factory
            ->createNamed($options['antispam_honeypot_field'], TextType::class, null, $formOptions)
            ->createView($view);

        $view->children[$options['antispam_honeypot_field']] = $formView;
    }

    /**
     * {@inheritdoc}
     */
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
            ->setAllowedTypes('antispam_honeypot_field', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}