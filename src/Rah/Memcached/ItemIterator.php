<?php

/*
 * rah_memcached - Memcached templates for Textpattern CMS
 * https://github.com/gocom/rah_memcached
 *
 * Copyright (C) 2022 Jukka Svahn
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
final class Rah_Memcached_ItemIterator implements \Iterator
{
    /**
     * Stores current index.
     *
     * @var int
     */
    private $index = 0;

    /**
     * Stores memcached instance.
     *
     * @var Rah_Memcached
     */
    private $memcached;

    /**
     * Stores cache keys.
     *
     * @var string[]
     */
    private $keys = [];

    /**
     * Constructor.
     */
    public function __construct(Rah_Memcached $memcached)
    {
        $this->memcached = $memcached;
        $this->keys = array_values($this->memcached->getAllKeys());
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * Current item.
     *
     * @return Rah_Memcached_Item
     */
    public function current(): mixed
    {
        return $this->memcached->get($this->keys[$this->index]);
    }

    /**
     * {@inheritdoc}
     */
    public function key(): mixed
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return isset($this->keys[$this->index]);
    }
}
