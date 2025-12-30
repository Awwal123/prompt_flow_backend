<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Services\GeminiService;
use App\Services\OpenAIService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use HttpResponses;

    /**
     * Fetch all chats 
     */
    public function getChats(Request $request)
    {
        $chats = Chat::where('user_id', $request->user()->id)
            ->withCount('messages')
            ->latest()
            ->get();

        return $this->success($chats, 'Chats fetched successfully');
    }

    /**
     * Create a new chat
     */
    public function createChat(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $chat = Chat::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
        ]);

        return $this->success($chat, 'Chat created successfully', 201);
    }

    /**
     * Delete a chat along with all its messages
     */
    public function deleteChat(Request $request, $chatId)
    {
        $chat = Chat::where('user_id', $request->user()->id)
            ->where('id', $chatId)
            ->first();

        if (! $chat) {
            return $this->error('Chat not found', 404);
        }

        $chat->messages()->delete();
        $chat->delete();

        return $this->success(null, 'Chat deleted successfully');
    }

    /**
     * Fetch all messages for a specific chat
     */
    public function getMessages(Request $request, $chatId)
    {
        $chat = Chat::where('user_id', $request->user()->id)
            ->where('id', $chatId)
            ->first();

        if (! $chat) {
            return $this->error('Chat not found', 404);
        }

        $messages = $chat->messages()->latest()->get();

        return $this->success($messages, 'Messages fetched successfully');
    }

    /**
     * Send a message in a chat (with AI response)
     */
public function sendMessage(
        Request $request,
        $chatId,
        GeminiService $aiService
    ) {
        $request->validate([
            'message' => 'required|string',
        ]);

        $chat = Chat::where('user_id', $request->user()->id)
            ->where('id', $chatId)
            ->first();

        if (! $chat) {
            return $this->error('Chat not found', 404);
        }

        // Save user message
        $userMessage = Message::create([
            'chat_id' => $chat->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'type' => 'user',
        ]);

        // Call Gemini AI
        $aiResponse = $aiService->sendPrompt($request->message);

        // Save AI message
        $aiMessage = Message::create([
            'chat_id' => $chat->id,
            'message' => $aiResponse,
            'type' => 'assistant',
        ]);

        return $this->success([
            'user_message' => $userMessage,
            'ai_message' => $aiMessage,
        ], 'Message sent successfully', 201);
    }
}