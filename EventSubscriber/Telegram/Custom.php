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
