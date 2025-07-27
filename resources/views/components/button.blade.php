<button {{ $attributes->merge(['type' => 'submit', 'class' => 'px-3 py-2 text-sm  text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm  me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700']) }}>
    {{ $slot }}
</button>
