<?php

namespace Mobile\Controller;

use Think\Controller;

	class GoodsQueryController extends Controller{
		public function querry(){
			$this->assign("uri", __ROOT__ . "/");
			$this->display();
		}
	}