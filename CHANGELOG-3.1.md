CHANGELOG for 3.1.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 3.1 versions.

To get the diff for a specific change, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/commit/XXX where XXX is
the commit hash. To get the diff between two versions, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/compare/v3.0.4...v3.1.0

* 3.1.8 (2016-01-07)
 * Removes store:true from mappings unless specifically defined

* 3.1.7 (2015-12-08)
 * Allow Elastica 2.3

* 3.1.6 (2015-09-17)

 * Allow Elastica 2.2
 * Add PHP 7 compatibility
 * Fix: Don't use parent identifier for objects/nested documents

* 3.1.5 (2015-08-05)

 * Allow Elastica 2.1
 * Remove usage of deprecated APIs to avoid deprecation warnings in Symfony 2.7

* 3.1.4 (2015-05-28)

 * Bump Elastica constraint to 2.0

* 3.1.3 (2015-04-02)

 * Fix Symfony 2.3 compatibility

* 3.1.2 (2015-03-27)

 * Fix the previous release

* 3.1.1 (2015-03-27)

 * Fix PopulateCommand trying to set formats for ProgressBar in Symfony < 2.5
 * Fix Provider implementations that depend on a batch size from going into 
   infinite loops

* 3.1.0 (2015-03-18)

 * BC BREAK: `Doctrine\Listener#scheduleForDeletion` access changed to private.
 * BC BREAK: `ObjectPersisterInterface` gains the method `handlesObject` that
   returns a boolean value if it will handle a given object or not.
 * BC BREAK: Removed `Doctrine\Listener#getSubscribedEvents`. The container
   configuration now configures tags with the methods to call to avoid loading
   this class on every request where doctrine is active. #729
 * BC BREAK: Added methods for retrieving aggregations when paginating results.
   The `PaginationAdapterInterface` has a new method, `getAggregations`. #726
 * Added ability to configure `date_detection`, `numeric_detection` and
   `dynamic_date_formats` for types. #753
 * New event `POST_TRANSFORM` which allows developers to add custom properties to
   Elastica Documents for indexing.
 * When available, the `fos:elastica:populate` command will now use the 
   ProgressBar helper instead of outputting strings. You can use verbosity
   controls on the command to output additional information like memory 
   usage, runtime and estimated time.
 * Added new option `property_path` to a type property definition to allow 
   customisation of the property path used to retrieve data from objects. 
   Setting `property_path` to `false` will configure the Transformer to ignore
   that property while transforming. Combined with the above POST_TRANSFORM event
   developers can now create calculated dynamic properties on Elastica documents
   for indexing. #794
 * Fixed a case where ProgressCommand would always ignore errors regardless of
   --ignore-errors being passed or not.
 * Added a `SliceFetcher` abstraction for Doctrine providers that get more
   information about the previous slice allowing for optimising queries during
   population. #725
 * New events `PRE_INDEX_POPULATE`, `POST_INDEX_POPULATE`, `PRE_TYPE_POPULATE` and
   `POST_TYPE_POPULATE` allow for monitoring when an index is about to be or has
   just been populated. #744
 * New events `PRE_INDEX_RESET`, `POST_INDEX_RESET`, `PRE_TYPE_RESET` and
   `POST_TYPE_RESET` are run before and after operations that will reset an
   index. #744
 * Added indexable callback support for the __invoke method of a service. #823
