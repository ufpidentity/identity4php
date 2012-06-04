<?php
require_once("connection_handler.php");

class IdentityServiceProvider {
  private $connection_handler;

  function __construct() {
    $this->connection_handler = new IdentityConnectionHandler();
  }

  function getConnectionHandler() {
    return $this->connection_handler;
  }


  /**
     <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
     <enrollment_pretext>
       <name>test</name>
       <result code="0" message="OK">SUCCESS</result>
       <form_element display_name="Password" name="passphrase">
         <element>&lt;input id=&quot;EnrollParam0&quot; type=&quot;password&quot; name=&quot;passphrase&quot; /&gt;</element>
       </form_element>
     </enrollment_pretext>
  */

  private function parsePreEnrollmentResult($xml) {
    $result = Result::unmarshallResult($xml);
    $pretext = array('name' => (string)$xml->name[0],
                     'result' => $result,
                     );
    if ($result->getText() == "SUCCESS") {
      $form_elements = array();

      foreach ($xml->form_element as $form_element) {
        $form_elements[] = FormElement::unmarshallFormElement($form_element);
      }
      $pretext['form_elements'] = $form_elements;
    }
    return $pretext;
  }

  private function parseEnrollmentResult($xml) {
    $result = Result::unmarshallResult($xml);
    
    $pretext = array('name' => (string)$xml->name[0],
                     'result' => $result,
                     );
    return $pretext;
  }

  /**
     <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
     <authentication_pretext>
       <name>richardl@ufp.com</name>
       <result xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="authenticationResult" confidence="0.0" level="0" code="0" message="OK">SUCCESS</result>
       <display_item name="secret">
         <display_name>Enter Secret</display_name>
         <form_element>&lt;input id=&quot;AuthParam0&quot; type=&quot;text&quot; name=&quot;secret&quot; /&gt;</form_element>
         <nickname>SAW (w/email)</nickname>
       </display_item>
     </authentication_pretext>
  */
  private function parseAuthenticationResult($xml) {
    $result = Result::unmarshallResult($xml);
    
    $pretext = array('name' => (string)$xml->name[0],
                     'result' => $result,
                     );

    if (($result->getText() == "SUCCESS") || ($result->getText() == "CONTINUE")) {
      $display_items = array();

      foreach ($xml->display_item as $display_item) {
        $display_items[] = DisplayItem::unmarshallDisplayItem($display_item);
      }
      $pretext['display_items'] = $display_items;
    }
    return $pretext;
  }

  function preAuthenticate($name, $host) {
    $data = array( "name" => $name, "client_ip" => $host);
    $xml = $this->connection_handler->sendMessage("preauthenticate", $data);
    return $this->parseAuthenticationResult($xml);
  }

  function preEnroll($name, $host) {
    $data = array( "name" => $name, "client_ip" => $host);
    $xml = $this->connection_handler->sendMessage("preenroll", $data);
    return $this->parsePreEnrollmentResult($xml);
  }

  function authenticate($name, $host, $params) {
    $xml = $this->makeRequest($name, $host, $params, 'authenticate');
    return $this->parseAuthenticationResult($xml);
  }

  function enroll($name, $host, $params) {
    $xml = $this->makeRequest($name, $host, $params, 'enroll');
    return $this->parseEnrollmentResult($xml);
  }
  
  function reenroll($name, $host, $params) {
    $xml = $this->makeRequest($name, $host, $params, 'reenroll');
    return $this->parseEnrollmentResult($xml);
  }

  function batchEnroll($fp, $readfunction) {
    error_log('batch enroll with readfunction: ' . $readfunction);
    $this->connection_handler->sendBatched('enroll', $fp, $readfunction);
  }
  
  function checkEnrollStatus() {
    $status = FALSE;
    $http_status = $this->connection_handler->checkEnrollStatus('enroll/status');
    if ($http_status == 200) {
      $status = TRUE;
    }
    return $status;
  }
      
  function makeRequest($name, $host, $params, $method) {
    $data = array( "name" => $name, "client_ip" => $host);
    foreach ($params as $key => $value) {
      $data[$key] = $value;
    }
    return $this->connection_handler->sendMessage($method, $data);
  }
}
?>