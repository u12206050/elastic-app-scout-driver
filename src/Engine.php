<?php declare(strict_types=1);

namespace ElasticAppScoutDriver;

use Elastic\AppSearch\Client\Client;
use Elastic\AppSearch\Client\ClientBuilder;
use Elastic\OpenApi\Codegen\Exception\NotFoundException;
use ElasticAppScoutDriver\Factories\SearchRequestFactoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as BaseCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine as AbstractEngine;
use Laravel\Scout\Searchable;
use stdClass;

final class Engine extends AbstractEngine
{
    /**
     * @var bool
     */
    private $refreshDocuments;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var SearchRequestFactoryInterface
     */
    private $searchRequestFactory;

    public function __construct(
      SearchRequestFactoryInterface $searchRequestFactory
    ) {
        $apiEndpoint = config('elastic_app.scout_driver.apiEndpoint');
        $apiKey = config('elastic_app.scout_driver.apiKey');

        $this->client = ClientBuilder::create($apiEndpoint, $apiKey)->build();

        $this->searchRequestFactory = $searchRequestFactory;
    }

    protected function engineName($model)
    {
        return str_replace('_', '-', $model->searchableAs());
    }

    protected function maintainEngine($engine): Client
    {
        try {
            $this->client->getEngine($engine);
        } catch (NotFoundException $err) {
            $this->client->createEngine($engine);
        }

        return $this->client;
    }

    /**
     * {@inheritDoc}
     */
    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $index = $this->engineName($models->first());
        $client = $this->maintainEngine($index);

        foreach ($models->chunk(100) as $chunk) {
            $documents = $chunk->map(function ($model) {
                return array_merge(
                    [ 'id' => $model->id ],
                    $model->toSearchableArray(),
                    $model->scoutMetadata()
                );
            });

            $this->maintainEngine($index)->indexDocuments($index, $documents->all());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $index = $this->engineName($models->first());
        $documentIds = $models->map(function ($item) {
          return $item->id;
        });

        return $this->maintainEngine($index)->deleteDocuments($index, $documentIds->all());
    }

    /**
     * {@inheritDoc}
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder);
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, compact('perPage', 'page'));
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  array  $options
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $client = $this->client;
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $client,
                $builder->query,
                $options
            );
        }

        $index = $this->engineName($builder->model);
        $searchRequest = $this->searchRequestFactory->makeFromBuilder($builder, $options);
        return $client->search($index, $builder->query, $searchRequest);
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param SearchResponse $results
     *
     * @return BaseCollection
     */
    public function mapIds($results)
    {
        return collect($results['results'])->pluck('id')->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param SearchResponse $results
     * @param Model          $model
     *
     * @return EloquentCollection
     */
    public function map(Builder $builder, $results, $model)
    {
        if (count($results['results']) === 0) {
            return $model->newCollection();
        }

        $ids = $this->mapIds($results['results']);
        $idPositions = array_flip($ids);

        return $model->getScoutModelsByIds(
                $builder, $ids
            )->filter(function ($model) use ($ids) {
                return in_array($model->getScoutKey(), $ids);
            })->sortBy(function ($model) use ($idPositions) {
                return $idPositions[$model->getScoutKey()];
            })->values();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param SearchResponse $results
     *
     * @return int
     */
    public function getTotalCount($results)
    {
        return $results['meta']['page']['total_results'];
    }

    /**
     * {@inheritDoc}
     */
    public function flush($model)
    {
        $index = $model->searchableAs();

        return $this->client->deleteEngine($index);
    }

    /**
     * Dynamically call the Elastic App Search client instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->client->$method(...$parameters);
    }
}
