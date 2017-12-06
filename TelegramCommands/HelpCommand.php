<?php

namespace drupol\sncbdelay_telegram\TelegramCommands;

use Telegram\Bot\Actions;

class HelpCommand extends AbstractUserCommand
{
    /**
     * @var string
     */
    protected $name = 'help';

    /**
     * @var string
     */
    protected $description = 'Display some help';

    /**
     * @var string
     */
    protected $usage = '/help';

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
        /** @var \Twig_Environment $twig */
        $twig = $this->getContainer()->get('twig');

        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $commands = [];
        foreach ($this->getTelegram()->getCommands() as $name => $command) {
            if ('debug' == $name) {
                continue;
            }
            $commands[$name] = $command->getDescription();
        }

        $response = $twig->render(
            '@SNCBDelayTelegram/Commands/Help/help.twig',
            [
                'commands' => $commands,
                'lang' => $this->getUserLanguage(),
            ]
        );

        $this->replyWithMessage([
            'text' => $response,
            'parse_mode' => 'Markdown',
            'reply_markup' => $this->getTelegram()->replyKeyboardMarkup([
                'keyboard' => [['/help']],
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }
}
