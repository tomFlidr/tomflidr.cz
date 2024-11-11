#!/bin/bash

env_name=$1
id_bg_process=$2

current_dir=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
www_dir=$(realpath "$current_dir/../../www")

cmd="php -d max_execution_time=0 -d memory_limit=-1 $www_dir/index.php controller=clis/executor action=execute env_name=$env_name id_bg_process=$id_bg_process"

bash -c "exec nohup setsid ${cmd} > /dev/null 2>&1 &"

echo 1
