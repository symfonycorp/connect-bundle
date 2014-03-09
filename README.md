SensioLabsConnectBundle
=======================

About
-----

This is the official bundle of the [SensioLabs Connect SDK](https://github.com/sensiolabs/connect).

Installation
------------

### Step 1: Install SensioLabsConnectBundle using [Composer](http://getcomposer.org)

Add SensioLabsConnectBundle in your `composer.json`:

    {
        "require": {
            "sensiolabs/connect-bundle": "~2.0"
        }
    }

Now tell composer to download the bundle by running the command:

    $ php composer.phar update sensiolabs/connect-bundle

### Step 2: Enable the bundle

Enable the bundle in the kernel:

    <?php

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new SensioLabs\Bundle\ConnectBundle\SensioLabsConnectBundle(),
            // ...
        );
    }

### Step 3: Configure your `config.yml` file

    # app/config/config.yml
    sensio_labs_connect:
        app_id:     Your app id
        app_secret: Your app secret
        scope:      Your app scope # SCOPE_EMAIL SCOPE_PUBLIC

Usage
-----

### Use SensioLabsConnect to authenticated your user

#### Step 1: Configure the security

If you don't want to persist your users, you can use `ConnectInMemoryUserProvider`:

    # app/config/security.yml
    security:
        providers:
            sensiolabs_connect:
                connect_memory: ~
        firewalls:
            dev: { pattern:  "^/(_(profiler|wdt)|css|images|js)/",  security: false }
            secured_area:
                pattern:    ^/
                sensiolabs_connect:
                    check_path: oauth_callback
                    login_path: sensiolabs_connect_new_session
                    remember_me: false
                    provider: sensiolabs_connect
                anonymous: true

You can also load specific roles for some users:

    # app/config/security.yml
    security:
        providers:
            sensiolabs_connect:
                connect_memory:
                    users:
                        90f28e69-9ce9-4a42-8b0e-e8c7fcc27713: "ROLE_CONNECT_USER ROLE_ADMIN"

**Note:** The `username` is the user uuid.

#### Step 2: Configure the routing

Import the default routing

    # app/config/routing.yml
    _sensiolabs_connect:
        resource: "@SensioLabsConnectBundle/Resources/config/routing.xml"

#### Step 3: Add some link to your templates:

You can generate a link to the SensioLabs Connect login page:

    <a href="{{ url('sensiolabs_connect_new_session') }}">Connect</a>

You can also specify the target URL after connection:

    <a href="{{ url('sensiolabs_connect_new_session') }}?target=XXX">Connect</a>

#### Step 4: Play with the user:

The API user is available through the security token:

    $user = $this->container->get('security.context')->getToken()->getApiUser();

You can also get access to the API root object:

    $accessToken = $this->container->get('security.context')->getToken()->getAccessToken();

    $api = $this->get('sensiolabs_connect.api');
    $api->setAccessToken($accessToken);

    $root = $api->getRoot();
    $user = $root->getCurrentUser();

If you use the built-in security component, you can access to the root api
directly:

    $api = $this->get('sensiolabs_connect.api');
    $user = $api->getRoot()->getCurrentUser();

License
-------

This bundle is licensed under the MIT license.
