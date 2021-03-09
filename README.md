
# Laravel Entity Services
[![PHP Unit](https://github.com/Saritasa/php-laravel-entity-services/workflows/PHP%20Unit/badge.svg)](https://github.com/Saritasa/php-laravel-entity-services/actions)
[![PHP CodeSniffer](https://github.com/Saritasa/php-laravel-entity-services/workflows/PHP%20Codesniffer/badge.svg)](https://github.com/Saritasa/php-laravel-entity-services/actions)
[![codecov](https://codecov.io/gh/Saritasa/php-laravel-entity-services/branch/master/graph/badge.svg)](https://codecov.io/gh/Saritasa/php-laravel-entity-services)
[![PHPv](https://img.shields.io/packagist/php-v/saritasa/laravel-entity-services.svg)](http://www.php.net)
[![Downloads](https://img.shields.io/packagist/dt/saritasa/laravel-entity-services.svg)](https://packagist.org/packages/saritasa/laravel-entity-services)

## Library for fast build laravel based application with simple CRUD operations.
As Repositories layer uses [laravel-repositories](https://github.com/Saritasa/php-laravel-repositories) library.

## Laravel 5.5/6.0
 Install the ```saritasa/laravel-entity-services``` package:

```
bash $ composer require saritasa/laravel-entity-services ```  
## Usage    
### Get service for model: 
 ```php    
 $entityServiceFactory = app(IEntityServiceFactory::class);  
 $entityService = $entityServiceFactory->build(User::class);
 ``` 
 *Note: if entity class not exists, EntityServiceException will be thrown
## Configuration
### Publish file
To publish configuration file you can run next command:  
```bash  
php artisan vendor:publish --tag=laravel_entity_services
```
It will copy file laravel_entity_services.php in config directory.
### Register custom entity service implementation
To register your own IEntityService implementation you can put it into configuration file, like:
```php
return [
 'bindings' => [\App\Models\User::class => \App\EntityServices\UserEntityService::class,],];
```
NOTE: Just remember that default IEntityServiceFactory implementation can work only with classes extended from EntityService. If you want change this behavior you should add your own implementation.  
### Available operations: 
#### Create:
```php
 $createdModel = $entityService->create($params); 
 ```
 #### Update:  
 ```php $entityService->update($model, $params); ```
 #### Delete:  
 ```php $entityService->delete($model); ```
 ### Custom service for entity:  
If you need use custom service for some entity, you can register it in factory using `register` method.  

**Example**:
```php
 $entityServiceFactory = app(IEntityServiceFactory::class);
 $entityService = $entityServiceFactory->register(User::class, YourServiceRealization::class);
 ```
*Note: Your realization must be extend EntityService class*  
### Events
EntityCreatedEvent - Throws when entity is created.  
EntityUpdatedEvent - Throws when entity is updated.  
EntityDeletedEvent - Throws when entity is deleted.  
    
## Contributing    
 1. Create fork, checkout it    
2. Develop locally as usual. **Code must follow [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)** -    
    run [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) to ensure, that code follows style guides    
3. **Cover added functionality with unit tests** and run [PHPUnit](https://phpunit.de/) to make sure, that all tests pass    
4. Update [README.md](README.md) to describe new or changed functionality    
5. Add changes description to [CHANGES.md](CHANGES.md) file. Use [Semantic Versioning](https://semver.org/) convention to determine next version number.    
6. When ready, create pull request    

### Make shortcuts
 If you have [GNU Make](https://www.gnu.org/software/make/) installed, you can use following shortcuts:    
    
* ```make cs``` (instead of ```php vendor/bin/phpcs```) -    
    run static code analysis with [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)    
    to check code style    
* ```make csfix``` (instead of ```php vendor/bin/phpcbf```) -    
    fix code style violations with [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)    
    automatically, where possible (ex. PSR-2 code formatting violations)    
* ```make test``` (instead of ```php vendor/bin/phpunit```) -    
    run tests with [PHPUnit](https://phpunit.de/)    
* ```make install``` - instead of ```composer install``` * ```make all``` or just ```make``` without parameters -    
    invokes described above **install**, **cs**, **test** tasks sequentially -    
    project will be assembled, checked with linter and tested with one single command    
 
## Resources
 * [Bug Tracker](http://github.com/saritasa/php-laravel-entity-services/issues)
* [Code](http://github.com/saritasa/php-laravel-entity-services)
* [Changes History](CHANGES.md)
* [Authors](http://github.com/saritasa/php-laravel-entity-services/contributors)
