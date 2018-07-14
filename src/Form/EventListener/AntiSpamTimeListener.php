<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Form\EventListener;

use Core23\AntiSpamBundle\Provider\TimeProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

final class AntiSpamTimeListener implements EventSubscriberInterface
{
    /**
     * Error message translation key.
     */
    private const ERROR_MESSAGE = 'time_error';

    /**
     * Translation domain.
     */
    private const TRANSLATION_DOMAIN = 'Core23AntiSpamBundle';

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

    /**
     * @param TimeProviderInterface $timeProvider
     * @param TranslatorInterface   $translator
     * @param array                 $options
     */
    public function __construct(TimeProviderInterface $timeProvider, TranslatorInterface $translator, array $options)
    {
        $this->timeProvider      = $timeProvider;
        $this->translator        = $translator;
        $this->options           = $options;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event): void
    {
        $form = $event->getForm();

        if (!$form->isRoot() || !$form->getConfig()->getOption('compound')) {
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
