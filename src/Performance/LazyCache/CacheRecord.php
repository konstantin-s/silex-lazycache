<?php

namespace Performance\LazyCache;

use \InvalidArgumentException;

class CacheRecord {

    protected $id;
    protected $uri;
    protected $hash;
    protected $lmt;
    protected $compromised = false;

    public function __construct(array $data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getUri() {
        return $this->uri;
    }

    public function getHash() {
        return $this->hash;
    }

    public function getLmt() {
        return $this->lmt;
    }

    public function getCompromised() {
        return $this->compromised;
    }

    public function setId($id) {
        if ($id && $this->id) {
            throw new InvalidArgumentException("Wrong param for " . __METHOD__ . ":{$id} (ALREADY SETTED TO:{$this->id}");
        }
        $this->id = $id;
    }

    public function setUri($uri) {
        if (!is_string($uri) || strlen($uri) <= 0) {
            throw new InvalidArgumentException("Wrong param for " . __METHOD__ . ":{$uri}");
        }
        $this->uri = $uri;
    }

    public function setHash($hash) {
        if (!is_string($hash) || strlen($hash) <= 0) {
            throw new InvalidArgumentException("Wrong param for " . __METHOD__ . ":{$hash}");
        }
        $this->hash = $hash;
    }

    public function setLmt($lmt) {
        if (!is_string($lmt) || strlen($lmt) <= 0) {
            throw new InvalidArgumentException("Wrong param for " . __METHOD__ . ":{$lmt}");
        }
        $this->lmt = $lmt;
    }

    public function setCompromised($compromised) {

        $this->compromised = (bool) $compromised;
    }

    public function __set($field, $value) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Setting the field '$field' is not valid for this entity.");
        }

        $mutator = "set" . ucfirst(strtolower($field));
        method_exists($this, $mutator) &&
                is_callable(array($this, $mutator)) ? $this->$mutator($value) : $this->$field = $value;

        return $this;
    }

    public function __get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Getting the field '$field' is not valid for this entity.");
        }

        $accessor = "get" . ucfirst(strtolower($field));
        return method_exists($this, $accessor) &&
                is_callable(array($this, $accessor)) ? $this->$accessor() : $this->$field;
    }

    public function toArray() {
        return get_object_vars($this);
    }

}
