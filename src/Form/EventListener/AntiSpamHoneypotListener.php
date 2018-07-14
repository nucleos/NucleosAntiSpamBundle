<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

final class AntiSpamHoneypotListener implements EventSubscriberInterface
{
    /**
     * Error message translation key.
     */
    private const ERROR_MESSAGE = 'honeypot_error';

    /**
     * Translation domain.
     */
    private const TRANSLATION_DOMAIN = 'Core23AntiSpamBundle';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param TranslatorInterface $translator
     * @param string              $fieldName
     */
    public function __construct(TranslatorInterface $translator, string $fieldName)
    {
        $this->translator        = $translator;
        $this->fieldName         = $fieldName;
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
        $data = $event->getData();

        if (!$form->isRoot() || !$form->getConfig()->getOption('compound')) {
            return;
        }

        // Honeypot trap hit
        if (!isset($data[$this->fieldName]) || !empty($data[$this->fieldName])) {
            $form->addError(new FormError($this->translator->trans(static::ERROR_MESSAGE, [], static::TRANSLATION_DOMAIN)));
        }

        // Remove honeypot
        if (is_array($data)) {
            unset($data[$this->fieldName]);
        }

        $event->setData($data);
    }
}
