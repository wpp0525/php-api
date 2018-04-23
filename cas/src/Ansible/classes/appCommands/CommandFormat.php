<?php
/**
 * @author dirc.wang
 */

namespace Ansible\classes\appCommands;

/**
 * Provides basic utility to config build the file system.
 *
 * @author dirc.wang
 */
abstract class CommandFormat{

  abstract protected function getTypeName();

  abstract public function runCommand($id, $ip, $chdir = '/tmp', $params = "", $chown);

  abstract public function commandList();

  abstract public function NeedParams();

  abstract public function getCommandString($id);
}
