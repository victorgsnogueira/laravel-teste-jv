<?php

namespace App\Http\Controllers;

use App\Events\PixStatusUpdated;
use App\Models\Pix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class PixController extends Controller
{
    private function getPixStats(): array
    {
        return [
            'generated' => Pix::where('status', 'generated')->count(),
            'paid' => Pix::where('status', 'paid')->count(),
            'expired' => Pix::where('status', 'expired')->count()
        ];
    }

    private function broadcastPixUpdate(): void
    {
        broadcast(new PixStatusUpdated($this->getPixStats()));
    }

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

        $this->broadcastPixUpdate();

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

    public function index(Request $request): JsonResponse
    {
        $pixes = $request->user()->pixes()->latest()->paginate(10);

        return response()->json($pixes);
    }

    public function show(string $token): JsonResponse
    {
        $pix = Pix::where('token', $token)->firstOrFail();

        if ($pix->isExpired()) {
            $pix->markAsExpired();
            $this->broadcastPixUpdate();
            
            return response()->json([
                'message' => 'PIX expirado',
                'status' => 'expired'
            ]);
        }

        $pix->markAsPaid();
        $this->broadcastPixUpdate();
        
        return response()->json([
            'message' => 'Pagamento confirmado com sucesso',
            'status' => 'paid'
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'generated' => Pix::where('user_id', auth()->id())->where('status', 'generated')->count(),
            'paid' => Pix::where('user_id', auth()->id())->where('status', 'paid')->count(),
            'expired' => Pix::where('user_id', auth()->id())->where('status', 'expired')->count(),
        ];

        return response()->json($stats);
    }
}
