<?php

namespace drupol\sncbdelay_telegram\EventSubscriber\Telegram;

use drupol\sncbdelay_telegram\EventSubscriber\AbstractTelegramDelayEventSubscriber;
use Symfony\Component\EventDispatcher\Event;

class Delay extends AbstractTelegramDelayEventSubscriber
{
    /**
     * @param \Symfony\Component\EventDispatcher\Event $event
     *
     * @return mixed|string
     */
    public function getMessage(Event $event)
    {
        $departure = $event->getStorage()['departure'];
        $station = $event->getStorage()['station'];
        $lines = $event->getStorage()['lines'];

        date_default_timezone_set('Europe/Brussels');

        return $this->twig->render(
            '@SNCBDelayTelegram/delay.twig',
            [
                'train' => $departure['vehicle'],
                'station_from' => $station['name'],
                'station_to' => $departure['stationinfo']['name'],
                'delay' => $departure['delay']/60,
                'date' => date('H:i', $departure['time']),
                'url' => $departure['departureConnection'],
                'platform' => $departure['platform'],
                'lines' => $lines,
            ]
        );
    }
}
