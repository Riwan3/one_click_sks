<?php

namespace App\Http\Controllers\Concerns;

use App\Exceptions\RosterV2Conflict;
use App\Exceptions\RosterV2Forbidden;
use App\Exceptions\RosterV2NotFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/** Memetakan exception domain MONCU/Roster V2 ke respons Inertia (error form) atau JSON {success, data|message}. */
trait HandlesMoncuErrors
{
    /** Jalankan aksi Inertia; pesan domain tampil sebagai error form `form`. */
    protected function guardPage(callable $action)
    {
        try {
            return $action();
        } catch (RosterV2Forbidden $e) {
            abort(403, $e->getMessage());
        } catch (RosterV2NotFound|RosterV2Conflict|InvalidArgumentException $e) {
            throw ValidationException::withMessages(['form' => $e->getMessage()]);
        }
    }

    /** Jalankan aksi JSON; sukses -> {success:true,data}, gagal -> {success:false,message} dengan status V1. */
    protected function guardJson(callable $action): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $action()]);
        } catch (RosterV2Forbidden $e) {
            return $this->failJson($e, 403);
        } catch (RosterV2NotFound $e) {
            return $this->failJson($e, 404);
        } catch (RosterV2Conflict $e) {
            return $this->failJson($e, 409);
        } catch (InvalidArgumentException $e) {
            return $this->failJson($e, 422);
        }
    }

    private function failJson(\Throwable $e, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $e->getMessage()], $status);
    }
}
