<?php

namespace drupol\sncbdelay_telegram\TelegramCommands;

use drupol\sncbdelay_telegram\Entity\Subscription;
use Telegram\Bot\Actions;

class AlertCommand extends AbstractUserCommand
{
    /**
     * @var string
     */
    protected $name = 'alert';

    /**
     * @var string
     */
    protected $description = 'Manage your alerts';

    /**
     * @var string
     */
    protected $usage = '/alert <text>';

    /**
     * @param $arguments
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return mixed|void
     */
    public function handle($arguments)
    {
        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        /** @var \Doctrine\ORM\EntityManager $orm */
        $orm = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var \Twig_Environment $twig */
        $twig = $this->getContainer()->get('twig');

        // Remove the prefix '/alert' and trim the string.
        $messageLine = trim(substr($this->getUpdate()->getMessage()->getText(), 7));

        $tokens = [];

        // Count existing alerts for this user.
        $countAlerts = $this->getCurrentSubscriptionPerUserCount();

        if (empty($messageLine)) {
            // When there is no message from the user, display this help text.
            $response = $twig->render(
                '@SNCBDelayTelegram/Commands/Alert/smallHelpText.twig',
                [
                    'lang' => $this->getUserLanguage(),
                ]
            );
            $this->replyWithMessage([
                'text' => $response,
                'parse_mode' => 'Markdown',
            ]);
        } else {
            if ($this->getMaxAlert() == $countAlerts) {
                // Users can set only X alerts.
                // Maybe this limit will change in the future.
                $response = $twig->render(
                    '@SNCBDelayTelegram/Commands/Alert/maxAlerts.twig',
                    [
                        'lang' => $this->getUserLanguage(),
                    ]
                );
                $this->replyWithMessage(['text' => $response]);
            } else {
                // When there is a message, parse the message into tokens.
                $tokens = explode(',', $messageLine);
                $tokens = array_map('trim', array_map('strtolower', $tokens));
            }
        }

        // For each tokens, save it in the database.
        foreach ($tokens as $token) {
            /** @var Doctrine\ORM\Query $query */
            $count = $orm->createQuery('SELECT COUNT(s.token) FROM drupol\sncbdelay_telegram\Entity\Subscription s WHERE s.chatId = :chatId AND s.token = :token')
                ->setParameter('token', $token)
                ->setParameter('chatId', $this->getUpdate()->getMessage()->getChat()->getId())
                ->getSingleScalarResult();

            // Check if the subscription already exist.
            if (0 == $count) {
                // Check if user is allowed to add more alerts.
                if ($countAlerts < $this->getMaxAlert()) {
                    $subscription = new Subscription();
                    $subscription->setChatId($this->getUpdate()->getMessage()->getChat()->getId());
                    $subscription->setToken($token);

                    $orm->persist($subscription);
                    $orm->flush();
                }
            }
        }

        $this->replyWithMessage([
            'text' => $this->getCurrentAlerts(),
            'parse_mode' => 'Markdown',
        ]);
    }
}
