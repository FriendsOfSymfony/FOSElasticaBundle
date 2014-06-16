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

* 3.0.0-ALPHA6

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
