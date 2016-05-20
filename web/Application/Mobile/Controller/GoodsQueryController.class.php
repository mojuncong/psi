<?php

namespace Mobile\Controller;

use Think\Controller;
use Mobile\Service\GoodsQueryService;

	

	class GoodsQueryController extends Controller{
		public function querygoodsprice(){
			$this->assign("uri", __ROOT__ . "/");
			$this->display();
		}
		
		public function select(){
			$this->assign("uri", __ROOT__ . "/");
			$selected= new GoodsQueryService();
			$this->ajaxReturn($selected->warehouse());
		} 
	}