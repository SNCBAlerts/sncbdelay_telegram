<?php

namespace drupol\sncbdelay_telegram\EventSubscriber;

use Doctrine\ORM\EntityManager;
use drupol\sncbdelay\EventSubscriber\AbstractEventSubscriber;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\Event;
use Telegram\Bot\Api;
use Twig\Environment;

abstract class AbstractTelegramEventSubscriber extends AbstractEventSubscriber
{
    /**
     * @var \Telegram\Bot\Api
     */
    protected $telegram;

    /**
     * AbstractTelegramEventSubscriber constructor.
     *
     * @param \Telegram\Bot\Api $telegram
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $parameters
     * @param \Twig\Environment $twig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param \Doctrine\ORM\EntityManager $doctrine
     */
    public function __construct(Api $telegram, ContainerBagInterface $parameters, Environment $twig, LoggerInterface $logger, CacheItemPoolInterface $cache, EntityManager $doctrine)
    {
        parent::__construct($parameters, $twig, $logger, $cache, $doctrine);
        $this->telegram = $telegram;
    }

    /**
     * @param \Symfony\Component\EventDispatcher\Event $event
     */
    public function process(Event $event)
    {
        $this->sendToChannel($event);
        $this->sendToSubscribers($event);
    }

    /**
     * Send an event to subscribers.
     *
     * @param \Symfony\Component\EventDispatcher\Event $event
     */
    public function sendToSubscribers(Event $event)
    {
        $text = $this->getMessage($event);

        $db = $this->doctrine->createQueryBuilder();
        $subscriptions = $db->select(['s.chatId', 's.token'])
            ->from('drupol\sncbdelay_telegram\Entity\Subscription', 's')
            ->distinct()->getQuery()->getResult();

        /** @var \drupol\sncbdelay_telegram\Entity\Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            if (false === stripos($text, $subscription['token'])) {
                continue;
            }

            $this->telegram->sendMessage([
                'chat_id' => $subscription['chatId'],
                'text' => $text,
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    /**
     * Send an event to the public channel.
     *
     * @param \Symfony\Component\EventDispatcher\Event $event
     */
    public function sendToChannel(Event $event)
    {
        $text = $this->getMessage($event);
        $telegramConfig = $this->parameters->get('telegram');

        $this->telegram->sendMessage([
            'chat_id' => $telegramConfig['public_channel'],
            'text' => $text,
            'parse_mode' => 'Markdown',
            'disable_notification' => true,
            'disable_web_page_preview' => true,
        ]);
    }
}
