Slim Doctrine ORM Middleware
============================

Slim Middleware to make integrating Doctrine ORM easy


Setup
-----

First of all, add the 'doctrine' config to the Slim application...

    use CubicMushroom\Slim\Middleware\DoctrineORMMiddleware;
    
    $config = [
        'doctrine' => [
            'driver' => 'pdo_mysql'
            'host' => 'localhost'
            'user' => 'dbUser'
            'password' => 'dbPassword'
            'port' => '3306'
            'dbname' => 'dbName'
        ],
    ];
    
    $app = new \Slim\Slim($config);
    
    // ...

The Middleware will use the config details from the app 'doctrine' config, unless you specify an alternative key in the 
options array, so all you then need to do is add the Middleware...

    // ...
    
    $options = [
       'annotationMetadataPaths' => ['path/to/entities']
    ];
   
    $app->add(new DoctrineORMMiddleware($options));
    
    // ...

The only required option is the `annotationMetadataPaths` key which should contain an array of paths to check for entity
class annotations.


Options
-------

Below are lists all the supported options you can pass to the constructor.

Each of the options is defined as a class constant on the DoctrineORMMiddleware, so you could use these when passing the 
options array in, to protect against any future changes.

### settingsKey (optional)

Default: doctrine

The key within the app config settings that the d/b connection details can be found


### serviceName (optional)

Default: @entity_manager 

The service name used for the entity manager service


### annotationMetadataPaths (required)

An array of paths to check for entity annotations


### annotationsAutoloaders (optional)

Default: array()

An array of class autoloaders to use when loading annotation classes.

Will be passed to Doctrine's \\Doctrine\\Common\\Annotations\\AnnotationRegistry::registerLoader() method when setting 
up service, to support loading of annotation classes.


### annotationsFiles (optional)

Default: array()

An array of files to load when attempting to load annotation classes.

Will be passed to Doctrine's \\Doctrine\\Common\\Annotations\\AnnotationRegistry::registerFile() method when setting 
up service, to support loading of annotation classes.


### annotationNamespaces (optional)

Default: array()

An array of mappings of namespaces to directory paths to use when loading annotation classes.

Will be passed to Doctrine's \\Doctrine\\Common\\Annotations\\AnnotationRegistry::registerAutoloadNamespace() method 
when setting up service, to support loading of annotation classes.