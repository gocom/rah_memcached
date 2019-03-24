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
 * <code>
 * define('RAH_MEMCACHED_HOST', 'localhost');
 * define('RAH_MEMCACHED_PORT', 11211);
 * </code>
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
    protected $prefix;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->prefix = 'Rah:'.get_pref('siteurl').':';

        $host = 'localhost';
        $port = 11211;

        if (defined('RAH_MEMCACHED_HOST')) {
            $host = (string) RAH_MEMCACHED_HOST;
        }

        if (defined('RAH_MEMCACHED_PORT')) {
            $port = (int) RAH_MEMCACHED_PORT;
        }

        $this->cache = new Memcached();
        $this->cache->addServer($host, $port);
    }

    /**
     * Adds a Memcached server.
     *
     * @param  string $host   Server hostname
     * @param  int    $port   Server port
     * @param  int    $weight Server try-out order
     * @return bool   FALSE on error
     */
    public function addServer($host, $port, $weight = 0)
    {
        $servers = $this->cache->getServerList();

        if (is_array($servers)) {
            foreach ($servers as $server) {
                if ((string) $server['host'] === (string) $host && (int) $server['port'] === (int) $port) {
                    return true;
                }
            }
        }

        return (bool) $this->cache->addServer($host, $port);
    }

    /**
     * Sets a key.
     *
     * @param  string $key        The key
     * @param  mixed  $value      The value
     * @param  int    $expiration The expiration in seconds
     * @return bool  FALSE on error
     */
    public function set($key, $value, $expiration = 0)
    {
        return (bool) $this->cache->set($this->prefix . $key, $value, $expiration);
    }

    /**
     * Gets a key.
     *
     * @param  string $key The key
     * @return mixed  The value, or FALSE on error
     */
    public function get($key)
    {
        $cache = $this->cache->get($this->prefix . $key);

        if (is_array($cache) && isset($cache['lastmod']) && $cache['lastmod'] !== get_pref('lastmod')) {
            return false;
        }

        return $cache;
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
