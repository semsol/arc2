<?php

/**
 * Adapter to enable usage of PDO functions.
 *
 * @author Benjamin Nowack <bnowack@semsol.com>
 * @author Konrad Abicht <konrad.abicht@pier-and-peer.com>
 * @license W3C Software License and GPL
 * @homepage <https://github.com/semsol/arc2>
 */

namespace ARC2\Store\Adapter;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * PDO Adapter - Handles database operations using PDO.
 */
class CachedPDOAdapter extends PDOAdapter
{
    protected $cacheEnabled;
    protected $cache;

    public function __construct(array $configuration = array())
    {
        parent::__construct($configuration);

        $this->initCache($configuration);
    }

    protected function initCache(array $configuration)
    {
        $this->cacheEnabled = isset($configuration['cache_enabled'])
                              && true === $configuration['cache_enabled'];

        if ($this->cacheEnabled) {
            // reuse existing cache instance, if it implements Psr\SimpleCache\CacheInterface
            if (isset($configuration['cache_instance'])
                && $configuration['cache_instance'] instanceof CacheInterface) {
                $this->cache = $configuration['cache_instance'];

            // create new cache instance
            } else {
                // FYI: https://symfony.com/doc/current/components/cache/adapters/filesystem_adapter.html
                $this->cache = new FilesystemCache('arc2', 0, null);
            }
        } else {
            throw new \Exception('Cache not enabled, therefore CachedPDOAdapter can not be used.');
        }
    }

    public function clearCache()
    {
        $this->cache->clear();
    }

    /**
     * @param string $sql
     *
     * @return array
     */
    public function fetchList($sql)
    {
        $key = hash('sha1', $sql);

        // sql query is known
        if ($this->cache->has($key)) {
            return $this->cache->get($key);

        } else {
            $result = parent::fetchList($sql);
            $this->cache->set($key, $result);
            return $result;
        }
    }

    /**
     * @param string $sql
     *
     * @return array
     */
    public function fetchRow($sql)
    {
        $key = hash('sha1', $sql);

        // sql query is known
        if ($this->cache->has($key)) {
            return $this->cache->get($key);

        } else {
            $result = parent::fetchRow($sql);
            $this->cache->set($key, $result);
            return $result;
        }
    }

    public function getCacheInstance()
    {
        return $this->cache;
    }

    public function getNumberOfRows($sql)
    {
        $key = hash('sha1', $sql);

        // sql query is known
        if ($this->cache->has($key)) {
            return $this->cache->get($key);

        } else {
            $result = parent::getNumberOfRows($sql);
            $this->cache->set($key, $result);
            return $result;
        }
    }

    /**
     * catches the first part of the query
     * we need that to determine if its an query which changes the DB in any way
     */
    protected function queryChangesDb($sql)
    {
        $sqlPart = substr(trim($sql), 0, 4);
        return true === in_array($sqlPart, ['CREA', 'DROP', 'DELE', 'INSE', 'RENA', 'UPDA',]);
    }

    public function simpleQuery($sql)
    {
        if ($this->queryChangesDb($sql)) {
            $this->cache->clear();
        }

        return parent::simpleQuery($sql);
    }

    public function exec($sql)
    {
        if ($this->queryChangesDb($sql)) {
            $this->cache->clear();
        }

        return parent::exec($sql);
    }
}
