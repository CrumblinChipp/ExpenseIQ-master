<div
    x-data="{ open: $persist(false).as('expenseiq-chat-open') }"
    x-init="$watch('open', value => {
        if (value) {
            $nextTick(() => {
                $refs.scroller.scrollTop = $refs.scroller.scrollHeight;
                $refs.input.focus();
            });
        }
    })"
    class="fixed bottom-6 right-6 z-40">

    {{-- Chat panel --}}
    <div
        x-show="open"
        x-transition.origin.bottom.right
        style="display: none;"
        class="absolute bottom-[72px] right-0 flex h-[560px] max-h-[calc(100vh-9rem)] w-[380px] max-w-[calc(100vw-3rem)] flex-col overflow-hidden rounded-[28px] border-2 border-[#F3C400] bg-white shadow-2xl">

        {{-- Header --}}
        <div class="flex items-center justify-between bg-[#FDE68A] px-5 py-4">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-400">
                    <i class="fa-solid fa-wand-magic-sparkles text-[#6B4F00]"></i>
                </div>

                <div>
                    <h2 class="font-bold leading-tight text-[#5D4300]">Ask ExpenseIQ</h2>
                    <p class="text-xs text-[#7B6100]">About your spending</p>
                </div>

            </div>

            <div class="flex items-center gap-1">

                @if(count($messages))
                    <button
                        type="button"
                        wire:click="clearChat"
                        title="Clear chat"
                        class="flex h-9 w-9 items-center justify-center rounded-full text-[#6B4F00] transition hover:bg-yellow-300">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                @endif

                <button
                    type="button"
                    @click="open = false"
                    title="Close"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-[#6B4F00] transition hover:bg-yellow-300">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

            </div>

        </div>

        {{-- Messages --}}
        <div
            x-ref="scroller"
            x-init="new MutationObserver(() => $el.scrollTop = $el.scrollHeight).observe($el, { childList: true, subtree: true })"
            class="flex-1 space-y-3 overflow-y-auto bg-[#FFFDF5] p-4">

            @forelse($messages as $index => $message)

                <div wire:key="message-{{ $index }}"
                     class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">

                    @if($message['role'] === 'user')

                        <div class="max-w-[85%] whitespace-pre-line break-words rounded-2xl rounded-br-md bg-[#F5C000] px-4 py-2 text-sm text-[#4D3900]">{{ $message['content'] }}</div>

                    @else

                        <div class="max-w-[85%] break-words rounded-2xl rounded-bl-md bg-[#FFF2C7] px-4 py-2 text-sm text-[#4D3900]
                            [&_p]:mb-2 [&_p:last-child]:mb-0 [&_ul]:mb-2 [&_ul]:list-disc [&_ul]:pl-5
                            [&_ol]:mb-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-1 [&_strong]:font-semibold">
                            {!! \Illuminate\Support\Str::markdown($message['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                        </div>

                    @endif

                </div>

            @empty

                <div class="flex flex-col items-center px-2 pt-6 text-center">

                    <i class="fa-solid fa-wand-magic-sparkles mb-3 text-3xl text-[#E5C85C]"></i>

                    <p class="font-semibold text-[#6A4B00]">What would you like to know?</p>

                    <p class="mt-1 text-xs text-gray-500">
                        I can see your expenses and categories for this month.
                    </p>

                    <div class="mt-5 flex flex-wrap justify-center gap-2">

                        @foreach([
                            'What is my average expense this month?',
                            'Which category do I spend the most on?',
                            'How does this month compare to last month?',
                        ] as $suggestion)

                            <button
                                type="button"
                                wire:click="ask(@js($suggestion))"
                                class="rounded-full border border-[#F2C94C] bg-white px-3 py-1.5 text-xs font-semibold text-[#7A5A00] transition hover:bg-[#FFF2C7]">
                                {{ $suggestion }}
                            </button>

                        @endforeach

                    </div>

                </div>

            @endforelse

            {{-- Typing indicator --}}
            <div wire:loading wire:target="send,ask" class="flex justify-start">
                <div class="rounded-2xl rounded-bl-md bg-[#FFF2C7] px-4 py-2 text-sm text-[#8A6A00]">
                    <i class="fa-solid fa-ellipsis fa-fade"></i> Thinking...
                </div>
            </div>

        </div>

        {{-- Error --}}
        @if($error)
            <div class="border-t border-red-200 bg-red-100 px-4 py-3 text-xs text-red-700">
                <i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $error }}
            </div>
        @endif

        {{-- Input --}}
        <form wire:submit="send" class="border-t border-[#F2C94C] bg-white p-3">

            <div class="flex items-center gap-2">

                <input
                    x-ref="input"
                    type="text"
                    wire:model="input"
                    maxlength="500"
                    autocomplete="off"
                    placeholder="Ask about your spending..."
                    class="min-w-0 flex-1 rounded-2xl border border-gray-300 px-4 py-2.5 text-sm outline-none transition focus:border-[#F2C94C]">

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="send,ask"
                    title="Send"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F5C000] text-[#4D3900] transition hover:bg-yellow-400 disabled:opacity-50">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>

            </div>

            @error('input')
                <p class="mt-1 px-1 text-xs text-red-500">{{ $message }}</p>
            @enderror

        </form>

    </div>

    {{-- Floating bubble --}}
    <button
        type="button"
        @click="open = ! open"
        title="Ask ExpenseIQ"
        class="flex h-16 w-16 items-center justify-center rounded-full bg-[#F5C000] text-[#4D3900] shadow-lg transition hover:scale-105 hover:bg-yellow-400">

        <i x-show="!open" class="fa-solid fa-comment-dots text-2xl"></i>
        <i x-show="open" style="display: none;" class="fa-solid fa-xmark text-2xl"></i>

    </button>

</div>