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
 * Stores template portions in Memcached.
 *
 * @param  array  $atts  Attributes
 * @param  string $thing Contained statement
 * @return string User markup
 *
 * <code>
 * <txp:rah_memcached name="section_nav">
 *  <txp:section_list>
 *      <txp:section />
 *  </txp:section_list>
 * </txp:rah_memcached>
 * </code>
 *
 * If used as a single tag, it fetched a value from the cache by
 * the key name.
 *
 * <code>
 * <txp:rah_memcached name="section_nav" />
 * </code>
 *
 * The tag can also be used to store variables in the memory,
 * skipping template execution and recreating the variables from
 * the state kept in memory.
 *
 * <code>
 * <txp:rah_memcached store="variables">
 *  <txp:variable name="variable1" value="value 1" />
 *  <txp:variable name="variable2" value="value 2" />
 * </txp:rah_memcached>
 * </code>
 *
 * A variable is stored if its updated or created within the
 * rah_memcached statement. Be aware, if a value of an existing
 * variable does not change, it is not kept in memory, leading into
 * potential issues.
 */

function rah_memcached($atts, $thing = null)
{
    global $variable;
    static $memcached = null;

    extract(lAtts(array(
        'expires' => 3600,
        'name'    => null,
        'store'   => 'markup',
    ), $atts));

    if ($memcached === null) {
        $memcached = new Rah_Memcached();
    }

    if ($thing === null) {

        if (!$name) {
            trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'name')));
            return '';
        }

        $value = $memcached->get($name);

        if (is_array($value)) {
            $variable = array_merge($variable, $value);
            return '';
        }

        return (string) $value;
    }

    if (($value = $memcached->get($name)) !== false) {
        return $value;
    }

    // Stores variables in memory as an serialized array.

    if ($store === 'variables') {
        $storable = array();
        $existing = $variable;
        parse($thing);

        foreach ($variable as $name => $value) {
            if (!isset($existing[$name]) || (string) $existing[$name] !== (string) $value) {
                $storable[(string) $name] = (string) $value;
            }
        }

        $memcached->set($name, $storable, (int) $expires);
        return '';
    }

    $value = (string) parse($thing);
    $memcached->set($name, $value, (int) $expires);

    return $value;
}
