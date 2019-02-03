<?php namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PermissionMask extends Model
{
    public $timestamps = false;

    protected $fillable = ['permission_store_id', 'permission_holder_id', 'permission_mask', 'permission_is_default'];

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param $userEntityId
     * @param bool $permissionIsDefault
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopePermissionStore(Builder $builder, $userEntityId, $permissionIsDefault = true)
    {
        return $builder->join('permission_stores', function ($q) use ($userEntityId, $permissionIsDefault) {
            $q->on('permission_masks.permission_store_id', '=', 'permission_stores.permission_store_id')
                ->where('permission_masks.permission_holder_id', '=', $userEntityId)
                ->where('permission_is_default', '=', $permissionIsDefault);
        });
    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param $entityId
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopePermissionRecord(Builder $builder, $entityId)
    {
        return $builder->join('permission_records', function ($q) use ($entityId) {
            $q->on('permission_stores.permission_store_id', '=', 'permission_records.permission_store_id')
                ->where('permission_records.entity_id', '=', $entityId);
        });
    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeEntityType(Builder $builder)
    {
        return $builder->join('entity_types', function ($q) {
            $q->on('entity_types.entity_type_id', '=', 'permission_records.permission_target_id');
        });
    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param $username
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeUser(Builder $builder, $username)
    {
        return $builder->join('users', function ($q) use ($username) {
            $q->on('users.user_id', '=', 'entity_types.entity_type_target_id')
            ->where('users.username', '=', $username);
        });
    }

    public static function getDefaultPermission($entityTypeId, $entityId)
    {
        return static::query()->select('permission_mask')
            ->permissionStore($entityTypeId)
            ->permissionRecord($entityId)
//            ->groupBy('permission_masks.permission_store_id')
//            ->groupBy('permission_masks.permission_mask')
            ->pluck('permission_mask')
            ->pop();
    }

    public static function getTargetPermission($entityTypeId, $entityId, $username)
    {
        return static::query()->select('permission_mask')
            ->permissionStore($entityTypeId,false)
            ->permissionRecord($entityId)
            ->entityType()
            ->user($username)
            ->pluck('permission_mask')
            ->pop();
    }
}
