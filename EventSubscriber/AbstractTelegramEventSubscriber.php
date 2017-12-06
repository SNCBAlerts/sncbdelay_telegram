<?php

namespace drupol\sncbdelay_telegram\EventSubscriber;

use Doctrine\ORM\EntityManager;
use drupol\sncbdelay\EventSubscriber\AbstractEventSubscriber;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractTelegramEventSubscriber extends AbstractEventSubscriber
{
    /**
     * @var \Telegram\Bot\Api
     */
    protected $telegram;

    /**
     * AbstractTelegramEventSubscriber constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Twig_Environment $twig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param \Doctrine\ORM\EntityManager $doctrine
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container, \Twig_Environment $twig, LoggerInterface $logger, CacheItemPoolInterface $cache, EntityManager $doctrine)
    {
        $this->telegram = $container->get('sncbdelay_telegram.telegram');
        parent::__construct($container, $twig, $logger, $cache, $doctrine);
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
            if (false !== stripos($text, $subscription['token'])) {
                $this->telegram->sendMessage([
                    'chat_id' => $subscription['chatId'],
                    'text' => $text,
                    'parse_mode' => 'Markdown'
                ]);
            }
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
        $telegramConfig = $this->getContainer()->getParameter('telegram');

        $this->telegram->sendMessage([
            'chat_id' => $telegramConfig['public_channel'],
            'text' => $text,
            'parse_mode' => 'Markdown',
            'disable_notification' => true,
            'disable_web_page_preview' => true,
        ]);
    }
}
