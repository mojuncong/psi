<?php

namespace Home\Service;



/**
 * 主菜单Service
 *
 * @author 李静波
 */
class MainMenuService extends PSIBaseService {

	/**
	 * 当前用户有权限访问的所有菜单项
	 */
	public function mainMenuItems() {
		$us = new UserService();
		
		$sql = "select id, caption, fid from t_menu_item
					where parent_id is null order by show_order";
		$db = M();
		$m1 = $db->query($sql);
		$result = array();
		
		$index1 = 0;
		foreach ( $m1 as $menuItem1 ) {
			
			$children1 = array();
			
			$sql = "select id, caption, fid from t_menu_item
						where parent_id = '%s' order by show_order ";
			$m2 = $db->query($sql, $menuItem1["id"]);
			
			// 第二级菜单
			$index2 = 0;
			foreach ( $m2 as $menuItem2 ) {
				$children2 = array();
				$sql = "select id, caption, fid from t_menu_item
							where parent_id = '%s' order by show_order ";
				$m3 = $db->query($sql, $menuItem2["id"]);
				
				// 第三级菜单
				$index3 = 0;
				foreach ( $m3 as $menuItem3 ) {
					if ($us->hasPermission($menuItem3["fid"])) {
						$children2[$index3]["id"] = $menuItem3["id"];
						$children2[$index3]["caption"] = $menuItem3["caption"];
						$children2[$index3]["fid"] = $menuItem3["fid"];
						$children2[$index3]["children"] = array();
						$index3 ++;
					}
				}
				
				$fid = $menuItem2["fid"];
				if ($us->hasPermission($fid)) {
					if ($fid) {
						// 仅有二级菜单
						$children1[$index2]["id"] = $menuItem2["id"];
						$children1[$index2]["caption"] = $menuItem2["caption"];
						$children1[$index2]["fid"] = $menuItem2["fid"];
						$children1[$index2]["children"] = $children2;
						$index2 ++;
					} else {
						if (count($children2) > 0) {
							// 二级菜单还有三级菜单
							$children1[$index2]["id"] = $menuItem2["id"];
							$children1[$index2]["caption"] = $menuItem2["caption"];
							$children1[$index2]["fid"] = $menuItem2["fid"];
							$children1[$index2]["children"] = $children2;
							$index2 ++;
						}
					}
				}
			}
			
			if (count($children1) > 0) {
				$result[$index1] = $menuItem1;
				$result[$index1]["children"] = $children1;
				$index1 ++;
			}
		}
		
		return $result;
	}
}