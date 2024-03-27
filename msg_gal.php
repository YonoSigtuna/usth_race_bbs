<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>旧站资源收入</title>
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/home_page.css">

	<style>
		/* 每条通知 */
		.msgs li {
			text-align: left;
			overflow: auto;
		}

		.none_read {
			background-color: #D6790E;
		}
	</style>

</head>
<body>
	<!-- 引入顶部导航栏 -->
	<?php require_once dirname(__FILE__).'/header.php'; ?>
	<?php
		// 未登录处理
		if (!$uid) {
			exit("<script>alert('当前页面需要登录才能查看内容')</script>");
		}
	?>





	

	<!-- TITLE分割 -->
	<div class="title_split_line" style="width: 35%;">
		<span>旧站资源收入</span>
	</div>


	<div class="board else" style="width: 90%">
		<header>
			<span>提取码:2333，解压码:fleeworld.top 或 飞越地平线fleeworld.top</span><br>
		</header>
		<main>
			<ul class="msgs">
	<?php
		$titles = mysqli_query($link, "SELECT subject, tid FROM pre_forum_post WHERE position=1 AND subject LIKE '%.《%》《%》' ORDER BY tid DESC LIMIT 1000 ;");

		// 每个每行
		while ($row = $titles->fetch_assoc()) {

			// 获取需要的数据
			$title =  $row['subject'];
			$tid = $row['tid'];

			// 根据tid查找百度网盘链接
			$pan = mysqli_query($link, "SELECT value FROM pre_forum_typeoptionvar WHERE tid=$tid AND optionid=14 LIMIT 1");
			$pan = $pan->fetch_assoc()['value'];

			echo "<li>${title}：<a onclick='request_old_download($tid)' href='#'>请求下载（一天限3次）</a></li>";
		}


	?>
			</ul>
		</main>
	</div>



<script>

	alert("推荐使用论坛左上角的搜索功能来查找想要的GAL")

	const request_old_download = (tid) => {
		// 构建xhr请求
		var data = new FormData()
		data.append("cmd", "request_old_download")
		data.append("tid", tid);

		// 发送xhr
		var xhr = new XMLHttpRequest()
		xhr.open("POST", './server.php', true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				// 获取xhr返回值
				var return_data = xhr.responseText

				if (return_data == 'refuse') {
					alert('你今日下载次数已超过限制。（默认用户一天限制3次下载请求）')
				} else {
					window.open(return_data, "_blank")
				}
			}
		}
	}




</script>


</body>




















<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>