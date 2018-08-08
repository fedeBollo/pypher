<?php
namespace Pypher;
use \GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\Connection\ConnectionManager;
use GraphAware\Neo4j\Client\HttpDriver\Configuration;
use Symfony\Component\EventDispatcher\EventDispatcher;
use GraphAware\Common\Connection\BaseConfiguration;

class PypherClientBuilder extends ClientBuilder {

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->config["client_class"] = PypherClient::class;
    }

    /**
     * Creates a new Client factory.
     *
     * @param array $config
     *
     * @return PypherClientBuilder
     */
    public static function create($config = [])
    {
        return new static($config);
    }

    /**
     * Add a connection to the handled connections.
     *
     * @param string            $alias
     * @param string            $uri
     * @param BaseConfiguration $config
     *
     * @return PypherClientBuilder
     */
    public function addConnection($alias, $uri, ConfigInterface $config = null)
    {
        //small hack for drupal
        if (substr($uri, 0, 7) === 'bolt://') {
            $parts = explode('bolt://', $uri );
            if (count($parts) === 2) {
                $splits = explode('@', $parts[1]);
                $split = $splits[count($splits)-1];
                if (substr($split, 0, 4) === 'ssl+') {
                    $up = count($splits) > 1 ? $splits[0] : '';
                    $ups = explode(':', $up);
                    $u = $ups[0];
                    $p = $ups[1];
                    $uri = 'bolt://'.str_replace('ssl+', '', $split);
                    $config = \GraphAware\Bolt\Configuration::newInstance()
                        ->withCredentials($u, $p)
                        ->withTLSMode(\GraphAware\Bolt\Configuration::TLSMODE_REQUIRED);
                }
            }
        }

        $this->config['connections'][$alias]['uri'] = $uri;

        if (null !== $config) {
            if ($this->config['connections'][$alias]['config'] = $config);
        }

        return $this;
    }

    /**
     * Builds a Client based on the connections given.
     *
     * @return PypherClient
     */
    public function build()
    {
        $connectionManager = new ConnectionManager();

        foreach ($this->config['connections'] as $alias => $conn) {
            $config =
                isset($this->config['connections'][$alias]['config'])
                    ? $this->config['connections'][$alias]['config']
                    : Configuration::create()
                    ->withTimeout(5);
            $connectionManager->registerConnection(
                $alias,
                $conn['uri'],
                $config
            );

            if (isset($conn['is_master']) && $conn['is_master'] === true) {
                $connectionManager->setMaster($alias);
            }
        }

        $ev = null;

        if (isset($this->config['event_listeners'])) {
            $ev = new EventDispatcher();

            foreach ($this->config['event_listeners'] as $k => $callbacks) {
                foreach ($callbacks as $callback) {
                    $ev->addListener($k, $callback);
                }
            }
        }

        return new $this->config['client_class']($connectionManager, $ev);
    }
}