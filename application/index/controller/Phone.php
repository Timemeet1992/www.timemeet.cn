<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use think\Db;
use app\index\model\Goods;
use app\index\model\Cla;
use app\index\model\Shop;
class Phone extends Controller
{
	//遍历手机版块
	public function phone()
	{
		$goods = new Goods();
		$cla = new Cla();
    	$cid = empty($_GET['cid'])?null:$_GET['cid'];
    	//查询分类
		$result = $cla->where('fatherid', 1)->where('goodsclass', '<>', 0)->where('isdisplay', 1)->select();
		$result = json_decode(json_encode($result));

		//查询商品数据
    	if($cid){
    		$res = $goods->where('classify', $cid)->where('isfall', 1)->select();
    		$res = json_decode(json_encode($res));
    	}else{
			if(!empty($_GET['sort'])){
				if(($sort = $_GET['sort']) == 1){
		        	$res = Db::table('phone_cla')   
			        ->alias('c')
			        ->where('fatherid',1)->where('isfall', 1)
			        ->join('phone_goods g','c.cid = g.classify')
			        ->order('price', 'desc')
			        ->select();	
				}elseif($sort == 2){
		        	$res = Db::table('phone_cla')   
			        ->alias('c')
			        ->where('fatherid',1)->where('isfall', 1)
			        ->join('phone_goods g','c.cid = g.classify')
			        ->order('create_time', 'desc')
			        ->select();	
				}else{
					$this->error('未定义的排序', 'index/phone/phone');
				}
				
			}else{
		        $res = Db::table('phone_cla')   
		        ->alias('c')
		        ->where('fatherid',1)->where('isfall', 1)
		        ->join('phone_goods g','c.cid = g.classify')
		        ->select();
			}
    		$res = json_decode(json_encode($res));
    	}
    	//dump($res);
    	$this->assign('classname',$result);
    	$this->assign('goods', $res);
		return $this->fetch('phone');
	}

	//遍历分类
	public function phoneone()
	{
		$goods = new Goods();

		if(empty($_GET['gid'])){
			$this->error('您选择的商品不存在','index/parts/parts');
		}else{
			$gid = $_GET['gid'];
	        $res = Db::table('phone_goods')   
	        ->alias('g')
	        ->where('gid',$gid)->where('isfall', 1)
	        ->join('phone_cla c','c.cid = g.classify')
	        ->join('phone_user u','u.uid = g.sellerid')
	        ->select();
	        if(!$res){
	        	$this->error('您选择的商品不存在','index/parts/parts');
	        }

	        if($res){
				$res = json_decode(json_encode($res))[0];
	        }else{
				$res = json_decode(json_encode($res));
	        }
    		
		}
		//dump($res);

		$this->assign('info', $res);
		return $this->fetch('phoneone');
	}


	//立即购买与加入购物车商品提交
	public function dophone()
	{
		$shop = new Shop();
		$goods = new Goods();
		//判断登陆
		$gid = empty($_POST['gid'])?null:$_POST['gid'];
		if(Session::get('uid')){
			$uid = Session::get('uid');
		}else{
			$this->error('请先登陆','admin/login/login');
		}
		//查询商品
		if($gid){
			$result = $goods->where('gid', $gid)->select();

			if(!$result){
				$this->error('商品不存在', url('index/phone/phoneone', ['gid'=>$gid]));
			}
		}else{
			$this->error('您没有操作任何商品');
		}
		$num = empty($_POST['order-num'])?null:$_POST['order-num'];
		//跳转操作
		if(!empty($_POST['action']) && $_POST['action'] == '立即购买'){
			$this->success('正在跳转至购买页', url('index/money/money', ['gid'=>$gid,'num'=>$num]));
		}elseif(!empty($_POST['action']) && $_POST['action'] == '加入购物车'){
			//存入购物车
			$shop->data([
				'goodsid' => $gid,
				'amount' => $num,
				'buyer' => $uid,
			]);
			$res = $shop->save();
			//提示判断
			if($res){
				$this->success('添加至购物车成功', url('index/phone/phoneone', ['gid'=>$gid]));
			}else{
				$this->error('添加至购物车失败', url('index/phone/phoneone', ['gid'=>$gid]));
			}
		}else{
			$this->error('请点击按钮进行提交', url('index/phone/phoneone', ['gid'=>$gid]));
		}
	}
}	