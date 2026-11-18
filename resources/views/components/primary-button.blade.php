<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-[linear-gradient(135deg,#4f46e5,#7c3aed)] shadow-md shadow-indigo-500/30 hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
