<?php
	
	// 
	// 
	// 获取用户对GAL的评分和状态
	// 
	// 
	function get_rating($tid, $uid="") {
		global $link;
		$tid_last_char = substr($tid, -1);

		// 用户评分
		if ($uid) {
			$result = mysqli_query($link, "SELECT date, score, state FROM `rating_$tid_last_char` WHERE tid=$tid AND uid=$uid");
			$result = $result->fetch_assoc();
			// 0 -> date
			// 1 -> score
			// 2 -> state

			// 如果评分不存在
			if (!$result) {
				$result['date'] = "无";
				$result['score'] = 0;
				$result['state'] = "未进行";
			}
			return $result;

		// 总评分
		} else {
			$tid_last_char = substr($tid, -1);
			$result = mysqli_query($link, "SELECT ROUND(AVG(score), 1) AS average, COUNT(tid) AS ratings FROM rating_$tid_last_char WHERE tid=$tid; ")->fetch_assoc();
			// ['average']
			// ['ratings']

			// 如果评分不存在
			if ($result['average'] == null) {
				$result['average'] = 0;
			}
			return $result;
		}
		
	}




	// 
	// 
	// 日志记录
	// 
	// 
	// \$user -> lzh_2(1)
	// \$title -> feng下的夏天
	function log_add($uid, $content, $tid="") {
		global $link;
		$date = get_time("Y-m-d H:i");

		// 用户替换
		if(strstr($content, "\$user")) {
			$uname = get_uname($uid);
			$content = str_replace("\$user", "<a href='./space.php?uid=$uid' target='_blank'>$uname($uid)</a>", $content);
		}

		// 帖子标题替换
		if(strstr($content, "\$title")) {
			$title = get_topic($tid, "title");
			$content = str_replace("\$title", "<a href='./view_topic.php?tid=$tid&page=0' target='_blank'>$title</a>", $content);
		}

		// 日志记录
		$year = get_time("Y");
		mysqli_query($link, "INSERT INTO `logs_$year` (uid, date, content, `read`) VALUES ('0', '$date', \"$content\", '0'); ");
	}
	
	
	
	
	
	// 
	// 
	// 获取用户头像
	// 
	// 
	function get_avatar($uid, $mod="small") {
		// 判断头像存在
		if (file_exists("./data/avatars/${uid}_small.jpg")) {
			// 小头像
			if ($mod == "small") {
				return "${uid}_small.jpg";
			}

			// 大头像
			if ($mod == "big") {
				return "${uid}.jpg";
			}

		// 头像不存在
		} else {
			// return "9.jpg";
			// 取所有头像文件
			$imgs = scandir(dirname(__FILE__).'/data/avatars/random');

			// 删去0键和1键，0为"."本级目录，1为".."上级目录，和缩略图缓存db
			unset($imgs[0]);
			unset($imgs[1]);
			unset($imgs[array_search('Thumbs.db', $imgs)]);

			// 随机取一个头像
			return ("random/" . $imgs[array_rand($imgs, 1)]);
		}
	}



	// 
	// 
	// 获取版块名字
	// 
	// 
	function get_board_name($fid) {
		// 定义版块名字
		$board = array(
			'1-1' => '「HTML」',
			'1-2' => '「JS」',
			'1-3' => '「CSS」',
			'1-4' => '「其他前端语言」',
			'2-1' => '「PHP」',
			'2-2' => '「MYSQL」',
			'3-1' => '「前后端疑难解惑」',
			'3-2' => '「前后端进阶优化」'
		);

		$name = $board[$fid];
		return($name);
	}



	// 
	// 
	// 加密，有效期5分钟
	// 
	// 
	function encode($text) {
		global $link;

		// 加密信息并储存进数据库
		$timestamp = time();
		$md5 = md5($text . '||' . get_uid() . '||'. $timestamp);
		mysqli_query($link, "INSERT INTO encode (md5, text, timestamp) VALUES ('$md5', '$text', $timestamp)");

		return $md5;
	}



	// 
	// 
	// 解密，有效期5分钟
	// 
	// 
	function decode($encode) {
		global $link;

		// 获取MD5对应的timestamp
		$timestamp = time() - 300; // 300秒
		$result = mysqli_query($link, "SELECT text, timestamp FROM encode WHERE md5='$encode' AND timestamp > $timestamp; ");
		$text = $result->fetch_assoc()['text'];

		// 从数据库删除
		mysqli_query($link, "DELETE FROM encode WHERE md5='$encode'; ");

		return $text;
	}



	// 
	// 
	// 判断uid是否为管理员
	// 
	// 
	function administrator($uid) {
		$administrators = [1, 73];
		if (in_array($uid, $administrators)) {
			return 1;
		} else {
			return 0;
		}
	}



	// 
	// 
	//	图片压缩，目前存在文件覆盖的BUG。
	// 
	// 
	function img_zip($sourcePath, $targetPath, $quality, $maxResolution) {
		// 判断文件不存在
		if (!file_exists($sourcePath)) {
			return("图片不存在");
		}
		
		// // 判断目标图片存在
		// if (file_exists($targetPath)) {
		// 	return("目标图片已存在");
		// }

		// 获取图片信息
		$imageInfo = getimagesize($sourcePath);
		$mime = $imageInfo['mime'];
		$width = $imageInfo[0];
		$height = $imageInfo[1];
	
		// 计算压缩后的尺寸（如果超过最大分辨率）
		if ($width > $maxResolution || $height > $maxResolution) {
			$ratio = min($maxResolution / $width, $maxResolution / $height);
			$newWidth = $width * $ratio;
			$newHeight = $height * $ratio;
		} else {
			$newWidth = $width;
			$newHeight = $height;
		}
	
		// 创建画布
		$image = imagecreatefromstring(file_get_contents($sourcePath));
	
		// 创建压缩后的画布
		$compressedImage = imagecreatetruecolor($newWidth, $newHeight);
	
		// 将原始图像复制到压缩画布中，并设置压缩质量
		imagecopyresampled($compressedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
	
		// 保存压缩后的图像
		imagejpeg($compressedImage, $targetPath, $quality);
	
		// 释放资源
		imagedestroy($image);
		imagedestroy($compressedImage);

		return("succ");
	}



	
	// 
	// 
	//	获取前端uid
	// 
	// 
	function get_uid() {
		global $link;

		// 根据sessionID查找uid
		$uid = mysqli_query($link, "SELECT uid FROM users_sessions WHERE sessionID='{$_COOKIE['sessionID']}'");
		$uid = $uid->fetch_assoc()['uid'];

		return($uid);
	}



	// 
	// 
	//	根据uid查找用户名
	// 
	// 
	function get_uname($uid) {
		global $link;

		// 拆分uid最后一位做分表
		$uid_last_char = substr($uid, -1);

		// 根据uid查找用户名
		$uname = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char}` WHERE uid={$uid}");
		$uname = $uname->fetch_assoc()['uname'];

		return($uname);
	}



	// 
	// 
	// 根据tid找fid
	// 
	// 
	function get_fid($tid) {
		global $link;

		// 查 topics_index 表
		$fid = mysqli_query($link, "SELECT fid FROM topics_index WHERE tid=$tid LIMIT 1");
		$fid = $fid->fetch_assoc()['fid'];

		return($fid);
	}



	// 
	// 
	// 根据tid获取帖子内容
	// 
	// 
	function get_topic($tid, $fetch='*') {
		global $link;

		// 根据tid获取fid
		$fid = get_fid($tid);

		// 根据tid和fid查表找帖子数据
		$tid_last_char = substr($tid, -1);
		$data = mysqli_query($link, "SELECT ${fetch} FROM `topics_${fid}_${tid_last_char}` WHERE tid=$tid LIMIT 1");
		$data = $data->fetch_assoc();

		// 根据 $fetch 需要返回
		if ($fetch != "*") {
			return($data[$fetch]);

		// 全返回
		} else {
			return($data);
		}
	}



	// 
	// 
	// 判断某表字段是否存在
	// 
	// 
	function mysql_exist($table, $fetch, $value, $else='') {
		global $link;

		// 判断目标数据库字段是否存在
		$result = mysqli_query($link, "SELECT IF(COUNT($fetch) > 0, 1, 0) FROM `$table` WHERE $fetch='$value' $else LIMIT 1;");
		$result = $result->fetch_row()[0];

		// 存在返回1，不存在返回0
		return($result);
	}



	// 
	// 
	// 删除文件夹
	// 
	// 
	function delete_folder($folder_path) {
		if (!is_dir($folder_path)) { // 验证文件夹是否存在
			return;
		}
	
		$files = array_diff(scandir($folder_path), array('.', '..')); // 获取文件夹中的文件和子文件夹（不包括"."和"..")
	
		foreach ($files as $file) {
			$filePath = $folder_path . '/' . $file;
			
			if (is_dir($filePath)) { // 如果是子文件夹
				delete_folder($filePath);
			} else { // 如果是文件，直接删除
				unlink($filePath);
			}
		}
	
		// 删除空文件夹
		rmdir($folder_path);
	}



	// 
	// 
	// 获取一个文件夹内的文件
	// 
	// 
	function get_files($path) {
		$files = scandir($path);

		// 删去0键和1键，0为"."本级目录，1为".."上级目录，和缩略图缓存db
		unset($files[0]);
		unset($files[1]);
		unset($files[array_search('Thumbs.db', $files)]);

		return($files);
	}



	// 
	// 
	// 获取fid对应的最新贴
	// 
	// 
	function get_newest_topic($fid) {
		global $link;

		// 获取当前fid最新tid
		$tid = mysqli_query($link, "SELECT tid FROM topics_index WHERE fid='$fid' ORDER BY tid DESC LIMIT 1;");
		$tid = $tid->fetch_assoc()['tid'];

		// 获取tid最后一位做分表
		$tid_last_char = substr($tid, -1);

		// 根据tid最后一位查表找最新帖子数据
		$data = mysqli_query($link, "SELECT uid, title, date FROM `topics_${fid}_{$tid_last_char}` WHERE tid={$tid} LIMIT 1;");
		$data = $data->fetch_assoc();
		// ['uid']
		// ['title']
		// ['date']

		// 获取uid对应的用户名
		$uid_ = $data['uid'];
		$uid_last_char_ = substr($uid_, -1);
		$uname_ = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char_}` WHERE uid={$uid_}");
		$data['uname'] = $uname_->fetch_assoc()['uname'];

		// 利用js更新数据
		$time_diff = time_diff("h", $data['date']);
		return("New：<a href='./view_topic.php?tid=$tid&page=0' class='newest_topic'>{$data['title']}</a> || ${time_diff}小时前 || {$data['uname']}");
	}



	// 
	// 
	// 获取fid对应的帖子数量
	// 
	// 
	function get_topics_count($fid) {
		global $link;

		// 获取fid对应的帖子数量
		$count = mysqli_query($link, "SELECT COUNT(fid) FROM topics_index WHERE fid='$fid' ;");
		$count = $count->fetch_assoc()['COUNT(fid)'];
		return($count);
	}



	// 
	// 
	// 计算时间差
	// 
	// 
	function time_diff($style, $time) {
		switch ($style) {
			case 'h':
				// 给定一个时区
				$timezone = new DateTimeZone('Asia/Shanghai');
				
				$datetime = DateTime::createFromFormat('Y-m-d H:i', $time, $timezone);
				$time = $datetime->getTimestamp();

				$diff = round((time() - $time) / 60 / 60);
				return($diff);
				break;

			case 'd':
				break;
		}
	}


	// 
	// 
	// 获取数据表中的一个值
	// 
	// 
	function get_value($table, $value, $if) {
		global $link;

		$result = mysqli_query($link, "SELECT $value FROM $table WHERE $if LIMIT 1");
		$result = $result->fetch_assoc()[$value];

		return($result);
	}





	// 
	// 
	// 格式化tag为字符串
	// 
	// 
	function format_tags_to_str($tid) {
		global $link;

		// 从tid获取tags，并且备份一次为格式化做准备
		$tags = get_topic($tid, "tags");
		$tags_ = $tags;

		if ($tags) {
			
			// 对tags进行拆分
			$tags = explode("|", $tags);

			// 循环每个tag对应的id
			for ($i = 0; $i < count($tags); $i++) {
				$id = $tags[$i];

				// 根据id查找对应的tag字符
				$tag = get_value("tags_index", "tag", "id=$id");

				// 对tags进行恢复
				$tags_ = str_replace($id, $tag, $tags_);
			}

			return($tags_);
		}
	}



	// 
	// 
	// 格式化tag为id
	// 
	// 
	function format_tags_to_id($tid) {
		global $link;

		// 从tid获取tags，并且备份一次为格式化做准备
		$tags = get_topic($tid, "tags");
		$tags_ = $tags;

		if ($tags) {
			
			// 对tags进行拆分
			$tags = explode("|", $tags);

			// 循环每个tag
			for ($i = 0; $i < count($tags); $i++) {
				$tag = $tags[$i];
				
				// 查询tag是否存在id
				$result = mysql_exist('tags_index', 'tag', "$tag");
				
				// 如果tag不存在，则创建tag
				if ($result != 1) {
					mysqli_query($link, "INSERT INTO `tags_index` (tag, count) VALUES ('$tag', 1)");

				// 如果tag存在，tag热度+1
				} else {
					mysqli_query($link, "UPDATE tags_index SET count = count + 1 WHERE tag='$tag'; ");
				}

				// 获取各个tag对应的id
				$id = get_value("tags_index", "id", "tag='$tag'");

				// 格式化tag储存字符串
				$tags_ = str_replace($tag, $id, $tags_);
			}

			// 更新tag
			$fid = get_fid($tid);
			$tid_last_char = substr($tid, -1);
			mysqli_query($link, "UPDATE `topics_${fid}_$tid_last_char` SET tags='$tags_' WHERE tid=$tid LIMIT 1");
		}
	}



	// 
	// 
	// 获取上海时间
	// 
	// 
	function get_time($format='Y-m-d H:i:s') {
		// 给定时区
		$timezone = new DateTimeZone('Asia/Shanghai');
		$date = new DateTime('now', $timezone);
		$date = $date->format($format);
		return($date);
	}

































?>