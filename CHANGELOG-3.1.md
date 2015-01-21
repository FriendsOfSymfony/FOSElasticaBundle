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
 * Added ability to configure `date_detection`, `numeric_detection` and
   `dynamic_date_formats` for types. #753
 * New event `POST_TRANSFORM` which allows developers to add custom properties to
   Elastica Documents for indexing.
