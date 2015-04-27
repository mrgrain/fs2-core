<?php
namespace Frogsystem\Frogsystem;


class LegacyText implements \ArrayAccess {

    private $container = [];

    public function __construct(LegacyConfig $config) {
        $lang = $config->config('language_text');

        $this->container = array(
            'frontend'  => new \lang ($lang, 'frontend'),
            'admin'     => new \lang ($lang, 'admin'),
            'template'  => new \lang ($lang, 'template'),
            'menu'      => new \lang ($lang, 'menu'),
            'fscode'    => new \lang ($lang, 'fscode'),
        );
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}