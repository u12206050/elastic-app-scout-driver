<?php declare(strict_types=1);

namespace ElasticAppScoutDriver\Factories;

use ElasticAdapter\Search\SearchRequest;
use Laravel\Scout\Builder;

interface SearchRequestFactoryInterface
{
    public function makeFromBuilder(Builder $builder, array $options = []): SearchRequest;
}
