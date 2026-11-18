<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-[linear-gradient(135deg,#3f7fa6,#2b6b8e)] shadow-md shadow-slate-900/10 hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
