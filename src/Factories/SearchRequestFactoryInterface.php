<?php declare(strict_types=1);

namespace ElasticAppScoutDriver\Factories;

use Laravel\Scout\Builder;

interface SearchRequestFactoryInterface
{
    public function makeFromBuilder(Builder $builder, array $options = []): ?array;
}
