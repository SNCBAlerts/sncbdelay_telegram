<?php

namespace drupol\sncbdelay_telegram\TelegramCommands;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Telegram\Bot\Commands\Command;

abstract class AbstractUserCommand extends Command
{
    use ContainerAwareTrait;

    /**
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return array
     */
    public function getCurrentSubscriptions()
    {
        /** @var \Doctrine\ORM\EntityManager $orm */
        $orm = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Display current alerts.
        $db = $orm->createQueryBuilder();

        return $db->select('s')
            ->from('drupol\sncbdelay_telegram\Entity\Subscription', 's')
            ->where('s.chatId = :chatId')
            ->setParameter('chatId', $this->getUpdate()->getMessage()->getChat()->getId())
            ->orderBy('s.token', 'ASC')
            ->getQuery()->getArrayResult();
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return mixed
     */
    public function getCurrentSubscriptionPerUserCount()
    {
        /** @var \Doctrine\ORM\EntityManager $orm */
        $orm = $this->getContainer()->get('doctrine.orm.entity_manager');

        $qb = $orm->createQueryBuilder();
        $qb->select('count(s.token)');
        $qb->from('drupol\sncbdelay_telegram\Entity\Subscription', 's');
        $qb->where('s.chatId = :chatId');
        $qb->setParameter('chatId', $this->getUpdate()->getMessage()->getChat()->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return string
     */
    public function getCurrentAlerts()
    {
        /** @var \Twig_Environment $twig */
        $twig = $this->getContainer()->get('twig');

        return $twig->render(
            '@SNCBDelayTelegram/Commands/Alert/currentAlerts.twig',
            [
                'subscriptions' => $this->getCurrentSubscriptions(),
                'lang' => $this->getUserLanguage(),
            ]
        );
    }

    /**
     * @return string
     */
    public function getUserLanguage()
    {
        $lang = $this->getUpdate()->getMessage()->getFrom()->get('language_code', 'en');

        return strtolower(trim(substr($lang,0, 2)));
    }

    /**
     * Max alerts per user.
     *
     * @return int
     */
    public function getMaxAlert()
    {
        return 4;
    }
}
