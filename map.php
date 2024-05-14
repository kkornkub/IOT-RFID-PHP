<?php

session_start();
include('config.php');

if (!isset($_SESSION['user_login'])) {
	header("location: index.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="css/style.css">

	<title>Project IOT</title>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<link rel="icon" type="image/x-icon" href="img/icon.ico">
</head>
<style>
	.container {
		width: 100%;
		max-width: 1200px;
		margin: 0 auto;
	}

	.header {
		background-color: #333;
		color: #fff;
		text-align: center;
		padding: 10px;
	}

	.footer {
		background-color: #333;
		color: #fff;
		text-align: center;
		padding: 10px;
	}

	table,
	th,
	td {
		border: 1px solid black;
		border-collapse: collapse;
		background-color: #fff;
	}

	td {
		text-align: center;
	}

	.tall-cell {
		height: 200px;
	}

	.main-cell {
		height: 140px;
	}
</style>

<body>
	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text ">Project IOT</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="dashboard.php">
					<i class='bx bxs-dashboard'></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li class="active">
				<a href="#">
					<i class='bx bxs-group'></i>
					<span class="text">Student position</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="logout.php" class="logout">
					<i class='bx bxs-log-out-circle'></i>
					<span class="text">Logout</span>
				</a>
			</li>
		</ul>
	</section>
	<!-- SIDEBAR -->

	<?php
	// ลิงค์ API 1
	$api_url1 = "https://sheets.googleapis.com/v4/spreadsheets/1k06d4fCd-XevkxeZxL0Ms6p0dMcopYRUZ2dg9YIW0gY/values/main%20tab?key=AIzaSyCHXXOaUAIcyz6zKqJ0YNxDNr7iOuLX5MM";

	// ลิงค์ API 2
	$api_url2 = "https://sheets.googleapis.com/v4/spreadsheets/1k06d4fCd-XevkxeZxL0Ms6p0dMcopYRUZ2dg9YIW0gY/values/STD_DATA?key=AIzaSyCHXXOaUAIcyz6zKqJ0YNxDNr7iOuLX5MM";

	// ตัวเลือก cURL
	$options1 = [
		CURLOPT_URL            => $api_url1,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS      => 10,
		CURLOPT_TIMEOUT        => 30,
		CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST  => "GET",
	];

	$options2 = [
		CURLOPT_URL            => $api_url2,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS      => 10,
		CURLOPT_TIMEOUT        => 30,
		CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST  => "GET",
	];

	// สร้าง cURL multi-handle
	$mh = curl_multi_init();

	// เพิ่ม cURL handles สำหรับแต่ละลิงค์
	$ch1 = curl_init();
	$ch2 = curl_init();
	curl_setopt_array($ch1, $options1);
	curl_setopt_array($ch2, $options2);
	curl_multi_add_handle($mh, $ch1);
	curl_multi_add_handle($mh, $ch2);

	// เริ่มทำการ execute ทั้งหมดพร้อมกัน
	$running = null;
	do {
		curl_multi_exec($mh, $running);
	} while ($running);

	// ดึงข้อมูลจากแต่ละลิงค์
	$response1 = curl_multi_getcontent($ch1);
	$response2 = curl_multi_getcontent($ch2);

	// ปิด cURL handles
	curl_multi_remove_handle($mh, $ch1);
	curl_multi_remove_handle($mh, $ch2);
	curl_multi_close($mh);


	// แปลง response เป็น JSON
	$json1 = json_decode($response1, true); // ถ้าใช้ true จะได้เป็น associative array
	$json2 = json_decode($response2, true);

	// ตรวจสอบว่าการแปลง JSON สำเร็จหรือไม่
	if ($json1 === null || $json2 === null) {
		echo 'Error decoding JSON';
	} else {

		$totalRowCount = -1;

		// นับจำนวนข้อมูลในชีท "STD_DATA"
		if (isset($json2['values'])) {
			$totalRowCount += count($json2['values']);
		}

		$rowCount = count($json2);

		$matchingCount = 0;

		$todayDate = date('Y-m-d');


		foreach ($json1['values'] as $item) {
			// Check if there is a "LAST_CHECK" key in each item
			if (isset($item[4])) { // Assuming LAST_CHECK is in the 5th column (index 4)
				// Extract the date part from "LAST_CHECK" and convert it to a format understandable
				// by strtotime function
				$lastCheckDate = date('Y-m-d', strtotime(str_replace('/', '-', $item[4])));

				// Compare with today's date
				if ($lastCheckDate == $todayDate) {
					$matchingCount++; // Increment the count if student came to school today
				}
			}
		}
	}
	?>

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu'></i>
			<form action="#" id="search-form">
				<div class="form-input">
					<input type="search" id="search-input" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Map</h1>
				</div>
			</div>

			<br><br><br>

			<div class="container">
				<div class="header">
					<h1>แปลนโรงเรียน</h1>
				</div>
				<table style="width:100%" id="realtime-table">
					<tr>
						<td id="room1" class="main-cell">ห้อง 1</td>
						<td id="room2" class="main-cell">ห้อง 2</td>
						<td id="parking" class="main-cell">ที่จอดรถ</td>
					</tr>
					<tr>
						<td id="courtyard" colspan="2" rowspan="3" class="tall-cell">ลาน <br></td>
					</tr>
					<tr>
						<td id="playground">สนามเด็กเล่น <br></td>
					</tr>
					<tr>
						<td id="pool">สระว่ายน้ำ <br></td>
					</tr>
				</table>
				<div class="footer">
					<p>© 2024</p>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

	<script>
		$(document).ready(function() {
			// Function เพื่อดึงข้อมูลและอัพเดตตาราง
			function updateTable() {
				// ลิงค์ API ของคุณ
				var api_url = "https://sheets.googleapis.com/v4/spreadsheets/1k06d4fCd-XevkxeZxL0Ms6p0dMcopYRUZ2dg9YIW0gY/values/main%20tab?key=AIzaSyCHXXOaUAIcyz6zKqJ0YNxDNr7iOuLX5MM";

				// ทำ AJAX request เพื่อดึงข้อมูล
				$.getJSON(api_url, function(data) {
					// ตรวจสอบข้อมูลและอัพเดตตาราง
					checkAndUpdateTable(data);
				});
			}

			// Function เพื่อตรวจสอบข้อมูลและอัพเดตตาราง
			function checkAndUpdateTable(data) {
				// สร้างตัวแปร HTML เพื่อเก็บเนื้อหาใหม่ของตาราง
				var courtyardHtmlContent = ' ลาน<br>';
				var playgroundHtmlContent = ' สนามเด็กเล่น<br>';
				var poolHtmlContent = ' สระว่ายน้ำ<br>';
				var room1HtmlContent = ' ห้อง 1<br>';
				var room2HtmlContent = ' ห้อง 2<br>';
				var parkingHtmlContent = ' ที่จอดรถ<br>';

				// Loop ทุกๆ รายการในข้อมูล
				data.values.forEach(function(student) {
					var stdId = student[1]; // Assuming STD_ID is in the first column (index 0)
					var zone = student[3]; // Assuming Zone is in the second column (index 1)

					// ตรวจสอบว่าโซนตรงกับเงื่อนไขที่ต้องการหรือไม่
					if (zone === "Courtyard") {
						// เพิ่มรหัสนักเรียนลงในตัวแปร HTML สำหรับลาน
						courtyardHtmlContent += '<i class="bx bxs-group"></i> ' + stdId + '<br>';
					} else if (zone === "Playground") {
						// เพิ่มรหัสนักเรียนลงในตัวแปร HTML สำหรับสนามเด็กเล่น
						playgroundHtmlContent += '<i class="bx bxs-group"></i> ' + stdId + '<br>';
					} else if (zone === "Pool") {
						// เพิ่มรหัสนักเรียนลงในตัวแปร HTML สำหรับสระว่ายน้ำ
						poolHtmlContent += '<i class="bx bxs-group"></i> ' + stdId + '<br>';
					} else if (zone === "Room1") {
						// เพิ่มรหัสนักเรียนลงในตัวแปร HTML สำหรับห้อง 1
						room1HtmlContent += '<i class="bx bxs-group"></i> ' + stdId + '<br>';
					} else if (zone === "Room2") {
						// เพิ่มรหัสนักเรียนลงในตัวแปร HTML สำหรับห้อง 2
						room2HtmlContent += '<i class="bx bxs-group"></i> ' + stdId + '<br>';
					} else if (zone === "Parking") {
						// เพิ่มรหัสนักเรียนลงในตัวแปร HTML สำหรับที่จอดรถ
						parkingHtmlContent += '<i class="bx bxs-group"></i> ' + stdId + '<br>';
					}
				});

				// อัพเดตตารางลานใน DOM
				$("#courtyard").html(courtyardHtmlContent);
				// อัพเดตตารางสนามเด็กเล่นใน DOM
				$("#playground").html(playgroundHtmlContent);
				// อัพเดตตารางสระว่ายน้ำใน DOM
				$("#pool").html(poolHtmlContent);
				// อัพเดตตารางห้อง 1 ใน DOM
				$("#room1").html(room1HtmlContent);
				// อัพเดตตารางห้อง 2 ใน DOM
				$("#room2").html(room2HtmlContent);
				// อัพเดตตารางที่จอดรถใน DOM
				$("#parking").html(parkingHtmlContent);
			}

			// เรียกใช้ Function updateTable เพื่อดึงข้อมูลและอัพเดตตารางทุกๆ 5 วินาที
			setInterval(updateTable, 5000);
		});
	</script>


	<script>
		document.getElementById('search-form').addEventListener('submit', function(event) {
			event.preventDefault(); // ป้องกันการโหลดหน้าใหม่เมื่อ submit form
			var searchText = document.getElementById('search-input').value.toLowerCase(); // รับข้อความที่ต้องการค้นหาและแปลงเป็นตัวพิมพ์เล็ก
			var tableRows = document.querySelectorAll('.table-data table tbody tr'); // เลือกทุกแถวของตาราง

			// วนลูปผ่านแถวของตารางเพื่อค้นหาข้อมูลที่ตรงกับข้อความที่ค้นหา
			tableRows.forEach(function(row) {
				var studentData = row.innerText.toLowerCase(); // ข้อมูลของนักเรียนในแถวนั้นๆ
				if (studentData.indexOf(searchText) === -1) { // ถ้าไม่พบข้อความที่ต้องการค้นหา
					row.style.display = 'none'; // ซ่อนแถวนั้น
				} else {
					row.style.display = ''; // แสดงแถวนั้น
				}
			});
		});
	</script>

	<script src="js/script.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>