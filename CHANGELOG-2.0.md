CHANGELOG for 2.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 2.0 minor versions.

To get the diff for a specific change, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/commit/XXX where XXX is
the commit hash. To get the diff between two versions, go to
https://github.com/FriendsOfSymfony/FOSElasticaBundle/compare/v2.0.0...v2.0.1

To generate a changelog summary since the last version, run
`git log --no-merges --oneline v2.0.0...2.0.x`

* 2.0.2 (2013-06-06)

 * 00e9a49: Allow Symfony dependencies until 3.0
 * 4b4a56d: Check for "indexes" key in Configuration::getNestings()
 * 8ffd1a7: Update install version and add links to compatibility info
 * 58e983f: Document installation via composer in README (closes #271)

* 2.0.1 (2013-04-04)

 * f0d3a4d: Ensure mongo extension is available in Travis CI
 * 1f26318: Avoid using a feature not supported in PHP5.3
