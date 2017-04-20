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
        ob_start(function () {});
        require __DIR__ . '/templates/MaintenancePanel.panel.phtml';
        return ob_get_clean();
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
        ob_start(function () {});
        require __DIR__ . '/templates/MaintenancePanel.tab.phtml';
        return ob_get_clean();
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

