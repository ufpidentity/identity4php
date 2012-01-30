<?php
class DisplayItem {
  private $name;
  private $display_name;
  private $form_element;
  private $nick_name;
  
  function __construct($name) {
    $this->name = $name;
  }

  function getName() {
    return $this->name;
  }

  function getDisplayName() {
    return $this->display_name;
  }

  function setDisplayName($display_name) {
    $this->display_name = $display_name;
  }

  function getFormElement() {
    return $this->form_element;
  }

  function setFormElement($form_element) {
    $this->form_element = $form_element;
  }

  function getNickName() {
    return $this->nick_name;
  }

  function setNickName($nick_name) {
    $this->nick_name = $nick_name;
  }

  static function unmarshallDisplayItem($display_item) {
    $d = new DisplayItem((string)$display_item['name']);
    $d->setDisplayName((string)$display_item->display_name);
    $d->setNickName((string)$display_item->nickname);
    $d->setFormElement((string)$display_item->form_element);
    return $d;
  }
}
?>