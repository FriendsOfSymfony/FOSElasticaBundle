CHANGELOG for 3.1.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 3.1 versions.

To get the diff for a specific change, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/commit/XXX where XXX is
the commit hash. To get the diff between two versions, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/compare/v3.0.4...v3.1.0

* 3.1.0 (Unreleased)

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
