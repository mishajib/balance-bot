<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function webhook(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        // Handle non-command messages
        if ($update->getMessage()) {
            $message = $update->getMessage();
            $text = $message->getText();
            $chatId = $message->getChat()->getId();

            // Custom message handling
            if (! str_starts_with($text, '/')) {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "You said: $text\n\nUse /help to see available commands.",
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * @throws TelegramSDKException
     */
    public function setWebhook()
    {
        $url = url('/telegram/webhook');
        $response = Telegram::setWebhook(['url' => $url]);

        return response()->json([
            'message' => 'Webhook set successfully',
            'url' => $url,
            'response' => $response,
        ]);
    }

    /**
     * @throws TelegramSDKException
     */
    public function getWebhookInfo()
    {
        $response = Telegram::getWebhookInfo();

        return response()->json($response);
    }

    /**
     * @throws TelegramSDKException
     */
    public function removeWebhook()
    {
        $response = Telegram::removeWebhook();

        return response()->json([
            'message' => 'Webhook removed',
            'response' => $response,
        ]);
    }
}
