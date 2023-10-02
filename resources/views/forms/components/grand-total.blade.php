<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @if($getRecord() != null )
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <input class="fi-input block w-full border-none bg-transparent py-1.5 text-base text-gray-950 outline-none transition duration-75" wire:model="name" value="{{ $getRecord()->transactionProducts->sum('total') }}">
    </div>

    @endif
</x-dynamic-component>
