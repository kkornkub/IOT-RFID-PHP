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

	<link rel="icon" type="image/x-icon" href="img/icon.ico">
</head>

<body>


	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bxs-smile'></i>
			<span class="text ">Project IOT</span>
		</a>
		<ul class="side-menu top">
			<li class="active">
				<a href="#">
					<i class='bx bxs-dashboard'></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="map.php">
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
					<h1>Dashboard</h1>
				</div>
			</div>

			<ul class="box-info">
				<li>
					<i class='bx bxs-calendar-check'></i>
					<span class="text">
						<h3><?php echo $totalRowCount; ?></h3>
						<p>จำนวนนักเรียนทั้งหมด</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-group'></i>
					<span class="text">
						<h3><?php echo  $matchingCount; ?></h3>
						<p>จำนวนนักเรียนที่มาโรงเรียนวันนี้</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-dollar-circle'></i>
					<span class="text">
						<h3><?php echo  $todayDate; ?></h3>
						<p></p>
					</span>
				</li>
			</ul>


			<?php
			// Define the number of rows per page
			$rowsPerPage = 5;

			// Check if json1 values are set
			if (isset($json1['values'])) {
				// Calculate the total number of pages
				$totalPages = ceil((count($json1['values']) - 1) / $rowsPerPage);

				// Get the current page number from the query string, default to 1
				$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

				// Calculate the starting index for the current page
				$startIndex = ($currentPage - 1) * $rowsPerPage + 1;

				// Calculate the ending index for the current page
				$endIndex = min($startIndex + $rowsPerPage - 1, count($json1['values']) - 1);

				// Display the table header
			?>
				<div class="table-data">
					<div class="order">
						<div class="head">
							<h3>ข้อมูลนักเรียนทั้งหมด</h3>
							<i class='bx bx-search'></i>
							<i class='bx bx-filter'></i>
						</div>
						<table>
							<thead>
								<tr>
									<th>UID</th>
									<th>Student ID</th>
									<th>School</th>
									<th>Zone</th>
									<th>Last Check</th>
								</tr>
							</thead>
							<tbody>
								<?php
								for ($i = $startIndex; $i <= $endIndex; $i++) {
									echo '<tr>';
									for ($j = 0; $j < 5; $j++) {
										echo '<td>' . htmlspecialchars($json1['values'][$i][$j]) . '</td>';
									}
									echo '</tr>';
								}
								?>
							</tbody>
						</table>
					</div>
				</div>

			<?php
				// Display pagination links
				echo '<div aria-label="Page navigation example"><ul class="pagination">';

				// Display Previous Page Link
				echo '<li class="page-item">';
				echo '<a class="page-link" href="?page=' . ($currentPage - 1) . '" aria-label="Previous">';
				echo '<span aria-hidden="true">&laquo;</span>';
				echo '<span class="sr-only">Previous</span>';
				echo '</a>';
				echo '</li>';

				// Display individual page links
				for ($page = 1; $page <= $totalPages; $page++) {
					echo '<li class="page-item ';
					echo ($page == $currentPage) ? 'active' : '';
					echo '"><a class="page-link" href="?page=' . $page . '">' . $page . '</a></li>';
				}

				// Display Next Page Link
				echo '<li class="page-item">';
				echo '<a class="page-link" href="?page=' . ($currentPage + 1) . '" aria-label="Next">';
				echo '<span aria-hidden="true">&raquo;</span>';
				echo '<span class="sr-only">Next</span>';
				echo '</a>';
				echo '</li>';

				echo '</ul></div>';
			} else {
				echo '<div class="table-data"><div class="order"><p>No data available</p></div></div>';
			}
			?>

		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

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