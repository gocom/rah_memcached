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
 * define('\RAH_MEMCACHED_HOST', 'localhost');
 * define('\RAH_MEMCACHED_PORT', 11211);
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
     * A prefix used to seperate Textpattern installation keys from one another.
     *
     * This prefix allows sharing same Memcached server between multiple
     * Textpattern sites.
     *
     * @var string
     */
    private $prefix;

    /**
     * Stores config.
     *
     * @var Rah_Memcached_Server
     */
    private $config;

    /**
     * Constructor.
     */
    public function __construct(Memcached $memcached, Rah_Memcached_Server $config)
    {
        $this->config = $config;
        $this->cache = $memcached;
        $this->cache->addServer($config);
    }

    /**
     * Gets prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Sets prefix.
     *
     * @param  string $prefix The prefix
     * @return $this
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
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
            throw new \Exception('Unable to add given servers to the pool.');
        }

        return $this;
    }

    /**
     * Adds an item to the cache.
     *
     * @param  Rah_Memcached_Item $item
     * @return $this
     * @throws Exception
     */
    public function set(Rah_Memcached_Item $item)
    {
        if ($this->cache->set($this->getPrefix() . $item->getName(), $item->getData(), $item->getExpires())) {
            return $this;
        }

        throw new \Exception('Unable to set');
    }

    /**
     * Gets a key.
     *
     * @param  string $key The key
     * @return mixed  Rah_Memcached_Item
     */
    public function get(string $key)
    {
        $data = $this->cache->get($this->prefix . $key);

        if (!is_array($data)) {
            throw new \Exception('Unable to get');
        }

        return new Rah_Memcached_Item($data);
    }

    /**
     * Flushes cache.
     *
     * @return bool
     */
    public function flush()
    {
        $keys = $this->cache->getAllKeys();

        if (is_array($keys)) {
            foreach ($keys as $key) {
                if (strpos($key, $this->prefix) === 0) {
                    $this->cache->delete($key, $delay);
                }
            }
        }

        return true;
    }

    /**
     * Whether the key is valid.
     *
     * A key must consist of namespace separated by a colon,
     * and have between 3 and 64 characters.
     *
     * @param  string $key The key to validate
     * @return bool   FALSE if invalid
     */
    public function isValidKey($key)
    {
        if (is_string($key) && strpos($key, ':')) {
            return ($length = strlen($key)) && $length >= 3 && $length <= 64;
        }

        return false;
    }

    /**
     * Gets a message describing the result of the last operation.
     *
     * @return string
     */
    public function getResultMessage()
    {
        return $this->cache->getResultMessage();
    }
}
