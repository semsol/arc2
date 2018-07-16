<?php

/**
 * @author Benjamin Nowack <bnowack@semsol.com>
 * @author Konrad Abicht <konrad.abicht@pier-and-peer.com>
 * @license W3C Software License and GPL
 * @homepage <https://github.com/semsol/arc2>
 */

namespace ARC2\Store\Adapter;

/**
 * It provides an adapter instance for requested adapter name.
 */
class AdapterFactory
{
    /**
     * @param string $adapterName
     * @param array $configuration Default is array()
     */
    public function getInstanceFor($adapterName, $configuration = array())
    {
        if (in_array($adapterName, $this->getSupportedAdapters())) {
            /*
             * mysqli
             */
            if ('mysqli' == $adapterName) {
                if (false == class_exists('\\ARC2\\Store\\Adapter\\mysqliAdapter')) {
                    require_once 'mysqliAdapter.php';
                }
                return new mysqliAdapter($configuration);
            /*
             * PDO
             */
            } elseif ('pdo' == $adapterName) {
                // use cache?
                if (isset($configuration['cache_enabled']) && true === $configuration['cache_enabled']) {
                    if (false == class_exists('\\ARC2\\Store\\Adapter\\CachedPDOAdapter')) {
                        require_once 'CachedPDOAdapter.php';
                    }
                    return new CachedPDOAdapter($configuration);
                // no cache
                } else {
                    if (false == class_exists('\\ARC2\\Store\\Adapter\\PDOAdapter')) {
                        require_once 'PDOAdapter.php';
                    }
                    return new PDOAdapter($configuration);
                }
            }
        }

        throw new \Exception(
            'Unknown adapter name given. Currently supported are: '
            .implode(', ', $this->getSupportedAdapters())
        );
    }

    /**
     * @return array
     */
    public function getSupportedAdapters()
    {
        return array('mysqli', 'pdo');
    }
}
