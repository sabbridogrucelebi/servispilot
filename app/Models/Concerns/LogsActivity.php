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
            $identifier = static::getModelIdentifier($model);
            $moduleName = static::getLocalizedModuleName($model);
            static::logActivity($model, 'created', "Yeni {$moduleName} Eklendi", $identifier);
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

            $identifier = static::getModelIdentifier($model);
            $moduleName = static::getLocalizedModuleName($model);
            static::logActivity($model, 'updated', "{$moduleName} Güncellendi", $identifier, $onlyChangedOld, $onlyChangedNew);
        });

        static::deleted(function (Model $model) {
            $identifier = static::getModelIdentifier($model);
            $moduleName = static::getLocalizedModuleName($model);
            static::logActivity($model, 'deleted', "{$moduleName} Silindi", $identifier, $model->getOriginal());
        });
    }

    protected static function logActivity(Model $model, string $action, string $title, string $identifier, array $oldValues = null, array $newValues = null)
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
            'description'  => static::generateDescription($action, $identifier, $newValues),
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

    protected static function getLocalizedModuleName(Model $model): string
    {
        $className = class_basename($model);
        $map = [
            'Vehicle'            => 'Araç',
            'Driver'             => 'Personel',
            'Trip'               => 'Sefer',
            'Fuel'               => 'Yakıt Fişi',
            'Document'           => 'Belge',
            'Customer'           => 'Müşteri',
            'VehicleMaintenance' => 'Bakım Kaydı',
            'TrafficPenalty'     => 'Trafik Cezası',
            'User'               => 'Kullanıcı',
            'Payroll'            => 'Maaş Bordrosu'
        ];
        return $map[$className] ?? 'Kayıt';
    }

    protected static function getModelIdentifier(Model $model): string
    {
        // Özel tanımlanmış alanlar
        $fields = [
            'plate',
            'name',
            'full_name',
            'title',
            'receipt_no',
            'penalty_no',
            'company_name',
            'document_name'
        ];

        foreach ($fields as $field) {
            if (!empty($model->{$field})) {
                return $model->{$field};
            }
        }

        // Modüle özel mantık
        $className = class_basename($model);
        if ($className === 'Payroll') {
            return ($model->period_month ?? 'Bilinmeyen Dönem') . ' dönemi bordrosu';
        }
        if ($className === 'Trip') {
            $date = $model->trip_date ? \Carbon\Carbon::parse($model->trip_date)->format('d.m.Y') : '';
            return "{$date} tarihli sefer";
        }
        if ($className === 'Fuel') {
            $date = $model->date ? \Carbon\Carbon::parse($model->date)->format('d.m.Y') : '';
            return "{$date} tarihli yakıt";
        }

        return "ID #" . $model->id;
    }

    protected static function generateDescription(string $action, string $identifier, ?array $newValues): string
    {
        switch ($action) {
            case 'created':
                return "[{$identifier}] sisteme eklendi.";
            case 'updated':
                $fields = array_keys($newValues ?? []);
                $fieldsStr = implode(', ', $fields);
                return "[{$identifier}] güncellendi. Değişen alanlar: {$fieldsStr}";
            case 'deleted':
                return "[{$identifier}] sistemden silindi.";
            default:
                return "[{$identifier}] üzerinde işlem yapıldı.";
        }
    }
}
