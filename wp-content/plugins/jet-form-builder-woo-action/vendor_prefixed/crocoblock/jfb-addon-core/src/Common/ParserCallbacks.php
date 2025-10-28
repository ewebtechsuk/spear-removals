<?php

namespace JFB\WooComm\Vendor\JFBCore;

abstract class ParserCallbacks
{
    protected abstract function callbacks();
    public function call_parse($attr, $source)
    {
        if ($this->is_exists($attr)) {
            return \call_user_func($this->callbacks()[$attr], $source);
        }
    }
    public function is_exists($attr)
    {
        $callbacks = $this->callbacks();
        return isset($callbacks[$attr]) && \is_callable($callbacks[$attr]);
    }
}
