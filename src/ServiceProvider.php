<?php declare(strict_types=1);

namespace ElasticAppScoutDriver;

use ElasticAppScoutDriver\Factories\SearchRequestFactory;
use ElasticAppScoutDriver\Factories\SearchRequestFactoryInterface;
use Illuminate\Support\ServiceProvider as AbstractServiceProvider;
use Laravel\Scout\EngineManager;

final class ServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string
     */
    private $configPath;
    /**
     * @var array
     */
    public $bindings = [
      SearchRequestFactoryInterface::class => SearchRequestFactory::class,
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->configPath = dirname(__DIR__) . '/config/elastic_app.scout_driver.php';
    }

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->configPath,
            basename($this->configPath, '.php')
        );
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->configPath => config_path(basename($this->configPath)),
        ]);

        resolve(EngineManager::class)->extend('elastic_app', static function () {
            return resolve(Engine::class);
        });
    }
}
