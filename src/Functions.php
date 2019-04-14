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
 * Stores template portions and variables in Memcached.
 *
 * ```
 * <rah::memcached name="site:section_nav">
 *  <txp:section_list>
 *      <txp:section />
 *  </txp:section_list>
 * </rah::memcached>
 * ```
 *
 * The tag can also be used to store variables in the memory,
 * skipping the variable execution and recreating the variables from
 * the state kept in memory.
 *
 * ```
 * <rah::memcached name="site:variables">
 *  <txp:variable name="variable1" value="value 1" />
 *  <txp:variable name="variable2" value="value 2" />
 * </rah::memcached>
 *
 * Variable1: <txp:variable name="variable1" />
 * Variable2: <txp:variable name="variable2" />
 * ```
 *
 * A variable's value is stored if its updated or created within the
 * rah_memcached statement, skipping the value assignment execution, but
 * still keeping the variable and its value available. Be aware,
 * if a value of an existing variable does not change, it is not kept in
 * memory.
 *
 * @param  array  $atts  Attributes
 * @param  string $thing Contained statement
 * @return string User markup
 */
function rah_memcached($atts, $thing = null)
{
    global $variable;
    static $memcached = null;

    extract(lAtts([
        'expires' => 0,
        'persist' => 0,
        'name' => null,
    ], $atts));

    if ($memcached === null) {
        $memcached = new Rah_Memcached(new Memcached(), new Rah_Memcached_DefaultServer());
    }

    if ($name === null && $thing !== null) {
        $name = 'rah_memcached_hash:'.md5($thing);
    }

    if ($memcached->isValidKey($name) === false) {
        trigger_error(gTxt('invalid_attribute_value', ['{name}' => 'name']));
        return '';
    }

    $lastmod = (int) get_pref('lastmod');

    try {
        $cache = $memcached->get($name);

        if ($cache->getLastmod() && $cache->getLastmod() < $lastmod) {
            throw new \Exception('Expired');
        }

        trace_add("[rah_memcached: '$name' found in cache]");

        if (($stored = $cache->getVariables())) {
            $variable = array_merge((array) $variable, $stored);
        }

        return $this->getMarkup();
    } catch (\Exception $e) {
        trace_add('[rah_memcached: ' . $e->getMessage() . ']');
    }

    $existing = $variable;
    $parsed = parse($thing);

    $item = new Rah_Memcached_Item();
    $item
        ->setName($name)
        ->setExpires((int) $expires)
        ->setMarkup($parsed);

    if (!$persist) {
        $item->setLastmod($lastmod);
    }

    if ($variable) {
        $storage = [];

        foreach ($variable as $var => $value) {
            if (!isset($existing[$var]) || $existing[$var] !== $value) {
                $storage[$var] = $value;
                trace_add("[rah_memcached: picked up variable '$var' for storage]");
            }
        }

        if ($storage) {
            $item->setVariables($storage);
        }
    }

    try {
        $memcached->set($item);
        trace_add("[rah_memcached: stored item '$name']");
    } catch (Exception $e) {
        trace_add("[rah_memcached: {$e->getMessage()}]");
    }

    return $parsed;
}
