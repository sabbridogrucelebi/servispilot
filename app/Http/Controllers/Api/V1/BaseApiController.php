<?php

/**
 * Base for all V1 API controllers consumed by the mobile app.
 * Any new endpoint here MUST have a matching mobile-app/src/api/ + screen consumer.
 * See WEB_MOBIL_SENKRON_KURALLARI.md
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;

class BaseApiController extends Controller
{
    use ApiResponse;
    
    /**
     * Aktif kullanıcının şirket ID'sini döndürür
     */
    protected function getCompanyId(): int
    {
        $id = auth()->user()->company_id;
        
        if (!$id) {
            // Süper Admin veya firması atanmamış bir kullanıcı ise Type Error (500)
            // yememek için int olan 0 dönüyoruz. Bu sayede veritabanı boş dizi döner, çökmeyi engelleriz.
            return 0;
        }

        return (int) $id;
    }

    protected function userHasPermission($user, string $permission): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        return $user->can($permission);
    }

    protected function success($message, $data = null, $meta = [])
    {
        return $this->successResponse($data, $message, 200, $meta);
    }

    protected function error($message, $code = 400, $errors = [])
    {
        return $this->errorResponse($message, $code, $errors);
    }
}
