<?php

namespace drupol\sncbdelay_telegram\EventSubscriber;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractTelegramCustomEventSubscriber extends AbstractTelegramEventSubscriber
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return ['sncbdelay.message.custom' => 'handler'];
    }

    /**
     * @param \Symfony\Component\EventDispatcher\Event $event
     */
    public function process(Event $event)
    {
        $this->sendToChannel($event);
        $text = $this->getMessage($event);

        $subscriptions = $this->doctrine->createQuery('SELECT DISTINCT(s.chatId) FROM drupol\sncbdelay_telegram\Entity\Subscription s')
            ->getArrayResult();

        /** @var \drupol\sncbdelay_telegram\Entity\Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $this->telegram->sendMessage([
                'chat_id' => $subscription['chatId'],
                'text' => $text,
                'parse_mode' => 'Markdown'
            ]);
        }
    }
}
