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
 * Default server configuration.
 */
final class Rah_Memcached_DefaultServer extends Rah_Memcached_Server
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        if (defined('\RAH_MEMCACHED_HOST')) {
            $this->setHost(\RAH_MEMCACHED_HOST);
        }

        if (defined('\RAH_MEMCACHED_PORT')) {
            $this->setPort(\RAH_MEMCACHED_PORT);
        }
    }
}
