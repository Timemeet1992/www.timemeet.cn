<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\model\User;

class Data extends Controller
{
	public function data()
	{
		return $this->fetch('data');
	}
	public function dodata()
	{
		$user = new User();
			
		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}	
		$data['realname'] = $_POST['realname'];
		$data['sex'] = $_POST['sex'];
		$data['email'] = $_POST['email'];
		$res = $user->save([
			'realname' => $_POST['realname'],
			'sex' => $_POST['sex'],
			'email' => $_POST['email']
			],['uid' => $uid]);
		//dump($res);
		if ($res) {
			$this->success('保存成功', 'index/data/data');
		} else {
			$this->error('保存失败', 'index/data/data');
		}
	}
}
