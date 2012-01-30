# Identity4php

PHP library for interacting with the ufpIdentity authentication system. 

To get started, you must generate a Certificate Signing Request and send it to <info@ufp.com>. Please provide the name and phone number of someone we can contact. 

Creating a Certificate Signing Request with OpenSSL

1. Generate a secret key

        openssl rand 16 > secret.key

1. Generate a public/private key pair encrypting the private key with secret key from Step 1

        openssl genrsa -out magrathea.key.pem -passout file:secret.key -des3 1024

1. Generate a Certificate Signing Request

You will be asked for location infomration. Please enter the information carefully.

        openssl req -new -passin file:secret.key -key magrathea.key.pem -out magrathea.csr.pem

   > The Common Name must be specified and unique. The common name is used to group logins for your site and is generally either a site name, a domain name or an email address. For example, for a development site on your own machine, an email (info@company.com) would be a desirable common name so you could apply all your logins to all your development sites. For distinct sites a fully qualified site name e.g. www.company.com would be appropriate for grouping all the logins for that site. If you wanted multiple sites to share logins, you could use the domain as a common name e.g. company.com. Please contact us if you have questions and we can help you decide the best fit for your needs.

1. Send the generated Certificate Signing Request (magrathea.csr.pem) to <info@ufp.com>

1. To save off the secret key generated in step 1 we recommend base64 encoding it

        cat secret.key | openssl enc -e -a

When you receive the certificate, you can initialize the ufpIdentity service provider as follows (assuming the secret.key has been saved off

      $provider = new IdentityServiceProvider();
      $provider->getConnectionHandler()->setCAInfo('truststore.pem');
      $provider->getConnectionHandler()->setSSLCert('magrathea.crt.pem');
      $provider->getConnectionHandler()->setSSLKey('magrathea.key.pem');
      $provider->getConnectionHandler()->setSSLKeyPassword(base64_decode($base64_encoded_secret_key));






