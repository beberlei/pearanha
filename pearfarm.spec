<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('Pearanha')
             ->setChannel('beberlei.pearfarm.org')
             ->setSummary('Simple Wrapper for an application-Level PEAR installer')
             ->setDescription('Simple Wrapper for an application-Level PEAR installer')
             ->setReleaseVersion('0.0.1')
             ->setReleaseStability('alpha')
             ->setApiVersion('0.0.1')
             ->setApiStability('alpha')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', 'Benjamin Eberlei', 'beberlei', 'kontakt@beberlei.de')
             ->addGitFiles()
             ->addExecutable('bin/pearanha')
             ;