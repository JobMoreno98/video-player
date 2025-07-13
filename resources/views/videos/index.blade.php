<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Videos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <a href="{{ route('videos-reproductor.create') }}" target="_blank" rel="noopener noreferrer">Crear video</a>
                <div class="p-6 text-gray-900">
                    @forelse ($videos as $item)
                        <b>Nombre:</b> {{ $item->nombre }}
                        <a href="{{ route('videos-reproductor.show', $item->id) }}" style="border: 1px solid black;"
                            target="_blank" rel="noopener noreferrer">Ver video</a>
                    @empty
                        No hay videos
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
