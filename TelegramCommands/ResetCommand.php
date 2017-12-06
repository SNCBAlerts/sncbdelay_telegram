<?php

namespace drupol\sncbdelay_telegram\TelegramCommands;

use Telegram\Bot\Actions;

class ResetCommand extends AbstractUserCommand
{
    /**
     * @var string
     */
    protected $name = 'reset';

    /**
     * @var string
     */
    protected $description = 'Clear your alerts';

    /**
     * @var string
     */
    protected $usage = '/reset <text>';

    /**
     * @param $arguments
     *
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

        $messageLine = trim(substr($this->getUpdate()->getMessage()->getText(), 7));

        $tokens = [];
        if (empty($messageLine)) {
            // When there is no message from the user, display this help text.
            $response = $twig->render(
                '@SNCBDelayTelegram/Commands/Reset/smallHelpText.twig',
                [
                    'lang' => $this->getUserLanguage(),
                ]
            );

            $keyboard = [];
            foreach ($this->getCurrentSubscriptions() as $subscription) {
                $keyboard[] = ['/reset ' . $subscription['token']];
            }

            $this->replyWithMessage([
                'text' => $response,
                'parse_mode' => 'Markdown',
                'reply_markup' => $this->getTelegram()->replyKeyboardMarkup([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ]),
            ]);
        } else {
            $tokens = explode(',', $messageLine);
            $tokens = array_map('trim', array_map('strtolower', $tokens));
        }

        foreach ($tokens as $token) {
            $qb = $orm->createQueryBuilder();
            $qb->delete('drupol\sncbdelay_telegram\Entity\Subscription', 's')
                ->where('LOWER(s.token) = :token')
                ->setParameter('token', $token)
                ->andWhere('s.chatId = :chatId')
                ->setParameter('chatId', $this->getUpdate()->getMessage()->getChat()->getId())
                ->getQuery()->execute();
        }

        $message = [
            'text' => $this->getCurrentAlerts(),
            'parse_mode' => 'Markdown',
            'reply_markup' => $this->getTelegram()->replyKeyboardMarkup([
                'keyboard' => [[]],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ])
        ];

        $keys = array_map(function ($subscription) {
            return '/reset ' . $subscription['token'];
        }, $this->getCurrentSubscriptions());

        if (!empty($keys)) {
            $message['reply_markup'] = $this->getTelegram()->replyKeyboardMarkup([
                'keyboard' => [$keys],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]);
        }

        $this->replyWithMessage($message);
    }
}
