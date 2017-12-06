<?php

namespace drupol\sncbdelay_telegram\EventSubscriber;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractTelegramDelayEventSubscriber extends AbstractTelegramEventSubscriber
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return ['sncbdelay.message.delay' => 'handler'];
    }

    /**
     * @param \Symfony\Component\EventDispatcher\Event $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function handler(Event $event)
    {
        $departure = $event->getStorage()['departure'];
        date_default_timezone_set('Europe/Brussels');

        $currentTime = time();

        $uniqid1 = sha1(serialize([static::class, $departure]));
        $uniqid2 = sha1(serialize([static::class, $departure['vehicleinfo']['name']]));

        $cache1 = $this->cache->getItem($uniqid1)->expiresAfter(new \DateInterval('PT10M'));
        $cache2 = $this->cache->getItem($uniqid2)->expiresAfter(new \DateInterval('PT10M'));

        if (
            !$cache1->isHit() &&
            !$cache2->isHit() &&
            $departure['time'] > $currentTime - 600 &&
            abs($departure['time'] - $currentTime) <= 1200 &&
            $departure['delay'] > 0
        ) {
            $this->process($event);

            $cache1->set($event);
            $cache2->set($event);

            $this->cache->save($cache1);
            $this->cache->save($cache2);
        }
    }
}
