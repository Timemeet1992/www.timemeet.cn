<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\User;
use think\Session;
class Auth extends Controller
{
	public function getregname(){
		$_REQUEST['username'] = 'jqy';
		$user = new User();
		if(empty($_REQUEST['username'])){
			die();
		}
		$var = $user->where('username',$_REQUEST['username'])->find();
		if(!$var){
			echo json_encode(['status' => 1, 'msg' => '用户名可用', 'data' => []]);die();
		}
		if($_REQUEST['username'] == $var->username){
			echo json_encode(['status' => 0, 'msg' => '用户名已存在', 'data' => []]);die();
		}else{
			echo json_encode(['status' => 1, 'msg' => '用户名可用', 'data' => []]);die();
		}
	}
	

}