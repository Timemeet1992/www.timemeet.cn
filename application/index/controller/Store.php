<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\Model\User;
use app\index\Model\News;
class Store extends Controller
{
	public function store()
	{
		return $this->fetch('store');
	}
	public function dostore()
	{
		$user = new User();
		$news = new News();
		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}

		$data['realname'] = $_POST['realname'];
		$data['idcard'] = $_POST['idcard'];
		$data['sex'] = $_POST['sex'];
		$data['email'] = $_POST['email'];
		//dump($_POST);
		$result = $user->save([
			'realname' => $data['realname'],
			'idcard' => $data['idcard'],
			'sex' => $data['sex'],
			'email' => $data['email']
			],['uid' => $uid]);
		//dump($result);
		if (!$result) {
			$this->error('输入的信息有误，请重新输入', 'index/store/store');
		}

		$res = $user->where('uid',$uid)->value('level');
		//dump($res);
		if ($res == 0) {
			$news->data([
				'supposing' => $uid,
				'data' => '8',
				'field' => 'level',
				'supfield' => 'uid',
				'tablename' => 'phone_user',
				'type' => '开店申请'
				]);
			$back = $news->save();
			//dump($back);
			if ($back) {
				$this->success('信息已提交,待审核', 'index/store/store');
			} else {
				$this->error('信息提交失败', 'index/store/store');
			}		
		}
	}	
}