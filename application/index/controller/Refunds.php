<?php
namespace app\index\controller;
use think\Controller;

class Refunds extends Controller
{
	public function refunds()
	{
		return $this->fetch('refunds');
	}
}