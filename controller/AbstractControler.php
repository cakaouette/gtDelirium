<?php

class AbstractControler
{
    private $_title;
    protected $_dangers = Array();
    protected $_primaries = Array();
    protected $_success = Array();
    protected $_warnings = Array();
    protected $_infos = Array();
    protected $_secondaries = Array();

    public function __construct($title = "")
    {
        $this->_title = "Delirium - $title";
    }

    protected function addMsg($type, $msg) {
        if ($type == "danger") {
            $this->_dangers[] = $msg;
        }
        if ($type == "warning") {
            $this->_warnings[] = $msg;
        }
        if ($type == "info") {
            $this->_infos[] = $msg;
        }
        if ($type == "success") {
            $this->_success[] = $msg;
        }
        if ($type == "primary") {
            $this->_primaries[] = $msg;
        }
        if ($type == "secondary") {
            $this->_secondaries[] = $msg;
        }
    }
    
    protected function getMsgs() {
        return Array("primary" => $this->_primaries,
                     "secondary" => $this->_secondaries,
                     "info" => $this->_infos,
                     "success" => $this->_success,
                     "warning" => $this->_warnings,
                     "danger" => $this->_dangers,
            );
    }
    
    protected function getTitle() {
        return $this->_title;
    }
}
