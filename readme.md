# Identity4php

PHP library for interacting with the ufpIdentity authentication system. 

To get started, you must generate a Certificate Signing Request and send it to <info@ufp.com>. Please provide the name and phone number of someone we can contact. 

Creating a Certificate Signing Request with OpenSSL

1. Generate a secret key

       openssl rand 16 > secret.key

1. Generate a public/private key pair encrypting the private key with secret key from Step 1

       openssl genrsa -out magrathea.key.pem -passout file:secret.key -des3 1024

1. Generate a Certificate Signing Request

       openssl req -new -passin file:secret.key -key magrathea.key.pem -out magrathea.csr.pem

1. Send the generated Certificate Signing Request (magrathea.csr.pem) to <info@ufp.com>

1. To save off the secret key generated in step 1 we recommend base64 encoding it

      cat secret.key | openssl enc -e -a

When you receive the certificate, you can initialize the ufpIdentity service provider as follows (assuming the secret.key has been saved off

      $provider = new IdentityServiceProvider();
      $provider->getConnectionHandler()->setCAInfo('truststore.pem');
      $provider->getConnectionHandler()->setSSLCert('magrathea.crt.pem');
      $provider->getConnectionHandler()->setSSLKey('magrathea.key.pem');
      $provider->getConnectionHandler()->setSSLKeyPassword(base64_decode($base64_encoded_secret_key));






