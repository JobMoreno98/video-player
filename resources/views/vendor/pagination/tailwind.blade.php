@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-center mt-6">
        <ul class="inline-flex items-center -space-x-px text-gray-700">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="px-3 py-2 ml-0 leading-tight bg-gray-200 rounded-l-lg cursor-not-allowed">&lsaquo;</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-2 ml-0 leading-tight text-blue-600 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-blue-700" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li aria-disabled="true"><span class="px-3 py-2 leading-tight bg-white border border-gray-300 cursor-default">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li aria-current="page"><span class="px-3 py-2 leading-tight text-white bg-blue-600 border border-blue-600">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" class="px-3 py-2 leading-tight text-blue-600 bg-white border border-gray-300 hover:bg-gray-100 hover:text-blue-700">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-2 leading-tight text-blue-600 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-blue-700" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @else
                <li aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="px-3 py-2 leading-tight bg-gray-200 rounded-r-lg cursor-not-allowed">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
