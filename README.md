[Elastica](https://github.com/ruflin/Elastica) integration in Symfony2

## Installation

### Install elasticsearch

http://www.elasticsearch.org/guide/reference/setup/installation.html

### Install Elastica

#### Download

With submodule:
    git submodule add git://github.com/ruflin/Elastica vendor/elastica

With clone:
    git clone git://github.com/ruflin/Elastica vendor/elastica

#### Register autoloading

    // app/autoload.php

    $loader->registerPrefixes(array(
        ...
        'elastica' => __DIR__.'/../vendor/elastica/lib',
    ));

### Install ElasticaBundle

#### Download

With submodule:
    git submodule add git://github.com/Exercise/ElasticaBundle src/Exercise/ElasticaBundle

With clone:
    git clone git://github.com/Exercise/ElasticaBundle src/Exercise/ElasticaBundle

#### Register autoloading

    // app/autoload.php

    $loader->registerNamespaces(array(
        ...
        'FOQ' => __DIR__.'/../src',
    ));

#### Register the bundle

    // app/AppKernel.php

    public function registerBundles()
    {
        return array(
            // ...
            new FOQ\ElasticaBundle\FOQElasticaBundle(),
            // ...
        );
    }
