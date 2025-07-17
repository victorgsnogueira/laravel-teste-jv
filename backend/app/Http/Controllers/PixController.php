<?php

namespace App\Http\Controllers;

use App\Models\Pix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PixController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $pix = Pix::create([
            'user_id' => auth()->id(),
            'token' => Str::uuid(),
            'amount' => $request->amount,
            'expires_at' => now()->addMinutes(10)
        ]);

        return response()->json([
            'message' => 'PIX gerado com sucesso',
            'data' => [
                'token' => $pix->token,
                'amount' => $pix->amount,
                'expires_at' => $pix->expires_at,
                'qr_code_url' => route('pix.show', $pix->token)
            ]
        ], 201);
    }

    public function show(string $token): JsonResponse
    {
        $pix = Pix::where('token', $token)->firstOrFail();

        if ($pix->isExpired()) {
            $pix->markAsExpired();
            return response()->json([
                'message' => 'PIX expirado',
                'status' => 'expired'
            ]);
        }

        $pix->markAsPaid();
        
        return response()->json([
            'message' => 'Pagamento confirmado com sucesso',
            'status' => 'paid'
        ]);
    }

    public function index(): JsonResponse
    {
        $stats = [
            'generated' => Pix::where('status', 'generated')->count(),
            'paid' => Pix::where('status', 'paid')->count(),
            'expired' => Pix::where('status', 'expired')->count()
        ];

        return response()->json($stats);
    }
}
