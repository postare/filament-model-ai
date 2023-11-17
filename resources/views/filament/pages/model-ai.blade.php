<x-filament-panels::page>
    <div x-data="{
        finished: @entangle('finished'),
        question: @entangle('question'),
    }" x-cloak>
        <x-filament-panels::form wire:submit="save">
            {{ $this->form }}
            <div :class="{ 'hidden': !finished && question }">
                <x-filament-panels::form.actions :actions="$this->getFormActions()" />
            </div>
        </x-filament-panels::form>

        @if ($question)
            <div class="prose mx-auto mt-6 max-w-4xl">
                <div class="my-4 w-full text-center" x-show="!finished">
                    @svg('heroicon-o-sparkles', 'text-primary-600 dark:text-primary-400 h-10 w-10 mx-auto animate-pulse')
                </div>
                <div class="rounded-lg border border-gray-200 p-6 py-2 text-gray-950 shadow-lg dark:border-white/10 dark:bg-white/5 dark:text-white">
                    <div wire:stream="ai_response">{!! $ai_response !!}</div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
