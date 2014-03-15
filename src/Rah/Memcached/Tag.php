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
 * Stores template portions and variables in Memcached.
 *
 * <code>
 * <txp:rah_memcached name="section_nav">
 *  <txp:section_list>
 *      <txp:section />
 *  </txp:section_list>
 * </txp:rah_memcached>
 * </code>
 *
 * The tag can also be used to store variables in the memory,
 * skipping the variable execution and recreating the variables from
 * the state kept in memory.
 *
 * <code>
 * <txp:rah_memcached name="variables">
 *  <txp:variable name="variable1" value="value 1" />
 *  <txp:variable name="variable2" value="value 2" />
 * </txp:rah_memcached>
 *
 * Variable1: <txp:variable name="variable1" />
 * Variable2: <txp:variable name="variable2" />
 * </code>
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

    extract(lAtts(array(
        'expires' => 0,
        'lastmod' => 1,
        'name'    => null,
    ), $atts));

    if ($name === null) {
        $name = 'rah_memcached_hash:'.md5($thing);
    }

    if ($memcached === null) {
        $memcached = new Rah_Memcached();
    }

    if (($cache = $memcached->get($name)) !== false) {

        if (is_array($cache)) {

            if ($cache['variables']) {
                $variable = array_merge((array) $variable, $cache['variables']);
            }

            return $cache['markup'];
        }

        return (string) $cache;
    }

    if ($thing === null) {
        return '';
    }

    $cache = array(
        'variables' => array(),
        'markup'    => '',
        'lastmod'   => null,
    );

    if ($lastmod) {
        $cache['lastmod'] = get_pref('lastmod');
    }

    $existingVariables = $variable;
    $cache['markup'] = (string) parse($thing);

    // Stores variables in memory as an serialized array.

    if ($variable) {
        foreach ($variable as $name => $value) {
            if (!isset($existingVariables[$name]) || (string) $existingVariables[$name] !== (string) $value) {
                $cache['variables'][(string) $name] = (string) $value;
            }
        }
    }

    if (!$cache['variables'] && $cache['lastmod'] === null) {
        $memcached->set($name, $cache['markup'], (int) $expires);
    } else {
        $memcached->set($name, $cache, (int) $expires);
    }

    return $cache['markup'];
}
