<?php

namespace drupol\sncbdelay_telegram\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Telegram\Bot\Exceptions\TelegramSDKException;

class Webhook extends AbstractController
{
    /**
     * @Route("/telegram/set", name="telegram_set_webhook")
     */
    public function setWebhook(Request $request)
    {
        /** @var \Telegram\Bot\Api $telegram */
        $telegram = $this->container->get('sncbdelay_telegram.telegram');

        $hook_url = 'https://sncbalerts.herokuapp.com/telegram/get';

        try {
            $telegram->setWebhook(['url' => $hook_url]);
        } catch (TelegramSDKException $e) {
            // @todo
        }

        return new JsonResponse(['result' => true]);
    }

    /**
     * @Route("/telegram/get", name="telegram_get_webhook")
     */
    public function getWebhook(Request $request)
    {
        /** @var \Telegram\Bot\Api $telegram */
        $telegram = $this->container->get('sncbdelay_telegram.telegram');

        $telegram->addCommands([
            $this->container->get('drupol\sncbdelay_telegram\TelegramCommands\AlertCommand'),
            $this->container->get('drupol\sncbdelay_telegram\TelegramCommands\HelpCommand'),
            $this->container->get('drupol\sncbdelay_telegram\TelegramCommands\ResetCommand'),
            $this->container->get('drupol\sncbdelay_telegram\TelegramCommands\DebugCommand'),
        ]);

        $telegram->commandsHandler(true);

        return new JsonResponse(['result' => true]);
    }

    /**
     * @Route("/telegram/unset", name="telegram_unset_webhook")
     */
    public function unsetWebhook(Request $request)
    {
        /** @var \Telegram\Bot\Api $telegram */
        $telegram = $this->container->get('sncbdelay_telegram.telegram');

        // Not implemented.

        return new JsonResponse(['result' => true]);
    }
}
