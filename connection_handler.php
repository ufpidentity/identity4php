<?php

require_once("resolver.php");

class IdentityConnectionHandler {
  private $curl_handle;
  private $resolver;

  function __construct() {
    $this->curl_handle = curl_init();
    curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->curl_handle, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($this->curl_handle, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($this->curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($this->curl_handle, CURLOPT_SSLCERTTYPE, "PEM");
    curl_setopt($this->curl_handle, CURLOPT_SSLKEYTYPE, "PEM");
    $this->resolver = new StaticIdentityResolver();
  }

  function setCAInfo($path) {
    curl_setopt($this->curl_handle, CURLOPT_CAINFO, $path);
  }

  function setSSLCert($path) {
    curl_setopt($this->curl_handle, CURLOPT_SSLCERT, $path);  // The name of a file containing a PEM formatted certificate.
  }

  function setSSLKey($path) {
    curl_setopt($this->curl_handle, CURLOPT_SSLKEY, $path);  // The name of a file containing a PEM formatted key.
  }

  function setSSLPassword($password) {
    curl_setopt($this->curl_handle, CURLOPT_SSLCERTPASSWD, $password); // The password required to use the CURLOPT_SSLCERT certificate.
  }

  function setSSLKeyPassword($password) {
    curl_setopt($this->curl_handle, CURLOPT_SSLKEYPASSWD, $password); // The password required to use the CURLOPT_SSLKEY key.
  }

  function __destruct() {
    curl_close($this->curl_handle);
  }

  function sendMessage($path, $queryparams) {
    $xml = null;
    $url = $this->resolver->getHost() . "/" . $path . "?" . http_build_query($queryparams, '', '&');
    curl_setopt($this->curl_handle, CURLOPT_URL, $url);
    $message = curl_exec($this->curl_handle);
    $http_code = curl_getinfo($this->curl_handle, CURLINFO_HTTP_CODE);
    if (!$message || ($http_code != 200)) {
      $error_xml = sprintf('<context><name>%s</name><result xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="defaultResult" message="Identity Service Failure : %d">FAILURE</result></context>', empty($queryparams['name'])?'Unknown':$queryparams['name'], $http_code);
      $xml = new SimpleXMLElement($error_xml);
    } else {
      $xml = new SimpleXMLElement($message);
    }
    return $xml;
  }

  function sendBatched($path, $fp, $readfunction) {
    $url = $this->resolver->getHost() . "/" . $path;
    curl_setopt($this->curl_handle, CURLOPT_URL, $url);
    curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, array('Content-Type: application/octet-stream'));
    curl_setopt($this->curl_handle, CURLOPT_READFUNCTION, $readfunction);
    curl_setopt($this->curl_handle, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($this->curl_handle, CURLOPT_INFILE, $fp);
    curl_setopt($this->curl_handle, CURLOPT_UPLOAD, TRUE);
    $message = curl_exec($this->curl_handle);
    $http_code = curl_getinfo($this->curl_handle, CURLINFO_HTTP_CODE);
    error_log('http_code: ' . $http_code);
  }

  function checkEnrollStatus($path) {
    $url = $this->resolver->getHost() . "/" . $path;
    curl_setopt($this->curl_handle, CURLOPT_URL, $url);
    $message = curl_exec($this->curl_handle);
    return curl_getinfo($this->curl_handle, CURLINFO_HTTP_CODE);
  }
}
?>