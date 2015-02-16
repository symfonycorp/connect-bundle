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
            "sensiolabs/connect-bundle": "~4.2"
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

Note: If you want to persist your users, see cookbooks.

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
                    failure_path: homepage # need to be adapted to your config, see step 5
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

#### Step 5: Handling Failures

> **Note**: this feature requires `sensiolabs/connect` `v3.0.0`

Several errors can occurred during the OAuth dance, for example the user can
deny your application or the scope you defined in `config.yml` can be different
from what you selected while creating your application on SensioLabsConnect.
Theses failures need to be handled.

Since `sensiolabs/connect` `v3.0.0`, failures handling is restored to the default
Symfony failure handling.

Therefore, if an error occurred, the error is stored in the session (with a
fallback on query attributes) and the user is redirected to the route/path
specificed in `failure_path` node of the `sensiolabs_connect` section of your
firewall in `security.yml`.

> **Warning**: You **need** to specifiy `failure_path`. If you don't, the user
> will be redirected back to `login_path`, meaning that will launch the
> SensioLabsConnect authentication and redirect the user to SensioLabsConnect
> which can lead to a redirection loop.

This means you need to fetch the authencation error if there is one and display
it in the view. This is similar to what you do for a typical login form on
Symfony2 (here we assume you have a `homepage` routing pointing to the
`AcmeWebsiteBundle:Default:homepage` controller):

```php
// src/Acme/Bundle/WebsiteBundle/Controller/DefaultController.php

namespace Acme\WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class DefaultController extends Controller
{
    public function homepageAction(Request $request)
    {
        $session = $request->getSession();

        // get the authentication error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        return $this->render(
            'AcmeWebsiteBundleBundle:Default:homepage.html.twig',
            array(
                'error' => $error,
            )
        );
    }
}
```

And then adapt your twig template:

```twig
{# src/Acme/Bundle/WebsiteBundle/Resources/views/Default/homepage.html.twig #}

{% if app.user %}
    Congrats! You are authenticated with SensioLabsConnect
{% elseif error %}
    {{ error.messageKey | trans(error.messageData, 'security') }}
{% else %}
    <a href="{{ url('sensiolabs_connect_new_session') }}">Connect with SensioLabsConnect</a>
{% endif %}
```

Cookbooks
---------

### How to persist users

#### Step 1 - Create a `User` entity:

    <?php

    namespace Sensiolabs\Bundle\HowToBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use SensioLabs\Connect\Api\Entity\User as ConnectApiUser;
    use Symfony\Component\Security\Core\User\UserInterface;

    /**
     * @ORM\Table(name="sl_user")
     * @ORM\Entity(repositoryClass="Sensiolabs\Bundle\HowToBundle\Repository\UserRepository")
     */
    class User implements UserInterface
    {
        /** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue(strategy="AUTO") */
        private $id;

        /** @ORM\Column(type="string", length=255) */
        private $uuid;

        /** @ORM\Column(type="string", length=255) */
        private $username;

        /** @ORM\Column(type="string", length=255) */
        private $name;

        public function __construct($uuid)
        {
            $this->uuid = $uuid;
        }

        public function updateFromConnect(ConnectApiUser $apiUser)
        {
            $this->username = $apiUser->getUsername();
            $this->name = $apiUser->getName();
        }

        public function getUuid()
        {
            return $this->uuid;
        }

        public function getUsername()
        {
            return $this->username;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getRoles()
        {
            return array('ROLE_USER');
        }

        public function getPassword()
        {
        }

        public function getSalt()
        {
        }

        public function eraseCredentials()
        {
        }
    }


#### Step 2 - Create the repository

    <?php

    namespace Sensiolabs\Bundle\HowToBundle\Repository;

    use Doctrine\ORM\EntityRepository;
    use Sensiolabs\Bundle\HowToBundle\Entity\User;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;

    class UserRepository extends EntityRepository implements UserProviderInterface
    {
        public function loadUserByUsername($uuid)
        {
            $user = $this->findOneByUuid($uuid);

            if (!$user) {
                $user = new User($uuid);
            }

            return $user;
        }

        public function refreshUser(UserInterface $user)
        {
            if (!$user instanceof User) {
                throw new UnsupportedUserException(sprintf('class %s is not supported', get_class($user)));
            }

            return $this->loadUserByUsername($user->getUuid());
        }

        public function supportsClass($class)
        {
            return 'Sensiolabs\Bundle\HowToBundle\Entity\User' === $class;
        }
    }

Don't forget to update your database.

#### Step 3 - Create the event listener

    <?php

    namespace Sensiolabs\Bundle\HowToBundle\EventListener;

    use Doctrine\ORM\EntityManager;
    use SensioLabs\Connect\Security\Authentication\Token\ConnectToken;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
    use Symfony\Component\Security\Http\SecurityEvents;

    class SecurityInteractiveLoginListener implements EventSubscriberInterface
    {
        private $em;

        public static function getSubscribedEvents()
        {
            return array(
                SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            );
        }

        public function __construct(EntityManager $em)
        {
            $this->em = $em;
        }

        public function onInteractiveLogin(InteractiveLoginEvent $event)
        {
            $token = $event->getAuthenticationToken();

            if (!$token instanceof ConnectToken) {
                return;
            }

            $user = $token->getUser();
            $user->updateFromConnect($token->getApiUser());

            $this->em->persist($user);
            $this->em->flush($user);
        }
    }

#### Step 4 - Wire everything


##### Step 4.1 - Add new services:

    <?xml version="1.0" ?>

    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

        <services>
            <service id="sensiolabs_howto.repository.user" class="Sensiolabs\Bundle\HowToBundle\Repository\UserRepository" factory-service="doctrine" factory-method="getRepository">
                <argument>SensiolabsHowToBundle:User</argument>
            </service>
            <service id="sensiolabs_howto.event_listener.interactive_login" class="Sensiolabs\Bundle\HowToBundle\EventListener\SecurityInteractiveLoginListener">
                <tag name="kernel.event_subscriber" />
                <argument type="service" id="doctrine.orm.entity_manager" />
            </service>
        </services>
    </container>

##### Step 4.2 - Configure security:

    security:
        encoders:
            Sensiolabs\Bundle\HowToBundle\Entity\User: plaintext

        providers:
            sensiolabs_connect:
                id: sensiolabs_howto.repository.user


#### Step 5 - Enjoy

You can store more thing if you want. But don't forget to update your
application scope.

License
-------

This bundle is licensed under the MIT license.
