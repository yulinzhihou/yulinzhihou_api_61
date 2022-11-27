<?php
namespace app\admin\exception;

use app\admin\model\ExceptionLog;
use app\library\JwtUtil;
use think\exception\Handle;
use think\exception\HttpResponseException;
use think\facade\Cache;
use think\facade\Env;
use think\facade\Session;
use think\Response;
use Throwable;
use Whoops\Handler\PrettyPageHandler;

/**
 * 后端模块异常处理类，提交异常数据到数据表
 * 前提是需要 yulinzhihou/think-whoops 插件包支持
 * 安装 composer require yulinzhihou/think-whoops
 */
class AdminExceptionHandle extends Handle
{
    /**
     * 异常日志接管到数据表
     * @param $request
     * @param $e
     * @return void
     */
    public function saveExceptionRecordsToDb($request, $e):void
    {
        // 获取管理员信息
        if (Session::has('admin_user_info') && Session::get('admin_user_info') != '') {
            // 表示是Session-Cookie 通讯
            $adminInfo = Session::get('admin_user_info');
        } elseif ($request->header('authorization') != '') {
            // Token方式
            // 前端请求携带的Token信息，根据请求头字段
            $token = $request->header('authorization');
            $isRas = Env::get('jwt.is_rsa',false);
            $key = $isRas ? root_path().Env::get('jwt.cert_path').DIRECTORY_SEPARATOR.Env::get('jwt.name').'.pem' : Env::get('jwt.app_key');
            $key = file_get_contents($key);
            $jwtInfo = JwtUtil::verification($key, $token,$isRas ? 'RS256' : 'HS256'); // 与签发的key一致
            if ($jwtInfo['status'] == 200) {
                $cacheKey = 'admin_login_info:user_id-'.$jwtInfo['data']['data']['uid'];
                if (Cache::has($cacheKey) && Cache::get($cacheKey) != '') {
                    // 传递用户信息给请求
                    $adminInfo = Cache::get($cacheKey);
                } else {
                    $adminInfo = $jwtInfo['data']['data']['user_info'];
                }

            } else {
                $adminInfo = [];
            }
        } else {
            $adminInfo = [];
        }

        // 存入接口请求日志
        $pathInfo = $request->pathinfo();
        $routeArr = explode('/',$pathInfo);
        if (count($routeArr) === 2) {
            $controller = array_pop($routeArr);
            $version = array_pop($routeArr);
            // 取方法名，
            $action = match ($request->method()) {
                'POST' => 'save',
                'GET' => 'index',
                'PUT' => 'read',
                'DELETE' => 'delete',
                default => '',
            };
        } else {
            $action = array_pop($routeArr);
            $controller = array_pop($routeArr);
            $version = array_pop($routeArr);
        }
        // 取出报错细节
        $errTrace = array_reverse($e->getTrace());
        // 当前报错信息和行号
        $err = array_pop($errTrace);
        // 当前报错文件
        $errPrevious = array_pop($errTrace);
        // 通过报错文件，获取类和方法
        $data = [
            'admin_id'      => $adminInfo['id']??0,
            'admin_name'    => $adminInfo['nickname']??'未登录',
            'app_name'      => app('http')->getName(),
            'url'           => $request->url(true),
            'ip'            => $request->ip(),
            'user_agent'    => $request->header('user_agent'),
            'params'        => json_encode($request->param()),
            'class'         => $controller,
            'action'        => $action,
            'type'          => $errPrevious['class']??'',
            'error_file'     => $err['file']??$e->getFile(),
            'error_line'    => $err['line']??$e->getLine(),
            'message'       => $e->getMessage(),
            'code'          => $e->getCode(),
            'sql'           => '',
            'data_create_time'=> date('Y-m-d H:i:s',time()),
            'create_time'   => time(),
            'update_time'   => time(),
        ];
        ExceptionLog::create($data);
    }

    public function render($request, Throwable $e): Response
    {
        // 不管是什么环境，先写入异常记录到数据库
        $this->saveExceptionRecordsToDb($request,$e);
        // Whoops 接管请求异常
        if (config('whoops.enable') && $this->app->isDebug()) {
            if ($e instanceof HttpResponseException) {
                return $e->getResponse();
            }

            // 兼容 Cors Postman 请求
            // $request->isAjax() 判断不太正常
            if ($request->isJson() || false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Postman') || (isset($_SERVER['HTTP_SEC_FETCH_MODE']) && $_SERVER['HTTP_SEC_FETCH_MODE'] === 'cors')) {
                return $this->handleAjaxException($e);
            }

            $this->app->whoops->pushHandler(new PrettyPageHandler());

            return Response::create(
                $this->app->whoops->handleException($e),
                'html',
                $e->getCode()
            );
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}