var http = require("http");
var url = require('url');
var fs = require('fs');
var socket_io = require('socket.io'); // import Socket.IO

var server = http.createServer(function(request, response) {
  console.log('Connection');
  var path = url.parse(request.url).pathname;

  switch (path) {
    case '/':
      response.writeHead(200, {'Content-Type': 'text/html'});
      response.write('Hello, World.');
      response.end();
      break;
    case '/socket.html':
      fs.readFile(__dirname + path, function(error, data) {
        if (error){
          response.writeHead(404);
          response.write("opps this doesn't exist - 404");
        } else {
          response.writeHead(200, {"Content-Type": "text/html"});
          response.write(data, "utf8");
        }
        response.end();
      });
      break;
    default:
      response.writeHead(404);
      response.write("opps this doesn't exist - 404");
      response.end();
      break;
  }
});

server.listen(8001);
var io = socket_io.listen(server);
var clients = [];
io.sockets.on('connection', function(socket) {



	socket.on('userjoin', function(username){
		  socket.username = username;
		  socket.join(socket.username);
		  //For unrecognised mode
		  var found = false;
		  for(index = 0; index < clients.length; index++) {
			if(username == clients[index]&&found == false){
				//Client found (Reconnect)
				process.stdout.write(socket.username+" reconnect!\n");
				found = true;
			}
		}
		  if(found == false){
				//New connection
				process.stdout.write(socket.username+" online!\n");
				clients.push(username);
		  }

		  /*var a = clients.indexOf(socket.username);
		  process.stdout.write(a+"\n");
		  if(a>0){
			//Client found (Reconnect)
			process.stdout.write(socket.username+" reconnect!\n");
		  }else{
			//New connection
			process.stdout.write(socket.username+" online!\n");
		    clients.push(username);
		  }*/

	});
	
		socket.on('userleave', function(username){
			clients.splice(clients.indexOf(username), 1);
			process.stdout.write(socket.username+" offline!\n");
		});
		
		// =======================================================================// 
		// ! Keep Updating the Client online status    ↓  //        
		// =======================================================================// 	
		setInterval(function() {
				socket.emit('userstat',clients);
		}, 1000);
		// =======================================================================// 
		// ! Keep Updating the Client online status     ↑  //        
		// =======================================================================//  
	 socket.on('addme',function(username) {
	  socket.username = username;
	  socket.emit('sendback', 'SERVER', {'sendback':'You have connected'}); 
	  socket.broadcast.emit('sendback', 'SERVER', {'sendback':username + ' is on deck'});
	  socket.join(socket.username);
	 });
    socket.emit('message', {'message': 'aaabbc'}); //Send out data
	//io.sockets.emit('sendback', {'sendback': "Someone joined!"}); //Send out data
var g_data = "";
	socket.on('baz', function(data){
        //console.log(data.letter); // Receive data
		//process.stdout.write(data.letter+"\n");
		dt = new Date();

		g_data = socket.username+" :"+data.letter;
		process.stdout.write(dt.getDate()+"-"+dt.getMonth()+"-"+dt.getFullYear()+" "+dt.getHours()+":"+dt.getMinutes()+":"+dt.getSeconds()+" To: "+data.destination+" From: "+socket.username+" Data:"+data.letter +"\n");
		
		if(data.destination != ""){
			//io.sockets.in(data.destination).emit('sendback',socket.username, {'sendback': g_data});
			io.to(data.destination).emit('sendback',socket.username, {'sendback': '(Private)'+g_data});//Private Message
			socket.emit('sendback',socket.username, {'sendback': '(Private)'+g_data});
		}else{
			io.sockets.emit('sendback',socket.username, {'sendback': g_data}); //Send out data(Anyone can see)
		}
    });


});