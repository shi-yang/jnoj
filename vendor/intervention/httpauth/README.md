# Intervention Httpauth Class

Library to manage HTTP authentication with PHP. Includes ServiceProviders for easy Laravel integration.

## Installation

The easiest way to install this package is via [Composer](https://getcomposer.org/).

Run Composer to install the package.

    $ composer require intervention/httpauth

The Httpauth class is built to work with the Laravel Framework. The integration is done in seconds.

Open your Laravel config file `config/app.php` and add the following lines.

In the `$providers` array add the service providers for this package.
    
    'providers' => array(

        ...

        Intervention\Httpauth\HttpauthServiceProvider::class

    ),
    

Add the facade of this package to the `$aliases` array.

    'aliases' => array(

        ...

        'Httpauth' => Intervention\Httpauth\Facades\Httpauth::class

    ),


## Usage

* Httpauth::__construct - Create new instance of Httpauth class
* Httpauth::make - Creates new instance of Httpaccess with given config parameters
* Httpauth::secure - Denies access for not-authenticated users

### Configuration

By default the authentication settings are fetched from `config/httpauth.php`. Please make sure to set your own options. 

If you are using Laravel 4, you can extract a configuration file to your app by running the following command:

    $ php artisan config:publish intervention/httpauth

After you published the configuration file for the package you can edit the local configuration file `app/config/packages/intervention/httpauth/httpauth.php`.

Here's a short explanation of the configuration directives.

**type** _string_

    Set the authentication type. Choose between `basic` and `digest` for a more secure type.

**realm** _string_

    The name of the secure resource.

**username** _string_

    The name the user has to enter to login

**password** _string_

    Login password

### Code example

```php
// create a new instance of Httpauth and call secure method
$auth = new Intervention\Httpauth\Httpauth;
$auth->secure();

// You can change the user authentication settings in the config files
// or change it at runtime like this
$config = array('username' => 'admin', 'password' => '1234');
$auth = new Intervention\Httpauth\Httpauth($config);
$auth->secure();
```


### Code example (Laravel)

```php
// the most simple way to secure a url is to call the secure method from a route
Httpauth::secure();

// You can change the user authentication settings in the config files
// or change it at runtime like this
$config = array('username' => 'admin', 'password' => '1234');
Httpauth::make($config)->secure();
```

## License

Intervention Httpauth Class is licensed under the [MIT License](http://opensource.org/licenses/MIT).
