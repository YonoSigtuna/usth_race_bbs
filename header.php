
<?php 
	// 获取访问域名
	$domain = $_SERVER['HTTP_HOST'];
	$request_url = $_SERVER['REQUEST_URI'];

	// 判断是否为usth项目链接
	if ($request_url == "/") {
		if (strstr($domain, "usth")) {
			header("Location: http://usth.fleeworld.top/bbs/index.php");
		}
	}
?>

















<?php
	// 必要的库
	require_once dirname(__FILE__).'/conn.php';
	require_once dirname(__FILE__).'/functions.php';
?>





<!-- 随机背景图片 -->
<head>
	<style>
		body {
			background-image: url(
				<?php
					// 获取所有背景
					$bgs = get_files(dirname(__FILE__)."/data/imgs/bgs");
					
					// 随机取一个背景
					$file_name = $bgs[array_rand($bgs, 1)];
					
					// 空格进行有效URL替换
					$file_name = str_replace(" ", "%20", $file_name);
					echo "'./data/imgs/bgs/$file_name'";
				?>
			);
			background-size: cover;
			background-position: center top;
			background-repeat: no-repeat;
			background-attachment: fixed;
			background-color: #FDEDC2;
		}
	</style>
</head>





<!-- 获取用户sessionID对应的uid -->
<?php
	// 前端存在sessionID
	if ($_COOKIE['sessionID']) {
		// 根据sessionID查找uid
		$uid = get_uid();
		$uid_last_char = substr($uid, -1);

		// uid不存在
		if (!$uid) {
			// 删除cookie
			setcookie('sessionID', '', time() - 2592000000, '/');
			exit('你当前登录已过期，请刷新页面重新登录！');
		}
	}
?>


<!-- 导航栏 -->
<div class="header">
	<img class="logo" src="./data/imgs/logo.png" title="返回主页" onclick="window.location.href = './index.php'" loading="lazy" alt="图片加载失败">
	<div class="search">
		<input type="text" placeholder="整站全文搜索"><span onclick="search()"></span>
	</div>

	<span class="text uname"><a href="
		<?php
			// 已登录
			if ($uid > 0) {
				echo "./user_admin.php";

			// 未登录
			} else {
				echo './register.php';
			}
		?>
	">
		<?php
			// 已登录
			if ($uid > 0) {

				// 根据uid查找uname
				$uname = get_uname($uid);
				echo $uname;

			// 未登录
			} else {
				echo '未登录(注册 / 登录)';
			}
		?>
	</a></span>

	<img class="avatar" src="./data/avatars/
		<?php
			// 判断头像存在
			if (file_exists("./data/avatars/${uid}_small.jpg")) {
				echo "${uid}_small.jpg";

			// 头像不存在
			} else {

				// 取所有头像文件
				$imgs = scandir(dirname(__FILE__).'/data/avatars/random');

				// 删去0键和1键，0为"."本级目录，1为".."上级目录，和缩略图缓存db
				unset($imgs[0]);
				unset($imgs[1]);
				unset($imgs[array_search('Thumbs.db', $imgs)]);

				// 随机取一个头像
				echo 'random/' . $imgs[array_rand($imgs, 1)];
			}
		?>
	" loading="lazy" alt="图片加载失败">

	<a href="./msg_system.php" target="_blank"><span class="msg"><img src="
		<?php
			$result = mysql_exist(sprintf("logs_%s", date('Y')), "uid", "0", "AND `read`=0");

			// 存在未读信息
			if ($result == 1) {
				echo "./data/imgs/msg_system.png";

			// 全部已读
			} else {
				echo "./data/imgs/msg_system_1.png";
			}

		?>
	" loading="lazy" alt="图片加载失败"></span></a>


	<a href="./msg.php" target="_blank"><span class="msg"><img src="
		<?php
			// 未登录 或 登录过期
			if (!$uid) {
				echo "./data/imgs/msg_none.png";

			// 已登录
			} else {
				// 判断用户是否有未读通知
				$result = mysqli_query($link, sprintf("SELECT IF(count(uid) > 0, 1, 0) from logs_%s WHERE uid=$uid and `read`=0", date('Y')));
				$result = $result->fetch_assoc()['IF(count(uid) > 0, 1, 0)'];

				// 存在返回1，不存在返回0
				if ($result == 1) {
					echo "./data/imgs/msg.png";
				} else {
					echo "./data/imgs/msg_none.png";
				}
			}
		?>
	" loading="lazy" alt="图片加载失败"></span></a>

	<span class="text broadcast">本站仅用于比赛交流演示！</span>
</div>













<?php
	// 更新最新日期
	$today = get_time("Y-m-d");
	if (get_value("sys_auto_increment_value", "value", "variable='today'") != $today) {
		
		// 更新日期
		mysqli_query($link, "UPDATE sys_auto_increment_value SET value = '${today}' WHERE variable='today' LIMIT 1;");

		// 清空今日浏览数
		mysqli_query($link, "UPDATE sys_auto_increment_value SET value='0' WHERE variable='views' LIMIT 1");

		// 获取根目录占用空间，转成GB
		$free_space = disk_free_space(dirname(__FILE__));
		$total_space = round($free_space / (1024 * 1024 * 1024), 2);

		// 更新今日硬盘占用
		mysqli_query($link, "UPDATE sys_auto_increment_value SET value='$total_space' WHERE variable='server_ram' LIMIT 1");

		// 获取数据库容量
		$sql_space = mysqli_query($link, "SELECT table_schema AS 'Database', SUM(data_length + index_length) / 1024 / 1024 AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema = 'fleedata' GROUP BY table_schema;");
		$sql_space = $sql_space->fetch_assoc()["Size (MB)"];
		$sql_space = number_format($sql_space, 2);

		// 更新今日数据库占用
		mysqli_query($link, "UPDATE sys_auto_increment_value SET value='$sql_space' WHERE variable='mysql_ram' LIMIT 1");

		// 
		// 获取今日推荐11个GAL
		// 
		$result = mysqli_query($link, "SELECT tid FROM topics_index WHERE fid='1-1' AND tid > 0 ORDER BY RAND() LIMIT 11;");

		while ($row = $result->fetch_assoc()) {
			// 获取tid
			$tid = $row['tid'];

			// 合并
			$tids = $tids . "$tid||";
		}

		// 去掉最后2个||
		$tids = substr($tids, 0, -2);

		mysqli_query($link, "UPDATE sys_auto_increment_value SET value='$tids' WHERE variable='recommend' LIMIT 1");
	}
?>













<script>
	function search() {
		// 获取搜索内容
		var content = document.querySelector(".search input").value

		// 搜索内容不存在
		if (!content) {
			alert("未输入搜索内容")

		// 搜索内容存在跳转
		} else {
			window.open(`./search.php?content=${content}`, "_blank")
		}
	}
</script>




