<?php
/**
 * FuzeWorks
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2015   TechFuze
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 * @version     Version 0.0.1
 */
?>

<html>
<head>
	<title>Page not found</title>
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
				       <p class="lead">Page not found</p>
				       <p>
				       		The requested page could not be found.
				       </p>
				       <p>
				       		Our apologies for the inconvenience. 
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