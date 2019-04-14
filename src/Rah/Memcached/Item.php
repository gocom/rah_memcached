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
 * Cache item.
 */
final class Rah_Memcached_Item
{
    /**
     * Stores the item data.
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        $this->data = (array)$data;
    }

    /**
     * Gets variables as an array map.
     *
     * @return array
     */
    public function getVariables(): array
    {
        return $this->getData('variables');
    }

    /**
     * Sets variables.
     *
     * @param  array $variables Variable map
     * @return $this
     */
    public function setVariables(array $variables)
    {
        $this->setData('variables', $variables);
        return $this;
    }

    /**
     * Gets last modification timestamp.
     *
     * @return int
     */
    public function getLastmod(): int
    {
        return (int) $this->getData('lastmod');
    }

    /**
     * Sets last modification timestamp.
     *
     * @param  int  $timestamp Timestamp
     * @return $this
     */
    public function setLastmod(int $timestamp)
    {
        return $this->setData('lastmod', $timestamp);
    }

    /**
     * Gets data value.
     *
     * @param  string $name The name
     * @return mixed
     */
    public function getData($name = null)
    {
        if ($name === null) {
            return $this->data;
        }

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * Sets data value.
     *
     * @param  string $name  The name
     * @param  mixed  $value The value
     * @return $this
     */
    public function setData(string $name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Gets expiration timestamp.
     *
     * @return int|null
     */
    public function getExpires(): ?int
    {
        return $this->getData('expires');
    }

    /**
     * Sets expiration period in seconds.
     *
     * @param  int $expiration
     * @return $this
     */
    public function setExpires(int $seconds)
    {
        return $this->setData('expires', $seconds);
    }

    /**
     * Gets name.
     *
     * @return string
     */
    public function getName(): string
    {
        if (($name = $this->getData('name')) !== null) {
            return $name;
        }

        return md5($this->getData('markup'));
    }

    /**
     * Sets name.
     *
     * @param  string $name
     * @return $this
     */
    public function setName(string $name)
    {
        return $this->setData('name', $name);
    }

    /**
     * Gets contained statement.
     *
     * @return string
     */
    public function getMarkup(): string
    {
        return (string) $this->getData('markup');
    }

    /**
     * Sets contained statement.
     *
     * @param  string $markup Markup
     * @return $this
     */
    public function setMarkup(string $markup)
    {
        return $this->setData('markup', $markup);
    }

    /**
     * Gets a key.
     */
    public function getKey(): string
    {
        return (string) $this->getData('key');
    }

    /**
     * Sets a key.
     *
     * @param  string $key The key
     * @return $this
     */
    public function setKey(string $key)
    {
        return $this->setData('key', $key);
    }
}
