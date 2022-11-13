<?php

namespace App\Entity;

class BossInfo
{
    private int $_id;
    private int $_bossId;
    private int $_infoId;
    private string $_type;
    private string $_extension;
    private string $_source;

    public function __construct($id, $bossId, $infoId, $type, $extension, $source) {
        $this->_id = (int) $id;
        $this->_bossId = (int) $bossId;
        $this->_infoId = (int) $infoId;
        $this->_type = $type;
        $this->_extension = $extension;
        $this->_source = $source;
    }

    public function getId() : int { return $this->_id; }
    public function getBossId() : int { return $this->_bossId; }
    public function getImageId() : int { return $this->_infoId; }
    public function getType() : string { return $this->_type; }
    public function getExtension() : string { return $this->_extension; }
    public function getSource() : string { return $this->_source; }
}