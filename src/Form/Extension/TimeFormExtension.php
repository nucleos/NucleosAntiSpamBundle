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

use Nucleos\AntiSpamBundle\Form\EventListener\AntiSpamTimeListener;
use Nucleos\AntiSpamBundle\Provider\TimeProviderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TimeFormExtension extends AbstractTypeExtension
{
    /**
     * @var TimeProviderInterface
     */
    private $timeProvider;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array<string, mixed>
     */
    private $defaults;

    public function __construct(TimeProviderInterface $timeProvider, TranslatorInterface $translator, array $defaults)
    {
        $this->timeProvider      = $timeProvider;
        $this->translator        = $translator;
        $this->defaults          = $defaults;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (true !== $options['antispam_time']) {
            return;
        }

        $providerOptions = [
            'min' => $options['antispam_time_min'],
            'max' => $options['antispam_time_max'],
        ];

        $builder
            ->addEventSubscriber(new AntiSpamTimeListener($this->timeProvider, $this->translator, $providerOptions))
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (null !== $view->parent || true !== $options['antispam_time'] || true !== $options['compound']) {
            return;
        }

        $this->timeProvider->createFormProtection($form->getName());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'antispam_time'         => $this->defaults['global'],
                'antispam_time_min'     => $this->defaults['min'],
                'antispam_time_max'     => $this->defaults['max'],
            ])
            ->setAllowedTypes('antispam_time', 'bool')
            ->setAllowedTypes('antispam_time_min', 'int')
            ->setAllowedTypes('antispam_time_max', 'int')
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            FormType::class,
        ];
    }
}
