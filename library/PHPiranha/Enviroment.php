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

require_once "PEAR/Config.php";

class PHPiranha_Enviroment
{
    /**
     * @var PEAR_Config
     */
    private $_pearConfig = null;

    public function __construct($vendorDir, $binDir, $cacheDir, $miscDir = null)
    {
        if ($miscDir == null) {
            $miscDir = $vendorDir;
        }

        $this->_pearConfig = new PEAR_Config($vendorDir."/.pearrc");

        // change the configuration for use
        $this->_pearConfig->set('php_dir',  $vendorDir);
        $this->_pearConfig->set('data_dir', $miscDir);
        $this->_pearConfig->set('test_dir', $miscDir);
        $this->_pearConfig->set('doc_dir',  $miscDir);
        $this->_pearConfig->set('www_dir',  $miscDir);
        $this->_pearConfig->set('bin_dir',  $binDir);

        $this->_pearConfig->set('preferred_state', "stable");

        // change the PEAR temp dirs
        $this->_pearConfig->set('cache_dir',    $cacheDir);
        $this->_pearConfig->set('download_dir', $cacheDir);
        $this->_pearConfig->set('temp_dir',     $cacheDir);

        $this->_pearConfig->set('verbose', 1);

        $GLOBALS['_PEAR_Config_instance'] = $this->_pearConfig;
    }

    public function getConfig()
    {
        return $this->_pearConfig;
    }
}