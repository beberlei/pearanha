<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
     ->setName('Pearanha')
     ->setChannel('beberlei.pearfarm.org')
     ->setSummary('Simple Wrapper for an application-Level PEAR installer')
     ->setDescription('Simple Wrapper for an application-Level PEAR installer')
     ->setReleaseVersion('0.0.2')
     ->setReleaseStability('alpha')
     ->setApiVersion('0.0.2')
     ->setApiStability('alpha')
     ->setLicense(Pearfarm_PackageSpec::LICENSE_BSD)
     ->setNotes('Initial release.')
     ->addMaintainer('lead', 'Benjamin Eberlei', 'beberlei', 'kontakt@beberlei.de')
     ->addGitFiles()
     ->addFilesSimple(array('README.markdown', 'LICENSE'), "doc")
     ->addExcludeFiles(array('.gitignore', 'package.xml', 'pearfarm.spec'))
     ->addExecutable('pearanha')
     ->setDependsOnPHPVersionMin('5.2.0');