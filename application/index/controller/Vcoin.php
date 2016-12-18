<?php
namespace app\index\controller;
use think\Controller;

class Vcoin extends Controller
{
	public function vcoin()
	{
		return $this->fetch('vcoin');
	}
}