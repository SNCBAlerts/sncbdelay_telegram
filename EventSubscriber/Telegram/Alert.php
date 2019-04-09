<?php

namespace drupol\sncbdelay_telegram\EventSubscriber\Telegram;

use drupol\sncbdelay_telegram\EventSubscriber\AbstractTelegramAlertEventSubscriber;
use Symfony\Component\EventDispatcher\Event;

class Alert extends AbstractTelegramAlertEventSubscriber
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
        $disturbance = $event->getStorage()['disturbance'];
        date_default_timezone_set('Europe/Brussels');

        return $this->twig->render(
            '@SNCBDelayTelegram/alert.twig',
            [
                'title' => $disturbance['title'],
                'description' => $disturbance['description'],
                'url' => $disturbance['link'],
                'time' => date('H:i', $disturbance['timestamp']),
            ]
        );
    }
}
