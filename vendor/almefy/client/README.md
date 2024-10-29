# Almefy PHP Client

A simple dependency-free PHP wrapper for the Almefy API.

## Quick Guide

Below is a quick guide how to integrate Almefy in your PHP project to test it easily. We are already
preparing a comprehensive documentation covering all possible use cases and parameters. So stay tuned.

_Please notice: this document is work in progress._

### Prerequisites

To successfully initialize the PHP client, you'll need an API key and API secret from Almefy.

```ini
# Example .env Data

ALMEFY_KEY=...
ALMEFY_SECRET=...
```

### Installation

**Almefy PHP Client** is available on Packagist as the [almefy/client](http://packagist.org/packages/almefy/client)
package. Run `composer require almefy/client` from the root of your project in terminal, and you are done. If you
cannot use `composer` for any reasons you can download the [latest version](https://github.com/almefy/almefy-php-client/releases)
from GitHub. The minimum PHP version currently supported is 8.0.

### Client Initialization

Once you have the package installed, you will need to instantiate a Client object. This example assumes
that you store the secrets in some environment variables:

```php
$client = new \Almefy\Client($_ENV['ALMEFY_KEY'], $_ENV['ALMEFY_SECRET']);
```

### Identity Enrollment

Before a user account can be used with the Almefy app, it needs to be enrolled and the device provisioned. The easiest
way to enroll an account with Almefy is to send the user an email with an enrollment QR Code inside. A good starting point
could be a "Connect with Almefy" button somewhere inside the protected area, which triggers the following process in the
backend:

```php
try {
    $enrollmentToken = $client->enrollIdentity('john.doe');
    
} catch (\Almefy\Exception\TransportException $e) {
    echo 'Could not connect to Almefy service: '.$e->getMessage();
}
```

The returned `$enrollmentToken` object provides a public `base64ImageData` property with the base64 encoded image data
that can be used in any HTML email.

If enabled, you can also use the Almefy API to send a generic enrollment email without the effort to build a custom
email-client compatible template:

```php
try {
    $client->enrollIdentity('john.doe', array(
        'sendEmail' => true
        'sendEmailTo' => 'john.doe@example.com'
    ));
    
} catch (\Almefy\Exception\TransportException $e) {
    echo 'Could not connect to Almefy service: '.$e->getMessage();
}
```
_Notice: Check out the [API Enrollment Reference](https://docs.almefy.com/api/reference.html#enroll-identity) for all available options._

This process creates or uses an existing identity and sends out an enrollment email with a QR Code inside, that needs to
be scanned with the Almefy app to provision it. Once done, the enrollment is completed, and the user is ready to
authenticate using the Almefy app.

### Frontend

Add the following few lines to your HTML frontend to show the Almefy image used for authentication.

```html
<!-- Place this HTML code wherever the Almefy image should appear -->
<div data-almefy-auth
     data-almefy-key="5bbf4923faf099a3515a40d9b0e6e6e32c890ef6cd7a8a120657c2f49d2341fe"
     data-almefy-auth-url="/path/to/auth-controller"></div>

<!-- Load the JavaScript library -->
<script async src="https://cdn.almefy.com/js/almefy-0.9.8.js"
        integrity="sha384-YrIFSeu+BqWh1wivbt+Q90LfEPlPMvlrel3UTRT2FWTc8P1HauLvZNQcoRBzCMpo"
        crossorigin="anonymous"></script>
```

### Authentication

The authentication controller configured in the `data-alemfy-auth-url` will receive the `X-Almefy-Authorization` header
from the Almefy API. The first thing needs to be done inside the controller is extracting the token from the header and
decode it (it is also verified).

```php
// Get the JWT from header
$jwt = $request->headers->get('X-Almefy-Authorization');

// decode it
$token = $client->decodeJwt($jwt);
```
_Notice: The JWT is provided using the `X-Almefy-Authorization` header for compatibility reasons (e.g. Apache webserver
not forwarding it to the PHP process), but also using the standard `Authorization: Bearer ...` header._

Next you should check if the user trying to authenticate is in your own identity management system and allowed to
authenticate. This is your own business logic, thus just as an example:

```php
// $userRepository is any PHP class used for database queries 
$user = $userRepository->retrieveByUsername($token->getIdentifier());

// Check any internal business logic
if (!$user->isEnabled() || !$user->isSubscriptionActive()) {
    return false;
}
```

The last and important step is to verify and confirm the authentication attempt on the Almefy system:

```php
if (!$client->authenticate($token)) {
    // Authentication attempt could not be verified or is invalid
    return false;
}
```

_Notice: For security reasons any network or server error will always return false._

At this stage you can authenticate the user by setting up session etc. and redirect him to the protected area.

### License
The Almefy PHP SDK is licensed under the Apache License, version 2.0.