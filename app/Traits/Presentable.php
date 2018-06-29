<?php namespace App\Traits;

use App\Filters\Filters;
use Illuminate\Database\Eloquent\Builder;

trait Presentable
{
    /**
     * @param $columns
     * @return array
     */
    public function getColumnInfo($columns): array
    {
        $sortable = array_flip($this->sortable);
        $result = [];
        foreach ($columns as $name => $info) {
            $result[$name] = [
                'name' => $name,
                'width' => (isset($info->width))?$info->width:'inherit',
                'label' => $info->name,
                'sortable' => isset($sortable[trans(sprintf('ajax.db_raw.%s', $name))])
            ];
        }
        return $result;
    }

    /**
     * @param mixed $query
     * @param \App\Filters\Filters $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, Filters $filters): Builder
    {
        return $filters->apply($query);
    }

}