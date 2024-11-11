@set env_name=%1
@set id_bg_process=%2

@set bin_dir=%cd%
@set app_root_dir=%bin_dir:\=/%/../..

:: @Fork.Debug.exe php.cmd -d max_execution_time=0 -d memory_limit=-1 "%app_root_dir%/www/index.php" controller=clis/executor action=execute env_name=%env_name% id_bg_process=%id_bg_process%

@Fork.exe php.cmd -d max_execution_time=0 -d memory_limit=-1 "%app_root_dir%/www/index.php" controller=clis/executor action=execute env_name=%env_name% id_bg_process=%id_bg_process%