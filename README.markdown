Pearanha is an application-level PEAR/Pyrus Installer
======================================================

With Pearanha you can manage dependencies of your application with the pear or
pyrus installers. Framework or library dependencies and their script files will be installed
into your application directory for easy use with version control systems and
PHAR deployment.

Internally Pearanha uses "pear config-create <new_pear_dir> <configfile>" and "pear -c <configfile>"
to manage the application specific pear registry.
