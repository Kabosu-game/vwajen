<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Raccourcis — toutes les rubriques admin
        </x-slot>
        <x-slot name="description">
            Même couverture que la <strong>barre latérale</strong> (musée, éducation, contenu, mobilisation, modération, adhésion, etc.).
        </x-slot>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($sections as $section)
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5"
                >
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ $section['title'] }}
                    </h3>
                    @if (! empty($section['description']))
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $section['description'] }}
                        </p>
                    @endif
                    <ul class="mt-3 space-y-2">
                        @foreach ($section['links'] as $link)
                            <li>
                                <a
                                    href="{{ $link['url'] }}"
                                    wire:navigate
                                    class="text-sm font-medium text-primary-600 hover:text-primary-500 hover:underline dark:text-primary-400"
                                >
                                    → {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
