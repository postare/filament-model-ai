<x-filament-panels::page>
    <div x-data="{
        finished: @entangle('finished'),
        question: @entangle('question'),
        init() {
            window.Livewire.on('typing', () => {
                console.log('typing');
            });
        }
    }" x-cloak>
        <x-filament-panels::form wire:submit="save">
            {{ $this->form }}
            <div :class="{'hidden': !finished && question }">
                <x-filament-panels::form.actions
                    :actions="$this->getFormActions()"
                />
            </div>
        </x-filament-panels::form>

        @if ($question)
            <div class="mt-6 prose mx-auto max-w-4xl">
                <div class="text-center w-full my-4" x-show="!finished">
                    @svg('heroicon-o-sparkles', 'h-12 w-12 mx-auto animate-pulse')
                </div>
                <div class="bg-white border shadow-lg rounded-lg p-6 py-2">
                    <div wire:stream="ai_response">{!! $ai_response !!}</div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
