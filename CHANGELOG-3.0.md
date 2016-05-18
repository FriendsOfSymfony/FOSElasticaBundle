CHANGELOG for 3.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 3.0 minor versions.

To get the diff for a specific change, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/commit/XXX where XXX is
the commit hash. To get the diff between two versions, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/compare/v3.0.0...v3.0.1

To generate a changelog summary since the last version, run
`git log --no-merges --oneline v3.0.0...3.0.x`

* 3.0.13 (2015-09-17)

 * Add PHP 7 compatibility
 * Fix: Don't use parent identifier for objects/nested documents

* 3.0.12 (2015-08-31)

 * Bump allowed Elastica version to 2.2

* 3.0.11 (2015-08-05)

 * Bump allowed Elastica version to 2.1
 * Fixed Symfony 2.7 deprecated messages

* 3.0.10 (2015-05-28)

 * Bump allowed Elastica version to 2.0

* 3.0.9 (2015-03-12)

 * Fix a bug in the BC layer of the type configuration for empty configs
 * Fix the service definition for the Doctrine listener when the logger is not enabled

* 3.0.8 (2014-01-31)

 * Fixed handling of empty indexes #760
 * Added support for `connectionStrategy` Elastica configuration #732
 * Allow Elastica 1.4

* 3.0.7 (2015-01-21)

 * Fixed the indexing of parent/child relations, broken since 3.0 #774
 * Fixed multi_field properties not being normalised #769

* 3.0.6 (2015-01-04)

 * Removed unused public image asset for the web development toolbar #742
 * Fixed is_indexable_callback BC code to support array notation #761
 * Fixed debug_logger for type providers #724
 * Clean the OM if we filter away the entire batch #737

* 3.0.0-ALPHA6

 * Moved `is_indexable_callback` from the listener properties to a type property called
   `indexable_callback` which is run when both populating and listening for object
   changes.
 * AbstractProvider constructor change: Second argument is now an `IndexableInterface`
   instance.
 * Annotation @Search moved to FOS\ElasticaBundle\Annotation\Search with FOS\ElasticaBundle\Configuration\Search deprecated
 * Deprecated FOS\ElasticaBundle\Client in favour of FOS\ElasticaBundle\Elastica\Client
 * Deprecated FOS\ElasticaBundle\DynamicIndex in favour of FOS\ElasticaBundle\Elastica\Index
 * Deprecated FOS\ElasticaBundle\IndexManager in favour of FOS\ElasticaBundle\Index\IndexManager
 * Deprecated FOS\ElasticaBundle\Resetter in favour of FOS\ElasticaBundle\Index\Resetter

* 3.0.0-ALPHA5 (2014-05-23)

 * Doctrine Provider speed up by disabling persistence logging while populating documents

* 3.0.0-ALPHA4 (2014-04-10)

 * Indexes are now capable of logging errors with Elastica
 * Fixed deferred indexing of deleted documents
 * Resetting an index will now create it even if it doesn't exist
 * Bulk upserting of documents is now supported when populating

* 3.0.0-ALPHA3 (2014-04-01)

 * a9c4c93: Logger is now only enabled in debug mode by default
 * #463: allowing hot swappable reindexing
 * #415: BC BREAK: document indexing occurs in postFlush rather than the pre* events previously.
 * 7d13823: Dropped (broken) support for Symfony <2.3
 * #496: Added support for HTTP headers
 * #528: FOSElasticaBundle will disable Doctrine logging when populating for a large increase in speed

* 3.0.0-ALPHA2 (2014-03-17)

 * 41bf07e: Renamed the `no-stop-on-error` option in PopulateCommand to `ignore-errors`
 * 418b9d7: Fixed validation of url configuration
 * 726892c: Ignore TypeMissingException when resetting a single type. This allows to create new types without having to recreate the whole index.
 * 7f53bad Add support for include_in_{parent,root} for nested and objects
