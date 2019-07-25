symfony/connect-bundle
======================

About
-----

This is the official bundle of the [SymfonyConnect SDK](https://github.com/symfonycorp/connect).

Installation
------------

### Step 1: Install symfony/connect-bundle using [Composer](http://getcomposer.org)

```bash
$ composer require symfonycorp/connect-bundle
```

If you're not using Symfony Flex, please take inspiration from
[this bundle's recipe](//github.com/symfony/recipes-contrib/blob/master/symfony/connect-bundle/)
to enable it.

### Step 2: Configure your `.env.local` file

```sh
SYMFONY_CONNECT_APP_ID='Your app id'
SYMFONY_CONNECT_APP_SECRET='Your app secret'
```

Usage
-----

### Use SymfonyConnect to authenticate your user

#### Step 1: Configure the security

> **Note:** If you want to persist your users, read the *Cookbooks* section.

If you don't want to persist your users, you can use `ConnectInMemoryUserProvider`:

```yaml
# config/packages/security.yaml
security:
    providers:
        symfony_connect:
            connect_memory: ~
    firewalls:
        # [...]

        secured_area:
            pattern: ^/
            symfony_connect:
                check_path: symfony_connect_callback
                login_path: symfony_connect_login
                failure_path: home # need to be adapted to your config, see step 4
                remember_me: false
                provider: symfony_connect
            anonymous: true
```

You can also load specific roles for some users:

```yaml
# config/packages/security.yaml
security:
    providers:
        symfony_connect:
            connect_memory:
                users:
                    90f28e69-9ce9-4a42-8b0e-e8c7fcc27713: "ROLE_CONNECT_USER ROLE_ADMIN"
```

**Note:** The `username` is the user uuid.

#### Step 2: Add some link to your templates

You can generate a link to the SymfonyConnect login page:

```twig
<a href="{{ url('symfony_connect_login') }}">Connect</a>
```

You can also specify the target URL after connection:

```twig
<a href="{{ url('symfony_connect_login') }}?target=XXX">Connect</a>
```

#### Step 3: Play with the user

The API user is available through the token storage, which you can get by autowiring
`Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage`.

```php
$user = $tokenStorage->getToken()->getApiUser();
```

If you use the built-in security component, you can access to the root api
directly by autowiring `SymfonyCorp\Connect\Api\Api $api`:

```
$user = $api->getRoot()->getCurrentUser();
```

You can also get access to the API root object by providing an access token explicitly:

```php
$accessToken = $tokenStorage->getToken()->getAccessToken();
$api->setAccessToken($accessToken);
$root = $api->getRoot();
$user = $root->getCurrentUser();
```

#### Step 4: Handling Failures

Several errors can occur during the OAuth dance, for example the user can
deny your application or the scope you defined in `symfony_connect.yaml` can be different
from what you selected while creating your application on SymfonyConnect.
Theses failures arehandled by the default Symfony failure handling.

Therefore, if an error occurred, the error is stored in the session (with a
fallback on query attributes) and the user is redirected to the route/path
specificed in `failure_path` node of the `symfony_connect` section of your
firewall in `security.yaml`.

> **Warning**: You **need** to specifiy `failure_path`. If you don't, the user
> will be redirected back to `login_path`, meaning that will launch the
> SymfonyConnect authentication and redirect the user to SymfonyConnect
> which can lead to a redirection loop.

This means you need to fetch the authentication error if there is one and display
it in the view. This is similar to what you do for a typical login form on
Symfony (here we assume you have a `home` route pointing to the following controller):

```php
// src/Controller/HomeController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(Request $request)
    {
        $session = $request->hasSession() ? $request->getSession() : null;

        // get the authentication error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        return $this->render('home.html.twig', ['error' => $error]);
    }
}
```

And then adapt your twig template:

```twig
{# templates/home.html.twig #}

{% if app.user %}
    Congrats! You are authenticated with SymfonyConnect
{% elseif error %}
    {{ error.messageKey | trans(error.messageData, 'security') }}
{% else %}
    <a href="{{ url('symfony_connect_login') }}">Log in with SymfonyConnect</a>
{% endif %}
```

Cookbooks
---------

### How to persist users

#### Step 1 - Create a `User` entity

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use SymfonyCorp\Connect\Api\Entity\User as ConnectApiUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
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
        return ['ROLE_USER'];
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
```

#### Step 2 - Create the repository

```php
<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository extends ServiceEntityRepository implements UserProviderInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername($uuid)
    {
        return $this->findOneByUuid($uuid) ?: new User($uuid);
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
        return User::class === $class;
    }
}
```

Don't forget to update your database.

#### Step 3 - Create the event listener

```php
<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use SymfonyCorp\Connect\Security\Authentication\Token\ConnectToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SecurityInteractiveLoginListener implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function registerUser(InteractiveLoginEvent $event)
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

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'registerUser',
        ];
    }
}
```

#### Step 4 - Configure security

```yaml
# config/packages/security.yaml
security:
    encoders:
        App\Entity\User: plaintext

    providers:
        symfony_connect:
            id: App\Repository\UserRepository
```

#### Step 5 - Enjoy

You can store more things if you want. But don't forget to update your
application scope.

License
-------

This bundle is licensed under the MIT license.
