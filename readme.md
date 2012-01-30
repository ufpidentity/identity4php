# Identity4php

**PHP library for interacting with the ufpIdentity authentication system.**

Read the Getting Started section of the ufpIdentity [Integration Document](http://www.ufp.com/identity/integration.html#getting_started)

When you receive a certificate, you can initialize the ufpIdentity
service provider as follows (assuming the secret key has been saved
off in base64 encoded format)

      $provider = new IdentityServiceProvider();
      $provider->getConnectionHandler()->setCAInfo('truststore.pem');
      $provider->getConnectionHandler()->setSSLCert('magrathea.crt.pem');
      $provider->getConnectionHandler()->setSSLKey('magrathea.key.pem');
      $provider->getConnectionHandler()->setSSLKeyPassword(base64_decode($base64_encoded_secret_key));
