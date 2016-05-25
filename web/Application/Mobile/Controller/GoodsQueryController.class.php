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
		public function goodspricelist(){
			$this->assign("uri", __ROOT__ . "/");
			$this->display();
		}
		public function querylist(){
			$this->assign("uri", __ROOT__ . "/");
			if (IS_POST) {
				$goodsname=I("post.goodsname");
				$warehouse=I("post.warehouse");
				$list= new GoodsQueryService();
				$this->ajaxReturn($list->goodlist($goodsname, $warehouse));
			}
			
		}
		public function goodsinfo(){
			$this->assign("uri", __ROOT__ . "/");
			$this->display();
		}
		public function queryinfo(){
			$this->assign("uri", __ROOT__ . "/");
			if (IS_POST) {
				$gcode=I("post.gcode");
				$warehouse=I("post.warehouse");
				$list= new GoodsQueryService();
				$this->ajaxReturn($list->goodinfos($gcode,$warehouse));
			}
		}
	}
	