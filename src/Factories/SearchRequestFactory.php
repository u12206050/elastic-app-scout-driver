<?php declare(strict_types=1);

namespace ElasticAppScoutDriver\Factories;

use Laravel\Scout\Builder;
use stdClass;

final class SearchRequestFactory implements SearchRequestFactoryInterface
{
    public function makeFromBuilder(Builder $builder, array $options = []): ?array
    {
        $query = $this->makeQuery($builder);

        $searchRequest = [];

        if ($filters = $this->makeFilters($builder)) {
            $searchRequest['filters'] = $filters;
        }

        if ($sort = $this->makeSort($builder)) {
            $searchRequest['sort'] = $sort;
        }

        if ($page = $this->makePaginator($builder, $options)) {
            $searchRequest['page'] = $page;
        }

        return empty($searchRequest) ? null : $searchRequest;
    }

    /**
     * Get the filter array for the query.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return array
     */
    protected function makeFilters(Builder $builder): ?array
    {
        return empty($builder->wheres) ? null : $builder->wheres;
    }

    /**
     * Make the order the results should be in
     */
    protected function makeSort(Builder $builder): ?array
    {
        $sort = collect($builder->orders)->pluck('direction', 'column');

        return $sort->isEmpty() ? null : $sort->all();
    }

    /**
     * Make the page result information
     */
    protected function makePaginator(Builder $builder, array $options): array
    {
        $page = [
            'size' => $options['perPage'] ?? $builder->limit,
            'current' => $options['page'] ?? 1
        ];

        return $page;
    }
}
