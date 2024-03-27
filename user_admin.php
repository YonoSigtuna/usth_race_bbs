<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>账号管理</title>
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">

	<style>
		/* 清除内外边距 */
		* {
			margin: 0;
			padding: 0;
		}
	</style>
</head>
<body>
	<!-- 引入顶部导航栏 -->
	<?php require_once dirname(__FILE__).'/header.php'; ?>





	<!-- TITLE分割 -->
	<div class="title_split_line">
		<span>&emsp;Hello！<?php echo $uname;?>&emsp;</span>
	</div>


	<!-- 头像 -->
	<img class="avatar_big" src="./data/avatars/
		<?php
			// 判断头像存在
			if (file_exists("./data/avatars/$uid.jpg")) {
				echo "$uid.jpg";

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
	" loading="lazy" alt="头像加载失败">



	<!-- 账号操作 -->
	<div class="board else">
		<header>
			<span>账号操作</span>
		</header>
		<main>
			<button class="button2" onclick="user_admin('replace_avatar')">更换头像</button>
			<button onclick="user_admin('quet')">退出登录</button>
			<button onclick="const new_psw = prompt('请输入你需要更改的新密码'); user_admin('replace_psw', new_psw)">更改密码</button>
			<button onclick="alert('更改需要联系站长，联系Q群127629029群主即可')">更换B站UID</button>
		</main>
	</div>





	<!-- 作品集 -->
	<?php
		// 获取作品集数据
		$data = mysqli_query($link, "SELECT * FROM users_data_${uid_last_char} WHERE uid=${uid} LIMIT 1");
		$data = $data->fetch_assoc();

		// 在线时间换算成h
		$data['online_time'] = $data['online_time'] / 60;
	?>
	<div class="board else">
		<header>
			<span>作品集</span>
		</header>
		<main>
			个性签名：<input type="text" id="sign" placeholder="Love Forever" value="<?php echo $data['sign']; ?>"><br><br>
			此生挚爱的图片：<input type="text" id="sign_img" placeholder="格式：tid|aid，例如:1|1" value="<?php echo $data['sign_img']; ?>">
			此生挚爱的语言：<input type="text" id="best_love_story" placeholder="友情是宽容，友情是仁慈" value="<?php echo $data['best_love_story']; ?>"><br>
			正在推进的学习：<input type="text" id="playing_story" placeholder="友情是不张扬，不自夸。" value="<?php echo $data['playing_story']; ?>">
			强烈推荐的语言：<input type="text" id="recommend_stories" placeholder="格式：A|B|C|D|E" value="<?php echo $data['recommend_stories']; ?>"><br><br>
			<button onclick="user_data_update()">全部信息提交</button>
		</main>
	</div>





	<!-- 个人数据 -->
	<div class="board else">
		<header>
			<span>其他数据</span>
		</header>
		<main>
			<span class="box">UID：<?php echo $data['uid']; ?></span>
			<span class="box">在线时间：><?php echo $data['online_time']; ?>小时</span>
			<span class="box">身份：<?php echo $data['identity']; ?></span>
			<span class="box">学分：<?php echo $data['credit']; ?>点</span>
			<span class="box">等级：<?php echo $data['academic_year']; ?></span>
			<span class="box">奖学金：<?php echo $data['schoolship']; ?>点</span>
			<span class="box">被处罚：<?php echo $data['judment_count']; ?>次</span>
			<span class="box">发帖数：<?php echo $data['canned_count']; ?>罐</span>
			<span class="box">注册时间：<?php echo $data['register_time']; ?></span>
			<span class="box">最后登录：<?php echo $data['last_login_time']; ?></span>
		</main>
	</div>





<!-- MD5 -->
<script src="https://cdn.bootcdn.net/ajax/libs/blueimp-md5/2.18.0/js/md5.js"></script>
<script>

	// 故事集数据更新
	const user_data_update = () => {
		const sign = document.getElementById('sign').value
		const sign_img = document.getElementById('sign_img').value
		const best_love_story = document.getElementById('best_love_story').value
		const playing_story = document.getElementById('playing_story').value
		const recommend_stories = document.getElementById('recommend_stories').value
		const stories_data = `${sign}||${sign_img}||${best_love_story}||${playing_story}||${recommend_stories}`

		// 通过xhr提交数据
		var data = new FormData()
		data.append("cmd", "user_data_update")
		data.append("info", stories_data)

		// 发送xhr
		const xhr = new XMLHttpRequest()
		xhr.open("POST", "./server.php", true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				try {
					alert('数据已更新')
					window.location.href = './user_admin.php'
				} catch (error) {
					console.error('xhr请求失败，失败code：' + error)
				}
			}
		}

		
		// xhr请求超时
		xhr.timeout = 5000	// ms
		xhr.ontimeout = () => alert("请求超时")
	}



	// 账号操作
	const user_admin = (cmd, value='') => {
		switch (cmd) {
			// 更换头像
			case 'replace_avatar':
				window.location.href = './upload_avatar.php'
				break





			// 更改密码 value = 新密码
			case 'replace_psw':
				if (!value) {
					alert('请输入需要更改的密码')
				} else {
					const new_psw = md5(value)

					// 通过xhr更新密码
					var data = new FormData()
					data.append("cmd", "replace_psw")
					data.append("psw", new_psw)

					// 发送xhr
					var xhr = new XMLHttpRequest()
					xhr.open("POST", "./server.php", true)
					xhr.send(data)

					// xhr处理
					xhr.onreadystatechange = () => {
						if(xhr.readyState == 4 && xhr.status == 200){
							try {
								alert('密码已更改')
							} catch (error) {
								console.error('xhr请求失败，失败code：' + error)
							}
						}
					}

					// xhr请求超时
					xhr.timeout = 5000	// ms
					xhr.ontimeout = () => alert("请求超时")
				}
				break



			// 退出登录
			case 'quet':
				// cookie信息整理
				const date = new Date();
				date.setDate(date.getDate() - 31); // 设置有效期为30天
				const expires = 'expires=' + date.toUTCString();
										
				// 设置cookie
				document.cookie = `sessionID=0; ` + expires + '; path=/'

				// 重新进入页面
				window.location.href = './register.php'
				break
		}
	}
</script>




















<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>