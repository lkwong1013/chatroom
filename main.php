<?php session_start(); ?>
<html>
  <head>
  	<style>
		.page-container{
			width:100%;
			height:100%;			
		}
		.head-container{
			width:75%;
			margin:auto;
		}
		.userstat-container, .msg-container{
			width:50%;
			margin:auto;
			float:left;
		}
		p{
			font-size:30px;
		}

		input#username, #destination, #text{
			font-size:30px;
		}
		input#btn_submit,input#btn_logout{
			font-size:30px;
			
		}
		div#user-online{
			border:2px solid #000;
		}
		span{
			font-size:16px;
		}
		#received{
			height:300px;
			overflow:scroll;
		}
	</style>
	<script src="node_modules/socket.io/node_modules/socket.io-client/socket.io.js"></script>
	    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	    <script>
		var socket = io.connect("http://192.168.1.2:8001");
		socket.emit('userjoin', "<?php echo $_SESSION['username'];?>"); 
		// =======================================================================// 
		// ! Keep Checking server status     ↓  //        
		// =======================================================================//  
				setInterval(function() {
				var status = socket.connected ;
					if(status==true){
						$('img#status-icon').attr("src", "icon/checkmark_32.png");
						$('#btn_submit').prop("disabled", false);


					}else{
						$('img#status-icon').attr("src", "icon/error_32.png");
						$('#btn_submit').prop("disabled", true);
					}
				}, 1000);

		// =======================================================================// 
		// ! Keep Checking server status     ↑  //        
		// =======================================================================//  
				socket.on('userstat',function(clients){
					$('#user-online').empty();
					for	(index = 0; index < clients.length; index++) {
						if(clients[index]!=null){
							$('#user-online').append("<p><img src='icon/user.png' style='vertical-align:middle;'/>"+clients[index]+"<p>");
						}
					}
					$('#user-online').append("<p>We got "+clients.length+" people(s) here!</p>");
				});


				socket.on('sendback', function(username, g_data) {
					$('#received').append("<p>"+g_data.sendback+"</p>");
					//result += g_data.sendback;
					//$('#received').text(result);
					var objDiv = document.getElementById("received");
					objDiv.scrollTop = objDiv.scrollHeight;
				  });
				$(document).ready(function() {

					$("#btn_logout").click(function(){
						socket.emit('userleave', "<?php echo $_SESSION['username'];?>"); 
					
						$.ajax({
						  type: 'POST',
						  url: 'logout.php',
						  success: window.location.replace("login.html"),
						});
					});
					$('form').submit(function(e){
						var data = $('#text').val();
						var dest = $('#destination').val();
						var source = $('#source').val();
					  socket.emit('baz', {
						'letter': data,
						'source': source,
						'destination': dest
					  });
					  $('#text').val("");//Reset Textarea
					  e.preventDefault();
					});
				});
		</script>
  </head>
  <?php if(isset($_SESSION['username'])){?>
  <body>
	<div class="page-container">
		<div class="head-container">
			<p>Welcome <?php echo $_SESSION['username']; ?></p>
			<input type="button" id="btn_logout" value="Logout"/><br><br>
			<p>Server Status: <img src="icon/help_32.png" id="status-icon" style="vertical-align:middle;"/></p>
		</div>
		<div class="msg-container">
		<form>
		  <span>Private to:</span><br><input type="text" id="destination" /></br>
		  <span>Message:</span><br><input type="text" id="text" /></br>
		  <input type="submit" value="Send" />
		 </form>
			<div id="received"></div>
		</div>
		<div class="userstat-container">

			<p>Who's there?</p>
			<div id="user-online">
			</div>
		</div>
	</div>
  </body>
  <?php }else{
			?><script>window.location.replace("login.html")</script><?php
		} ?>
</html>		