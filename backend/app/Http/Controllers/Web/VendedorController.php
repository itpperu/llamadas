<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use App\Models\Dispositivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class VendedorController extends Controller
{
    public function index()
    {
        $vendedores = Vendedor::with('dispositivos')->orderBy('nombre')->get();
        return view('vendedores.index', compact('vendedores'));
    }

    public function create()
    {
        return view('vendedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'usuario' => 'required|unique:vendedores,usuario',
            'password' => 'required|min:4',
            'telefono_corporativo' => 'nullable',
            'device_uuid' => 'required|unique:dispositivos,device_uuid',
        ]);

        try {
            DB::beginTransaction();

            $vendedor = Vendedor::create([
                'nombre' => $request->nombre,
                'usuario' => $request->usuario,
                'password_hash' => Hash::make($request->password),
                'telefono_corporativo' => $request->telefono_corporativo,
                'estado' => 'activo'
            ]);

            Dispositivo::create([
                'vendedor_id' => $vendedor->id,
                'device_uuid' => $request->device_uuid,
                'marca' => 'Asignado web',
                'modelo' => 'Manual',
                'activo' => true
            ]);

            DB::commit();

            return redirect()->route('vendedores.index')->with('success', 'Vendedor y dispositivo registrados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }

    public function edit(Vendedor $vendedor)
    {
        return view('vendedores.edit', compact('vendedor'));
    }

    public function update(Request $request, Vendedor $vendedor)
    {
        $request->validate([
            'nombre' => 'required',
            'telefono_corporativo' => 'nullable',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $data = [
            'nombre' => $request->nombre,
            'telefono_corporativo' => $request->telefono_corporativo,
            'estado' => $request->estado,
        ];

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        $vendedor->update($data);

        return redirect()->route('vendedores.index')->with('success', 'Vendedor actualizado correctamente.');
    }

    /**
     * Exportar vendedores a CSV (server-side fallback)
     */
    public function export()
    {
        $vendedores = Vendedor::with('dispositivos')->orderBy('nombre')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="vendedores_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($vendedores) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Nombre', 'Usuario', 'Teléfono Corporativo', 'UUID Dispositivo', 'Estado']);

            foreach ($vendedores as $v) {
                $deviceUuid = $v->dispositivos->pluck('device_uuid')->implode(', ') ?: 'Sin dispositivo';
                fputcsv($file, [
                    $v->nombre,
                    $v->usuario,
                    $v->telefono_corporativo ?? '-',
                    $deviceUuid,
                    $v->estado,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
