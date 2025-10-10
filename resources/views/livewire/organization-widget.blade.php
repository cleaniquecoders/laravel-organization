<div class="bg-white rounded-lg shadow-md border border-gray-200">
    <!-- Widget Header -->
    <div class="px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Organizations</h3>
            @if($showQuickActions)
                <button wire:click="showCreateForm"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New
                </button>
            @endif
        </div>
    </div>

    <!-- Current Organization -->
    @if($currentOrganization)
        <div class="px-4 py-3 bg-blue-50 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-xs font-bold text-white">
                                {{ substr($currentOrganization->name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ $currentOrganization->name }}
                        </p>
                        <p class="text-xs text-blue-600">Current workspace</p>
                    </div>
                </div>
                @if($showQuickActions)
                    <button wire:click="showManageForm"
                            class="text-gray-400 hover:text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- Recent Organizations -->
    @if($recentOrganizations->count() > 0)
        <div class="px-4 py-3">
            <h4 class="text-sm font-medium text-gray-700 mb-3">
                @if($currentOrganization)
                    Switch Organization
                @else
                    Your Organizations
                @endif
            </h4>
            <div class="space-y-2">
                @foreach($recentOrganizations as $organization)
                    @if(!$currentOrganization || $organization->id !== $currentOrganization->id)
                        <button wire:click="switchToOrganization({{ $organization->id }})"
                                class="w-full flex items-center space-x-3 p-2 rounded-md hover:bg-gray-50 transition-colors duration-150 text-left">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 bg-gradient-to-r from-gray-400 to-gray-600 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold text-white">
                                        {{ substr($organization->name, 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $organization->name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    @if($organization->owner_id === auth()->id())
                                        Owner
                                    @else
                                        Member
                                    @endif
                                </p>
                            </div>
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Quick Stats -->
    @if($organizations->count() > 0)
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">
                    Total: {{ $organizations->count() }} organization{{ $organizations->count() !== 1 ? 's' : '' }}
                </span>
                @if($showQuickActions)
                    <button wire:click="showOrganizationList"
                            class="text-blue-600 hover:text-blue-700 font-medium">
                        View All
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- Empty State -->
    @if($organizations->count() === 0)
        <div class="px-4 py-6 text-center">
            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No organizations</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating your first organization.</p>
            @if($showQuickActions)
                <div class="mt-4">
                    <button wire:click="showCreateForm"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Organization
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading.flex wire:target="switchToOrganization,showCreateForm,showManageForm,showOrganizationList"
         class="absolute inset-0 bg-white bg-opacity-75 items-center justify-center rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm text-gray-600">Loading...</span>
        </div>
    </div>
</div>
