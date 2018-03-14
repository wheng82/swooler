<?php

namespace apps\index\controllers;

use mix\web\Controller;
use mix\web\UploadFile;
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
