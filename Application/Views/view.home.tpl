<html>
<head>
	<title>FuzeWorks - Home</title>
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
			<div class='col-lg-12 col-md-4 col-sm-12'>
				<div id='contentPanel' class="panel panel-default" style='display:none'>
				    <div id='1' class="panel-body" style='display:none'>
				       <p class="lead">Welcome to FuzeWorks</p>
				       <p>
				       		This website is currently under development.
				       </p>
				       <p>
				       		{if $adminMail == ''}
				       		For further information, please contact the website administrator</a> 
				       		{else}
				       		For further information, please contact <a href='mailto:{$adminMail}'>{$adminMail}</a> 
				       		{/if}
				       		
				       </p>
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
				$("#contentPanel").fadeIn(500);
				$("#1").fadeIn(500);
			}
		</script>
	</footer>
</body>
</html>