<?php
class FormElement {
  private $name;
  private $display_name;
  private $form_element;

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

  /**
<enrollment_pretext>
  <name>richardl@ufp.comt</name>
  <result xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="enrollmentResult" level="0" code="0" message="OK">SUCCESS</result>
  <form_element display_name="Password" name="passphrase">
    <element>&lt;input id="EnrollParam0" type="password" name="passphrase" /&gt;</element>
  </form_element>
</enrollment_pretext>
  */
  static function unmarshallFormElement($form_element) {
    $f = new FormElement((string)$form_element['name']);
    $f->setDisplayName((string)$form_element['display_name']);
    $f->setFormElement((string)$form_element->element);
    return $f;
  }
}
?>
