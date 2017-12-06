<?php

namespace drupol\sncbdelay_telegram\EventSubscriber;

abstract class AbstractTelegramAlertEventSubscriber extends AbstractTelegramEventSubscriber
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return ['sncbdelay.message.alert' => 'handler'];
    }
}
