# Changes History

1.2.1
-----
- Removed method `validate` from IEntityService contract
+ Added getting validation rules from entity service instead repository
+ Rename EntityServiceBindingException to EntityServiceRegisterException
+ Fixed description typos
+ Service factory now have less dependencies and leave resolving it on
DI-container side
+ EntityService lost EntityServiceFactory dependency
+ EntityServiceRegisterException now will be thrown immediately when
attempt to register custom entity service with wrong parameters
+ Increase unit tests coverage

1.1.1
-----
Switched to Dingo/Api 2.0 beta (which contains bugfix in authentication)

1.1.0
-----
+ Add package configuration file.
+ Add ability to register custom entity services implementation using configuration file.
+ Make implementation of IEntityServiceFactory in DI container as singleton by default.
+ Improve default provider documentation.
+ Fix bug with call undefined property in EntityServiceFactory.

1.0.0
-----
Initial version
