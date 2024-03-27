







	<!-- 终端 -->
	<div class="cmd">
		<div class="board">
			<header>
				<span>终端</span>
				<button class="button2 close" onclick="cmd.close()">关闭</button>
			</header>
			<main></main>
		</div>
	</div>





















<!-- 左下角音乐播放器 -->
<!-- <div class="music_">

</div> -->



<!-- 右下角显示壁纸按钮 -->
<div class="show_bg" onclick="show_bg()">
	<img src="./data/imgs/winter_bell.png" title="观看背景 / 回到论坛" alt="图片加载失败" loading="lazy">
</div>













<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

	class cmd_control {
		constructor() {
			// 总DOM获取
			const cmd = document.querySelector('.cmd')
			const cmd_box = document.querySelector('.cmd .board')
			const cmd_title = document.querySelector('.cmd .board header')

			// 拖动功能，cmd title按下事件
			cmd_title.addEventListener('mousedown', function(e) {
				var x = e.pageX - cmd_box.offsetLeft
				var y = e.pageY - cmd_box.offsetTop

				// 鼠标移动事件
				document.addEventListener('mousemove', cmd_move)
				function cmd_move(e) {
					// 重新赋值给cmd_box
					cmd_box.style.left = e.pageX - x + 'px'
					cmd_box.style.top = e.pageY - y + 'px'
				}

				// 鼠标松开事件
				document.addEventListener('mouseup', function() {
					document.removeEventListener('mousemove', cmd_move)
				})
			})
		}

		// 显示cmd
		open() {
			document.querySelector('.cmd').style.display = 'block'

			// 窗口聚焦
			document.querySelector(".cmd").scrollIntoView({behavior: "smooth", block: "start"})
		}

		// 关闭cmd
		close() {
			document.querySelector('.cmd').style.display = 'none'
		}

		// 修改title
		title(title) {
			document.querySelector('.cmd header span').textContent = title
		}

		// 修改内容
		content(content) {
			document.querySelector('.cmd main').innerHTML = content
		}

		// 修改cmd宽度
		width(num) {
			document.querySelector('.cmd .board').style.width = `${num}`
		}
	}

	// 加载cmd类
	const cmd = new cmd_control()







	// 
	// 
	// 显示背景
	// 
	// 
	const show_bg = () => {
		// 从header导航栏中索取visibility属性判定是否需要回显
		var state = document.querySelector(".header")

		// 需要恢复div
		if (state.style.visibility == 'hidden') {
			// 获取body标签内的所有div元素
			var divs = document.body.getElementsByTagName('div')

			// 遍历所有div显示
			for (var i = 0; i < divs.length; i++) {
				divs[i].style.visibility = 'visible'
			}

		// 隐藏div显示背景
		} else {
			// 获取body标签内的所有div元素
			var divs = document.body.getElementsByTagName('div')

			// 遍历所有div隐藏
			for (var i = 0; i < divs.length; i++) {
				divs[i].style.visibility = 'hidden'
			}

			// 保留class="show_bg"功能按钮
			var show_bg = document.querySelector(".show_bg")
			show_bg.style.visibility = 'visible'
		}
	}


	// 
	// 
	//	往返延迟计算
	// 
	// 
	const measureLatency = () => {
		var startTime = new Date().getTime();
			
		// 通过ajax请求往返延迟
		$.ajax({
			url: './ping.php',
			type: 'GET',
			success: function(response) {
				var endTime = new Date().getTime();
					
				// 时间差计算
				var latency = endTime - startTime;

				// DOM回显
				document.querySelector('#ping_test').textContent = latency + 'ms'
			},
			error: function(xhr, status, error) {
				document.querySelector('#ping_test').textContent = '失败..'
			}
		});
	}
	measureLatency();



	// 
	// 
	// 3分钟记录添加一次在线时间
	// 
	// 
	function add_online_time() {
		// 通过xhr更新密码
		var data = new FormData()
		data.append("cmd", "add_online_time")
		console.log('+1')

		// 发送xhr
		var xhr = new XMLHttpRequest()
		xhr.open("POST", "./server.php", true)
		xhr.send(data)

		// 进入下一次循环
		setTimeout(add_online_time, 3 * 60 * 1000);
	}

	setTimeout(add_online_time, 3 * 60 * 1000);



















</script>