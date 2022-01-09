<?php


class Character
{
    private int $_id;
    private string $_name;
    private int $_elementId;
    private string $_elementName;
    private int $_grade;

    public function __construct(int $id, string $name, int $elementId, int $grade, string $elementName = "") {
        $this->_id = $id;
        $this->_name = $name;
        $this->_elementId = $elementId;
        $this->_elementName = $elementName;
        $this->_grade = $grade;
    }

    public function getId(): int { return $this->_id; }
    public function getName(): string { return $this->_name; }
    public function getElementInfo(): Array { return Array("id" => $this->_elementId, "name" => $this->_elementName); }
    public function getGrade(): int { return $this->_grade; }
}