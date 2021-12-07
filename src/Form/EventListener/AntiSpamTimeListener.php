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

namespace Nucleos\AntiSpamBundle\Form\EventListener;

use Nucleos\AntiSpamBundle\Provider\TimeProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AntiSpamTimeListener implements EventSubscriberInterface
{
    /**
     * Error message translation key.
     */
    private const ERROR_MESSAGE = 'time_error';

    /**
     * Translation domain.
     */
    private const TRANSLATION_DOMAIN = 'NucleosAntiSpamBundle';

    private TimeProviderInterface $timeProvider;

    private TranslatorInterface $translator;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(TimeProviderInterface $timeProvider, TranslatorInterface $translator, array $options)
    {
        $this->timeProvider      = $timeProvider;
        $this->translator        = $translator;
        $this->options           = $options;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSubmit(FormEvent $event): void
    {
        $form = $event->getForm();

        if (!$form->isRoot() || null === $form->getConfig()->getOption('compound')) {
            return;
        }

        // Out of time hit
        if (!$this->timeProvider->isValid($form->getName(), $this->options)) {
            $form->addError(new FormError($this->translator->trans(static::ERROR_MESSAGE, [], static::TRANSLATION_DOMAIN)));
        }

        // Remove old entry
        $this->timeProvider->removeFormProtection($form->getName());
    }
}
