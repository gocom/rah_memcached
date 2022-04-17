<?php

/*
 * rah_memcached - Memcached templates for Textpattern CMS
 * https://github.com/gocom/rah_memcached
 *
 * Copyright (C) 2019 Jukka Svahn
 *
 * This file is part of rah_memcached.
 *
 * rah_memcached is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * rah_memcached is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with rah_memcached. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Extends Memcached with Textpattern installation specific storage.
 *
 * Configuration happens by defining few constants in config.php:
 *
 * ```
 * define('RAH_MEMCACHED_HOST', 'localhost');
 * define('RAH_MEMCACHED_PORT', 11211);
 * ```
 */
final class Rah_Memcached
{
    /**
     * Stores a Memcached instance.
     *
     * @var Memcached
     */
    private $cache;

    /**
     * Stores config.
     *
     * @var Rah_Memcached_AbstractServer
     */
    private $config;

    /**
     * Constructor.
     */
    public function __construct(Memcached $memcached, Rah_Memcached_AbstractServer $config)
    {
        $this->config = $config;
        $this->cache = $memcached;

        $this->setOptions($this->config->getOptions());

        if ($this->config->getUsername()) {
            $this->cache->setOption(Memcached::OPT_BINARY_PROTOCOL, true);

            $this->cache->setSaslAuthData(
                $this->config->getUsername(),
                $this->config->getPassword()
            );
        }

        $this->addServer($this->config);
    }

    /**
     * Sets options.
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        if ($options) {
            $this->cache->setOptions($options);
        }

        return $this;
    }

    /**
     * Adds a Memcached server.
     *
     * @param  Rah_Memcached_Server $server
     * @return $this
     * @throws Exception
     */
    public function addServer(Rah_Memcached_Server $server)
    {
        return $this->addServers([$server]);
    }

    /**
     * Gets an array of servers in the pool.
     *
     * @return Rah_Memcached_Server[]
     */
    public function getServers(): array
    {
        $servers = $this->cache->getServerList();

        if (!is_array($servers)) {
            return [];
        }

        return array_map(function ($server) {
            return (new Rah_Memcached_Server())
                ->setHost($server['host'])
                ->setPort($server['port'])
                ->setWeight($server['weight']);
        }, $servers);
    }

    /**
     * Adds multiple servers to the pool.
     *
     * @param  Rah_Memcached_Server[] $servers
     * @return $this
     * @throws \Exception
     */
    public function addServers(array $servers)
    {
        $pool = $this->getServers();

        $servers = array_filter($servers, function ($server) use ($pool) {
            foreach ($pool as $current) {
                if ($current->getHost() === $server->getHost() && $current->getPort() === $server->getPort()) {
                    return false;
                }
            }

            return true;
        });

        $status = $this->cache->addServers(array_map(function ($server) {
            return [
                $server->getHost(),
                $server->getPort(),
                $server->getWeight(),
            ];
        }, $servers));

        if ($status === false) {
            throw new \Exception(
                $this->getErrorMessage('Unable to add given servers to the pool')
            );
        }

        return $this;
    }

    /**
     * Adds an item to the cache.
     *
     * @param  Rah_Memcached_Item $item
     * @return $this
     * @throws \Exception
     */
    public function set(Rah_Memcached_Item $item)
    {
        $item->setKey($this->config->getPrefix() . $item->getName());

        if ($this->cache->set($item->getKey(), $item->getData(), $item->getExpires())) {
            return $this;
        }

        throw new \Exception(
            $this->getErrorMessage('Adding item to Memcached failed')
        );
    }

    /**
     * Gets a key.
     *
     * @param  string $key The key
     * @return Rah_Memcached_Item
     * @throws \Exception
     */
    public function get(string $key)
    {
        $data = $this->cache->get($this->config->getPrefix() . $key);

        if (!is_array($data)) {
            throw new \Exception(
                $this->getErrorMessage('Getting item from Memcached failed')
            );
        }

        return new Rah_Memcached_Item($data);
    }

    /**
     * Flushes cache.
     *
     * @return $this
     */
    public function flush()
    {
        foreach ($this->getAllKeys() as $key) {
            $this->cache->delete($key);
        }

        return $this;
    }

    /**
     * Gets all keys.
     *
     * @return string[]
     */
    public function getAllKeys(): array
    {
        $keys = $this->cache->getAllKeys();

        if (!is_array($keys)) {
            return [];
        }

        return array_filter($keys, function ($key) {
            return strpos($key, $this->config->getPrefix()) === 0;
        });
    }

    /**
     * Gets collection of items.
     *
     * @return Rah_Memcached_ItemIterator
     */
    public function getItems()
    {
        return new Rah_Memcached_ItemIterator($this);
    }

    /**
     * Gets an error message.
     *
     * @param string $message
     *
     * @return string
     */
    private function getErrorMessage(string $message): string
    {
        $errorMessage = method_exists($this->cache, 'getLastErrorMessage')
            ? $this->cache->getLastErrorMessage()
            : $this->cache->getResultMessage();

        $errorCode = method_exists($this->cache, 'getLastErrorCode')
            ? $this->cache->getLastErrorCode()
            : $this->cache->getResultCode();

        return \sprintf(
            "%s. Last Memcached operation result: %s: '%s'.",
            $message,
            $errorCode,
            $errorMessage
        );
    }
}
