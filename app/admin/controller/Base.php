<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\ApiLog;
use app\admin\model\ExceptionLog;
use app\BaseController;
use app\library\AsyncDoo;
use app\library\Upload;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use think\db\exception\PDOException;
use think\exception\FileException;
use think\facade\Db;
use think\facade\Env;
use think\facade\Filesystem;
use think\Response\Json;
use WebPConvert\WebPConvert;

/**
 * 后台接口基类
 */
class Base extends BaseController
{
    /**
     * 管理员信息
     */
    protected array $adminInfo = [];

    /**
     * 需要额外加入的请求数据，
     */
    protected array $params = [];

    /**
     * 查询过滤字段，需要的字段请写入
     */
    protected array $field = [];

    /**
     * 定义精准搜索条件
     */
    protected array $focus = [];

    /**
     * 定义模糊搜索条件
     */
    protected array $vague = [];

    /**
     * 定义区间查询条件
     */
    protected array $range = [];

    /**
     * 定义排序字段
     */
    protected array $order = [];

    /**
     * 分页页码
     */
    protected int $page = 0;

    /**
     * 分页数量
     */
    protected int $size = 10;

    /**
     * 导入文件首行类型
     * 支持comment/name
     * 表示注释或字段名默认为字段注释
     */
    protected string $importHeadType = 'comment';

    /**
     * 模型单例
     */
    protected mixed $model;

    /**
     * 验证器单例
     */
    protected mixed $validate;

    /**
     * 返回给客户端请求的数据
     * @var array
     */
    protected array $returnData = [];

    /**
     * http返回状态码，200表示请求成功，504表示 请求失败
     */
    protected int $status = 200;

    /**
     * 提示信息
     * @var string
     */
    protected string $msg = '';

    /**
     * 返回给前端的状态码。0表示请求数据成功，其他值有对应含义，具体不做展开分析，可以和status配合进行一系列的返回情况的判断
     */
    protected int $code = 0;

    /**
     * 定义JWT
     */
    protected Object $jwt;

    /**
     * 接收请求的数据
     */
    protected array $inputData = [];

    /**
     * 请求的字段名
     */
    protected array $inputField = [];

    /**
     * 列表显示字段，默认为对应数据表全部字段，包括验证规则
     */
    protected array $indexField = [];

    /**
     * 编辑页字段，默认为对应数据表全部字段，包括验证规则
     */
    protected array $editField = [];

    /**
     * 编辑接口忽略字段，不允许提交过来的字段
     */
    protected array $editExpectField = [];

    /**
     * 新增页面字段，默认为对应数据表全部字段，包括验证规则
     */
    protected array $addField = [];

    /**
     * 新增接口忽略字段，不允许提交过来的字段
     */
    protected array $addExpectField = [];

    /**
     * 详情接口字段，默认为对应数据表全部字段，包括验证规则
     */
    protected array $infoField = [];

    /**
     * 删除前置检测条件，是否有被使用的数据，如果有，则不让删除。并提示，默认不检测，直接删除数据 false = 未被使用
     */
    protected bool $isDeleteUsed = false;

    /**
     * 用于记录执行的sql语句
     */
    protected string $sql = '';

    /**
     * 导入图片字段的名称
     */
    private string $importTitleField = '';

    /**
     * 初始化方法
     */
    public function initialize()
    {
        /**
         * 全局接收请求的参数
         */
        if (!empty($this->inputField)) {
            $tmpField = $this->inputField;
        } else {
            $tmpField = '';
        }
        $this->inputData = $this->request->param($tmpField);

        // 提取请求条件 模糊查询 精准查询
        if (isset($this->inputData['search']) && $this->inputData['search'] != '') {
            foreach ($this->inputData['search'] as $item) {
                $search = json_decode($item,true);
                if (is_field_exists($search,'operator')) {
                    switch ($search['operator']) {
                        case '=':
                            $this->focus[$search['field']] = $search['val'];
                            break;
                        case 'LIKE':
                        case 'like' :
                            $this->vague[$search['field']] = $search['val'];
                            break;
                        case 'RANGE' :
                        case 'range' :
                            if (false !== strrpos($search['val'],',')) {
                                $rangeArr = explode(',',$search['val']);
                                if ($search['render'] == 'datetime') {
                                    foreach ($rangeArr as $key => $range) {
                                        $rangeArr[$key] = strtotime($range);
                                    }
                                }
                                $this->range[$search['field']] = $rangeArr;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        // 提取请求条件 排序字段
        if (isset($this->inputData['order']) && $this->inputData['order'] != '' && (false !== strrpos($this->inputData['order'],','))) {
            $order = explode(',',$this->inputData['order']);
            if (count($order) === 2) {
                $this->order[$order[0]] = $order[1];
            }
        }

        /**
         * 回写管理员信息
         */
        $this->adminInfo = $this->request->user_info ?? [];
        /**
         * 更新登录用户的token有效时间
         */
    }

    /**
     * 处理数据结构
     * @param array $data   请求接收到的数据
     * @param array $name   数据库字段，需要查询的字段名称
     * @param string $type  构建查询数据类型，只有3种。vague=模糊，focus=准确，order=排序
     * @param array $condition  条件，比如此字段值不能为空，或者不能等于0之类的。['',0],主要是前端请求提交过来的值，当这个条件成立的时候，相应搜索条件不成立
     * @return bool
     */
    public function doDataStructure(array $data,array $name,string $type = 'vague',array $condition = ['','0']):bool
    {
        if (empty($data) || empty($condition) || empty($type) || empty($name)) {
            return false;
        }
        //定义type的类型，只有3种。模糊，准确，排序
        if (in_array($type,['vague','focus','order'])) {
            foreach ($name as $value) {
                if (isset($data[$value]) && !in_array($data[$value],$condition,true)) {
                    $this->$type[$value] = $data[$value];
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 公共方法验证器
     * @param string $sceneName 对应场景
     * @param array $data   需要验证的数据，数组结构
     */
    public function commonValidate(string $sceneName,array $data) :bool
    {
        if(!$this->validate->scene($sceneName)->check($data)) {
            if (false === strrpos($this->validate->getError(),'|')) {
                $this->code = -1;
                $this->msg  = $this->validate->getError();
            } else {
                $err = explode('|',$this->validate->getError());
                if (count($err) > 1) {
                    $this->code = (int)$err[1];
                    $this->msg  = $err[0];
                } else {
                    $this->code = -1;
                    $this->msg  = 'error validate message';
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 控制器返回方法
     * @param array|string $msg     返回的消息
     * @param array|bool $result    返回的结果
     */
    public function jr(mixed $msg, mixed $result = false): \think\Response\Json
    {
        if (is_array($msg)) {
            if (count($msg) === 2) {
                $this->msg  = $result ? $msg[1] : $msg[0];
            } else {
                //如果只传一个值。
                $this->msg = $msg[0];
            }
        } elseif (is_string($msg)) {
            $this->msg = $msg;
        } else {
            $this->msg = $msg ? $msg : 'error invalid message';
        }

        // 增加自定义返回条件兼容
        if ($this->status !== 200 && $this->status !== 504) {
            // 增加自定义状态和code的定义
            $this->returnData = is_bool($result) ? [] : $result;
            $this->code = !empty($result) || is_bool($result) ? 0 : 1;

        } elseif ($this->code !== 0 && $this->code !== 1) {
            // 增加自定义状态和code的定义
            $this->returnData = is_bool($result) ? [] : $result;
            $this->status = !empty($result) || is_bool($result) ? 200 : 504;
        } else {
            $this->code = $result ? 0 : 1;
            $this->status = $result ? 200 : 504;
            $this->returnData = is_bool($result) ? [] : $result;
        }

        // 存入接口请求日志
        $pathInfo = $this->request->pathinfo();
        $routeArr = explode('/',$pathInfo);
        if (count($routeArr) === 2) {
            $controller = array_pop($routeArr);
            $version = array_pop($routeArr);
            // 取方法名，
            $action = match ($this->request->method()) {
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

        // 接口日志
        $logData = [
            "admin_id"	    =>	$this->adminInfo['id'] ?? 0,
            "admin_name"    =>	$this->adminInfo['username'] ?? '未登录',
            "user_agent"    =>	$this->request->header('user_agent'),
            "app_name"	    =>	app('http')->getName(),
            "method"	    =>	$this->request->method(),
            "version"	    =>	$version??'v1',
            "controller"    =>	$controller??'',
            "action"	    =>	$action??'',
            "url"	        =>	$this->request->url(true),
            // 页面标题
            "title"	        =>	'',
            "code"	        =>	$result ? 200 : 504,
            "params"	    =>	json_encode($this->request->param()),
            "result"	    =>  json_encode(!is_array($result) ? [] : $result),
            "sql"	        =>	$this->sql,
            "ip"	        =>	$this->request->ip(),
            "waste_time"	=>	round(microtime(true) - $this->app->getBeginTime(),2),
            "data_create_time"	=>	date('Y-m-d H:i:s',time()),
            "create_time"	=>	time(),
            "update_time"	=>	time()
        ];
        if (!Env::get('app_debug')) {
            AsyncDoo::asyncApiLog($logData);
        } else {
            ApiLog::create($logData);
        }
        // 全局返回
        $data = [
            'status'        => $this->status,
            'code'          => $this->code,
            'data'          => $this->returnData,
            'message'       => $this->msg,
            'time'          => time(),
            'date'          => date('Y-m-d H:i:s',time())
        ];

        /**
         * http 状态码
         * 信息响应 (100–199)
         * 成功响应 (200–299)
         * 重定向消息 (300–399)
         * 客户端错误响应 (400–499)
         * 服务端错误响应 (500–599)
         */
        if ($this->status > 1000 || $this->status < 100) {
            return json('严重错误：请填写正确状态码【信息响应 (100–199)，成功响应 (200–299)，重定向消息 (300–399)，客户端错误响应 (400–499)，服务端错误响应 (500–599)】',504);
        } else {
            return json($data,$this->status);
        }

    }

    /**
     * 显示资源列表
     */
    public function index() :\think\Response\Json
    {
        try {
            // 增加插入式钩子，如果需要额外增手动增加字段到请求数据列表里面，使用params
            if (!empty($this->params)) {
                $this->inputData = array_merge($this->inputData,$this->params);
            }
            //判断是否需要分页
            if (is_field_exists($this->inputData,'page',4)) {
                $this->page = (int)$this->inputData['page'];
            }
            //判断是否需要分页
            if (is_field_exists($this->inputData,'size',4)) {
                $this->page = (int)$this->inputData['size'];
            }

            // 列表输出字段
            if (isset($this->indexField) && !empty($this->indexField)) {
                $this->field = $this->indexField;
            }

            $result = $this->model->getIndexList($this->page,$this->size,$this->field,$this->vague,$this->focus,$this->order,$this->range);

            $this->sql = $this->model->getLastSql();
            //构建返回数据结构
            return $this->jr('获取成功',!empty($result) ? $result : true);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('详情数据异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 显示指定的资源
     */
    public function read():\think\Response\Json
    {
        try {
            //前置拦截
            if (!isset($this->inputData['id']) || (int)$this->inputData['id'] <= 0) {
                return $this->jr('请输入需要获取的id值');
            }
            //额外增加请求参数
            if (!empty($this->params)) {
                $this->inputData = array_merge($this->inputData,$this->params);
            }
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            // 列表输出字段
            if (isset($this->infoField) && !empty($this->infoField)) {
                $this->field = $this->infoField;
            }
            $result = $this->model->getInfo((int)$this->inputData['id'],[],$this->field);
            $this->sql = $this->model->getLastSql();
            return $this->jr(['获取失败','获取成功'],$result);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('详情数据异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 保存新建的资源
     */
    public function save():\think\Response
    {
        try {
            //前置拦截
            if (empty($this->inputData)) {
                return $this->jr('请检查提交过来的数据');
            }
            // 额外增加请求参数
            if (!empty($this->params)) {
                $this->inputData = array_merge($this->inputData,$this->params);
            }
            // 保存单独的请求参数
            if (isset($this->addField) && !empty($this->addField)) {
                $this->inputData = array_merge($this->inputData,$this->addField);
            }
            // 忽略指定忽略字段
            if (!empty($this->addExpectField)) {
                foreach ($this->addExpectField as $field) {
                    if (isset($this->inputData[$field])) {
                        unset($this->inputData[$field]);
                    }
                }
            }
            // 验证器
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }

            $result = $this->model->addData($this->inputData);
            $this->sql = $this->model->getLastSql();
            return $this->jr(['新增失败','新增成功'],$result);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('新增异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 保存更新的资源
     */
    public function update():\think\Response\Json
    {
        try {
            //前置拦截
            if (!isset($this->inputData['id']) || (int)$this->inputData['id'] <= 0) {
                return $this->jr('请输入正确的需要修改的ID值');
            }
            //额外增加请求参数
            if (!empty($this->params)) {
                $this->inputData = array_merge($this->inputData,$this->params);
            }
            // 保存单独的请求参数
            if (isset($this->editField) && !empty($this->editField)) {
                $this->inputData = array_merge($this->inputData,$this->editField);
            }
            // 忽略指定忽略字段
            if (!empty($this->editExpectField)) {
                foreach ($this->editExpectField as $field) {
                    if (isset($this->inputData[$field])) {
                        unset($this->inputData[$field]);
                    }
                }
            }
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            $result = $this->model->editData($this->inputData);
            $this->sql = $this->model->getLastSql();
            return $this->jr(['修改失败','修改成功'],$result);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('修改数据异常，请查看异常日志或者日志文件进行修复');
        }
    }

    /**
     * 删除指定资源
     */
    public function delete():\think\Response\Json
    {
        try {
            //前置拦截
            if (!isset($this->inputData['id']) || (int)$this->inputData['id'] <= 0) {
                return $this->jr('请输入需要删除的ID值');
            }
            //额外增加请求参数
            if (!empty($this->params)) {
                $this->inputData = array_merge($this->inputData,$this->params);
            }
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }
            // 增加删除关联引用查询
            if (!$this->isDeleteUsed) {
                $result = $this->model->delData($this->inputData);
            } else {
                return $this->jr('删除失败！该数据被引用，不能删除，请解除引用关系再删除！');
            }
            $this->sql = $this->model->getLastSql();
            return $this->jr(['删除失败','删除成功'],$result);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('删除异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 通用上传类，主要是本地文件
     */
    public function upload():\think\Response\Json
    {
        try {
            $file = $this->request->file('file');
            $upload     = new Upload($file);
            $attachment = $upload->upload(null, $this->adminInfo['id']);
            unset($attachment['create_time'], $attachment['quote']);
            $this->sql = $this->model->getLastSql();
            return $this->jr(['上传文件失败','上传文件成功'],$attachment);
        } catch (\Exception|FileException $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('上传异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 上传图片并转换成webp格式，支持多图上传
     */
    public function uploadImage():\think\Response\Json
    {
        try {
            $files = $this->request->file();
            if (!$files) {
                return $this->jr("请选择上传的文件");
            }
            $data = $localFile = [];

            foreach ($files as $key => $file) {
                if (is_array($file)) {
                    // 单上传名称，多图上传 同一名称的数组文件上传.image[0] image[1]
                    foreach ($file as $key1 => $file1) {
                        if ($this->commonValidate(__FUNCTION__, [$key => $file1])) {
                            return $this->jr($this->validate->getError());
                        }
                        //上传本地
                        $tempFile = Filesystem::disk('public')->putFile('',$file1,'unique_id');
                        $localFile[$key1] = [
                            'file'       => $tempFile,
                            'real_name' => $file1->getOriginalName(),
                            'md5'       => md5_file($file1->getPathname())
                        ];
                    }
                } else {
                    //多上传名称，单图上传  //不同字段文件名上传 image img icon
                    // 单名称，单图上传
                    if ($this->commonValidate(__FUNCTION__, [$key => $file])) {
                        return $this->jr($this->validate->getError());
                    }
                    //上传本地
                    $tempFile = Filesystem::disk('public')->putFile('',$file,'unique_id');
                    $localFile[$key] = [
                        'file'       => $tempFile,
                        'real_name' => $file->getOriginalName(),
                        'md5'       => md5_file($file->getPathname())
                    ];
                }
            }
            // 上传云存储或者本地数据组装
            if (!empty($localFile)) {
                foreach ($localFile as $filename) {
                    $localPath = Env::get('upload.local_path','storage').DIRECTORY_SEPARATOR.$filename['file'];
                    $source = public_path() . $localPath;
                    $destination = $source . '.webp';
                    $options = [];
                    WebPConvert::convert($source, $destination, $options);
                    $data[] = [
                        'cdn'       => Env::get('upload.cdn'),
                        'origin'    => $filename['real_name'],
                        'file'       => $filename['file'],
                        'md5'       => $filename['md5'],
                        'url'       => Env::get('upload.cdn').$localPath,
                        'r_path'    => $localPath,
                        'w_path'    => $localPath.'.webp'
                    ];
                    // 表示是否要本地存储，如果不需要，则删除本地
                    if (!Env::get('upload.is_local_exists',true)) {
                        @unlink($source);
                    }
                }
            }
            $this->sql = $this->model->getLastSql();
            return $this->jr('上传成功',$data);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('上传文件异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * 拖拽排序
     */
    public function sortable():\think\Response
    {
        try {
            //前置拦截
            if (empty($this->inputData)) {
                return $this->jr('请检查提交过来的数据');
            }
            // 额外增加请求参数
            if (!empty($this->params)) {
                $this->inputData = array_merge($this->inputData,$this->params);
            }
            // 保存单独的请求参数
            if (isset($this->editField) && !empty($this->editField)) {
                $this->inputData = array_merge($this->inputData,$this->addField);
            }
            // 忽略指定忽略字段
            if (!empty($this->editExpectField)) {
                foreach ($this->editExpectField as $field) {
                    if (isset($this->inputData[$field])) {
                        unset($this->inputData[$field]);
                    }
                }
            }
            // 验证器
            if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
                return $this->message(true);
            }

            $result = $this->model->sortable($this->inputData);
            $this->sql = $this->model->getLastSql();
            return $this->jr(['更新排序失败','更新排序成功'],$result);
        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
            return $this->jr('新增异常，请查看异常日志或者日志文件进行修复');
        }

    }

    /**
     * Excel导入
     * @return Json
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function import():\think\Response\Json
    {
        $file = $this->request->file('file');
        if (!$file) {
            return $this->jr('没有上传文件');
        }
        if ($this->commonValidate(__FUNCTION__,['file'=>$file])) {
            return $this->message(true);
        }
        $filename = Filesystem::disk('public')->putFile('', $file, 'unique_id');
        $filePath = public_path().'storage/'  . $filename;
        if (!is_file($filePath)) {
            return $this->jr('没找到数据');
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            return $this->jr('文件格式不对');
        }
        if ($ext === 'csv') {
            $file = fopen($filePath, 'r');
            $filePath = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp = fopen($filePath, "w");
            $n = 0;
            while ($line = fgets($file)) {
                $line = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding != 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($file) || fclose($fp);

            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
            $imageReader = IOFactory::createReader('Xls');
        } else {
            $reader = new Xlsx();
            $imageReader = IOFactory::createReader('Xlsx');
        }
        //加载文件
        $insert = [];
        try {
            if (!$PHPExcel = $reader->load($filePath)) {
                return $this->jr('没找到数据');
            }
            // 读取文件中的第一个工作表
            $currentSheet = $PHPExcel->getSheet(0);
            // 读取文件中的图片
            $imageWorkSheet = $imageReader->load($filePath)->getSheet(0);
            // 找到图片集合
            $imagesCollection = $imageWorkSheet->getDrawingCollection();
            // 取得最大的列号
            $allColumn = $currentSheet->getHighestDataColumn();
            // 取得一共有多少行
            $allRow = $currentSheet->getHighestRow();
            // 获取最大列数
            $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);

            $fields = [];
            for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $fields[] = trim($val);
                }
            }
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $values = [];
                // 第一行表格的位置 a2 b2 c2 d2 ……
                $coordinates = [];
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $coordinates[] = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getCoordinate();
                    $values[] = is_null($val) ? '' :  (is_string($val) ? trim($val) : $val);
                }
                // 如果是空行，直接过滤掉
                if (count(array_unique($values)) <= 1 ) {
                    unset($values);
                    continue;
                }

                $row = [];
                // 组装每一行的数据
                $temp = array_combine($fields, $values);
                $temp1 = array_combine($fields, $coordinates);
                foreach ($temp as $k => $v) {
                    // 判断是否是图片
                    $fileName = '';
                    if ($this->importTitleField[$k] == 'image' || $this->importTitleField[$k] == 'cover') {
                        // 暂时不考虑图片
                        continue;
                        // 执行保存图片
                        $imagePath = Config::get('filesystem.disks.images.url').DIRECTORY_SEPARATOR.date('Ymd',time()).DIRECTORY_SEPARATOR;
                        if (!is_dir(public_path().$imagePath)) {
                            mkdir(public_path().$imagePath,0777,true);
                        }

                        foreach ($imagesCollection as $images) {
                            $imagesFileName = $images->getCoordinates().'-'. md5(chr(mt_rand(1,256)));
                            if ($temp1[$k] == $images->getCoordinates()) {
                                switch ($images->getExtension()) {
                                    case 'jpg':
                                    case 'jpeg':
                                        $imagesFileName .= '.jpeg';
                                        $source = imagecreatefromjpeg($images->getPath());
//                                      dump($source);
                                        $jpg = imagejpeg($source,public_path().$imagePath.$imagesFileName);
                                        break;
                                    case 'gif' :
                                        $imagesFileName .= '.gif';
                                        $source = imagecreatefromgif($images->getPath());
                                        $gif = imagegif($source,public_path().$imagePath.$imagesFileName);
                                        break;
                                    case 'png':
                                        $imagesFileName .= '.png';
                                        $source = imagecreatefrompng($images->getPath());
                                        $png = imagepng($source,public_path().$imagePath.$imagesFileName);
                                        break;
                                }
                                // 将本地文件上传到七牛云
                                $source = $imagePath.$imagesFileName;
//                                $localFile = rtrim(public_path(),'/'). $source;
//                                // 上传云存储或者本地数据组装
//                                if (file_exists($localFile)) {
//                                    // 判断是否上传七牛云
//                                    if (!Env::get('upload.IS_UPLOAD_CLOUD',true)) {
//                                        $q = $qn[$bucket];
//                                        $result = $this->$q->uploadLocalFileToQnCloud($localFile,ltrim($source,'/'));
//                                        dump($result);
//                                        if (!empty($result)) {
//                                            $fileName = $result['filename'];
//                                        }
//                                    }
//                                }
                                $row[$this->importTitleField[$k]] = ltrim($source,'/');
                            }
                        }
                    } else {
                        if (isset($this->importTitleField[$k]) && $k != '') {
                            $row[$this->importTitleField[$k]] = $v;
                        }
                    }
                }
                if (!empty($row)) {
                    $insert[] = $row;
                }
            }
            //需要关联查询的字段，进行关联相询翻译
        } catch (\Exception $exception) {
            return $this->jr($exception->getMessage());
        }
        //批量新增
        try {
            $count = 0;
            $failCount = 0;
//            foreach ($insert as $item) {
//                if ($this->commonValidate(__FUNCTION__,$item)) {
//                    $failCount++;
//                    continue;
//                }
            $res = $this->model->save($insert[0]);
//                $count++;
//            }

            if (count($insert) > $count) {
                $this->code = 0;
                $this->status = 504;
                $this->msg = '总共【'.count($insert).'】，成功导入【'.$count.'】条记录,还有【'.(count($insert) - $count).'】条记录未导入成功';
            } else {
                $this->code = 1;
                $this->status = 200;
                $this->msg = '总共【'.count($insert).'】，成功导入【'.$count.'】条记录,有【'.(count($insert) - $count).'】条记录未导入成功';
            }
            return json($this->message());

        } catch (PDOException $exception) {
            $this->msg = $exception->getMessage();
            if (preg_match("/.+Integrity constraint violation: 1062 Duplicate entry '(.+)' for key '(.+)'/is", $this->msg, $matches)) {
                $this->msg = "导入失败，包含【{$matches[1]}】的记录已存在";
            }
            $this->code = 0;
            $this->status = 504;
            return json($this->message());
        } catch (\Exception $e) {
            $this->code = 0;
            $this->status = 504;
            $this->msg = $e->getMessage();
            return json($this->message());
        }
    }

    /**
     * Excel导出，
     * @param array $data
     * @param int $count
     * @param string $fileName
     * @param array $options
     * @return Json
     */
    protected function excelExport(array $data = [], int $count = 10 ,string $fileName = '', array $options = []):\think\Response\Json
    {
        try {
            if (empty($data)) {
                $this->msg = '没有选择需要导出的数据！';
                return json($this->message());
            }
            set_time_limit(0);
            $objSpreadsheet = new Spreadsheet();
            //设置全局字体，大小
            $styleArray = [
                'font' => [
                    'bold' => false,
                    'color' => ['rgb'=>'000000'],
                    'size' => 14,
                    'name' => 'Verdana'
                ]
            ];
            $objSpreadsheet->getDefaultStyle()->applyFromArray($styleArray);
            /* 设置默认文字居左，上下居中 */
            $styleArray = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ];
            $objSpreadsheet->getDefaultStyle()->applyFromArray($styleArray);
            /* 设置Excel Sheet */
            $activeSheet = $objSpreadsheet->setActiveSheetIndex(0);

            /* 打印设置 */
            if (isset($options['print']) && $options['print']) {
                /* 设置打印为A4效果 */
                $activeSheet->getPageSetup()->setPaperSize(PageSetup:: PAPERSIZE_A4);

                /* 设置打印时边距 */
                $pValue = 1 / 2.54;
                $activeSheet->getPageMargins()->setTop($pValue / 2);
                $activeSheet->getPageMargins()->setBottom($pValue * 2);
                $activeSheet->getPageMargins()->setLeft($pValue / 2);
                $activeSheet->getPageMargins()->setRight($pValue / 2);
            }

            $row = 2;
            $col = 0;
            /* 行数据处理 */
            foreach ($data as $sKey => $sItem) {
                /* 默认文本格式 */
                $pDataType = DataType::TYPE_STRING;
                /* 设置单元格格式 */
                if (isset($options['format']) && !empty($options['format'])) {
                    $colRow = Coordinate::coordinateFromString($sKey);

                    /* 存在该列格式并且有特殊格式 */
                    if (isset($options['format'][$colRow[0]]) &&
                        NumberFormat::FORMAT_GENERAL != $options['format'][$colRow[0]]) {
                        $activeSheet->getStyle($sKey)->getNumberFormat()
                            ->setFormatCode($options['format'][$colRow[0]]);

                        if (false !== strpos($options['format'][$colRow[0]], '0.00') &&
                            is_numeric(str_replace(['￥', ','], '', $sItem))) {
                            /* 数字格式转换为数字单元格 */
                            $pDataType = DataType::TYPE_NUMERIC;
                            $sItem     = str_replace(['￥', ','], '', $sItem);
                        }
                    } elseif (is_int($sItem)) {
                        $pDataType = DataType::TYPE_NUMERIC;
                    }
                }

                if ($col < count($options['alignCenter'])) {
                    if (strlen($sItem) <= 255) {
                        $activeSheet->getColumnDimension($options['alignCenter'][$col])->setWidth(100);
                    } else {
                        $activeSheet->getColumnDimension($options['alignCenter'][$col])->setAutoSize(true);
                    }
                }
                $activeSheet->getRowDimension($row)->setRowHeight(30);
                $activeSheet->setCellValueExplicit($sKey, $sItem, $pDataType);
                $row++;
                $col++;
                /* 存在:形式的合并行列，列入A1:B2，则对应合并 */
                if (str_contains($sKey, ":")) {
                    $options['mergeCells'][$sKey] = $sKey;
                }
                if (isImage(public_path().$sItem) && file_exists(public_path().$sItem)) {
                    $activeSheet->setCellValueExplicit($sKey, '', $pDataType);
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('Logo');
                    $drawing->setPath(Env::get('root_path').'public'.$sItem);
                    $drawing->setResizeProportional(false);
                    $drawing->setHeight(60);
                    $drawing->setCoordinates($sKey);
                    $drawing->setOffsetX(12);
                    $drawing->setOffsetY(12);
                    $drawing->getShadow()->setVisible(true);
//                    $drawing->getShadow()->setDirection(45);
                    $drawing->setWorksheet($objSpreadsheet->getActiveSheet());
                }
            }
            unset($data);
            /* 设置锁定行 */
            if (isset($options['freezePane']) && !empty($options['freezePane'])) {
                $activeSheet->freezePane($options['freezePane']);
                unset($options['freezePane']);
            }
            /* 设置宽度 */
            if (isset($options['setWidth']) && !empty($options['setWidth'])) {
                foreach ($options['setWidth'] as $swKey => $swItem) {
                    $activeSheet->getColumnDimension($swKey)->setWidth($swItem);
                }
                unset($options['setWidth']);
            } else {
                $end = min($count + 64, 80);
                foreach(range(chr(65),chr($end)) as $columnID) {
                    $activeSheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            }
            /* 设置背景色 */
            if (isset($options['setARGB']) && !empty($options['setARGB'])) {
                foreach ($options['setARGB'] as $sItem) {
                    $activeSheet->getStyle($sItem)
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB(Color::COLOR_YELLOW);
                }

                unset($options['setARGB']);
            }
            /* 设置公式 */
            if (isset($options['formula']) && !empty($options['formula'])) {
                foreach ($options['formula'] as $fKey => $fItem) {
                    $activeSheet->setCellValue($fKey, $fItem);
                }

                unset($options['formula']);
            }
            /* 合并行列处理 */
            if (isset($options['mergeCells']) && !empty($options['mergeCells'])) {
                $activeSheet->setMergeCells($options['mergeCells']);
                unset($options['mergeCells']);
            }
            /* 设置居中 */
            if (isset($options['alignCenter']) && !empty($options['alignCenter'])) {

                foreach ($options['alignCenter'] as $acItem) {
                    $activeSheet->getStyle($acItem)->applyFromArray($styleArray);
                }

                unset($options['alignCenter']);
            }
            /* 设置加粗 */
            if (isset($options['bold']) && !empty($options['bold'])) {
                foreach ($options['bold'] as $bItem) {
                    $activeSheet->getStyle($bItem)->getFont()->setBold(true);
                }

                unset($options['bold']);
            }
            /* 设置单元格边框，整个表格设置即可，必须在数据填充后才可以获取到最大行列 */
            if (isset($options['setBorder']) && $options['setBorder']) {
                $border    = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN, // 设置border样式
                            'color'       => ['argb' => 'FF000000'], // 设置border颜色
                        ],
                    ],
                ];
                $setBorder = 'A1:' . $activeSheet->getHighestColumn() . $activeSheet->getHighestRow();
                $activeSheet->getStyle($setBorder)->applyFromArray($border);
                unset($options['setBorder']);
            }

            $fileName = !empty($fileName) ? $fileName : (date('YmdHis') . '.xlsx');

            if (!isset($options['savePath'])) {
                /* 直接导出Excel，无需保存到本地，输出07Excel文件 */
                header('Content-Type: application/vnd.ms-excel,application/x-rar-compressed,application/vnd.openxmlformats-officedocument.wordprocessingml.document; Charset=UTF-8');
                header('Access-Control-Expose-Headers: Content-Disposition');
                header(
                    "Content-Disposition:attachment;filename=" . iconv(
                        "utf-8", "GB2312//TRANSLIT", $fileName
                    )
                );
                header('Cache-Control: max-age=0');//禁止缓存
                header("Content-Transfer-Encoding:binary");
                $savePath = 'php://output';
            } else {
                $savePath = $options['savePath'];
            }
            ob_clean();
            ob_start();
            $objWriter = IOFactory::createWriter($objSpreadsheet, 'Xlsx');
            $objWriter->save($savePath);
            /* 释放内存 */
            $objSpreadsheet->disconnectWorksheets();
            unset($objSpreadsheet);
            ob_end_flush();
            exit;
        } catch (\Exception $e) {
            $this->msg = $e->getMessage();
            return json($this->message());
        }
    }

    /**
     * 表格导出数据前置方法
     */
    public function export():\think\Response\Json
    {
        $ids = $this->request->param();
        if ($this->commonValidate(__FUNCTION__,$ids)) {
            return json($this->message(true));
        }
        $data = $this->model->getExportData($ids);
        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $this->importHeadType = 'name';
        $table = $this->model->getTable();
        $database = Env::get('database.database');
        //字段名与注释的数组，
        $fieldArr = [];
        $list = Db::query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        foreach ($list as $v) {
            if ($this->importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_COMMENT'];
            }
        }

        $newData = [];/*表格数据*/
        $newHeader = [];/*表格表头*/
        $cols = []; /*分别占用表格哪几列*/
        if (array_key_exists(0,$data)) {
            $header = array_keys($data[0]);
            foreach ($header as $k => $v) {
                if ($k <= 25) {
                    $newHeader[chr($k+65).'1'] = $fieldArr[$v]?:$v;
                    $cols[$k] = chr($k+65);
                } else {
                    $newHeader[chr(65).chr($k-26+65).'1'] = $fieldArr[$v]?:$v;
                    $cols[$k] = chr(65).chr($k-26+65);
                }

            }
        }

        foreach ($data as $k => $v) {
            $index = 0;
            foreach ($v as $v1) {
                if ($index <= 25) {
                    $header = chr($index+65);
                    $header .= $k+2;
                    $newData[$header] = $v1;/*获取表头*/
                } else {
                    $header = chr(65).chr($index-26+65);
                    $header .= $k+2;
                    $newData[$header] = $v1;/*获取表头*/
                }
                $index++;
            }
        }
        $newData = array_merge($newHeader,$newData);
        $options = [
            'print' =>false,
            'freezePane'=>'A2',
            // 设置列宽
            //'setWidth'=>['A'=>40,'B'=>30,'C'=>20,'D'=>25,'E'=>20,'F'=>15,'G'=>10,'H'=>10],
            'setBorder'=>true,
            'alignCenter'=>$cols,
            'bold'=>array_keys($newHeader),
        ];
        return $this->excelExport($newData,count($newHeader),'export-excel-'.time().'.xlsx',$options);
    }

    /**
     * curl请求
     * @param $url  string 请求的url链接
     * @param $data string|array|mixed 请求的数据
     * @param bool $is_post 是否是post请求，默认false
     * @param array $options 是否附带请求头
     */
    public function curlHttp(string $url, array $data, bool $is_post=false, array $options=[]):array
    {
        $data  = json_encode($data);
        $headerArray = [
            'Content-type: application/json;charset=utf-8',
            'Accept: application/json'
        ];
        $curl = curl_init();
        $arr = [];
        array_push($arr,$url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);
        if ($is_post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($options['cookie'])) {
            curl_setopt($curl, CURLOPT_COOKIE, $options['cookie']);
        } else {
            $headerArray = array_merge($headerArray,$options);
        }
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($curl);
        $http_status = curl_errno($curl);
        $http_msg = curl_error($curl);
        curl_close($curl);
        if ($http_status == 0) {
            return json_decode($output, true);
        } else {
            return ['status' => $http_status, 'message' => $http_msg, 'data' => []];
        }
    }

    /**
     * 打印调试信息到日志
     * @param $data
     * @param string $string
     */
    public function dLog($data, string $string = 'debug'): void
    {
        $newData = [];
        if (is_array($data)) {
            $newData = json_encode($data);
        } else {
            $newData = $data;
        }
        \think\facade\Log::record($string. '==' . $newData);
    }
}
