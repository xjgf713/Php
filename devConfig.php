<?php
/**
 * Created by PhpStorm.
 * User: jingaofeng
 * Date: 2018/5/22
 * Time: 上午11:44
 */

/**
 * 仅配置 个人的开发环境
 */
if (YII_ENV_DEV && YII_DEBUG) {
    //重写log地址
    $config['components']['log']['targets'][0]['logFile'] = '/data/www/jingf/app_log/susuan_game/app.log';  //LOGFILE
    $config['components']['log']['targets'][1]['logFile'] = '/data/www/jingf/app_log/susuan_game/washgoldOnlineDebug.log';  //ONLINE_DEBUG_LOG_LOGFILE

    /*    //重写mysql 配置
        $mysqlUserName = 'test';
        $mysqlPassword = 'OnlyKf!@#';

        //adventure
        $adventureDsn = 'mysql:host=10.10.228.163;port=3303;dbname=adventure;charset=utf8';
        $adventureMysql = array(
            'username' => $mysqlUserName,
            'dsn' => $adventureDsn,

            'masterConfig' => [
                'username'   => $mysqlUserName,
                'password'   => $mysqlPassword,
                'attributes' => [
                    \PDO::ATTR_TIMEOUT => 5,
                ],
            ],
            'masters'      => [
                "dsn" => $adventureDsn
            ],

            'slaveConfig' => [
                'username'   => $mysqlUserName,
                'password'   => $mysqlPassword,
                'attributes' => [
                    \PDO::ATTR_TIMEOUT => 5,
                ],
            ],
            'slaves'      => [
                "dsn" => $adventureDsn
            ],
        );
        $config['components']['adventure'] = array_merge($config['components']['adventure'], $adventureMysql);*/

    //设置php.ini
    ini_set('display_errors', 'On');
    ini_set('error_reporting', E_ALL);
    ini_set('xdebug.default_enable', 0);

    //配置xhprof
    ini_set('xhprof.output_dir', '/data/www/jingf/app_log/xhprof');
    include_once(__DIR__ . '/../xhprof_lib/utils/xhprof_lib.php');
    include_once(__DIR__ . '/../xhprof_lib/utils/xhprof_runs.php');

    xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

    //打印数据
    if (!function_exists('dd')) {
        //打印数据
        function dd()
        {
            $values = func_get_args();
            echo "<pre>";
            foreach ($values as $v) {
                var_dump($v);
            }

            $xhprof_data = xhprof_disable();
            $xhprof_runs = new XHProfRuns_Default();
            $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
            exit();
        }
    }


    //吃饭
    if (!function_exists('lunch')) {
        function lunch()
        {
            $lunchList = array(
                '阿一公',
                '重庆小面',
                '焦梦杰羊汤',
                '驴肉火烧',
                '绿色餐厅',
                '新奇特餐厅',
                '烤肉饭',
                '鱼你在一起',
                '猪蹄饭',
                '卤肉饭',
                '西少爷',
                '小恒水饺',
                '田老师',
                '回味居',
                '串炒饭',
                '大胖水饺',
                '山西刀削面',
                '卤太宗',
                '',
                '',
                '',
            );
            return $lunchList[array_rand($lunchList)];
        }
    }

    if (!function_exists('makeConfig')) {
        function makeConfig($config)
        {

            return $config;
        }
    }
}


