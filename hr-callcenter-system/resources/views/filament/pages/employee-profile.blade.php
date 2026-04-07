<x-filament-panels::page>
    @if($this->employee)
        {{ $this->profileInfolist }}
    @else
        <div class="text-center py-12">
            <x-heroicon-o-exclamation-triangle class="mx-auto h-12 w-12 text-warning-500" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Employee Record Found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Your account is not linked to an employee record. Please contact your administrator.
            </p>
        </div>
    @endif
</x-filament-panels::page>
