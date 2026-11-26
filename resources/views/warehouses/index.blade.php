<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestão e Aprovação de Armazéns
            </h2>
            @if ($pendingCount > 0)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                    {{ $pendingCount }} {{ $pendingCount === 1 ? 'solicitação pendente' : 'solicitações pendentes' }}
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-8" x-data="{ rejectModalOpen: false, rejectAction: '', rejectWarehouseName: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Abas de Filtro por Situação --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-2">
                @php
                    $tabs = [
                        'Solicitado' => 'Fila de Aprovação',
                        'Ativo' => 'Ativos',
                        'Recusado' => 'Recusados',
                        'Inativo' => 'Inativos',
                        'Todas' => 'Todos',
                    ];
                @endphp

                @foreach ($tabs as $key => $label)
                    <a href="{{ route('admin.warehouses.index', ['situacao' => $key]) }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition {{ $situation === $key ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Tabela de Armazéns --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Armazém</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Justificativa / Motivo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Situação</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            @forelse ($warehouses as $warehouse)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">#{{ $warehouse->warehouse_id }} - {{ $warehouse->warehouse_name }}</div>
                                        <div class="text-gray-500 text-xs mt-1">{{ $warehouse->warehouse_description }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $warehouse->requester?->name ?? 'Sistema / Inicial' }}
                                        @if ($warehouse->requester?->email)
                                            <div class="text-xs text-gray-400">{{ $warehouse->requester->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 max-w-md">
                                        @if ($warehouse->warehouse_request_reason)
                                            <div><span class="font-medium text-gray-600">Pedido:</span> {{ $warehouse->warehouse_request_reason }}</div>
                                        @endif
                                        @if ($warehouse->warehouse_cancelation_reason)
                                            <div class="mt-1 text-red-600"><span class="font-medium">Recusa:</span> {{ $warehouse->warehouse_cancelation_reason }}</div>
                                        @endif
                                        @if (! $warehouse->warehouse_request_reason && ! $warehouse->warehouse_cancelation_reason)
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badgeClasses = match ($warehouse->warehouse_situation) {
                                                'Ativo' => 'bg-emerald-100 text-emerald-800',
                                                'Solicitado' => 'bg-amber-100 text-amber-800',
                                                'Recusado' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClasses }}">
                                            {{ $warehouse->warehouse_situation }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                        @if ($warehouse->warehouse_situation === 'Solicitado')
                                            <form action="{{ route('admin.warehouses.approve', $warehouse) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded hover:bg-emerald-700">
                                                    Aprovar
                                                </button>
                                            </form>

                                            <button type="button"
                                                    @click="
                                                        rejectModalOpen = true;
                                                        rejectWarehouseName = @js($warehouse->warehouse_name);
                                                        rejectAction = '{{ route('admin.warehouses.reject', $warehouse) }}';
                                                    "
                                                    class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700">
                                                Recusar
                                            </button>
                                        @elseif (in_array($warehouse->warehouse_situation, ['Ativo', 'Inativo'], true))
                                            <form action="{{ route('admin.warehouses.toggle-status', $warehouse) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="px-3 py-1.5 bg-gray-700 text-white text-xs font-semibold rounded hover:bg-gray-800">
                                                    {{ $warehouse->warehouse_situation === 'Ativo' ? 'Inativar' : 'Reativar' }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        Nenhum armazém encontrado para este filtro.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($warehouses->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $warehouses->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal de Motivo da Recusa --}}
        <div x-show="rejectModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="rejectModalOpen = false" class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    Recusar Armazém: <span x-text="rejectWarehouseName"></span>
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Informe o motivo da recusa para que o operador visualize na tela de armazéns.
                </p>

                <form :action="rejectAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="warehouse_cancelation_reason" class="block text-sm font-medium text-gray-700">
                            Motivo da recusa *
                        </label>
                        <textarea id="warehouse_cancelation_reason"
                                  name="warehouse_cancelation_reason"
                                  rows="3"
                                  required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                  placeholder="Ex: Já existe um barracão ativo para produtos em reforma."></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button"
                                @click="rejectModalOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-md hover:bg-red-700">
                            Confirmar Recusa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
