<x-filament-widgets::widget>
    <x-filament::section
        :collapsible="true"
        :collapsed="false"
    >
        <x-slot name="heading">
            Répertoire complet des écrans admin
        </x-slot>
        <x-slot name="description">
            {{ $count }} ressource(s) détectée(s) dans le panneau — même chose que le menu latéral, sans rien oublier (généré automatiquement).
        </x-slot>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
            <table class="w-full divide-y divide-gray-200 text-start text-sm dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-3 py-2 font-semibold text-gray-950 dark:text-white">Groupe</th>
                        <th class="px-3 py-2 font-semibold text-gray-950 dark:text-white">Écran</th>
                        <th class="px-3 py-2 font-semibold text-gray-950 dark:text-white">Ouvrir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($rows as $row)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5">
                            <td class="whitespace-nowrap px-3 py-2 text-gray-600 dark:text-gray-400">
                                {{ $row['group'] }}
                            </td>
                            <td class="px-3 py-2 text-gray-950 dark:text-white">
                                {{ $row['label'] }}
                            </td>
                            <td class="px-3 py-2">
                                <a
                                    href="{{ $row['url'] }}"
                                    wire:navigate
                                    class="font-medium text-primary-600 hover:underline dark:text-primary-400"
                                >
                                    Ouvrir →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
