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
use Symfony\Component\Form\FormInterface;
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

    /**
     * @var TimeProviderInterface
     */
    private $timeProvider;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array
     */
    private $options;

    public function __construct(TimeProviderInterface $timeProvider, TranslatorInterface $translator, array $options)
    {
        $this->timeProvider      = $timeProvider;
        $this->translator        = $translator;
        $this->options           = $options;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::POST_SUBMIT => 'postSubmit',
        ];
    }

    public function preSubmit(FormEvent $event): void
    {
        $form = $event->getForm();

        if (!$this->isApplicableToForm($form)) {
            return;
        }

        // Out of time hit
        if (!$this->timeProvider->isValid($form->getName(), $this->options)) {
            $form->addError(new FormError($this->translator->trans(static::ERROR_MESSAGE, [], static::TRANSLATION_DOMAIN)));
        }

        // Remove old entry
        $this->timeProvider->removeFormProtection($form->getName());
    }

    private function isApplicableToForm(FormInterface $form): bool
    {
        return $form->isRoot() && null !== $form->getConfig()->getOption('compound');
    }

    public function postSubmit(FormEvent $event): void
    {
        $form = $event->getForm();

        if (!$this->isApplicableToForm($form)) {
            return;
        }

        // If form has errors, set the time again
        if (!$form->isValid()) {
            $this->timeProvider->createFormProtection($form->getName());
        }
    }
}
