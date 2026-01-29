<x-filament-widgets::widget>
    <div class="flex justify-between">
        <div class="flex items-center gap-4">
            <img src="{{ filament()->getUserAvatarUrl(auth()->user()) }}" class="h-12 w-12 rounded-full">

            <div class="flex flex-col items-start">
                <p class="text-lg font-semibold">
                    {{ $this->getGreeting() }}, {{ auth()->user()->name }}!
                </p>

                <x-filament::badge color="info" size="sm" class="mt-0.5">
                    {{ strtoupper(auth()->user()->role ?? 'USER') }}
                </x-filament::badge>
            </div>
        </div>

        <div class="flex items-center gap-1">
            <x-filament::badge color="success">
                Online
            </x-filament::badge>




            <form method="POST" action="{{ filament()->getLogoutUrl() }}">
                @csrf
                <x-filament::icon-button type="submit" icon="heroicon-o-arrow-left-on-rectangle" color="gray"
                    tooltip="Sign out" />
        </div>

    </div>
</x-filament-widgets::widget>
