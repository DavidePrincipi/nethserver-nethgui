<?php
/**
 * @package Adapter
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Table adapter provide an array like access to all database keys of a given type
 *
 * @package Adapter
 */
class NethGui_Adapter_TableAdapter implements NethGui_Adapter_AdapterInterface, ArrayAccess, IteratorAggregate, Countable
{

    /**
     *
     * @var NethGui_Core_ConfigurationDatabase
     */
    private $database;
    private $type;
    private $filter;
    /**
     *
     * @var ArrayObject
     */
    private $data;
    /**
     *
     * @var ArrayObject
     */
    private $changes;

    public function __construct(NethGui_Core_ConfigurationDatabase $db, $type, $filter = FALSE)
    {
        $this->database = $db;
        $this->type = $type;
        $this->filter = $filter;
    }

    private function lazyInitialization()
    {
        $this->data = new ArrayObject();
        
        $rawData =$this->database->getAll($this->type, $this->filter);
        
        if(is_array($rawData)) {
            // skip the first column, where getAll() returns the key type.
            foreach($rawData as $key => $row) {
                $this->data[$key] = array_slice($row, 1);
            }
        }
                
        $this->changes = new ArrayObject();
    }

    public function count()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->count();
    }

    public function delete()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }
                      
        foreach (array_keys($this->data->getArrayCopy()) as $key) {
            unset($this[$key]);
        }
    }

    public function get()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this;
    }

    public function set($value)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        if ( ! is_array($value) && ! $value instanceof Traversable) {
            throw new InvalidArgumentException('Value must be an array!');
        }

        foreach ($value as $key => $props) {
            $this[$key] = $props;
        }
    }

    public function save()
    {
        if ( ! $this->isModified()) {
            return 0;
        }

        $saveCount = 0;

        foreach ($this->changes as $args) {
            $method = array_shift($args);
            call_user_func_array(array($this->database, $method), $args);
            $saveCount ++;
        }

        $this->changes = new ArrayObject();
        
        $this->modified = FALSE;

        return $saveCount;
    }

    public function getIterator()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->getIterator();
    }

    public function isModified()
    {
        return $this->changes instanceof ArrayObject && count($this->changes) > 0;
    }

    public function offsetExists($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        if ( ! is_array($value) && ! $value instanceof Traversable) {
            throw new InvalidArgumentException('Value must be an array!');
        }

        if (isset($this[$offset])) {
            $this->changes[] = array('setProp', $offset, $value);
        } else {
            $this->changes[] = array('setKey', $offset, $this->type, $value);
        }

        $this->data->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        unset($this->data[$offset]);
        $this->changes[] = array('deleteKey', $offset);
    }

}