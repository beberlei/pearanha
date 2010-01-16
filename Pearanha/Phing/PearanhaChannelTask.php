<?php
/**
 * Pearanha
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

require_once dirname(__FILE__)."/../Runner/PearRunner.php";

class PearanhaChannelTask extends Task
{
    private $pearConfigFile = null;

    private $channel = null;

    public function setPearConfigFile($pearConfigFile)
    {
        $this->pearConfigFile = $pearConfigFile;
    }

    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    public function main()
    {
        $runner = new Pearanha_Runner_PearRunner($this->pearConfigFile);
        $runner->run(array(__FILE__, 'channel-discover', $this->channel));
    }
}