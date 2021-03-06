<?php
namespace Nethgui\Adapter;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Handles record-like operations for a database.
 * 
 * The internal value is an hash of values
 * 
 * States: 
 * NEW, CLEAN, MODIFIED:{DELETED, DIRTY}
 * 
 * Transactions:
 * CREATED -> CLEAN
 * CREATED -> DIRTY, when the key has been set and does not exist in arr
 * CLEAN -> DIRTY -> DELETED
 * CLEAN -> DELETED
 * DIRTY -> CLEAN
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class RecordAdapter implements \Nethgui\Adapter\AdapterInterface, \ArrayAccess, \IteratorAggregate
{
    const CREATED = 0;
    const CLEAN = 1;
    const DIRTY = 2;
    const DELETED = 3;

    /**
     * The records container
     * @var \ArrayAccess
     */
    protected $arr;

    /**
     *
     * @var string
     */
    private $keyValue = NULL;

    /**
     *
     * @var bool
     */
    private $state = 0;

    /**
     * This is our record, the fields container
     * @var \ArrayObject
     */
    private $data = NULL;

    public function __construct(\ArrayAccess $arr = NULL)
    {
        $this->arr = $arr;
        $this->data = new \ArrayObject();
    }

    public function setTableData(\ArrayAccess $arr)
    {
        $this->arr = $arr;
        return $this;
    }

    public function setKeyValue($value)
    {
        if ($value === $this->keyValue) {
            return $this;
        } elseif (isset($this->keyValue)) {
            throw new \LogicException(sprintf('%s: the record key is already set', __CLASS__), 1336463530);
        }
        $this->keyValue = $value;

        // put the missing tableAdapter values into the current data:
        $this->mergeTableData();

        return $this;
    }

    public function getKeyValue()
    {
        return $this->keyValue;
    }

    public function delete()
    {
        $this->state = self::DELETED;
    }

    public function get()
    {
        return $this->data->getArrayCopy();
    }

    public function isModified()
    {
        return $this->state === self::DIRTY || $this->state === self::DELETED;
    }

    public function save()
    {
        if (is_null($this->arr)) {
            throw new \LogicException(sprintf('%s: you must setTableData() before save().', __CLASS__), 1354113867);
        }

        if ( ! $this->isModified()) {
            return FALSE;
        }
        
        if (is_null($this->getKeyValue())) {
            throw new \LogicException(sprintf('%s: you must setKeyValue() before save().', __CLASS__), 1336388582);
        }

        if ($this->state === self::DELETED) {
            $this->arr->offsetUnset($this->getKeyValue());
            $this->data = new \ArrayObject();
            //$this->keyValue = NULL;
        } else {
            $this->mergeTableData();
            $this->arr->offsetSet($this->getKeyValue(), $this->data->getArrayCopy());
        }

        $this->state = self::CLEAN;
    }

    public function set($value)
    {
        if (is_array($value)) {
            $value = new \ArrayObject($value);
        }

        if ( ! $value instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('%s: the $value must be an array or an instance of Traversable interface', __CLASS__), 1336388598);
        }

        foreach ($value as $propName => $propValue) {
            $this->offsetSet($propName, $propValue);
        }
    }

    public function offsetExists($offset)
    {
        return $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->data->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ( ! $this->offsetExists($offset) ||
            $this->offsetGet($offset) !== $value) {
            $this->data->offsetSet($offset, $value);
            if ($this->state !== self::DELETED) {
                $this->state = self::DIRTY;
            }
        }
    }

    public function offsetUnset($offset)
    {
        $this->data->offsetUnset($offset);
    }

    public function getIterator()
    {
        return $this->data->getIterator();
    }

    private function mergeTableData()
    {
        $keyValue = $this->getKeyValue();

        // Nothing to do if setTableData() has not been called
        if ($this->arr === NULL) {
            return;
        }

        // Mark record as modified if we are a new record
        if ( ! $this->arr->offsetExists($keyValue)) {
            $this->state = self::DIRTY;
            return;
        }

        // DB fields are transfered into the object storage. Record state
        // is unchanged and existing values are retained.
        foreach ($this->arr->offsetGet($keyValue) as $key => $value) {
            if ( ! $this->data->offsetExists($key)) {
                $this->data->offsetSet($key, $value);
            }
        }
    }

}
