<?php
  session_start();
  $email = $_SESSION['identity_email'];
  unset($_SESSION['identity_email']);
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
        <p>We attempted to mail the newly generated Certificate
        Signing Request for <?php echo $email; ?>. Check the
        <code>/tmp/csr_generator.log</code> for any errors. We created
        three additional files in <code>/tmp</code>.</p>

        <p><code>identity.csr.pem</code> is the Certificate Signing
        Request. If you don't hear from us in 24 hours. Please email
        the <code>identity.csr.pem</code> to us at <a
        href="mailto:&#105;&#110;&#102;&#111;&#64;&#117;&#102;&#112;&#46;&#99;&#111;&#109;?subject=Certificate Signing Request">&#105;&#110;&#102;&#111;&#64;&#117;&#102;&#112;&#46;&#99;&#111;&#109;</a>.</p>

        <p>DO NOT mail <code>identity.key.pem</code> and <code>secret.key</code>. The <code>identity.key.pem</code> is your private key which is encrypted with <code>secret.key</code>. Please move thes files to the identity directory where the identity PHP files are located. Make sure a proper .htaccess file is also there to prevent anyone from requesting any .pem. Once you have initialized the <span style="color: #bebebe;">ufp</span><span style="color: black;">Identity</span> PHP library with <code>identity.key.pem</code> and <code>secret.key</code>, you can delete the <code>secret.key</code> file.</p>
      </div>
      </div>
    </div>
  </body>
</html>
