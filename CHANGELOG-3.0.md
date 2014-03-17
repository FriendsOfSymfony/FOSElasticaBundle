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

* 3.0.0-ALPHA3 (xxxx-xx-xx)

 * #463: allowing hot swappable reindexing
 * #415: BC BREAK: document indexing occurs in postFlush rather than the pre* events previously.
 * 7d13823: Dropped (broken) support for Symfony <2.3

* 3.0.0-ALPHA2 (2014-03-17)

 * 41bf07e: Renamed the `no-stop-on-error` option in PopulateCommand to `ignore-errors`
 * 418b9d7: Fixed validation of url configuration
 * 726892c: Ignore TypeMissingException when resetting a single type. This allows to create new types without having to recreate the whole index.
 * 7f53bad Add support for include_in_{parent,root} for nested and objects
