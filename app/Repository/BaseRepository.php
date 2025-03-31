<?php

namespace App\Repository;

class BaseRepository
{
    protected function paginateQuery($reqParam, $query)
    {
        $page = $reqParam['page'] ?? $reqParam['page_index'] ?? 1;
        $pageSize = $reqParam['page_size'] ?? 5;

        return $query->paginate($pageSize, ['*'], 'page', $page);
    }

}
