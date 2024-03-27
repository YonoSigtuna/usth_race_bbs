


<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>论坛主页</title>
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/home_page.css">

</head>
<body>
	<?php
		// 引入顶部导航栏
		require_once dirname(__FILE__).'/header.php';

		// 浏览量 + 1
		mysqli_query($link, "UPDATE sys_auto_increment_value SET value = value + 1 WHERE variable='views';");
	?>








	<br>
	<br>








	<!-- TITLE分割 -->
	<div class="title_split_line" style="width: 35%;">
		<img src="./data/imgs/view.png" loading="lazy" alt="图片加载失败">
		<span>前端 / 后端学术交流论坛</span>
	</div>







	
	<!-- 右侧操作版块 -->
	<div class="board sub_block">
		<header>最新发帖</header>
		<main>
			<ul class="newest_topics">
				<?php
					// 获取最新5个tid
					$data = mysqli_query($link, "SELECT * FROM topics_index ORDER BY tid DESC LIMIT 5;");

					// 循环每个tid找到对应的帖子数据
					while ($row = $data->fetch_assoc()) {
						$fid = $row['fid'];
						$tid = $row['tid'];

						// 获取tid最后一位
						$tid_last_char = substr($tid, -1);

						// 根据tid最后一位查表
						$title = mysqli_query($link, "SELECT title FROM `topics_${fid}_{$tid_last_char}` WHERE tid={$tid} LIMIT 1");
						$title = $title->fetch_assoc()['title'];

						// 输出li标签
						echo "<li><a href='./view_topic.php?tid=$tid&page=0' target='_blank'>$title</a></li>";
					}
				?>
			</ul>
		</main>

		<!-- 热门标签10个 -->
		<header>热门Tag</header>
		<main class="box_father">
			<?php 
				$tags = mysqli_query($link, "SELECT tag FROM `tags_index` ORDER by count DESC LIMIT 20;");
				while ($row = $tags->fetch_assoc()) {
					$tag =  $row['tag'];

					echo "<p class='box'>$tag</p>";
				}
			?>
			<br>
		</main>


		<!-- 论坛负载 -->
		<header>论坛负载</header>
		<main>
			<?php
				// 获取最新tid - 1
				$tids = get_value("sys_auto_increment_value", "value", "variable='tid'") - 1;

				// 获取uid总数
				$uids = get_value("sys_auto_increment_value", "value", "variable='uid'") - 1;

				// 获取浏览总量
				$views = get_value("sys_auto_increment_value", "value", "variable='views'");

				// 获取服务器占用空间
				$server_ram = get_value("sys_auto_increment_value", "value", "variable='server_ram'");

				// 获取数据库占用空间
				$mysql_ram = get_value("sys_auto_increment_value", "value", "variable='mysql_ram'");
			?>
			<ul>
				<li>论坛总贴数：<span><?php echo $tids; ?>贴</span></li>
				<li>论坛总人数: <span><?php echo $uids; ?>人</span></li>
				<li>今日浏览量: <span><?php echo $views; ?>次</span></li>
				<li>储存：<span><?php echo $server_ram; ?>GB</span></li>
				<li>备份：<span>1/8TB</span></li>
				<li>数据库：<span><?php echo $mysql_ram; ?>MB</span></li>
				<li>往返延迟：<span id="ping_test">测试中...</span></li>
			</ul>
		</main>
	</div>











	<div class="board block">
		<header>
			<span>前端版块</span>
			<span>前端开发即创建Web页面或app等前端界面给用户的过程。</span>
		</header>

		<main>
			<div>
				<!-- fid=1-1 -->
				<div class="section">
					<img src="./data/imgs/board/1-1.png" loading="lazy" alt="图片加载失败">
					<span class="name">
						<a href="./forum_card.php?fid=1-1&page=0" target="_blank">「HTML」</a>
						<span>总贴数: <?php echo get_topics_count("1-1") ?></span>
					</span>
					<span class="info">HTML的全称为超文本标记语言，是一种标记语言。</span>
					<span class="data" title="最新帖子"><?php echo get_newest_topic("1-1"); ?></span>
				</div>

				<!-- fid=1-2 -->
				<div class="section section_r">
					<img src="./data/imgs/board/1-2.png" loading="lazy" alt="图片加载失败">
					<span class="name">
						<a href="./forum_card.php?fid=1-2&page=0" target="_blank">「JS」</a>
						<span>总贴数: <?php echo get_topics_count("1-2") ?></span>
					</span>
					<span class="info">JavaScript是一种轻量化，解释型，即时编译型的编程语言。</span>
					<span class="data" title="最新帖子"><?php echo get_newest_topic("1-2"); ?></span>
				</div>
			</div>

			<!-- 第二行 -->
			<div style="margin-top: 1.5%;">

				<!-- fid=1-3 -->
				<div class="section">
					<img src="./data/imgs/board/1-3.png" loading="lazy" alt="图片加载失败">
					<span class="name">
						<a href="./forum_card.php?fid=1-3&page=0" target="_blank">「CSS」</a>
						<span>总贴数: <?php echo get_topics_count("1-3") ?></span>
					</span>
					<span class="info">是一种用来表现HTML或XML等文件样式的计算机语言。</span>
					<span class="data" title="最新帖子"><?php echo get_newest_topic("1-3"); ?></span>
				</div>

				<!-- fid=1-4 -->
				<div class="section section_r">
					<img src="./data/imgs/board/1-4.png" loading="lazy" alt="图片加载失败">
					<span class="name">
						<a href="./forum_card.php?fid=1-4&page=0" target="_blank">「其他前端语言」</a>
						<span>总贴数: <?php echo get_topics_count("1-4") ?></span>
					</span>
					<span class="info">Just living in the Database</span>
					<span class="data" title="最新帖子"><?php echo get_newest_topic("1-4"); ?></span>
				</div>
			</div>
		</main>
	</div>












	<div class="board block">
		<header>
			<span>后端版块</span>
			<span>后端开发的重点在于算法设计、数据结构、性能优化等方面</span>
		</header>
		<main>
			<!-- 第一行 -->
			<div>

				<!-- fid=2-1 -->
				<div class="section" id="fid_2-1">
					<img src="./data/imgs/board/2-1.png" loading="lazy" alt="图片加载失败">
					<span class="name">
						<a href="./forum_card.php?fid=2-1&page=0" target="_blank">「PHP」</a>
						<span>总贴数: <?php echo get_topics_count("2-1") ?></span>
					</span>
					<span class="info" title="亲情、友情、爱情，还是烦恼、困惑、忧愁，体味人间的真情~">亲情、友情、爱情，还是烦恼、困惑、忧愁，体味人间的真情~</span>
					<span class="data" title="最新帖子"><?php echo get_newest_topic("2-1"); ?></span>
				</div>

				<!-- fid=2-2 -->
				<div class="section section_r">
					<img src="./data/imgs/board/2-2.png" loading="lazy" alt="图片加载失败">
					<span class="name">
						<a href="./forum_card.php?fid=2-2&page=0" target="_blank">「MYSQL」</a>
						<span>总贴数: <?php echo get_topics_count("2-2") ?></span>
					</span>
					<span class="info">永不终结的世界图书馆，兼以各类文学作品。</span>
					<span class="data" title="最新帖子"><?php echo get_newest_topic("2-2"); ?></span>
				</div>
			</div>
		</main>
	</div>









	



	<div class="board block">
		<header>
			<span>综合交流区</span>
			<span>与服务器度过的每一日，都是无可替代的珍贵时光。</span>
		</header>
		<main>
			<!-- 第一行 -->
			<div>
				<!-- fid=3-1 -->
				<div class="section">
					<img src="./data/imgs/board/3-1.png" loading="lazy" alt="图片加载失败">
					<span class="name">
						<a href="./forum_list.php?fid=3-1&page=0" target="_blank">「前后端疑难解惑」</a>
						<span>总贴数: <?php echo get_topics_count("3-1"); ?></span>
					</span>
					<span class="info">在这里你不懂的，我们都会帮助你解决！</span>
					<span class="data" title="最新帖子"><?php echo get_newest_topic("3-1"); ?></span>
				</div>

				<!-- fid=3-2 -->
				<div class="section section_r">
					<img src="./data/imgs/board/3-2.png" loading="lazy" alt="图片加载失败">
					<span class="name">
						<a href="./forum_list.php?fid=3-2&page=0" target="_blank">「前后端进阶优化」</a>
						<span>总贴数: <?php echo get_topics_count("3-2"); ?></span>
					</span>
					<span class="info" title="欢笑着，嬉闹着，奔跑着，而累了。休息后抬头仰望，被那深邃蓝色所吸引，回神过来，已置身于璀璨星海中。">欢笑着，嬉闹着，奔跑着，而累了。休息后抬头仰望，被那深邃蓝色所吸引，回神过来，已置身于璀璨星海中。</span>
					<span class="data" title="最新帖子"><?php echo get_newest_topic("3-2"); ?></span>
				</div>
			</div>
		</main>
	</div>







	<!-- 资讯推送 -->
	<div class="board news">
		<header>「论坛建立详细」</header>
		<div name="分割线"></div>
		<main>
			本站仅用于参赛演示使用。<br><br>
			提供2个账号用于演示：<br>
			管理员账号admin，密码ss123321<br>
			普通用户账号lzh，密码ss123321<br><br>
			网站前后端开发：XXX<br>
		</main>
	</div>




















<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>