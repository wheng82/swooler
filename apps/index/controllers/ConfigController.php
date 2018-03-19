<?php
/**
 * Created by PhpStorm.
 * User: wheng
 * Date: 2018/3/16
 * Time: 下午3:17
 */

namespace apps\index\controllers;

use \PhpMyAdmin\SqlParser\Parser;
use \PhpMyAdmin\SqlParser\Utils\Query;
use mix\exception\Err;
use mix\web\Controller;
use mix\web\UploadFile;
use \Swoole\Coroutine;
use apps\index\models\IndexForm;

class ConfigController extends Controller
{
    // system mix app create obj
    private $db;
    private $rdb;
    private $req;
    private $sess;
    private $log;
    // request need
    private $sql = '';
    // explain config create var
    private $sql_maintable = '';
    private $tablearr = array();

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->req = array_merge(\Mix::app()->request->get(), \Mix::app()->request->post());
        $this->db = \Mix::app()->rdb; //mysql pdo rdb connect
        $this->rdb = \Mix::app()->redis;
        $this->sess = \Mix::app()->session;
        $this->log = \Mix::app()->log;
    }

    public function actionSelectsql(){
        $this->explainsql();  //safe check and set sql maintable
        $onerow = $this->db->createCommand($this->sql)->queryOne();
        foreach($onerow as $k => $v){
            $res['data']['k'][]['name'] = $k;
            $res['data']['k'][]['readOnly'] = false;
        }
        $res['date']['v'][] = $onerow;
        $res['code'] = 0;
        return $res;
    }

    public function actionSaveconfig(){
        $this->explainsql();
        extract($this->req);
        $data = [
            [   'config' => $config,
                'maintable' => $this->sql_maintable,
                'parser' => json_encode($this->tablearr),
                'remark' => $remark,
                'sql' => $sql,
                ],
        ];
        $success = $this->db->batchInsert('sys_sqltable', $data)->execute();
        $affectedRows = $this->db->getRowCount();
        $row['data']['tid'] = $this->db->getLastInsertId();
        if($affectedRows > 0){
            $res['code'] = 0;
            return $res;
        }
    }

    private function add(){
        
    }

    private function explainsql(){
        $this->sql = $this->req['sql'];
        $sql = strtolower($this->sql);
        if($this->selectsqlcheck() === false){
            throw new Err('sql include insert update delete or not have select',701);
        }else{
            $this->tablearr = $this->parser();
            $this->sql_maintable = $this->tablearr['from'][0]['table'];
            return;
        }
    }

    //sql safe check
    private function selectsqlcheck(){
        $safe_sql = array("insert","update","delete","flush","privileges","grant","drop","set","explain","rename");
        foreach ($safe_sql as $k=>$val){
            if(strpos($this->sql,$val)!= false) return false;
        }
        if(is_numeric(strpos($this->sql,'select'))) return true;
        return false;
    }

    private function parser(){
        $parser = new \PhpMyAdmin\SqlParser\Parser($this->sql);
        $param = $parser->statements[0];
        $param = $this->object_to_array($param);
        return $param;
    }

    private function object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)$this->object_to_array($v);
            }
        }
        return $obj;
    }
}