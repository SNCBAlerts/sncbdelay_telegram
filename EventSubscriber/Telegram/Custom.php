<?php

namespace drupol\sncbdelay_telegram\EventSubscriber\Telegram;

use drupol\sncbdelay_telegram\EventSubscriber\AbstractTelegramCustomEventSubscriber;
use Symfony\Component\EventDispatcher\Event;

class Custom extends AbstractTelegramCustomEventSubscriber
{

    /**
     * @param \Symfony\Component\EventDispatcher\Event $event
     *
     * @return mixed|string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getMessage(Event $event)
    {
        return $this->twig->render(
            '@SNCBDelayTelegram/custom.twig',
            [
                'message' => $event->getStorage()['message'],
            ]
        );
    }
}
