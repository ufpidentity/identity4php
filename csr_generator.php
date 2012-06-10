<?php
ini_set('error_log', '/tmp/csr_generator.log');

session_start();

$method = $_SERVER['REQUEST_METHOD'];
error_log($method);
switch ($method) {
case 'POST':
    $email = NULL;

    $dn = array();
    error_log(print_r($_POST, TRUE));

    foreach ($_POST as $k => $v) {
        ${$k} = $v;
        $pos = strpos($k, 'identity_');
        if ($pos !== FALSE) {
            $name = substr($k, strlen('identity_'));
            $dn[$name] = $v;
            if ($name == 'emailAddress') {
                $email = $v;
            }
        }
    }
    error_log(print_r($dn, TRUE));
    // Generate a new private (and public) key pair
    $config = array('private_key_bits' => 2048);
    $privkey = openssl_pkey_new($config);

    // Generate a certificate signing request
    $csr = openssl_csr_new($dn, $privkey);

    // encrypt and export the key material
    $encrypt_key = get_key();
    $success = openssl_pkey_export_to_file($privkey, '/tmp/identity.key.pem', ($encrypt_key == NULL)?NULL:base64_decode($encrypt_key));
    if ($success) {
        openssl_free_key($privkey);

        // save off csr
        $success = openssl_csr_export_to_file($csr, '/tmp/identity.csr.pem');
        if ($success) {
            // attempt to mail out the csr
            $success = openssl_csr_export($csr, $csrout);
            if ($success) {
                $headers = 'From: ' . $email . "\r\n" .
                    'Reply-To: ' . $email . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                $success = mail('info@ufp.com', 'Certificate Request', $csrout, $headers);
                if (!$success)
                    error_log('failed to mail certificate request');
            } else
                error_log('failed to export certificate request to string');
        } else
            error_log('failed to export certificate request to file');
    } else
        error_log('failed to export private key to file');
    $_SESSION['identity_email'] = $email;
    header('Location: csr_success.php');
    break;
default:
    error_log("unhandled method: $method");
    break;
}

function get_key($bit_length = 128) {
    $fp = FALSE;
    $key = NULL;

    if (@is_readable('/dev/urandom')) {
        $fp = @fopen('/dev/urandom', 'rb');
    } else {
        $fp = @fopen('https://www.random.org/cgi-bin/randbyte?format=f&nbytes=16', 'rb');
    }
    if ($fp !== FALSE) {
        $key = base64_encode(@fread($fp,($bit_length + 7) / 8));
        @fclose($fp);
    }
    if ($key == NULL)
        error_log('unable to get a key');
    else {
        $fp = @fopen('/tmp/secret.key', 'wb');
        if ($fp !== FALSE) {
            @fwrite($fp, $key);
            @fclose($fp);
        }
    }
    return $key;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>ufp Identity - Certificate Request Generation Form</title>
    <link rel="stylesheet" href="https://www.ufp.com/identity/css/main.css"/>
    <link rel="stylesheet" href="s.css"/>
    <script src="https://www.ufp.com/identity/scripts/jquery.min.js" type="text/javascript"></script>
    <script src="csr.js" type="text/javascript"></script>
  </head>
  <body>
    <div id="container">
      <div id="formOuter">
        <p>In order to connect to the <span style="color:
        #bebebe;">ufp</span><span style="color:
        black;">Identity</span> service client authenticated SSL is
        required. The examples and libraries take care of most of this
        for you, however you must have a certificate and private
        key. It is important you do this, so no one but you has access
        to the private key. This form will generate a Certificate
        Signing Request, a private key and a secret key that encrypts
        the private key. All you need to do is enter in a few fields
        describing your company.</p>

        <p>Make sure to read carefully and enter the values in
        carefully. If there are errors or inconsistencies we will ask
        you to redo the certificate signing request. The elements you
        will need to provide are as follows:</p>

        <dl>
          <dt>Country Code</dt>
          <dd>This is a two-letter country code. You can find your own country code <a href="http://www.iso.org/iso/country_names_and_code_elements">here.</a></dd>
          <dt>State or Province Name</dt>
          <dd>This is the state or province name fully spelled out, so California rather than CA or any postal abbreviation.</dd>
          <dt>Locality</dt>
          <dd>This further identifies your location, city or other. Again this is fully spelled out so San Francisco rather than SF or any other abbreviation.</dd>
          <dt>Company/Organization</dt>
          <dd>This is your full company name as its registered. The exact abbreviations are required to match however your company is actually registered.</dd>
          <dt>Organizational Unit</dt>
          <dd>This is to further identify what department of your organization is going to be utilizing the <span style="color:
          #bebebe;">ufp</span><span style="color: black;">Identity</span> service. This field is not required, but is useful for organizational purposes.</dd>
          <dt>Domain Name</dt>
          <dd>Put some thought into your domain name as this is how the <span style="color: #bebebe;">ufp</span><span style="color: black;">Identity</span> service will identify you. You can put anything you like here but typical examples are your actual domain name e.g. example.com which would allow you to use the <span style="color:
          #bebebe;">ufp</span><span style="color: black;">Identity</span> service for a host of machines. You can also tie it to specific machine e.g. www.example.com. Any unique identifier will work but if you have questions please dont hesitate to <a href="mailto:&#105;&#110;&#102;&#111;&#64;&#117;&#102;&#112;&#46;&#99;&#111;&#109;?subject=Domain Name Question">contact us</a> with any questions.</dd>
          <dt>Email</dt>
          <dd>This should be a valid email and should allow us to contact someone responsible.</dd>
        </dl>

        <h3>Certificate Request Generation Form</h3>
        <form id="x500" method="post" action="csr_generator.php">
          <div class="input">
            <input type="text" id="country" name="identity_countryName" class="field required" title="Please provide a country code" />
            <div class="description"><a href="http://www.iso.org/iso/country_names_and_code_elements">2 Letter Country Code</a> e.g. US</div>
          </div>
          <div class="input">
            <input type="text" id="state" name="identity_stateOrProvinceName" class="field required" title="Please provide a state" />
            <div class="description">Full state or province name e.g. California</div>
          </div>
          <div class="input">
            <input type="text" id="locality" name="identity_localityName" class="field required" title="Please provide a locality/city" />
            <div class="description">Full locality name/city e.g. San Francisco</div>
          </div>
          <div class="input">
            <input type="text" id="organization" name="identity_organizationName" class="field required" title="Please provide an organization/company" />
            <div class="description">Company e.g. Internet Widgets Pty Ltd</div>
          </div>
          <div class="input">
            <input type="text" id="organizationalUnit" name="identity_organizationalUnitName" class="field" title="Please provide a organizational unit" />
            <div class="description">Section e.g. Manufacturing Department</div>
          </div>
          <div class="input">
            <input type="text" id="commonName" name="identity_commonName" class="field required" title="Please provide a common name" />
            <div class="description">Domain Name e.g. example.com</div>
          </div>
          <div class="input">
            <input type="text" id="emailAddress" name="identity_emailAddress" class="field required" title="Please provide an email address" />
            <div class="description">Your valid email e.g. alice@example.com</div>
            <input type="submit" value="Submit"/>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>