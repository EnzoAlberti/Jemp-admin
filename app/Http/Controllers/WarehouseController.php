<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'situacao' => ['nullable', Rule::in([...Warehouse::SITUATIONS, 'Todas'])],
        ]);

        $situation = $validated['situacao'] ?? Warehouse::REQUESTED;

        $warehouses = Warehouse::query()
            ->with('requester:id,name,email')
            ->when($situation !== 'Todas', fn ($query) => $query->where('warehouse_situation', $situation))
            ->orderByDesc('warehouse_id')
            ->paginate(10)
            ->withQueryString();

        $pendingCount = Warehouse::where('warehouse_situation', Warehouse::REQUESTED)->count();

        return view('warehouses.index', compact('warehouses', 'situation', 'pendingCount'));
    }

    public function approve(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->warehouse_situation = Warehouse::ACTIVE;
        $warehouse->warehouse_cancelation_reason = null;
        $warehouse->save();

        return redirect()
            ->route('admin.warehouses.index', ['situacao' => Warehouse::REQUESTED])
            ->with('status', "Armazém \"{$warehouse->warehouse_name}\" aprovado e ativado com sucesso!");
    }

    public function reject(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_cancelation_reason' => ['required', 'string', 'max:4000'],
        ], [
            'warehouse_cancelation_reason.required' => 'Informe o motivo da recusa do armazém.',
        ]);

        $warehouse->warehouse_situation = Warehouse::REJECTED;
        $warehouse->warehouse_cancelation_reason = $validated['warehouse_cancelation_reason'];
        $warehouse->save();

        return redirect()
            ->route('admin.warehouses.index', ['situacao' => Warehouse::REQUESTED])
            ->with('status', "Solicitação do armazém \"{$warehouse->warehouse_name}\" recusada.");
    }

    public function toggleStatus(Warehouse $warehouse): RedirectResponse
    {
        if (! in_array($warehouse->warehouse_situation, [Warehouse::ACTIVE, Warehouse::INACTIVE], true)) {
            return redirect()
                ->back()
                ->with('error', 'Apenas armazéns ativos ou inativos podem ter o status alternado.');
        }

        $warehouse->warehouse_situation = $warehouse->warehouse_situation === Warehouse::ACTIVE
            ? Warehouse::INACTIVE
            : Warehouse::ACTIVE;
        $warehouse->save();

        return redirect()
            ->back()
            ->with('status', "Status do armazém \"{$warehouse->warehouse_name}\" alterado para {$warehouse->warehouse_situation}.");
    }
}
