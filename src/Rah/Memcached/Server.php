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
 * Server configuration.
 */
class Rah_Memcached_Server
{
    /**
     * Stores hostname.
     *
     * @var string
     */
    private $host = 'localhost';

    /**
     * Stores port.
     *
     * @var int
     */
    private $port = 11211;

    /**
     * Stores weight.
     *
     * @var int
     */
    private $weight = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Configure.
     */
    protected function configure(): void
    {
    }

    /**
     * Sets hostname.
     *
     * @param  string $host The hostname
     * @return $this
     */
    public function setHost(string $host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Sets port.
     *
     * @param  int $port The port
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Sets weight.
     *
     * @return $this
     */
    public function setWeight(int $weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * Gets hostname.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Gets port.
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Gets weight.
     *
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }
}
