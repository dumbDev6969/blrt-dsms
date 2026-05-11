<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;

new class extends Component
{
    #[Validate('required|min:2|max:500')]
    public string $message = '';

    // Only used for rendering — actual data lives in session()
    public array $chatHistory = [];

    // ─────────────────────────────────────────
    // Lifecycle
    // ─────────────────────────────────────────

    public function mount(): void
    {
        // Sync session history into public property on first render
        $this->chatHistory = session()->get('chatbot_history', []);

        // Seed a welcome message if this is a fresh session
        if (empty($this->chatHistory)) {
            $this->appendMessage('bot', "Hi! I'm the BLRT Assistant. How can I help you today?");
        }
    }

    // ─────────────────────────────────────────
    // Computed Property — keeps $chatHistory fresh
    // ─────────────────────────────────────────

    public function getChatHistoryProperty(): array
    {
        return session()->get('chatbot_history', []);
    }

    // ─────────────────────────────────────────
    // Main Action
    // ─────────────────────────────────────────

    public function sendMessage(): void
    {
        // Step 1 — Validate
        $this->validate();

        // Step 2 — Sanitize input
        $userText = strip_tags(trim($this->message));

        // Step 3 — Append user message immediately (Optimistic UI)
        $this->appendMessage('user', $userText);

        // Step 4 — Clear input field
        $this->message = '';

        // Step 5 — Scroll to bottom after user bubble appears
        $this->dispatch('scroll-to-bottom');

        // Step 6 — Call Dialogflow with graceful failure
        $botReply = $this->queryDialogflow($userText);

        // Step 7 — Append bot response
        $this->appendMessage('bot', $botReply);

        // Step 8 — Scroll again after bot bubble appears
        $this->dispatch('scroll-to-bottom');
    }

    // ─────────────────────────────────────────
    // Dialogflow API Call
    // ─────────────────────────────────────────

    private function queryDialogflow(string $userText): string
    {
        $projectId   = config('services.dialogflow.project_id');
        $credentials = config('services.dialogflow.credentials_json');
        $sessionId   = $this->getOrCreateSessionId();

        $sessionsClient = null;

        try {
            $sessionsClient = new SessionsClient([
                'credentials' => $credentials,
            ]);

            $sessionName = $sessionsClient->sessionName($projectId, $sessionId);

            $textInput = (new TextInput())
                ->setText($userText)
                ->setLanguageCode('en-US');

            $queryInput = (new QueryInput())
                ->setText($textInput);

            $request = (new DetectIntentRequest())
                ->setSession($sessionName)
                ->setQueryInput($queryInput);

            $response     = $sessionsClient->detectIntent($request);
            $queryResult  = $response->getQueryResult();
            $fulfillment  = $queryResult->getFulfillmentText();

            return $fulfillment ?: "I'm not sure how to respond to that. Could you rephrase?";

        } catch (\Exception $e) {
            Log::error('Dialogflow API error', [
                'message'    => $e->getMessage(),
                'session_id' => $sessionId,
                'user_input' => $userText,
            ]);

            return "I'm having trouble connecting right now. Please try again in a moment.";

        } finally {
            $sessionsClient?->close();
        }
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    private function appendMessage(string $role, string $content): void
    {
        $history = session()->get('chatbot_history', []);

        $history[] = [
            'role'      => $role,       // 'user' | 'bot'
            'content'   => $content,
            'timestamp' => now()->format('H:i'),
        ];

        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        session()->put('chatbot_history', $history);
        $this->chatHistory = $history;
    }

    private function getOrCreateSessionId(): string
    {
        return session()->remember('chatbot_session_id', fn () => (string) str()->uuid());
    }

    public function clearChat(): void
    {
        session()->forget(['chatbot_history', 'chatbot_session_id']);
        $this->chatHistory = [];
        $this->appendMessage('bot', "Chat cleared! How can I help you?");
    }
};
?>

<div 
    x-data="{ 
        isOpen: false,
        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.chatContainer) {
                    this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                }
            });
        }
    }"
    x-on:scroll-to-bottom.window="scrollToBottom()"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end"
>
    <!-- ─── Chat Window ─── -->
    <div 
        x-show="isOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-10 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-10 scale-95"
        class="mb-4 w-80 md:w-96 h-[500px] bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-zinc-200 dark:border-zinc-700 flex flex-col overflow-hidden"
        style="display: none;"
    >
        <!-- Header -->
        <div class="p-3.5 bg-accent text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <flux:avatar size="sm" class="bg-white/20 text-white">
                        <flux:icon name="chat-bubble-left-ellipsis" variant="mini" class="size-5" />
                    </flux:avatar>
                    <span class="absolute -bottom-0.5 -right-0.5 size-2.5 bg-green-400 border-2 border-blue-500 rounded-full"></span>
                </div>
                <div>
                    <flux:heading size="sm" class="!text-white !font-semibold">BLRT Assistant</flux:heading>
                    <flux:subheading size="sm" class="!text-blue-100/80 !text-[10px]">Always active</flux:subheading>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="clearChat" class="!text-white/70 hover:!text-white hover:!bg-white/15 !size-7" />
                <flux:button size="sm" variant="ghost" icon="x-mark" @click="isOpen = false" class="!text-white/70 hover:!text-white hover:!bg-white/15 !size-7" />
            </div>
        </div>

        <!-- Chat History -->
        <div 
            x-ref="chatContainer"
            x-init="$nextTick(() => scrollToBottom())"
            role="log" 
            aria-live="polite"
            class="flex-1 overflow-y-auto p-4 space-y-3 bg-zinc-50 dark:bg-zinc-950/50 scroll-smooth"
        >
            @foreach($this->chatHistory as $chat)
                <div class="flex {{ $chat['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="flex flex-col max-w-[80%] {{ $chat['role'] === 'user' ? 'items-end' : 'items-start' }}">
                        <div class="flex items-center gap-1.5 mb-1">
                            @if($chat['role'] === 'bot')
                                <flux:badge size="sm" color="zinc" class="!text-[9px] !px-1.5 !py-0 !font-semibold !uppercase !tracking-wider">Assistant</flux:badge>
                            @endif
                            <span class="text-[10px] text-zinc-400 dark:text-zinc-500 tabular-nums">{{ $chat['timestamp'] }}</span>
                            @if($chat['role'] === 'user')
                                <flux:badge size="sm" color="blue" class="!text-[9px] !px-1.5 !py-0 !font-semibold !uppercase !tracking-wider">You</flux:badge>
                            @endif
                        </div>
                        <div @class([
                            'px-3.5 py-2.5 rounded-2xl text-sm leading-relaxed',
                            'bg-accent text-white rounded-tr-sm shadow-sm' => $chat['role'] === 'user',
                            'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 rounded-tl-sm shadow-xs' => $chat['role'] === 'bot',
                        ])>
                            {{ $chat['content'] }}
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Typing Indicator -->
            <div wire:loading wire:target="sendMessage" class="flex justify-start">
                <div class="flex flex-col items-start max-w-[80%]">
                    <div class="px-4 py-3 bg-white dark:bg-zinc-800 rounded-2xl rounded-tl-sm border border-zinc-200 dark:border-zinc-700 shadow-xs">
                        <div class="flex gap-1">
                            <span class="size-1.5 bg-zinc-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                            <span class="size-1.5 bg-zinc-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                            <span class="size-1.5 bg-zinc-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer / Input -->
        <div class="p-3 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700">
            <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                <flux:input 
                    wire:model.live="message" 
                    placeholder="Type your message..." 
                    autocomplete="off"
                    size="sm"
                    class:input="!rounded-lg"
                />
                <flux:button 
                    type="submit" 
                    variant="primary" 
                    size="sm" 
                    icon="paper-airplane" 
                    wire:loading.attr="disabled"
                    class="shrink-0"
                />
            </form>
        </div>
    </div>

    <!-- ─── Toggle Button ─── -->
    <button 
        @click="isOpen = !isOpen" 
        class="size-14 rounded-full bg-accent text-white shadow-lg hover:bg-blue-600 transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center justify-center relative group cursor-pointer"
    >
        <flux:icon x-show="!isOpen" name="chat-bubble-left-ellipsis" class="size-7" />
        <flux:icon x-show="isOpen" name="x-mark" class="size-7" style="display: none;" />
        
        <span x-show="!isOpen" class="absolute -top-0.5 -right-0.5 size-3.5 bg-red-500 rounded-full border-2 border-white dark:border-zinc-800 animate-pulse"></span>
    </button>
</div>