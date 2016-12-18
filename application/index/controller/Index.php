<?php
namespace app\index\controller;
use think\Controller;
use app\admin\model\User;
class Index extends Controller
{
	//首页显示
    public function index()
    {

    	return $this->fetch('index');
    }
}
