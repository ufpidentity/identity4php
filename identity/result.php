<?php
class Result {
  private $confidence;
  private $level;
  private $message;
  private $text;
  private $code;

  function __construct($text) {
    $this->text = $text;
  }

  function getText() {
    return $this->text;
  }

  function getMessage() {
    return $this->message;
  }

  function setMessage($message) {
    $this->message = $message;
  }

  function getLevel() {
    return $this->level;
  }

  function setLevel($level) {
    $this->level = $level;
  }

  function getConfidence() {
    return $this->confidence;
  }

  function setConfidence($confidence) {
    $this->confidence = $confidence;
  }

  function setCode($code) {
      $this->code = $code;
  }

  function getCode() {
      return $this->code;
  }

  static function unmarshallResult($xml) {
    $result = new Result((string)$xml->result[0]);
    $result->setLevel((string)$xml->result['level']);
    $result->setConfidence((string)$xml->result['confidence']);
    $result->setMessage((string)$xml->result['message']);
    $result->setCode((int)$xml->result['code']);
    return $result;
  }
}
?>