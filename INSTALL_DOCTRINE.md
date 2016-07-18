# Doctrine Installation

If you are using Doctrine, installation is really a piece of cake.  First:

    composer require "saeven/zf3-circlical-user"
     
This will install the library and all required parts.
     
### Configuration

 - Copy `/vendor/saeven/zf3-circlical-user/config/circlical.user/local.php.dist` into your global autoload folder, remove
the dist extension so that Zend Framework picks it up
 - Substitute the 'provider/user' config key, with your own User entity
 - Make your User entity implement [UserInterface](src/CirclicalUser/Provider/UserInterface)
 - Add CirclicalUser to your `application.config.php` (effectively, loading the module)
 - Run Doctrine's CLI schema update command, and execute the changes `php public/index.php orm:schema-tool:update --dump-sql`
 
The library is now accessible!
 
#### Recommended Changes

For production use, you should change the crypto_key configuration element to be unique.  This is generated using Halite.
Like so:

    KeyFactory::generateEncryptionKey()->getRawKeyMaterial()

The crypto_key, is a base64 of this value.

    base64_encode( KeyFactory::generateEncryptionKey()->getRawKeyMaterial() );
    
*TODO: Provide a CLI command to generate custom keys*





     
     
