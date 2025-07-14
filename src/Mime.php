<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb;

/**
 * Summary of Mime
 */
class Mime {
    public $name = "";
    public $source = [];
    public $extensions = [];

    public function __construct(array $params) {
        $this->name = $params['name'] ?? $this->name;
        $this->source = $params['source'] ?? $this->source;
        $this->extensions = $params['extensions'] ?? $this->extensions;
    }
}