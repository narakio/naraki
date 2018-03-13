<?php namespace App\Filters;

use Illuminate\Database\Query\Builder;

class Group extends Filters
{
    protected $filters = ['sortBy', 'order', 'name'];
    protected $acceptedSortColumns = ['group_name'];

    /**
     * @param string $name
     * @return \Illuminate\Database\Query\Builder
     */
    public function sortBy($name)
    {
        if (isset($this->acceptedSortColumns[$name])) {
            return $this->builder
                ->orderBy($name,
                    trans(
                        sprintf('ajax.filters.%s',
                            $this->getFilter('order')
                        )
                    ) ?? 'asc'
                );
        }
        return $this->builder;
    }

    /**
     * @param string $name
     * @return \Illuminate\Database\Query\Builder
     */
    public function name($name)
    {
        return $this->builder->where(
            trans(sprintf('ajax.db_raw.%s', 'group_name')),
            'like',
            sprintf('%%%s%%', $name));
    }

}