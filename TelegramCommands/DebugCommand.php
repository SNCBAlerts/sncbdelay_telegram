<?php

namespace drupol\sncbdelay_telegram\TelegramCommands;

use Telegram\Bot\Actions;

class DebugCommand extends AbstractUserCommand
{
    /**
     * @var string
     */
    protected $name = 'debug';

    /**
     * @var string
     */
    protected $description = 'Debug';

    /**
     * @var string
     */
    protected $usage = '/debug';

    /**
     * @param $arguments
     *
     * @return mixed|void
     */
    public function handle($arguments)
    {
        /** @var \Twig_Environment $twig */
        $twig = $this->getContainer()->get('twig');

        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $lang = $this->getUpdate()->getMessage()->getFrom()->get('language_code', 'en');
        $lang = strtolower(trim(substr($lang,0, 2)));

        $response = $twig->render(
            '@SNCBDelayTelegram/Commands/Alert/currentAlerts.twig',
            [
                'subscriptions' => [],
                'lang' => $lang,
            ]
        );

        $subscriptions = $this->getCurrentSubscriptions();

        $keyboard = [];
        foreach ($subscriptions as $subscription) {
            $keyboard[] = ['/reset ' . $subscription['token']];
        }

        $this->replyWithMessage([
            'text' => $response,
            'reply_markup' => $this->getTelegram()->replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }
}
