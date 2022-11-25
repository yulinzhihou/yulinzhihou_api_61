#!/usr/bin/env bash
# author yulinzhihou@gmail.com
# date 2022-07-09
# desc: 开发使用快速合建脚本。
# 参数1：模块名：如：admin
# 参数2：版本号 如：v1
# 参数3：类名: 如：Goods
#php think make:controller "$1"@"$2"/"$3" --plain
#php think make:model "$1"@"$3"
#php think make:validate "$1"@"$3"

php think yc:create "$1"@"$2"/"$3"
php think ym:create "$1"@"$3"
php think yv:create "$1"@"$3"