<?php

/*
 * rah_memcached - Memcached templates for Textpattern CMS
 * https://github.com/gocom/rah_memcached
 *
 * Copyright (C) 2014 Jukka Svahn
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
 * along with rah_flat. If not, see <http://www.gnu.org/licenses/>.
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

class Rah_Memcached extends Memcached
{
    /**
     * A prefix used to seperate Textpattern installation keys from one another.
     *
     * This prefix allows sharing same Memcached server between multiple
     * Textpattern sites.
     *
     * @var string
     */

    protected $rahKeyPrefix;

    /**
     * {@inheritdoc}
     */

    public function __construct()
    {
        $this->rahKeyPrefix = 'Rah:'.get_pref('siteurl').':';
        parent::__construct();

        $host = 'localhost';
        $port = 11211;

        if (defined('RAH_MEMCACHED_HOST')) {
            $host = (string) RAH_MEMCACHED_HOST;
        }

        if (defined('RAH_MEMCACHED_PORT')) {
            $port = (int) RAH_MEMCACHED_PORT;
        }

        $this->addServer($host, $port);
    }

    /**
     * {@inheritdoc}
     */

    public function addServer($host , $port, $weight = 0)
    {
        $servers = $this->getServerList();

        if (is_array($servers)) {
            foreach ($servers as $server) {
                if ($server['host'] == $host and $server['port'] == $port) {
                    return true;
                }
            }
        }

        return parent::addServer($host , $port);
    }

    /**
     * {@inheritdoc}
     */

    public function set($key, $value, $expiration = 0)
    {
        if ($key === null) {
            $key = md5((string) $value);
        }

        return parent::set($this->rahKeyPrefix . $key, $value, $expiration);
    }

    /**
     * {@inheritdoc}
     */

    public function get($key, $cache_cb = null, &$cas_token = null)
    {
        return parent::get($this->rahKeyPrefix . $key);
    }

    /**
     * {@inheritdoc}
     */

    public function flush($delay = 0)
    {   
        $keys = $this->getAllKeys();

        if (is_array($keys)) {
            foreach ($keys as $key) {
                if (strpos($key, $this->rahKeyPrefix) === 0) {
                    parent::delete($key, $delay);
                }
            }
        }

        return true;
    }
}
