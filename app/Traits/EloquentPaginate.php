<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Use in Controller.php
trait EloquentPaginate
{

    private $limit =   10;

    /**
     * Get the value of limit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the value of limit
     *
     * @return  EloquentPaginate
     */
    public function setLimit($limit)
    {
        $this->limit = (int)$limit;

        return $this;
    }

    /**
     * Execute query
     *
     * @param Builder $builder
     * @param boolean $paginate
     * @param int|null $length
     * @return  Collection|LengthAwarePaginator
     */
    private function execute($builder, $paginate, $length = null)
    {
        if ($length && $length != '') {
            $this->setLimit($length);
        }

        if ($paginate) {
            return $builder->paginate($this->getLimit())->withQueryString();
        } else {
            if ($length && $length != '') {
                $builder = $builder->limit($this->getLimit());
            }

            return $builder->get();
        }
    }
}
