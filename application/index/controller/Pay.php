<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\Model\User;
use app\index\Model\News;

class Pay extends Controller
{
	public function pay()
	{
		return $this->fetch('pay');
	}	
	public function dopay()
	{
		$news = new News();
		$user = new User();

		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}
		if ($_POST['username'] != $_POST['reusername']) {
			$this->error('两次账号不一致，请重新输入','index/pay/pay');
		} else {
			$data['supposing'] = $_POST['username'];
		}
		
		$res = $user->where('username',$data['supposing'])->find();
		if (empty($res)) {
			$this->error('账号不存在，请重新输入', 'index/pay/pay');
		}
		$data['data'] = $_POST['money'];
		$news->data([
			'supposing' => $data['supposing'],
			'data' => $data['data'],
			'field' => 'money',
			'supfield' => 'username',
			'tablename' => 'phone_user',
			'type' => '充值'
			]);
		$result =  $news->save();
		if ($result) {
			$this->success('信息已提交,待审核', 'index/pay/pay');
		} else {
			$this->error('信息提交失败', 'index/pay/pay');
		}
	}
}