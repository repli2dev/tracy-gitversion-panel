<?php

namespace JanDrabek\Tracy;
use Tracy\IBarPanel;

/**
 * Panel showing branch and commit hash of this branch to be able identify deployed version.
 *
 * @author Jan Drábek
 * @author Vojtěch Vondra
 */
class GitVersionPanel implements IBarPanel
{

	private $read = false;

	private $branch;
	private $commit;

	public function getPanel()
	{
		$this->parseHead();
		return '<h1>Git Version</h1><p>Revision: ' . $this->getCurrentCommitHash() . '</p>';
	}

	protected function getCurrentBranchName()
	{
		$this->parseHead();
		if ($this->branch) {
			return $this->branch;
		} elseif ($this->commit) {
			return 'detached';
		}
		return 'not versioned';
	}

	protected function getCurrentCommitHash()
	{
		$this->parseHead();
		if ($this->commit) {
			return $this->commit;
		}
		return 'not versioned';
	}

	public function getTab()
	{
		return '<img alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAedJREFUeNqMk79LXEEQx+e8FyKIGAKBNCKxiXiIP4r8EB9iEUFFSKFgDPkDrjpLi3QhfYSApSAoop34I4WNXAoVq9icheH8dXLxok9Ofd6+3Vtnxn2PxzuiGfgwszO735vZtxf7Mv8BIlZn/AU8YJ+HZ6FKKQUhPsVjlkNQHKlVQGYpKX3BAStuTb9rHeLFyvbMtFTyDMPl+7qokp4EpKus9FJPy3u4EXmGYspRzeypgAU8z4tLqdJ2oh/KugjO1R+GYspRjfYgEIVH8ISgYezVjbkk+lG77Q0X1rbmyc0ik4j61wiWKAnyPw2jN8Llgsl/DO19iSSN4G5wB9gBhHHFNRPNI8nOlt4Uzp7B+BXlWIB+KYxbchmznkJ+IxpJPXqsobtjEMXkJq7fkkBsZDwRHkm3tzdzcLzvQEfChvrnjVBX+5Rz679m4NmTBtCiGn6kF0DrsmX5rfhG7ZP19QzDUSED23tL2FExqBddB2JeDY+FAsElBuZfYnpnAXKHebg4u6RPybmm1hdQdK4gd3CKh7W9+D2rKjooGYFctkCHJzD8ipwi3/7mz1Mnh3z4Na637t6BeRCBgFdiX8ifkxsLlSb3944h+hkt/0n6JuRdR9G8OTRW8ZBCfyZWz2xmk34M/2G3AgwAYPB4kNQnLB4AAAAASUVORK5CYII=" />'
		. $this->getCurrentBranchName();
	}

	private function parseHead()
	{
		if (!$this->read) {
			$scriptPath = $_SERVER['SCRIPT_FILENAME'];
			$dir = realpath(dirname($scriptPath));
			while ($dir !== false && !is_dir($dir . '/.git')) {
				flush();
				$currentDir = $dir;
				$dir .= '/..';
				$dir = realpath($dir);

				// Stop recursion to parent on root directory
				if ($dir == $currentDir) {
					break;
				}
			}

			$head = $dir . '/.git/HEAD';
			if ($dir && is_readable($head)) {
				$branch = file_get_contents($head);
				if (strpos($branch, 'ref:') === 0) {
					$parts = explode('/', $branch);
					$this->branch = $parts[2];

					$commitFile = $dir . '/.git/' . trim(substr($branch, 5, strlen($branch)));
					if (is_readable($commitFile)) {
						$this->commit = file_get_contents($commitFile);
					}
				} else {
					$this->commit = $branch;
				}
			}
			$this->read = true;
		}
	}

}

