CHANGELOG for 3.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 3.1 versions.

To get the diff for a specific change, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/commit/XXX where XXX is
the commit hash. To get the diff between two versions, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/compare/v3.0.4...v3.1.0

* 3.1.0

* BC BREAK: `DoctrineListener#scheduleForDeletion` access changed to private.
* BC BREAK: `ObjectPersisterInterface` gains the method `handlesObject` that
  returns a boolean value if it will handle a given object or not.
