<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivationController extends Controller
{
    private array $machines = [
        'W1' => 'Lavadora 1',
        'W2' => 'Lavadora 2',
        'W3' => 'Lavadora 3',
        'D1' => 'Secadora 1',
        'D2' => 'Secadora 2',
        'D3' => 'Secadora 3',
    ];

    private array $activationTypes = [
        'bancard',
        'points',
        'subscription',
        'manual_admin',
        'technical_test',
    ];

    public function dashboard()
    {
        $activations = Activation::orderByDesc('id')
            ->limit(50)
            ->get();

        return view('dashboard', [
            'machines' => $this->machines,
            'activations' => $activations,
        ]);
    }

    public function storeFromWeb(Request $request)
    {
        $data = $request->validate([
            'machine_id' => ['required', 'string'],
            'activation_type' => ['required', 'string'],
            'reference' => ['nullable', 'string'],
        ]);

        if (!array_key_exists($data['machine_id'], $this->machines)) {
            return back()->with('error', 'Máquina no válida.');
        }

        if (!in_array($data['activation_type'], $this->activationTypes)) {
            return back()->with('error', 'Tipo de activación no válido.');
        }

        $activation = Activation::create([
            'activation_id' => 'ACT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5)),
            'controller_id' => config('services.laundry.controller_id'),
            'machine_id' => $data['machine_id'],
            'activation_type' => $data['activation_type'],
            'reference' => $data['reference'] ?? 'FRONTEND_TEST',
            'admin_user' => 'demo_admin',
            'status' => 'pending',
            'message' => 'Activación creada desde frontend',
        ]);

        return back()->with('success', "Orden creada: {$activation->activation_id} para {$activation->machine_id}");
    }

    public function pending(Request $request, string $controllerId)
    {
        $this->verifyDeviceToken($request);

        if ($controllerId !== config('services.laundry.controller_id')) {
            return response()->json([
                'ok' => false,
                'message' => 'Controlador no reconocido',
            ], 404);
        }

        $activation = Activation::where('controller_id', $controllerId)
            ->where('status', 'pending')
            ->orderBy('id')
            ->first();

        if (!$activation) {
            return response()->json([
                'ok' => true,
                'activation' => null,
            ]);
        }

        $activation->update([
            'status' => 'processing',
            'sent_at' => now(),
            'message' => 'Orden enviada al controlador',
        ]);

        return response()->json([
            'ok' => true,
            'activation' => [
                'activation_id' => $activation->activation_id,
                'machine_id' => $activation->machine_id,
                'activation_type' => $activation->activation_type,
                'reference' => $activation->reference,
                'customer_id' => $activation->customer_id,
                'admin_user' => $activation->admin_user,
                'amount' => $activation->amount,
            ],
        ]);
    }

    public function result(Request $request, string $activationId)
    {
        $this->verifyDeviceToken($request);

        $data = $request->validate([
            'status' => ['required', 'string'],
            'message' => ['nullable', 'string'],
            'machine_id' => ['required', 'string'],
            'controller_id' => ['required', 'string'],
        ]);

        if (!in_array($data['status'], ['activated', 'failed'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Estado inválido',
            ], 422);
        }

        $activation = Activation::where('activation_id', $activationId)->first();

        if (!$activation) {
            return response()->json([
                'ok' => false,
                'message' => 'Activación no encontrada',
            ], 404);
        }

        if ($activation->controller_id !== $data['controller_id']) {
            return response()->json([
                'ok' => false,
                'message' => 'La activación no pertenece a este controlador',
            ], 403);
        }

        $activation->update([
            'status' => $data['status'],
            'message' => $data['message'] ?? null,
            'processed_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Resultado registrado correctamente',
            'activation' => [
                'activation_id' => $activation->activation_id,
                'machine_id' => $activation->machine_id,
                'status' => $activation->status,
            ],
        ]);
    }

    public function resetProcessing()
    {
        Activation::where('status', 'processing')
            ->update([
                'status' => 'pending',
                'message' => 'Reiniciado manualmente a pending',
                'sent_at' => null,
            ]);

        return back()->with('success', 'Órdenes en processing reiniciadas a pending.');
    }

    private function verifyDeviceToken(Request $request): void
    {
        $token = $request->bearerToken();
        $expected = config('services.laundry.device_token');

        if (!$token || !hash_equals($expected, $token)) {
            abort(response()->json([
                'ok' => false,
                'message' => 'Device token inválido',
            ], 401));
        }
    }
}