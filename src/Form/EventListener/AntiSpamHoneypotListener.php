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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AntiSpamHoneypotListener implements EventSubscriberInterface
{
    /**
     * Error message translation key.
     */
    private const ERROR_MESSAGE = 'honeypot_error';

    /**
     * Translation domain.
     */
    private const TRANSLATION_DOMAIN = 'NucleosAntiSpamBundle';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $fieldName;

    public function __construct(TranslatorInterface $translator, string $fieldName)
    {
        $this->translator        = $translator;
        $this->fieldName         = $fieldName;
    }

    public static function getSubscribedEvents()
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

        $data = $event->getData();

        // Honeypot trap hit
        if (!isset($data[$this->fieldName]) || '' !== (string) $data[$this->fieldName]) {
            $form->addError(new FormError($this->translator->trans(static::ERROR_MESSAGE, [], static::TRANSLATION_DOMAIN)));
        }

        // Remove honeypot
        if (\is_array($data)) {
            unset($data[$this->fieldName]);
        }

        $event->setData($data);
    }
}
