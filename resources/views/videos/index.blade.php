<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Videos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('videos-reproductor.create') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded"  rel="noopener noreferrer">
                    + Crear video
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @forelse ($videos as $item)
                    <div class="border-b border-gray-200 py-4">
                        <p class="text-lg font-medium text-gray-800 flex items-center justify-between">
                            <span>🎬 <span class="font-semibold">Nombre:</span> {{ $item->nombre }}</span>
                            
                            @php
                                // Asumiendo que $item->estatus puede ser 'procesando', 'listo', 'error'
                                $statusColor = match($item->status) {
                                    'Finalizado' => 'text-green-600',
                                    'En proceso' => 'text-yellow-600',
                                    'Error' => 'text-red-600',
                                    default => 'text-gray-600',
                                };
                            @endphp
                            <span class="text-sm font-semibold {{ $statusColor }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </p>

                        <a href="{{ route('videos-reproductor.show', $item->id) }}"
                            class="inline-block mt-2 text-blue-600 hover:text-blue-800 font-semibold border border-blue-600 px-3 py-1 rounded"
                            target="_blank" rel="noopener noreferrer">
                            ▶ Ver video
                        </a>
                    </div>

                @empty
                    <p class="text-gray-600">No hay videos disponibles.</p>
                @endforelse

                <!-- Paginación -->
                <div class="mt-6">
                    {{ $videos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
