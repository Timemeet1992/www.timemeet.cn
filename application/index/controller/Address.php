<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\Model\Addr;
use app\index\Model\User;

class Address extends Controller
{
	//显示地址页面
	public function address()
	{
		$addr = new Addr();
		$user = new User();
		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}
		$result = json_decode(json_encode($addr->where('auid',$uid)->select()));
		$res = $user->where('uid',$uid)->value('mailaddr');
		//dump($result);
		//dump($res);
		$this->assign('addrs',$result);
		$this->assign('def',$res);
		return $this->fetch('address');
	}
	//添加地址
	public function doaddress()
	{
		$addr = new Addr();
		$user = new User();
		//dump($_POST);
		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}
		$data['addr'] = $_POST['address'];
		if (empty($data['addr'])) {
			$this->error('请填写详细地址','index/address/address');
		}
		$data['name'] = $_POST['receiverName'];
		if (empty($data['name'])) {
			$this->error('请填写收件人姓名','index/address/address');
		}
		$data['phone'] = $_POST['mobilePhone'];
		if (empty($data['phone'])) {
			$this->error('请输入手机号码','index/address/address');
		}
		$data['create_time'] = time();
		$result = $addr->data([
			'addr' => $data['addr'],
			'name' => $data['name'],
			'phone' => $data['phone'],
			'create_time' => $data['create_time'],
			'auid' => $uid
			]);
		$result->save();
		if ($result) {
			$this->success('添加成功','index/address/address');
		} else {
			$this->error('添加失败', 'index/address/address');
		}
		
		if ($_POST['defaultAddr'] == 1) {
			$res = $addr->where('create_time',$data['create_time'])->where('auid',$uid)->value('aid');
			if (!$res) {
				$this->error('添加默认地址失败','index/address/address');
			}
			$fuck = $user->save(['mailaddr' => $res],['uid' => $uid]);			
		}
		if ($fuck) {
			$this->success('添加成功','index/address/address');
		} else {
			$this->error('添加失败', 'index/address/address');
		}
	}
	//设置默认地址
	public function dodefault()
	{
		//dump($_GET);
		$user = new User();
		$aid = $_GET['aid'];

		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}
		$result = $user->save(['mailaddr' => $aid],['uid' => $uid]);
		if ($result) {
			$this->success('设置默认地址成功','index/address/address');
		} else {
			$this->error('设置默认地址失败','index/address/address');
		}
	}	
}