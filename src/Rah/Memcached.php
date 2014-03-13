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
        $this->rahKeyPrefix = 'Rah:Textpattern:'.get_pref('version').':'.get_pref('siteurl').':';
        parent::__construct();
        $this->addServer(RAH_MEMCACHED_HOST, RAH_MEMCACHED_PORT);
    }

    /**
     * {@inheritdoc}
     */

    public function addServer($host , $port, $weight = 0)
    {
        $servers = $this->memcached->getServerList();

        if (is_array($servers)) {
            foreach ($servers as $server) {
                if ($server['host'] == $host and $server['port'] == $port) {
                    return true;
                }
            }
        }

        return $this->memcached->addServer($host , $port);
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
}
