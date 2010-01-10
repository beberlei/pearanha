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

class PHPiranha_Runner_ProjectRunner
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
            echo "An error occured: ".$e->getMessage()."\n";
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
        $projectDir = $this->promptInput('Enter path to existing project root', (isset($_SERVER['PWD'])) ? $_SERVER['PWD'] : '');
        if (!file_exists($projectDir)) {
            throw new Exception("Invalid project root directory given, does not exist!");
        }
        if (!is_writable($projectDir)) {
            throw new Exception("Project directory is not writable.");
        }

        if (!chdir($projectDir)) {
            throw new Exception("Could not change to project directory");
        }

        $style = $this->promptInput('Specifiy Project Style (zf, manual)', 'zf');
        switch($style) {
            case 'zf':
                $vendorDir = "vendor";
                $cacheDir = "temp";
                $miscDir = "vendor";
                $binDir = "bin";
                break;
            case 'manual':
                $vendorDir = $this->promptInput('Enter Vendor directory inside project root');
                $cacheDir = $this->promptInput('Enter PEAR Cache directory inside project root');
                $miscDir = $this->promptInput('Enter Misc (Tests, Docs, i.e.) directory inside project root');
                $binDir = $this->promptInput('Enter Bin/Scripts directory inside project root');
                break;
            default:
                throw new Exception("Invalid project style given.");
        }

        $vendorDir = $projectDir."/".$vendorDir;
        $cacheDir = $projectDir."/".$cacheDir;
        $miscDir = $projectDir."/".$miscDir;
        $binDir = $projectDir."/".$binDir;

        $this->createDirectoryIfNotExists($vendorDir);
        $this->createDirectoryIfNotExists($cacheDir);
        $this->createDirectoryIfNotExists($miscDir);
        $this->createDirectoryIfNotExists($binDir);

        $applicationPhpiranaExexutableTemplate = $this->generateExecutableTemplate(array(
            'VENDOR' => $vendorDir,
            'CACHE' => $cacheDir,
            'MISC' => $miscDir,
            'BIN' => $binDir,
            'INCLUDEPATH' => realpath(dirname(__FILE__)."/../../")
        ));

        $executableFile = $binDir."/my_phpiranha";
        file_put_contents($executableFile, $applicationPhpiranaExexutableTemplate);
        if(!chmod($executableFile, 0700)) {
            throw new Exception("Could not make my_phpiranha file executable for user.");
        }
    }

    private function generateExecutableTemplate($placeholders)
    {
        $phpBin = (isset($_SERVER['_'])) ? '#!'.$_SERVER['_'] : '';

        $template = <<<EOT
$phpBin
<?php
/**
 * Application specific PEAR installer
 *
 * Code generated by PHPiranha
 */
\$includeDir = "##INCLUDEPATH##";
if (strpos(\$includeDir, '@php_dir@') !== false) {
    \$includeDir = realpath(dirname(__FILE__)."/../library/");
}

set_include_path(\$includeDir . DIRECTORY_SEPARATOR . get_include_path());

require_once "PHPiranha/Runner/PearRunner.php";

\$env = new PHPiranha_Enviroment(
    "##VENDOR##",
    "##BIN##",
    "##CACHE##",
    "##MISC##"
);

\$runner = new PHPiranha_Runner_PearRunner(\$env);
\$runner->run(\$argv);
EOT;
        foreach ($placeholders AS $p => $v) {
            $template = str_replace('##'.$p.'##', $v, $template);
        }


        return $template;
    }

    private function createDirectoryIfNotExists($dir)
    {
        if (!file_exists($dir)) {
            if(!is_writable(dirname($dir))) {
                throw new Exception("Parent Directory '".dirname($dir)."' is not writable or does not exist.");
            }
            @mkdir($dir);
        }
        if (!is_dir($dir)) {
            throw new Exception("Could not find and create directory $dir");
        }
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