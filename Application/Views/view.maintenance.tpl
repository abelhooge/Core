<html>
<head>
	<title>Maintenance</title>
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body {
			background: #ecf0f1;
			border-bottom: 1px solid #DDD;
			padding: 100px 0 100px;
			font-size: 16px;
		}

		#setupcontainer {
			max-width: 950px;
			margin-left: auto;
			margin-right: auto;
		}

		.col-centered{
		    float: none;
		    margin: 0 auto;
		}

	</style>
</head>
<body>
	<div id='setupcontainer'>
		<div class='row'>
			<div id='logo' class='col-lg-5 col-centered' style='margin-top:20%;'>
				<img src='http://myfuze.net/Fuze2tra.png' />
			</div>

			<div class='col-lg-12 col-md-12 col-sm-12'>
				<div id='contentPanel' class="panel panel-default" style='display:none'>
				    <div id='1' class="panel-body" style='display:none'>
				       <p class="lead">Dear visitor,</p>
				       <p>
				       		This site is currently under maintenance. This has resulted in that you are not able to view the website. 
				       </p>
				       <p>
				       		Our apologies for the inconvenience. 
				       </p>
				       <p><small>if you have any questions, please send your question to <a href='mailto:contact@fuzenetwerk.com'>contact@fuzenetwerk.com</a></small></p>

				    </div>
            	</div>
			</div>
		</div>
	</div>
	<footer>
		<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

		<script>
			var currentPage = 0;
			var currentProgress = 0;
			
			start();
			function start() {
				$("#logo").animate({ 'marginTop': '0%' }, 1000);
				$("#progress").fadeIn(2000);
				$("#contentPanel").fadeIn(2000);
				$("#1").fadeIn(2000);
				$("#buttons").fadeIn(2000);
				currentPage = 1;
			}
		</script>
	</footer>
</body>
</html>