<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CashRegisterApprovalController extends Controller
{
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $cashRegister = CashRegister::findOrFail($id);
            
            // Verificar permisos
            /** @var \App\Models\User|null $user */
            $user = $request->user();
            if (!$user || !$user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para aprobar cajas'
                ], 403);
            }
            
            // Verificar que la caja esté cerrada y no aprobada
            if ($cashRegister->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede aprobar una caja que está abierta'
                ], 400);
            }
            
            if ($cashRegister->is_approved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta caja ya está aprobada'
                ], 400);
            }
            
            // Aprobar la caja
            $cashRegister->reconcile(true, 'Aprobado desde lista por '.$user->name, $user->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Caja aprobada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar la caja: ' . $e->getMessage()
            ], 500);
        }
    }
}
