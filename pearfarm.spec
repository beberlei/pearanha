<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
     ->setName('Pearanha')
     ->setPackageNameAsBaseInstallDir(false)
     ->setChannel('beberlei.pearfarm.org')
     ->setSummary('Simple Wrapper for an application-Level PEAR installer')
     ->setDescription('Simple Wrapper for an application-Level PEAR installer')
     ->setReleaseVersion('0.1.1')
     ->setReleaseStability('alpha')
     ->setApiVersion('0.1.1')
     ->setApiStability('alpha')
     ->setLicense(Pearfarm_PackageSpec::LICENSE_BSD)
     ->setNotes('Next release, now with two phing tasks for channel registering and package installation.')
     ->addMaintainer('lead', 'Benjamin Eberlei', 'beberlei', 'kontakt@beberlei.de')
     ->addGitFiles()
     ->addFilesSimple(array('README.markdown', 'LICENSE'), "doc")
     ->addExcludeFiles(array('.gitignore', 'package.xml', 'pearfarm.spec'))
     ->addExecutable('pearanha')
     ->setDependsOnPHPVersionMin('5.2.0');