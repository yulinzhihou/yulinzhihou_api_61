#!/usr/bin/env bash
# author : yulinzhihou
# email : yulinzhihou@gmail.com
# date  : 2022-10-15
# 用于批量导入 游戏数据文件的异步执行，
php think queue:work --queue async_exec_method