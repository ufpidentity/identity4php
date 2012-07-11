<?php
ini_set('error_log', '/tmp/php.log');
ini_set('date.timezone', 'America/Los_Angeles');
require_once('identity/identity.php');
require_once('identity/result.php');
require_once('identity/display_item.php');

$provider = null;

session_start();

$error = null;
$message = null;

$method = $_SERVER['REQUEST_METHOD']; // get the method, in this example we only handle POST
error_log($method);
switch ($method) {
case 'POST':
  $provider = identity_get_service_provider(); // get the service provider
  error_log('POST with session data: ' . print_r($_SESSION, true));

  // if we don't have any session variables when we get the POST, we are in a pre authentication state since we know nothing
  if (empty($_SESSION['IDENTITY_USERNAME_KEY']) && empty($_SESSION['IDENTITY_DISPLAY_ITEMS'])) {
    error_log('preauthenticate with $_POST:' . print_r($_POST, true));
    // make sure to send the client ip i.e. $_SERVER['REMOTE_ADDR'] also make sure the POST variable ('username') corresponds with input name
    $pretext = $provider->preAuthenticate($_POST['username'], $_SERVER['REMOTE_ADDR']);
    if ($pretext['result']->getText() == 'SUCCESS') {
      // if we got success, make sure to use the returned 'name' rather than the value of the POST variable
      $_SESSION['IDENTITY_USERNAME_KEY'] = $pretext['name'];
      // serialize the dynamic inputs for later rendering on the login page
      $_SESSION['IDENTITY_DISPLAY_ITEMS'] = serialize($pretext['display_items']);
      error_log('preauthenticate: pretext ' . print_r($pretext, TRUE));
    } else {
      // otherwise we set the error; n.b. DO NOT indicate to the user the errors that occurred. 
      $error = $pretext['result']->getMessage();
    }

  // if we DO have some session variables when we get the POST, we know were in an authentication state  
  } else if (!empty($_SESSION['IDENTITY_DISPLAY_ITEMS'])) {
    error_log('authenticate with $_POST:' . print_r($_POST, true));
    /*
     * The following logic iterates over our serialized session variables so we don't send over arbitrary
     * POST variables. We only want the relevant input values.
     */
    $params = array();
    $display_items = unserialize($_SESSION['IDENTITY_DISPLAY_ITEMS']);
    foreach ($display_items as $display_item) {
      $parameter_name = $display_item->getName();
      error_log('looking for ' . print_r($parameter_name, TRUE));
      $params[$parameter_name] = $_POST[$parameter_name];
    }
    // again be sure to use the client ip and the session value for user name rather than anything passed in
    $context = $provider->authenticate($_SESSION['IDENTITY_USERNAME_KEY'], $_SERVER['REMOTE_ADDR'], $params);
    error_log('authenticate returned : ' . print_r($context['result'], TRUE));
    if ($context['result']->getText() == 'CONTINUE') {
      // CONTINUE means more authentication is required. Reset the display items and DO let the user see the message to help them understand what is required. 
      $_SESSION['IDENTITY_DISPLAY_ITEMS'] = serialize($context['display_items']);
      $message = $context['result']->getMessage();
    } else if ($context['result']->getText() == 'RESET') {
      /*
       * RESET means the Identity service has no context for this authentication request. Either mistakenly authentication 
       * was called with no prior pre-authentication or the authentication context has expired. Expiration may happen if the user
       * does not login in the required amount of time. 
       */

      // reset everything as if you are starting again
      unset($_SESSION['IDENTITY_USERNAME_KEY']);
      unset($_SESSION['IDENTITY_DISPLAY_ITEMS']);
      $error = $context['result']->getMessage();
      header('Location: example.php');
    } else if ($context['result']->getText() == 'SUCCESS') {
      // SUCCESS means the user has successfully logged in. Unset the Identity session variables and do whatever your particular webapp does for a logged in user. 
      $_SESSION['authenticated'] = $_SESSION['IDENTITY_USERNAME_KEY'];
      unset($_SESSION['IDENTITY_USERNAME_KEY']);
      unset($_SESSION['IDENTITY_DISPLAY_ITEMS']);
      $message = $context['result']->getMessage();
    } else {
      // if an error occurred, do NOT show the user
      $error = $context['result']->getMessage();
    }
  }
  break;

default:
  error_log("unhandled method: $method");
  break;
}

function identity_get_service_provider($reset = FALSE) {
  static $provider;

  if ($reset) {
    unset($provider);
  } else {
    if (!isset($provider)) {
      $provider = new IdentityServiceProvider();
      $provider->getConnectionHandler()->setCAInfo('identity/truststore.pem');
      $provider->getConnectionHandler()->setSSLCert('example.pem');
      $provider->getConnectionHandler()->setSSLPassword('test');
    }
  }
  return $provider;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>ufp Identity | Guest Login</title>
    <link rel="stylesheet" type="text/css" href="https://www.ufp.com/identity/css/main.css" />
    <link rel="stylesheet" type="text/css" href="r.css" />
    <script src="https://www.ufp.com/login/scripts/jquery.min.js"></script>
    <script src="https://www.ufp.com/login/scripts/login.js"></script>				
  </head>
  <body>
	<div id="container">
    <div id="formOuter">
      <div id="callToActionLeft">
      <p><span style="color: #bebebe;">ufp</span><span style="color: #111;">Identity</span> is a revolutionary authentication system that provides login services for websites. The service is secure and flexible enough for banking/healthcare and government applications but cost-effective enough to be applicable to every site.</p> 
      </div>
      <div id="formRight">
        <p>Username: guest, Password: guest</p>
        <form id="login" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php /* We show a form input if the session variable 'IDENTITY_USERNAME_KEY' is empty or non-existent, else we show a static label */ ?>
          <?php if (!empty($_SESSION['IDENTITY_USERNAME_KEY'])) { ?>
          <div>
            <span class="static" id="login_username"><?php echo $_SESSION['IDENTITY_USERNAME_KEY']; ?></span>
          </div>
          <?php } else { ?>
          <div class="input">
            <input type="text" name="username" id="login_username" class="field required" title="Please provide a username" />
            <div class="description">Username</div>
          </div>
          <?php } ?>
<?php /* we iterate over the display items and render them, there are many ways to do this but somehow showing the display item nickname is very helpful for the user */ ?>
          <?php 
            if (!empty($_SESSION['IDENTITY_DISPLAY_ITEMS'])) { 
            $display_items = unserialize($_SESSION['IDENTITY_DISPLAY_ITEMS']);
            foreach ($display_items as $index => $display_item) {
          ?>
          <div>
            <?php echo $display_item->getFormElement(); ?>
            <div class="description"><?php echo $display_item->getDisplayName(); ?> - <?php echo $display_item->getNickName(); ?></div>
          </div>
          <?php } ?>
          <?php } ?>

          <div class="submit">
            <button type="submit" id="">Login <img src="https://www.ufp.com/identity/images/iconMore.png" style="vertical-align: middle;" /></button>
          </div>
        </form>
        
        <div id="message">
<?php /* We DO NOT want to show errors typically, FOR EXAMPLE ONLY */ ?>
          <?php if (isset($error)) { ?>
          <span id="identity_error"><?php echo $error; ?></span>
          <?php } ?>   

<?php /* We DO want to show messages typically */ ?>
          <?php if (isset($message)) { ?>
          <span id="identity_message"><?php echo $message; ?></span>
          <?php } ?>   
        </div>
               </div>
      </div>
      <div id="push"></div>
    </div>

    <div id="footer">
      <div id="innerFooter">
        <p>Copyright &copy; <span style="color: #bebebe;">ufp</span><span style="color: black;">Identity</span></p>
      </div>
    </div>
<?php /* used to set focus to the input the user must enter */ ?>
    <script type="text/javascript">
      <!--//--><![CDATA[//><!--
      if (document.forms['login'].elements['AuthParam0'])
        document.forms['login'].elements['AuthParam0'].focus();
      //--><!]]>
    </script>	
  </body>
</html>