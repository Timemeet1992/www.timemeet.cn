<?php
namespace app\index\controller;
use think\Controller;

class Service extends Controller
{
	public function service(){
		return $this->fetch('service');
	}
}