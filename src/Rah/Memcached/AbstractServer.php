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
 * Abstract server configuration.
 */
abstract class Rah_Memcached_AbstractServer
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
     * Stores key prefix.
     *
     * @var int
     */
    private $prefix = '';

    /**
     * Stores lastmod.
     *
     * @var int
     */
    private $lastmod = 0;

    /**
     * Stores username.
     *
     * @var string
     */
    private $username = '';

    /**
     * Stores password.
     *
     * @var string
     */
    private $password = '';

    /**
     * Stores options.
     *
     * @var array
     */
    private $options = [];

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
     * Sets key prefix.
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

    /**
     * Gets key prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Sets last modification timestamp.
     *
     * @param int $timestamp Lastmod timestamp
     * @return $this
     */
    public function setLastmod(int $timestamp)
    {
        $this->lastmod = $timestamp;
        return $this;
    }

    /**
     * Gets last modification timestamp.
     *
     * @return int
     */
    public function getLastmod(): int
    {
        return $this->lastmod;
    }

    /**
     * Sets the username.
     *
     * @param  string $username Username
     * @return $this
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Gets the username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Sets the password.
     *
     * @param  string $password Password
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Gets the password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets options.
     *
     * @param  array $options Options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Gets options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
