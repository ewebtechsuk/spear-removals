<?php

namespace JFB\WooComm\Vendor\JFBCore;

trait BlockEditorData
{
    public abstract function editor_data() : array;
    public abstract function editor_labels() : array;
    public abstract function editor_help() : array;
}
