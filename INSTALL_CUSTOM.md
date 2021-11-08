# Custom Installation

Installation is simple:

    composer require "saeven/zf3-circlical-user"
     
This will install the library and all required parts.

### Create Your Providers
     
You'll need to roll three providers of your own:

* A Role provider that implements [RoleProviderInterface](src/CirclicalUser/Provider/RoleProviderInterface.php). See [RoleMapper](src/CirclicalUser/Mapper/RoleMapper.php) for a sample implementation.
* A Group Permission provider that implements [GroupPermissionProviderInterface](src/CirclicalUser/Provider/GroupPermissionProviderInterface.php). See [GroupPermissionMapper](src/CirclicalUser/Mapper/GroupPermissionMapper.php) for a sample implementation.
* A User Permission provider that implements [UserPermissionProviderInterface](src/CirclicalUser/Provider/UserPermissionProviderInterface.php). See [UserPermissionMapper](src/CirclicalUser/Mapper/GroupPermissionMapper.php) for a sample implementation.

These will get plugged into the library using existing factories.  Don't forget to create factories of your own, as required.

     
### Configuration

 - Copy `/vendor/saeven/zf3-circlical-user/config/circlical.user/local.php.dist` into your global autoload folder, remove
the dist extension so that Zend Framework picks it up
 - Substitute the 'providers/user' config key, with your own User entity
 - Make your User entity implement [UserInterface](src/CirclicalUser/Provider/UserInterface.php)
 - Change 'providers/role' to be the class name of your Role provider
 - Change 'providers/rules/group' and 'providers/rules/user' to the class names of your Group and User Permission providers, respectively.
 - Add CirclicalUser to your `application.config.php` (effectively, loading the module)
 
The library is now connected, and should work.


 
#### Recommended Changes

For production use, you should change the crypto_key configuration element to be unique.  This is generated using Halite.
Like so:

    KeyFactory::generateEncryptionKey()->getRawKeyMaterial()

The crypto_key, is a base64 of this value.

    base64_encode( KeyFactory::generateEncryptionKey()->getRawKeyMaterial() );
    
*TODO: Provide a CLI command to generate custom keys*





     
     
