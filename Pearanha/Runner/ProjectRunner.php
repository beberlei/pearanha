<?php
/**
 * Copyright (c) 2010, Benjamin Eberlei
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 *     * Neither the name of the phpiranha nor the names of its contributors may be used to
 *       endorse or promote products derived from this software without specific prior
 *       written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
 * THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   phpiranha
 * @copyright 2010 Benjamin Eberlei
 * @author    Benjamin Eberlei <kontakt@beberlei.de>
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

require_once "PearRunner.php";

class Pearanha_Runner_ProjectRunner
{
    /**
     * @param array $argv
     */
    public function run($argv)
    {
        try {
            $this->_doRun($argv);
            exit(0);
        } catch(Exception $e) {
            echo "An error occured: ".$e->getMessage().PHP_EOL;
            exit(1);
        }
    }

    protected function _doRun($argv)
    {
        if (isset($argv[1])) {
            $action = $argv[1];
        } else {
            throw new Exception("No action was specified.");
        }


        switch($action) {
            case 'generate-project':
                $this->runGenerateProject($argv);
                break;
            default:
                throw new Exception("Invalid action specified.");
        }
    }

    public function runGenerateProject($argv)
    {
        if(!isset($argv[2])) {
            $projectDir = $this->promptInput('Enter path to new project pear/vendor directory', (isset($_SERVER['PWD'])) ? $_SERVER['PWD'] : '');
        } else {
            $projectDir = $argv[2];
        }
        $projectDir = realpath($projectDir);
        if(!file_exists($projectDir)) {
            throw new Exception("PEAR/Vendor directory does not exist! Aborting..");
        }
        $pearConfDir = $projectDir . DIRECTORY_SEPARATOR . ".pearrc";

        $config = $this->createConfig($projectDir, $pearConfDir);
        $binDir = $config->get('bin_dir');

        if (!file_exists($binDir)) {
            if (!is_writable(dirname($binDir))) {
                throw new Exception("Can't write to $projectDir");
            }
            mkdir($binDir);
        }

        $applicationPhpiranaExexutableTemplate = $this->generateExecutableTemplate(array(
            'configfile' => $pearConfDir,
            'includepath' => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR),
        ));

        $executableFile = $binDir . DIRECTORY_SEPARATOR . "my_pearanha";
        file_put_contents($executableFile, $applicationPhpiranaExexutableTemplate);

        if ($this->isWindows()) {
            $executableFile = $binDir . DIRECTORY_SEPARATOR . "my_pearanha.bat";

            $executableBatTemplate  = "@echo off\n".
            $executableBatTemplate .= 'set PHPBIN="@php_bin@"'."\n";
            $executableBatTemplate .= '"@php_bin@" "@bin_dir@\pearanha" %*'."\n";
            file_put_contents($executableFile, $executableBatTemplate);
        }

        if(!chmod($executableFile, 0700)) {
            throw new Exception("Could not make my_pearanha file executable for user.");
        }

        echo 'Successfully created my_pearanha application PEAR installer at "'.$executableFile.'"'.PHP_EOL;
    }

    private function isWindows()
    {
        return ((substr(PHP_OS, 0,3)) == 'WIN');
    }

    private function createConfig($root, $pearConfDir)
    {
        $old = error_reporting(0);

        $windows = $this->isWindows();

        $ds2 = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;
        $root = preg_replace(array('!\\\\+!', '!/+!', "!$ds2+!"),
                             array('/', '/', '/'),
                            $root);
        if ($root{0} != '/') {
            if ($windows) {
                throw new Exception('Root directory must be an absolute path beginning ' .
                    'with "/", was: "' . $root . '"');
            }

            if (!preg_match('/^[A-Za-z]:/', $root)) {
                throw new Exception('Root directory must be an absolute path beginning ' .
                    'with "\\" or "C:\\", was: "' . $root . '"');
            }
        }

        if ($windows) {
            $root = str_replace('/', '\\', $root);
        }

        if (!file_exists($pearConfDir) && !@touch($pearConfDir)) {
            throw new Exception('Could not create "' . $pearConfDir. '"');
        }

        $config = new PEAR_Config($pearConfDir, '#no#system#config#', false, false);
        if ($root{strlen($root) - 1} == '/') {
            $root = substr($root, 0, strlen($root) - 1);
        }

        $config->noRegistry();
        $config->set('php_dir', $windows ? "$root" : "$root", 'user');
        $config->set('data_dir', $windows ? "$root\\pear\\data" : "$root/data");
        $config->set('www_dir', $windows ? "$root\\pear\\www" : "$root/pear/www");
        $config->set('cfg_dir', $windows ? "$root\\pear\\cfg" : "$root/pear/cfg");
        $config->set('ext_dir', $windows ? "$root\\pear\\ext" : "$root/pear/ext");
        $config->set('doc_dir', $windows ? "$root\\pear\\docs" : "$root/pear/docs");
        $config->set('test_dir', $windows ? "$root\\pear\\tests" : "$root/pear/tests");
        $config->set('cache_dir', $windows ? "$root\\pear\\cache" : "$root/pear/cache");
        $config->set('download_dir', $windows ? "$root\\pear\\download" : "$root/pear/download");
        $config->set('temp_dir', $windows ? "$root\\pear\\temp" : "$root/pear/temp");
        $config->set('bin_dir', $windows ? "$root\\" : "$root/");
        $config->writeConfigFile();

        error_reporting($old);

        return $config;
    }

    private function generateExecutableTemplate($placeholders)
    {
        $template = <<<EOT
<?php
/**
 * Application specific PEAR installer
 *
 * Code generated by PEARanha
 */
\$includeDir = "##INCLUDEPATH##";
if (strpos(\$includeDir, '@php_dir@') !== false) {
    \$includeDir = realpath(dirname(__FILE__)."/../library/");
}

set_include_path(\$includeDir . DIRECTORY_SEPARATOR . get_include_path());

require_once "Pearanha/Runner/PearRunner.php";

\$runner = new Pearanha_Runner_PearRunner("##CONFIGFILE##");
\$runner->run(\$argv);
EOT;

        if (!$this->isWindows()) {
            $template = "#!/usr/bin/env php\n".$template;
        }

        foreach ($placeholders AS $p => $v) {
            $p = strtoupper($p);
            $template = str_replace('##'.$p.'##', $v, $template);
        }


        return $template;
    }

    private function promptInput($question, $default = '')
    {
        echo "> $question [$default]:\n";
        echo "> ";
        $value = trim(fgets(STDIN, 4096));
        if (strlen($value) == 0) {
            return $default;
        } else {
            return $value;
        }
    }
}