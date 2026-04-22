<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            static::logActivity($model, 'created', 'Yeni kayıt oluşturuldu');
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            
            // Hassas alanları gizleyelim (örneğin password)
            $hidden = ['password', 'remember_token', 'public_image_upload_token'];
            $oldValues = array_diff_key($model->getOriginal(), array_flip($hidden));
            $newValues = array_diff_key($model->getAttributes(), array_flip($hidden));
            
            // Sadece değişen alanları filtrele
            $onlyChangedOld = array_intersect_key($oldValues, $changes);
            $onlyChangedNew = array_intersect_key($newValues, $changes);

            static::logActivity($model, 'updated', 'Kayıt güncellendi', $onlyChangedOld, $onlyChangedNew);
        });

        static::deleted(function (Model $model) {
            static::logActivity($model, 'deleted', 'Kayıt silindi', $model->getOriginal());
        });
    }

    protected static function logActivity(Model $model, string $action, string $title, array $oldValues = null, array $newValues = null)
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        $module = static::guessModuleName($model);

        ActivityLog::create([
            'company_id'   => $user->company_id,
            'user_id'      => $user->id,
            'module'       => $module,
            'action'       => $action,
            'subject_type' => get_class($model),
            'subject_id'   => $model->id,
            'title'        => $title,
            'description'  => static::generateDescription($model, $action, $newValues),
            'old_values'   => $oldValues,
            'new_values'   => $newValues,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }

    protected static function guessModuleName(Model $model): string
    {
        $className = class_basename($model);
        
        $map = [
            'Vehicle'            => 'vehicles',
            'Driver'             => 'drivers',
            'Trip'               => 'trips',
            'Fuel'               => 'fuels',
            'Document'           => 'documents',
            'Customer'           => 'customers',
            'VehicleMaintenance' => 'maintenances',
            'TrafficPenalty'     => 'penalties',
            'User'               => 'users',
        ];

        return $map[$className] ?? strtolower($className);
    }

    protected static function generateDescription(Model $model, string $action, ?array $newValues): string
    {
        $name = $model->name ?? $model->full_name ?? $model->plate ?? $model->id;
        
        switch ($action) {
            case 'created':
                return "{$name} kaydı sisteme eklendi.";
            case 'updated':
                $fields = array_keys($newValues ?? []);
                $fieldsStr = implode(', ', $fields);
                return "{$name} kaydında güncellenen alanlar: {$fieldsStr}";
            case 'deleted':
                return "{$name} kaydı sistemden silindi.";
            default:
                return "{$name} üzerinde işlem yapıldı.";
        }
    }
}
