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

// urgs php4 produces so many warnings
error_reporting(0);

require_once 'PEAR.php';
require_once 'PEAR/Frontend.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/Command.php';
require_once 'Console/Getopt.php';

require_once 'PHPiranha/Enviroment.php';

class PHPiranha_Runner_PearRunner
{
    /**
     * @var PHPiranha_Enviroment
     */
    private $_env;

    /**
     * @param PHPiranha_Enviroment $env
     */
    public function __construct(PHPiranha_Enviroment $env)
    {
        $this->_env = $env;
    }

    public function run($argv)
    {
        error_reporting(0);
        if (!defined('PEAR_RUNTYPE')) {
            define('PEAR_RUNTYPE', 'pear');
        }
        define('PEAR_IGNORE_BACKTRACE', 1);
        @ini_set('allow_url_fopen', true);
        if (!ini_get('safe_mode')) {
            @set_time_limit(0);
        }
        ob_implicit_flush(true);
        @ini_set('track_errors', true);
        @ini_set('html_errors', false);
        @ini_set('magic_quotes_runtime', false);

        $pear_package_version = "1.9.0";

        PEAR_Command::setFrontendType('CLI');
        $all_commands = PEAR_Command::getCommands();
        $progname = PEAR_RUNTYPE;
        array_shift($argv);
        $options = Console_Getopt::getopt2($argv, "c:C:d:D:Gh?sSqu:vV");
        if (PEAR::isError($options)) {
            $this->usage($options);
        }

        $opts = $options[0];

        $store_user_config = false;
        $store_system_config = false;
        $verbose = 1;

        $config = $this->_env->getConfig();

        $ui = PEAR_Command::getFrontendObject();
        $ui->setConfig($config);
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, "displayFatalError"));
        if (ini_get('safe_mode')) {
            $ui->outputData('WARNING: running in safe mode requires that all files created ' .
                    'be the same uid as the current script.  PHP reports this script is uid: ' .
                    @getmyuid() . ', and current user is: ' . @get_current_user());
        }

        $verbose = $config->get("verbose");
        $cmdopts = array();
        foreach ($opts as $opt) {
            $param = !empty($opt[1]) ? $opt[1] : true;
            switch ($opt[0]) {
                case 'd':
                    if ($param === true) {
                        die('Invalid usage of "-d" option, expected -d config_value=value, ' .
                                'received "-d"' . "\n");
                    }
                    $possible = explode('=', $param);
                    if (count($possible) != 2) {
                        die('Invalid usage of "-d" option, expected -d config_value=value, received "' .
                                $param . '"' . "\n");
                    }
                    list($key, $value) = explode('=', $param);
                    $config->set($key, $value, 'user');
                    break;
                case 'D':
                    if ($param === true) {
                        die('Invalid usage of "-d" option, expected -d config_value=value, ' .
                                'received "-d"' . "\n");
                    }
                    $possible = explode('=', $param);
                    if (count($possible) != 2) {
                        die('Invalid usage of "-d" option, expected -d config_value=value, received "' .
                                $param . '"' . "\n");
                    }
                    list($key, $value) = explode('=', $param);
                    $config->set($key, $value, 'system');
                    break;
                case 's':
                    $store_user_config = true;
                    break;
                case 'S':
                    $store_system_config = true;
                    break;
                case 'u':
                    $config->remove($param, 'user');
                    break;
                case 'v':
                    $config->set('verbose', $config->get('verbose') + 1);
                    break;
                case 'q':
                    $config->set('verbose', $config->get('verbose') - 1);
                    break;
                case 'V':
                    $this->usage(null, 'version');
                case 'c':
                case 'C':
                    break;
                default:
                // all non pear params goes to the command
                    $cmdopts[$opt[0]] = $param;
                    break;
            }
        }

        if ($store_system_config) {
            $config->store('system');
        }

        if ($store_user_config) {
            $config->store('user');
        }

        $command = (isset($options[1][0])) ? $options[1][0] : null;
        if (empty($command) && ($store_user_config || $store_system_config)) {
            exit;
        }
        if ($command == 'help') {
            $this->usage(null, @$options[1][1]);
        }

        if (!$config->validConfiguration()) {
            PEAR::raiseError('CRITICAL ERROR: no existing valid configuration files found in files found');
        }

        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
        $cmd = PEAR_Command::factory($command, $config);
        PEAR::popErrorHandling();
        if (PEAR::isError($cmd)) {
            $this->usage(null, @$options[1][0]);
        }

        $short_args = $long_args = null;
        PEAR_Command::getGetoptArgs($command, $short_args, $long_args);
        array_shift($options[1]);
        $tmp = Console_Getopt::getopt2($options[1], $short_args, $long_args);

        if (PEAR::isError($tmp)) {
            break;
        }

        list($tmpopt, $params) = $tmp;
        $opts = array();
        foreach ($tmpopt as $foo => $tmp2) {
            list($opt, $value) = $tmp2;
            if ($value === null) {
                $value = true; // options without args
            }

            if (strlen($opt) == 1) {
                $cmdoptions = $cmd->getOptions($command);
                foreach ($cmdoptions as $o => $d) {
                    if (isset($d['shortopt']) && $d['shortopt'] == $opt) {
                        $opts[$o] = $value;
                    }
                }
            } else {
                if (substr($opt, 0, 2) == '--') {
                    $opts[substr($opt, 2)] = $value;
                }
            }
        }

        $ok = $cmd->run($command, $opts, $params);
        if ($ok === false) {
            PEAR::raiseError("unknown command `$command'");
        }

        if (PEAR::isError($ok)) {
            PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, "displayFatalError"));
            PEAR::raiseError($ok);
        }

    }

    private function usage($error = null, $helpsubject = null)
    {
        global $progname, $all_commands;
        $stderr = fopen('php://stderr', 'w');
        if (PEAR::isError($error)) {
            fputs($stderr, $error->getMessage() . "\n");
        } elseif ($error !== null) {
            fputs($stderr, "$error\n");
        }

        if ($helpsubject != null) {
            $put = $this->cmdHelp($helpsubject);
        } else {
            $put =
                    "Commands:\n";
            $maxlen = max(array_map("strlen", $all_commands));
            $formatstr = "%-{$maxlen}s  %s\n";
            ksort($all_commands);
            foreach ($all_commands as $cmd => $class) {
                $put .= sprintf($formatstr, $cmd, PEAR_Command::getDescription($cmd));
            }
            $put .=
                    "Usage: $progname [options] command [command-options] <parameters>\n".
                    "Type \"$progname help options\" to list all options.\n".
                    "Type \"$progname help shortcuts\" to list all command shortcuts.\n".
                    "Type \"$progname help <command>\" to get the help for the specified command.";
        }
        fputs($stderr, "$put\n");
        fclose($stderr);
        exit(1);
    }

    private function cmdHelp($command)
    {
        global $progname, $all_commands, $config;
        if ($command == "options") {
            return
                    "Options:\n".
                    "     -v         increase verbosity level (default 1)\n".
                    "     -q         be quiet, decrease verbosity level\n".
                    "     -c file    find user configuration in `file'\n".
                    "     -C file    find system configuration in `file'\n".
                    "     -d foo=bar set user config variable `foo' to `bar'\n".
                    "     -D foo=bar set system config variable `foo' to `bar'\n".
                    "     -G         start in graphical (Gtk) mode\n".
                    "     -s         store user configuration\n".
                    "     -S         store system configuration\n".
                    "     -u foo     unset `foo' in the user configuration\n".
                    "     -h, -?     display help/usage (this message)\n".
                    "     -V         version information\n";
        } elseif ($command == "shortcuts") {
            $sc = PEAR_Command::getShortcuts();
            $ret = "Shortcuts:\n";
            foreach ($sc as $s => $c) {
                $ret .= sprintf("     %-8s %s\n", $s, $c);
            }
            return $ret;

        } elseif ($command == "version") {
            return "PEAR Version: ".$GLOBALS['pear_package_version'].
                    "\nPHP Version: ".phpversion().
                    "\nZend Engine Version: ".zend_version().
                    "\nRunning on: ".php_uname();

        } elseif ($help = PEAR_Command::getHelp($command)) {
            if (is_string($help)) {
                return "$progname $command [options] $help\n";
            }

            if ($help[1] === null) {
                return "$progname $command $help[0]";
            }

            return "$progname $command [options] $help[0]\n$help[1]";
        }

        return "Command '$command' is not valid, try '$progname help'";
    }

}