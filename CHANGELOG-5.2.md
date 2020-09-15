CHANGELOG for 5.2.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 5.2 versions.

### 5.2.0 (2020-09-15)

* Added Symfony 5 support.
* Dropped Symfony 4.0, 4.1 and 4.2 support.
* Fixed paginate subscriber when request stack is empty.
* Fixed Doctrine deprecation notice in RegisterListenersService.
* Do not prompt for confirmation in `ResetTemplatesCommand` if not interactive.
