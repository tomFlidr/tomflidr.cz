<?php

namespace App\Tools;

class Git {

	protected string $repoFullPath;

	public function __construct (string $repoFullPath) {
		$this->repoFullPath = $repoFullPath;
	}

	public function GetHeadCommitSummary ($branchName = 'origin/master'): ?\stdClass {
		$rawBranchHeadCommitHash = $this->callGitCli('rev-parse HEAD ' . $branchName);
		if (!$rawBranchHeadCommitHash) return NULL;
		$branchHeadCommitHashArr = explode("\n", $rawBranchHeadCommitHash);
		$branchHeadCommitHash = $branchHeadCommitHashArr[0];
		
		$rawBranchHeadCommitInfo = $this->callGitCli('show ' . $branchHeadCommitHash . ' --format=%an_-_-_-_%ae_-_-_-_%ai_-_-_-_%s_-_-_-_');
		if (!$rawBranchHeadCommitInfo) return NULL;
		$rawBranchHeadCommitInfoArr = explode('_-_-_-_', $rawBranchHeadCommitInfo);
		unset($rawBranchHeadCommitInfoArr[4]);
		list($authorName, $authorEmail, $dateTimeRaw, $commitMessage) = $rawBranchHeadCommitInfoArr;
		$dateTime = \DateTime::createFromFormat('Y-m-d H:i:s O', $dateTimeRaw);

		$changedFiles = [];
		$rawBranchHeadCommitChangedFiles = $this->callGitCli('diff-tree --no-commit-id --name-only -r ' . $branchHeadCommitHash);
		if ($rawBranchHeadCommitChangedFiles) 
			$changedFiles = explode("\n", $rawBranchHeadCommitChangedFiles);

		$result = (object) [
			'branchName'		=> $branchName,
			'commitHash'		=> $branchHeadCommitHash,
			'commitMessage'		=> $commitMessage,
			'dateTime'			=> $dateTime,
			'authorName'		=> $authorName,
			'authorEmail'		=> $authorEmail,
			'changedFiles'		=> $changedFiles,
			'changedFilesCount'	=> count($changedFiles),
		];

		return $result;
	}

	protected function callGitCli (string $cmdArgsStr): string {
		$isWin = \App\Tools\Cli::GetIsWin();
		if ($isWin) {
			$cmdArgsArr = explode(' ', $cmdArgsStr);
			// TODO: try to set git full path here:
			$result = \App\Tools\Cli::RunScript('Git', $cmdArgsArr);
		} else {
			$cwd = getcwd();
			chdir($this->repoFullPath);
			$result = shell_exec('git ' . $cmdArgsStr);
			chdir($cwd);
		}
		return trim($result);
	}
}