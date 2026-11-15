<?php
	// Checking if user is logged in or not
require_once 'php/config.php';
session_start();
if(!isset($_SESSION['email']) || empty($_SESSION['email'])) 
{
	header("location: index.php");
	exit;
}
else
{
	$mgr = $_SESSION['email'];
	$user = $_SESSION['name'];
}
$proj_name="";
$btnClass = "";
if($_GET)
{
	$proj_name = $_GET['proj_name'];
	$btnClass = "disabled";
}
else
{
	$sql = "SELECT proj_name,uuid from wf_projects where status = 1 AND proj_mgr = '".$mgr."'";
	$res = mysqli_query($link, $sql);
	if (mysqli_num_rows($res) == 1) 
	{
		$row = mysqli_fetch_assoc($res);
		$proj_name = $row['proj_name'];
		$uuid = $row['uuid'];
	}
	else {
		echo "<script>window.onload = function noProj(){document.getElementById('container').innerHTML = '<p align=\"center\">No Active Project</p>';}</script>";
	}
}

// radial
$rad_sql = 'SELECT module, curr_progress FROM wf_module_progress WHERE log IN (SELECT max(log) FROM wf_module_progress GROUP BY module) AND proj_name = "'.$proj_name.'"';
$result = mysqli_query($link, $rad_sql);
$rad_series = array();
$rad_label = array();
while($rad_row_prog = mysqli_fetch_assoc($result)) {
	array_push($rad_series, $rad_row_prog["curr_progress"]);
	array_push($rad_label, $rad_row_prog["module"]);
}

$rad_s = json_encode($rad_series);
$rad_l = json_encode($rad_label);


// overall graph
$ovr_sql = "SELECT DISTINCT(date(log)) AS `date`,avg(curr_progress) AS `curr_prog`, avg(exp_progress) AS `exp_prog` FROM wf_module_progress  WHERE proj_name = '".$proj_name."' group by date(log)";
$result = mysqli_query($link, $ovr_sql);
$ovr_series_c = array();
$ovr_series_e = array();
$ovr_date = array();
while($ovr_row_prog = mysqli_fetch_assoc($result)) {
	array_push($ovr_series_c, $ovr_row_prog["curr_prog"]);
	array_push($ovr_series_e, $ovr_row_prog["exp_prog"]);
	array_push($ovr_date, date("F j",strtotime($ovr_row_prog["date"])));
}
$ovr_s_c = json_encode($ovr_series_c);
$ovr_s_e = json_encode($ovr_series_e);
$ovr_d = json_encode($ovr_date);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Workflow Analyzer home</title>
	<link rel="icon" type="image/png" href="res/img/favicon.png" />
	<link rel="stylesheet" type="text/css" href="res/css/style.min.css">
	<link rel="stylesheet" type="text/css" href="res/css/font-awesome/all.min.css">
	<link rel="stylesheet" type="text/css" href="res/css/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="res/css/bootstrap/bootstrap-datepicker.min.css">
	<script type="text/javascript" src="res/script/jQuery/jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="res/script/index.min.js"></script>
	<script type="text/javascript" src="res/script/bootstrap/bootstrap.min.js"></script>
	<script type="text/javascript" src="res/script/bootstrap/bootstrap-datepicker.min.js"></script>
	<script type="text/javascript" src="res/script/charts/apexcharts.js"></script>
	<script type="text/javascript" src="res/script/vuejs/vue.min.js"></script>
	<script type="text/javascript" src="res/script/vuejs/vueapexcharts.js"></script>
	<script>
		setInterval(function()
		{
			const isMobile = detectmob();
			if (isMobile)
			{
				//window.location("mobileSiteLinkGoesHere");
				document.body.innerHTML = "<h1 align='middle'>This should redirect to mobile site if we werent lazy :P</h1>";
			}
			else if($( window ).width() < 1365 && $( window ).height() < 767)
			{
				document.body.innerHTML = "<h1 align='middle'>Please make browser window bigger for best experience and refresh page</h1>";
			}
		}, 1000);
	</script>
	<script type="text/javascript">
		$('.datepicker').datepicker({
			format: 'yyyy-mm-dd',
			startDate: '-0d'
		});
	</script>   
	<script type="text/javascript">
		function open_body(contentId)
		{
			document.getElementById('body-container').innerHTML = document.getElementById('body-'+contentId.id+'').innerHTML;
		}
	</script>
	<script type="text/javascript">
		function rotate()
		{
			//$(".calendar-icon").toggleClass("rotate");
			$(".calendar-container").toggleClass("show");
		}
	</script>
</head>
<body style="overflow: hidden;">

	<!--floating calendar widget-->

	<div onclick="rotate()" class="calendar-icon"><i style="align-self: center;" class="fas fa-calendar-alt"></i></div>
	<div class="calendar-container">
		<div class="calendar-data">
			<?php
			$sql = 'SELECT subject, start_date FROM wf_calendar WHERE proj_name = "'.$proj_name.'" AND start_date = (SELECT min(start_date) FROM wf_calendar)';
			$result = mysqli_query($link, $sql);
			while($row = mysqli_fetch_assoc($result)) {
				echo "<p style='color:white;'>".$row['subject']."</p>";
				echo "<p style='color:white;'>".date("F j, Y h:i", strtotime($row['start_date']))."</p><br>";
			}
			?>
		</div>
	</div>

	<!--navbar-->
	<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
		<a class="navbar-brand" href="home.php">Workflow Analyzer</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Manage Projects</a>
					<div class="dropdown-menu" aria-labelledby="navbarDropdown">
						<span class="dropdown-item" data-toggle="modal" data-target="#addProjectModal">Add project</span>
						<span class="dropdown-item" data-toggle="modal" data-target="#viewPrivProjModal">Previous projects</span>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Staff</a>
					<div class="dropdown-menu" aria-labelledby="navbarDropdown">
						<span <?php echo 'class="dropdown-item '.$btnClass.'"'; ?> data-toggle="modal" data-target="#addStaffModal">Add Staff</span>
						<span <?php echo 'class="dropdown-item '.$btnClass.'"'; ?> data-toggle="modal" data-target="#viewStaffModal">View Staff</span>
					</div>
				</li>
			</ul>
			<span class="navbar-text"><i class="fas fa-project-diagram"></i>&nbsp;<?php echo $proj_name;?>&nbsp;&nbsp;&nbsp;</span>
			<span class="navbar-text"><i class="fas fa-user"></i>&nbsp;<?php echo $user; ?>&nbsp;&nbsp;</span>
			<button class="btn btn-sm btn-outline-danger my-1 my-sm-0" onclick="location.href = 'php/signout.php'"><i class="fal fa-sign-out"></i>  Sign out</button>
		</div>
	</nav>

	<!--main container-->
	<div style="border-radius: 15px" class="container">
		<!--tabs bar-->
		<nav class="navbar navbar-expand-lg navbar-light bg-light2 justify-content-center">
			<div class=" btn-group btn-round btn-group-justified w-100" data-toggle="buttons">
				<label style="border-top-left-radius: 20px; border-bottom-left-radius: 20px;" class="btn btn-outline-dark active">
					<input value="" type="radio" name="placement" id="size3" autocomplete="off" checked="" onchange="containerSwitcher(this)">Report
				</label>
				<label class="btn btn-outline-dark">
					<input value=" fixed-top" type="radio" name="placement" id="size2" autocomplete="off" onchange="containerSwitcher(this)">Status
				</label>
				<label style="border-top-right-radius: 20px; border-bottom-right-radius: 20px;" class="btn btn-outline-dark">
					<input value=" fixed-bottom" type="radio" name="placement" id="size1" autocomplete="off" onchange="containerSwitcher(this)">E-Mails
				</label>
			</div>
		</nav>
		<!--Emails window-->
		<div id="email-container" class="row hide">
			
			<div style="height: inherit; overflow-x: auto;" class="col-sm-4">
				<table class="table table-hover">
					<?php
					$sql = 'SELECT `to`, `frm`, `subject`, `body`, `proj_name`, `log` FROM `wf_mails` where proj_name = "'.$proj_name.'"';
					$res = mysqli_query($link, $sql);
					if (mysqli_num_rows($res) > 0)
					{
						$i = 0;
						while ($row = mysqli_fetch_assoc($res)) 
						{
							echo "<tr class='email-min'>";
							echo "<td id='" . $i . "' onclick='open_body(this)''><strong>from:</strong>" . $row['frm'] . "<br><strong>to:</strong>" . $row['to'] . "<br><strong>subject</strong>: " . $row['subject'] . "</td>";
							echo "<td style='display: none;' id='body-" . $i . "'>" . $row['body'] . "</td>";
							echo "</tr>";
							$i++;
						}
					}
					?>
				</table>
			</div>
			<div id="body-container" class="email-max col-sm-8">Email content will appear here</div>
		</div>
		
		<!--status window-->
		<div id="status-container" class="hide">
			<p style="align-self: center; margin: 150px;">
				<?php
				$sql = 'SELECT score, log FROM wf_sentiments WHERE proj_name = "'.$proj_name.'" AND log = (SELECT max(log) FROM wf_sentiments WHERE proj_name = "'.$proj_name.'")';
				$res = mysqli_query($link, $sql);

				$sql_2 = "SELECT status FROM wf_projects WHERE proj_name = '".$proj_name."'";
				$res_2 = mysqli_query($link, $sql_2);
				if(mysqli_num_rows($res_2) == 1) {
					$row_2 = mysqli_fetch_assoc($res_2);
					if($row_2['status'] == 0) {
						echo "Project Completed.";
					}
					elseif (mysqli_num_rows($res) == 1)  {

						$row = mysqli_fetch_assoc($res);

						if($row['score'] == 1) {
							echo "As per the detailed analysis of your project, it was found that the workflow is progressing as expected and there are high chances that it would be completed on time.";
						}
						elseif($row['score'] == 0) {
							echo "As per the detailed analysis of your project, it was found that the workflow is not progressing as expected and there are high chances that it would get delayed. It is advisable that you contact your team as soon as possible.";
						}
						else echo "Status Unavailable at the moment.";
					}
					else echo "Status Unavailable at the moment.";
				}
				?>				
			</p>
		</div>
		<!--review window-->
		<div id="review-container">
			<!-- overall progress chart -->
			<div id="chart">
				<apexchart type=line height=350 :options="chartOptions" :series="series" />
			</div>
			<!-- module progress chart -->
			<div id="radialChart">
				<apexchart type=radialBar height=350 :options="chartOptions" :series="series" />
			</div>
		</div>

	</div>

	<!-- Add Project Modal -->

	<div style="overflow-y: auto;" class="modal" id="addProjectModal">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">

				<!-- Modal Header -->
				<div class="modal-header">
					<h4 class="modal-title">Add Project</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<!-- Modal body -->
				<div style="overflow: auto; max-height: 500px;" class="modal-body">
					<form id="add-proj-form" method="post">
						<label>Project Name</label>
						<input class="form-control" type="text" name="projName" placeholder="Project Name">
						<br>
						<label>Start Date</label>
						<div class="input-group date" data-provide="datepicker">
							<input type="text" name="startDate" class="form-control" readonly="">
							<div class="input-group-append">
								<a href="#" class="btn btn-dark disabled"><i class="fal fa-calendar-plus"></i></a>
							</div>
						</div>
						<br>
						<label>Deadline</label>
						<div class="input-group date" data-provide="datepicker">
							<input type="text" name="deadline" class="form-control" readonly="">
							<div class="input-group-append">
								<a href="#" class="btn btn-dark disabled"><i class="fal fa-calendar-plus"></i></a>
							</div>
						</div>
						<br>
						<label>Number of modules</label>
						<div class="input-group">
							<input class="form-control" id="modulecount" type="text" name="noOfModules" placeholder="No. of modules">
							<div class="input-group-append">
								<span class="btn btn-outline-success" onclick="addField();"><i class="far fa-check"></i></span>
							</div>
						</div>
						<div id="moduleNames"></div>
					</form>
				</div>

				<!-- Modal footer -->
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="add-proj"><i class="fal fa-folder-plus"></i>&nbsp;ADD</button>
				</div>

			</div>
		</div>
	</div>

	<!-- Add Staff Modal -->

	<div style="overflow-y: auto;" class="modal " id="addStaffModal">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">

				<!-- Modal Header -->
				<div class="modal-header">
					<h4 class="modal-title">Add Staff</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<!-- Modal body -->
				<div style="overflow: auto; min-height: 500px; max-height: 500px;" class="modal-body">
					<form name="add_name" id="add_name">
						<table id="dynamic_field">
							<tr id="row0">
								<td><input class="form-control" type="text" name="staffName[]" placeholder="Staff Name"></td>
								<td><input class="form-control" type="email" name="staffEmail[]" placeholder="Staff Email"></td>
							</tr>
						</table>
					</form>
				</div>

				<!-- Modal footer -->
				<div class="modal-footer">
					<button class="btn btn-warning" id="add" type="add"><i class="fas fa-user-plus"></i></button>
					<button type="button" class="btn btn-success" id="addStaff"><i class="fal fa-users-medical"></i>&nbsp;&nbsp;ADD</button>
				</div>

			</div>
		</div>
	</div>

	<!-- View Staff Modal -->

	<div style="overflow-y: auto;" class="modal" id="viewStaffModal">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">

				<!-- Modal Header -->
				<div class="modal-header">
					<h4 class="modal-title">Project Staff</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<!-- Modal Body -->
				<div style="overflow: auto; min-height: 500px; max-height: 500px;" class="modal-body">
					<table class="table table-hover w-100">
						<?php
						$sql="SELECT name, email FROM wf_emails where proj_name = (SELECT proj_name FROM wf_projects WHERE status = 1 && proj_mgr = '" . $_SESSION['email'] . "')";
						$result = mysqli_query($link,$sql);
						if (mysqli_num_rows($result) > 0) {
							echo "<tr><th>Name</th><th>Email</th></tr>";
							while($row = mysqli_fetch_array($result)) {
								echo "<tr><td>" . $row['name'] . "</td>"?><td><a href="mailto:<?php echo $row['email'];?>" target="_top"><?php echo $row['email']?></a></td></tr><?php
							}
						}
						else {
							echo "<h3 style='align: center'>0 results</h3>";
						}
						?>				
					</table>
				</div>
			</div>
		</div>
	</div>


	<!-- Previous Project Modal -->

	<div style="overflow-y: auto;" class="modal " id="viewPrivProjModal">
		<div class="modal-dialog modal-dialog-centered modal-lg">
			<div class="modal-content">
				<!-- Modal Header -->
				<div class="modal-header">
					<h4 class="modal-title">Previous Projects</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<!-- Modal body -->
				<div style="overflow: auto; min-height: 500px; max-height: 500px;" class="modal-body">
					<table class="table table-hover w-100">
						<?php
						$sql="SELECT * FROM wf_projects where proj_mgr ='" . $_SESSION['email'] . "' && status = 0";
						$result = mysqli_query($link,$sql);
						if (mysqli_num_rows($result) > 0)
						{
							echo "<tr><th>Project Name</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
							while($row = mysqli_fetch_array($result))
							{
								echo "<tr><td>".$row['proj_name']."</td><td> ".date("F j, Y", strtotime($row['start_date']))."</td><td> ".date("F j, Y", strtotime($row['end_date']))."</td><td><span class = 'btn btn-sm btn-outline-warning' onclick='open_proj(`".$row['proj_name']."`);'>Status</span></td></tr>";
							}
						} else {
							echo "<h3 style='align: center's>0 results</h3>";
						}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!--Scripts go below this line-->

	<script>  
		$(document).ready(function()
		{
			var i=1;  
			$('#add').click(function()
			{  
				i++;  
				$('#dynamic_field').append('<tr id="row'+i+'"><td><input class="form-control" type="text" name="staffName[]" placeholder="Staff Name"></td><td><input class="form-control" type="email" name="staffEmail[]" placeholder="Staff Email"></td><td><button class="btn btn-outline-danger btn_remove" name="remove" id="'+i+'"><i class="fas fa-user-minus"></i></button></td></tr>');
			});  
			$(document).on('click', '.btn_remove', function()
			{  
				var button_id = $(this).attr("id");   
				$('#row'+button_id+'').remove();  
			});  
			$('#addStaff').click(function()
			{            
				$.ajax(
				{
					url:"php/addstaff.php",
					method:"POST",
					data:$('#add_name').serialize(),
					success:function(data)
					{
						if(data == "Staff Added Successfully") {
							if (confirm("Staff added successfully, do you want to send email?")) 
							{
								$.ajax({
									type: "GET",
									url: "https://consent-workflow-analyzer.azurewebsites.net/sendmail/",
									data: {
										email: <?php echo "'".$mgr."'";?>,
										uuid: <?php echo "'".$uuid."'";?>,
									},
									success: function (data) {
										alert(data);
									}
								});
							}
						}
						else
						{
							alert(data);
							$('#add_name')[0].reset();
						}
					}
				});  
			});
			$('#add-proj').click(function()
			{            
				$.ajax(
				{
					url:"php/addProject.php",
					method:"POST",
					data:$('#add-proj-form').serialize(),
					success:function(data)
					{
						if (data == 'success') 
						{
							window.location.href = "https://consent-workflow-analyzer.azurewebsites.net/man_consent/";
						} 
						else
						{
							alert(data);
						}
					}
				});  
			});
		});  
	</script>
	<script>
		function addField()
		{
			document.getElementById("moduleNames").innerHTML = " ";
			var x = document.getElementById("modulecount").value;
			for (var i = 1; i <= x; i++) 
			{
				$("#moduleNames").append('<br><label>Module '+i+' name</label><input class="form-control" type="text" name="moduleName[]" placeholder="Module '+i+'"><div class="input-group"><div class="input-group date w-50" data-provide="datepicker"><input type="text" name="moduleStartDate[]" class="form-control" readonly="" placeholder="Start Date"><div class="input-group-append"><a href="#" class="btn btn-dark disabled"><i class="fal fa-calendar-plus"></i></a></div></div><div class="input-group date w-50" data-provide="datepicker"><input type="text" name="moduleDeadline[]" class="form-control" readonly="" placeholder="Deadline"><div class="input-group-append"><a href="#" class="btn btn-dark disabled"><i class="fal fa-calendar-plus"></i></a></div></div></div>')
			}
		}
		function open_proj(proj)
		{
			window.location.href = 'home.php?proj_name='+proj+'';
		}
	</script>
	<!-- radial graph script -->
	<script>
		new Vue({
			height: 500,
			el: '#radialChart',
			components: {
				apexchart: VueApexCharts,
			},
			data: {

				series: <?php echo $rad_s;?>,
				chartOptions: {
					plotOptions: {
						radialBar: {
							offsetY: -30,
							startAngle: 0,
							endAngle: 270,
							hollow: {
								margin: 5,
								size: '30%',
								background: 'transparent',
								image: undefined,
							},
							dataLabels: {
								name: {
									show: false,

								},
								value: {
									show: false,
								}
							}
						}
					},
					labels: <?php echo $rad_l;?>,
					legend: {
						show: true,
						floating: true,
						fontSize: '16px',
						position: 'left',
						offsetX: 170,
						offsetY: 10,
						labels: {
							useSeriesColors: true,
						},
						markers: {
							size: 0
						},
						formatter: function(seriesName, opts) {
							return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex]
						},
						itemMargin: {
							horizontal: 1,
						}
					},
					responsive: [{
						breakpoint: 480,
						options: {
							legend: {
								show: false
							}
						}
					}]
				}
			},

		})
	</script>
	<!-- Overall Progress Graph -->
	<script>
		new Vue({
			el: '#chart',
			components: {
				apexchart: VueApexCharts,
			},
			data: {
				series: [
				{
					name: "Expected Progress",
					data: <?php echo $ovr_s_e;?>
				},
				{
					name: "Actual Progress",
					data: <?php echo $ovr_s_c;?>
				}
				],
				chartOptions: {
					chart: {
						shadow: {
							enabled: true,
							color: '#000',
							top: 18,
							left: 7,
							blur: 10,
							opacity: 1
						},
						toolbar: {
							show: false
						}
					},
					colors: ['#77B6EA', '#545454'],
					dataLabels: {
						enabled: true,
					},
					stroke: {
						curve: 'smooth'
					},
					title: {
						text: 'Project Progress',
						align: 'left'
					},
					grid: {
						borderColor: '#e7e7e7',
						row: {
              colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
              opacity: 0.5
          },
      },
      markers: {

      	size: 6
      },
      xaxis: {
      	categories: <?php echo $ovr_d;?>,
      	title: {
      		text: 'Date',
      		min: 1,
      		max: 31
      	}
      },
      yaxis: {
      	title: {
      		text: 'Progress %'
      	},
      	min: 5,
      	max: 100
      },
      legend: {
      	position: 'top',
      	horizontalAlign: 'right',
      	floating: true,
      	offsetY: -25,
      	offsetX: -5
      }
  }
},
})
</script>
</body>
</html>