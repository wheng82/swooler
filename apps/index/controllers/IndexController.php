<?php

namespace apps\index\controllers;

use mix\web\Controller;
use mix\web\UploadFile;
use Swoole\Coroutine;
use apps\index\models\IndexForm;

class IndexController extends Controller
{

    private $db;
    private $rdb;
    private $req;
    private $sess;
    private $log;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->req = array_merge(\Mix::app()->request->get(),\Mix::app()->request->post());
        $this->db = \Mix::app()->rdb; //mysql pdo rdb connect
        $this->rdb = \Mix::app()->redis;
        $this->sess = \Mix::app()->session;
        $this->log = \Mix::app()->log;
    }

    // 默认动作
    public function actionIndex(){

        return 'Hello World' . PHP_EOL;
    }

    public function actionRDB(){

        // input form get
        \Mix::app()->varDump($this->req);
        sleep(2);

        // mysql test sync
        $data = [
            'name'    => 'xiaoliu',
            'content' => 'hahahaha',
        ];
        $this->db->insert('test', $data)->execute();
        $insertId = $this->db->getLastInsertId();
        \Mix::app()->varDump($insertId);

        // redis test  sync
        $this->rdb->set('key', 'value');
        $this->rdb->setex('key', 7200, 'value');
        $myinsert = $this->rdb->get('key');
        \Mix::app()->varDump($myinsert);

        // log test
        $message = "abccc";
        $this->log->info($message);
        $this->log->debug($message);

        // session test
        $userinfo = [
            'uid'      => 1088,
            'openid'   => 'yZmFiZDc5MjIzZDMz',
            'username' => '小明',
        ];
        $this->sess->set('userinfo', $userinfo);
        $sess = $this->sess->get('userinfo');
        \Mix::app()->varDump($sess,true);  //如果想打印dump 只有最后一个dump用true 参数 否则内容输出在命令行
        return $userinfo;  // 直接返回json格式数据  web/Response.php defaultFormat

    }

    //upload  and  resize jpg system
    public function actionUpload(){
        $file = UploadFile::getInstanceByName('upfile');
        $extname = $file->getExtension();
        $access = array("txt","docx");
        if(in_array($extname,$access)){
            $newname = $file->getRandomName();
            $filepath = "/opt/swooler/apps/index/public/static/img/";
            $file->saveAs($filepath.$newname);
        }else{
            throw new \Error("no access this ext file",701);
        }

        $res['code'] = 0;
        $res['filepath'] = $filepath.$newname;
        $res['url'] = "http://www.xxx.com/a/b/c/".$newname;
        return $res;

    }

    // async mysql client  test check
    public function actionComysql(){
        $mysql = new \Swoole\Coroutine\MySQL();
        $res = $mysql->connect(['host' => '127.0.0.1', 'user' => 'root', 'password' => 'square82', 'database' => 'wheng']);
        $mysql->setDefer();

        if ($res == false) {
            //$response->end("MySQL connect fail!");
            return;
        }
        $mysql->query('select sleep(11)');  //并不支持多开  在一个线程waitpid时候其他不能用同一个对象开

        return;
    }

    // async curl client
    public function actionCocurl(){
        $cli = new \Swoole\Coroutine\Http\Client('127.0.0.1', 9501);
        $cli->setHeaders([
            'Host' => "localhost",
            "User-Agent" => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set([ 'timeout' => 0.1]);  //这个timeout 不是超过这个时间就不请求了，而是超过这个时间就把cpu交给后面的任务
        $cli->setDefer();  //声明延迟收包
        $cli->get('/index/rdb/');
        $cli->recv();
        $cli->get('/index/rdb/');
        $cli->recv();
        $cli->get('/index/rdb/');
        $cli->recv();
        $cli->get('/index/rdb/');
        $cli->recv();
        //var_dump($cli->body());  //如果想要结果 从这里拿 这是同步逻辑
        return true;
    }

    // async 直接调用用户函数 或者执行命令行
    public function actionCofunc(){
        \Swoole\Coroutine::set(array(
            'max_coroutine' => 4096,
        ));
        \Swoole\Coroutine::call_user_func($this->actionIndex());  //调度类库方法
        \Swoole\Coroutine::call_user_func($this->actionIndex());
        \Swoole\Coroutine::call_user_func($this->actionIndex());
        \Swoole\Coroutine::call_user_func($this->actionIndex());
        //$cmd = "/user/bin/php index.php a b";
        //$cmd = \Swoole\Coroutine::exec($cmd); //直接调度命令行
        return;
    }

//    // API 范例
//    public function actionApiExample()
//    {
//        // 使用模型
//        $model             = new IndexForm();
//        $model->attributes = \Mix::app()->request->get() + \Mix::app()->request->post();
//        $model->setScenario('test');
//        if (!$model->validate()) {
//            return ['code' => 1, 'message' => '参数格式效验失败', 'data' => $model->errors];
//        }
//        $model->save();
//        // 响应
//        \Mix::app()->response->format = \mix\web\Response::FORMAT_JSON;
//        return ['code' => 0, 'message' => 'OK'];
//    }
//
//    // WebSite 范例
//    public function actionWebSiteExample()
//    {
//        // 使用模型
//        $model             = new IndexForm();
//        $model->attributes = \Mix::app()->request->get() + \Mix::app()->request->post();
//        $model->setScenario('test');
//        if (!$model->validate()) {
//            return $this->render('web_site_example', ['message' => '参数格式效验失败', 'errors' => $model->errors]);
//        }
//        $model->save();
//        // 响应
//        return $this->render('web_site_example', ['message' => '新增成功']);
//    }

}
