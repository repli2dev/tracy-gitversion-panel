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

    private $dir;

    private $branch;
    private $commit;

    public function getPanel()
    {
        $this->parseHead();
        ob_start(function () {});
        require __DIR__ . '/templates/GitVersionPanel.panel.phtml';
        return ob_get_clean();
    }

    protected function getLogTail($rowCount=5)
    {
        $dir = $this->findGitDir();
        $logHead = $dir.'/logs/HEAD';
        if(!$dir || !is_readable($logHead)){
            return [];
        }
        $fp = fopen($logHead,'r');
        fseek($fp, -1, SEEK_END);
        $pos = ftell($fp);
        $log = "";
        $rowCounter = -1;
        while($rowCounter <= $rowCount && $pos >= 0) {
            $char = fgetc($fp);
            $log = $char.$log;
            if($char == "\n")
                $rowCounter++;
            fseek($fp, $pos--);
        }
        return explode("\n",trim($log));
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
        require __DIR__ . '/templates/GitVersionPanel.tab.phtml';
        return ob_get_clean();
    }

    private function findGitDir(){
        if($this->dir)
            return $this->dir;

        $scriptPath = $_SERVER['SCRIPT_FILENAME'];
        $dir = realpath(dirname($scriptPath));
        while ($dir !== false) {
            flush();
            $currentDir = $dir;
            $dir .= '/..';
            $dir = realpath($dir);
            $gitDir = $dir . '/.git';
            if(is_dir($gitDir)){
                $this->dir = $gitDir;
                return $gitDir;
            }
            // Stop recursion to parent on root directory
            if ($dir == $currentDir) {
                break;
            }
        }
        return NULL;
    }

    private function parseHead()
    {
        if (!$this->read) {
            $dir = $this->findGitDir();

            $head = $dir . '/HEAD';
            if ($dir && is_readable($head)) {
                $branch = file_get_contents($head);
                if (strpos($branch, 'ref:') === 0) {
                    $parts = explode('/', $branch);
                    $this->branch = $parts[2];

                    $commitFile = $dir . '/' . trim(substr($branch, 5, strlen($branch)));
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

