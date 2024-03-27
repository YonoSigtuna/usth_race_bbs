<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>搜索结果</title>
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
		<span>搜索结果</span>
	</div>


	<div class="board else" style="width: 90%">
		<header>
			<span>搜索方式为整站全文搜索，需要消耗一定时间请耐心等待</span><br>
		</header>
		<main>
			<ul class="msgs">
				<?php
					// 获取URL中的搜索内容
					$content = $_GET['content'];

					// 从数据库中匹配搜索
					$tids = mysqli_query($link, "SELECT * FROM topics_index ORDER BY tid DESC");

					// 循环所有tid
					while ($row = $tids->fetch_assoc()) {
						$fid = $row['fid'];
						$tid = $row['tid'];
						$tid_last_char = substr($tid, -1);

						// 根据fid和tid查找匹配内容
						$result = mysqli_query($link, "SELECT title, uid, date FROM `topics_${fid}_${tid_last_char}` WHERE (title LIKE '%$content%' OR content LIKE '%$content%') AND tid IN (SELECT tid FROM `topics_${fid}_${tid_last_char}` WHERE tid=$tid);");
						$result = $result->fetch_assoc();

						// 获取标题
						$title = $result['title'];
						if ($title) {
							
							// 获取各项数据
							$uid = $uid;
							$uname = get_uname($uid);
							$date = $result['date'];
							$board_name = get_board_name($fid);
							// 2024-01-25 21:40 -> lzh_2(1) 在版块XXX发帖 这是帖子标题 跳转<a href='./view_topic.php?tid=$tid&page=0' target='_blank'>$title</a>

							$format = "$date -> $uname($uid) 在版块 $board_name 发帖 <a href='./view_topic.php?tid=$tid&page=0' target='_blank'>$title</a>";

							// 标题包含
							if (strstr($title, $content)) {
								echo "<li><span class='box none_read'>标题包含</span>$format</li>"; 

							// 内容包含
							} else {
								echo "<li><span class='box'>内容包含</span>$format</li>";
							}
						}
					}
				?>
			</ul>
		</main>
	</div>
</body>




















<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>