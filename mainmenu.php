<?php
require 'DBcon.php';  // Ensure DBcon.php correctly establishes $conn
session_start(); // เริ่มเซสชัน

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['memid'])) {
    header("Location: index.php"); // หากยังไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปที่หน้าเข้าสู่ระบบ
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>BGDice - Board Game Dice</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="css/search.css" rel="stylesheet">
    <link href="css/imgadjust.css" rel="stylesheet">


    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <?php
    require 'DBcon.php';  // Ensure DBcon.php correctly establishes $conn
    
    // Query with INNER JOIN to get all relevant data from both tables
    $query = "SELECT m.*, c.bgclassid, c.bgclass, t.bgtypeid, t.bgtype FROM bgmanage as m 
            INNER JOIN bgclass as c ON c.bgclassid = m.ref_bgclassid 
            INNER JOIN bgtype as t ON t.bgtypeid = m.ref_bgtypeid
            ORDER BY m.bgid ASC";
    $result = $conn->query($query);

    if (!$result) {
        die("Error: " . $conn->error);
    }

    $queryrd = "SELECT m.*, c.bgclassid, c.bgclass, t.bgtypeid, t.bgtype FROM bgmanage as m 
            INNER JOIN bgclass as c ON c.bgclassid = m.ref_bgclassid 
            INNER JOIN bgtype as t ON t.bgtypeid = m.ref_bgtypeid
            ORDER BY m.bgid ASC";
    $resultrd = $conn->query($queryrd);

    if (!$resultrd) {
        die("Error: " . $conn->error);
    }

    $queryimg = "SELECT bgimg, bgid FROM bgmanage";
    $resultimg = $conn->query($queryimg);

    if (!$resultimg) {
        die("Error: " . $conn->error);
    }

    // ดึงข้อมูลจากตาราง bgclass
    $sql = "SELECT c.*, 
               (SELECT COUNT(m.bgid) 
                FROM bgmanage AS m 
                WHERE m.ref_bgclassid = c.bgclassid) AS total_count
        FROM bgclass AS c";
    $resultclass = $conn->query($sql);
    if (!$resultclass) {
        die("Error: " . $conn->error);
    }

    // Search Box
    elseif (isset($_POST['keyword'])) {
        // ดึงค่าจาก POST
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

        // เริ่มต้นสร้าง query สำหรับค้นหา
        $sql = "SELECT bgm.*, bgc.bgclass, bgt.bgtype 
                FROM bgmanage bgm
                JOIN bgclass bgc ON bgm.ref_bgclassid = bgc.bgclassid 
                JOIN bgtype bgt ON bgm.ref_bgtypeid = bgt.bgtypeid
                WHERE 1=1"; // ใช้ WHERE 1=1 เพื่อให้ต่อเงื่อนไขเพิ่มเติมได้ง่าย
    
        if (!empty($keyword)) {
            $sql .= " AND bgm.bgname LIKE '%" . $conn->real_escape_string($keyword) . "%'";
        }


        // รันคำสั่ง SQL
        $result = $conn->query($sql);
        if (!$result) {
            die("Error: " . $conn->error);
        }
    }
    ?>


    <div class="container-xxl bg-white p-0">
        <!-- Spinner Start -->
        <div id="spinner"
            class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Navbar Start -->
        <div class="container-fluid nav-bar navbar-expand-lg bg-transparent">
            <nav class="navbar navbar-expand-lg bg-white navbar-light py-0 px-4 rounded">
                <a href="profile.php" class="navbar-brand d-flex align-items-center text-center">
                    <div class="icon p-2 me-2">
                        <?php
                        $memid = $_SESSION['memid'];

                        // ดึงข้อมูลผู้ใช้
                        $user_query = "SELECT * FROM bgmem WHERE memid='$memid'";
                        $user_result = $conn->query($user_query);

                        if ($user_result->num_rows > 0) {
                            $user_data = $user_result->fetch_assoc();
                            // ตรวจสอบว่ามีข้อมูลรูปภาพหรือไม่
                        }
                        ?>
                        <img class="img-fluid"
                            src="data:image/png;base64,<?php echo htmlspecialchars($user_data['memimg'] ?: base64_encode(file_get_contents('img/all.png'))); ?>"
                            style="width: 50px; height: 50px; border-radius: 50px;">
                    </div>
                    <h1 class="m-0 text-primary">BGDice</h1>
                </a>
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto">

                        <form id="searchf" action="category.php" method="POST">
                            <div id="search">
                                <svg viewBox="0 0 420 60" xmlns="http://www.w3.org/2000/svg">
                                    <rect class="bar" />

                                    <g class="magnifier">
                                        <circle class="glass" />
                                        <line class="handle" x1="32" y1="32" x2="44" y2="44"></line>
                                    </g>

                                    <g class="sparks">
                                        <circle class="spark" />
                                        <circle class="spark" />
                                        <circle class="spark" />
                                    </g>

                                    <g class="burst pattern-one">
                                        <circle class="particle circle" />
                                        <path class="particle triangle" />
                                        <circle class="particle circle" />
                                        <path class="particle plus" />
                                        <rect class="particle rect" />
                                        <path class="particle triangle" />
                                    </g>
                                    <g class="burst pattern-two">
                                        <path class="particle plus" />
                                        <circle class="particle circle" />
                                        <path class="particle triangle" />
                                        <rect class="particle rect" />
                                        <circle class="particle circle" />
                                        <path class="particle plus" />
                                    </g>
                                    <g class="burst pattern-three">
                                        <circle class="particle circle" />
                                        <rect class="particle rect" />
                                        <path class="particle plus" />
                                        <path class="particle triangle" />
                                        <rect class="particle rect" />
                                        <path class="particle plus" />
                                    </g>
                                </svg>
                                <input type="search" name="keyword" aria-label="Search for inspiration"
                                    placeholder="Search..." />
                            </div>

                            <div id="results">
                            </div>
                        </form>

                        <a href="profile.php"
                            class="nav-item nav-link"><?php echo htmlspecialchars($user_data['memname']) ?></a>
                        <a href="mainmenu.php" class="nav-item nav-link">Home</a>
                        <a href="category.php" class="nav-item nav-link">BoardGame Category</a>
                        <a href="logout.php" class="nav-item nav-link">Log out</a>
                    </div>
            </nav>
        </div>
        <!-- Navbar End -->

        <!-- Header Start -->
        <div class="container-fluid header bg-white p-0">
            <div class="owl-carousel header-carousel">
                <div class="owl-carousel-item">
                    <img class="img-fluid collapse img-fluid-collapse imgadjust" src="img/bg1.jpg" alt="" style="height: 600px;">
                </div>
                <div class="owl-carousel-item">
                    <img class="img-fluid collapse img-fluid-collapse imgadjust" src="img/bg2.jpg" alt="" style="height: 600px;">
                </div>
                <div class="owl-carousel-item">
                    <img class="img-fluid collapse img-fluid-collapse imgadjust" src="img/bg3.jpg" alt="" style="height: 600px;">
                </div>
                <div class="owl-carousel-item">
                    <img class="img-fluid collapse img-fluid-collapse imgadjust" src="img/bg4.jpg" alt="" style="height: 600px;">
                </div>
            </div>
            <div class="row g-0 align-items-center flex-column-reverse flex-md-row">
                <div class="col-md-6 p-5 mt-lg-5">
                    <h1 class="display-5 animated fadeIn mb-4">Find A <span class="text-primary">BoardGame</span> you
                        want to try it</h1>
                    <p class="animated fadeIn mb-4 pb-2">Choose your favorite board game and have fun with it.</p>
                    <a href="category.php" class="btn btn-primary py-3 px-5 me-3 animated fadeIn">Category</a>
                </div>
            </div>
        </div>
        <!-- Header End -->


        <!-- Category Start -->
        <div class="container-xxl bg-white p-0 py-5">
            <div class="container">
                <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                    <br>
                    <h1 class="mb-3">Category</h1>
                    <p>Here is a list of all available categories.</p>
                </div>
                <div class="row g-4">
                    <?php
                    $sql = "SELECT COUNT(bgid) AS sum FROM bgmanage";

                    // Execute the query
                    $counterall = $conn->query($sql);

                    // Check if the query was successful
                    if (!$counterall) {
                        die("Error: " . $conn->error);
                    } else {
                        // Fetch the result
                        $row = $counterall->fetch_assoc(); // Fetch the associative array from the result
                    
                        // Display the total count of bgid
                        echo '<div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                            <a class="cat-item d-block bg-light text-center rounded p-3" href="category.php">
                                <div class="rounded p-4" id="All">
                                    <div class="icon mb-3">
                                        <img class="img-fluid" src="img/all.png" alt="Icon" style="width: 45px; height: 45px;">
                                    </div>
                                    <h6>Show All(' . $row['sum'] . ')</h6>
                                    <span>Show All BoardGame</span>
                                </div>
                            </a>
                        </div>';
                    }
                    ?>
                    <?php
                    if (mysqli_num_rows($resultclass) > 0) {
                        while ($row = $resultclass->fetch_assoc()) {
                            $imgDataclass = base64_encode($row['bgclassimg']);
                            $totalCount = $row['total_count'];
                            $srcc = 'data:image/jpeg;base64,' . $imgDataclass;
                            echo '
                        <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                            <a class="cat-item d-block bg-light text-center rounded p-3" href="category.php?idc=' . $row['bgclassid'] . '#category-' . $row['bgclassid'] . '">
                                <div class="rounded p-4" id="category-' . $row['bgclassid'] . '">
                                    <div class="icon mb-3">
                                        <img class="img-fluid" src="' . $srcc . '" alt="Icon" style="width: 45px; height: 45px;">
                                    </div>
                                    <h6>' . $row['bgclass'] . '</h6>
                                    <span>' . $totalCount . ' Items</span>
                                </div>
                            </a>
                        </div>';
                        }
                    } else {
                        echo "No results";
                    }
                    ?>

                    <script>
                        window.onload = function () {
                            const urlParams = new URLSearchParams(window.location.search);
                            const id = urlParams.get('idc');
                            if (id) {
                                const element = document.getElementById('category-' + id);
                                if (element) {
                                    element.scrollIntoView({
                                        behavior: 'smooth'
                                    });
                                }
                            }
                        };
                    </script>


                    <!-- Category End -->

                    <!-- BG List Start -->
                    <div class="container-xxl bg-white p-0 py-5">
                        <div class="container">
                            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s"
                                style="max-width: 600px;">
                                <br>
                                <h1 class="mb-3">Category</h1>
                                <p>Here is a list of all available categories.</p>
                            </div>
                            <div class="row g-2">
                                <!-- Start of two columns -->
                                <?php
                                if (mysqli_num_rows($resultimg) > 0) {
                                    $counterimg = 0; // ตัวนับ
                                    while ($rowimg = $resultimg->fetch_assoc()) {
                                        if ($counterimg == 6) { // หยุดเมื่อครบ 6 รูป
                                            break;
                                        }
                                        $imgDataimg = base64_encode($rowimg['bgimg']);
                                        $srcimg = 'data:image/jpeg;base64,' . $imgDataimg;
                                        echo '
                    <div class="property-item position-relative rounded col-lg-6 col-md-6 mb-2">
                        <a href="boardgame.php?bgid=' . intval($rowimg['bgid']) . '">
                            <img class="property-item position-relative img-fluid rounded bg-light p-1 w-100 mb-2 wow fadeInUp" data-wow-delay="0.1s" src="' . $srcimg . '" alt="" style="height: 400px; width: 100%; object-fit: cover;">
                        </a>
                    </div>';
                                        $counterimg++; // เพิ่มตัวนับ
                                    }
                                } else {
                                    echo '<div>No Picture</div>';
                                }
                                ?>
                                <!-- End of two columns -->
                            </div>
                        </div>
                    </div>
                    <!-- BG List End -->




                    <!-- About Start -->
                    <?php
                    if (mysqli_num_rows($resultrd) > 0) {
                        // สร้าง array เพื่อเก็บข้อมูลทั้งหมด
                        $rows = array();
                        while ($row = mysqli_fetch_assoc($resultrd)) {
                            $rows[] = $row;
                        }

                        // สุ่ม index ของ array
                        $randomIndex = array_rand($rows);

                        // ดึงข้อมูลสุ่ม
                        $randomRow = $rows[$randomIndex];

                        // ประมวลผลข้อมูล
                        $imgData = base64_encode($randomRow['bgimg']);
                        $src = 'data:image/jpeg;base64,' . $imgData;

                        // กำหนดความยาวสูงสุดสำหรับ bgrule
                        $maxLength = 100;
                        $bgrule = $randomRow['bgrule'];

                        // จำกัดความยาวของ bgrule
                        if (strlen($bgrule) > $maxLength) {
                            $bgrule = substr($bgrule, 0, $maxLength) . '...'; // เพิ่ม '...' หากข้อมูลยาวเกิน 200 ตัว
                        }

                        echo '
                        <div class="container-xxl py-5">
                        <div class="container">
                            <div class="row g-5 align-items-center">
                                <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                                <div class="property-item rounded about-img position-relative overflow-hidden p-5 pe-0">
                            <img class="img-fluid w-100 rounded" src="' . $src . '">
                        </div>
                    </div>

                        <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                            <h1 class="fa fa-map-marker-alt mb-4">' . htmlspecialchars($randomRow['bgname']) . '</h1>
                            <p class="mb-4">' . htmlspecialchars($randomRow['bgdescript']) . '</p>
                            <p><i class="text-primary me-3"></i>Difficult : ' . htmlspecialchars($randomRow['bgdiff']) . '/5</p>
                            <p><i class="text-primary me-3"></i>Player : ' . htmlspecialchars($randomRow['bgplayer']) . '</p>
                            <p><i class="text-primary me-3"></i>Price : ' . htmlspecialchars($randomRow['bgprice']) . ' ฿</p>
                            <h1 class="fa fa-ruler-combined mb-4">Rule</h1>
                            <p class="mb-4">' . htmlspecialchars($bgrule) . '</p>
                            <a class="btn btn-primary py-3 px-5 mt-3" href="boardgame.php?bgid=' . intval($randomRow['bgid']) . '">Read More</a>
                        </div>';
                    } else {
                        echo "No results";
                    }
                    ?>

                    <a id="randomButton" class="btn btn-primary py-3 px-5 mt-3"
                        href="?random=1&submitted=true">Random</a>

                    <script>
                        window.onload = function () {
                            // ตรวจสอบว่ามีการกดปุ่มและมีตำแหน่งที่บันทึกไว้ใน sessionStorage หรือไม่
                            const savedPosition = sessionStorage.getItem('scrollPosition');
                            if (window.location.search.includes('submitted=true') && savedPosition) {
                                // เลื่อนกลับไปที่ตำแหน่งเดิมที่บันทึกไว้
                                window.scrollTo({
                                    top: parseInt(savedPosition),
                                    behavior: 'smooth'
                                });
                                sessionStorage.removeItem('scrollPosition'); // ลบข้อมูลเมื่อเลื่อนเสร็จแล้ว
                            }
                        };

                        // ปรับให้ปุ่ม Random ส่งข้อมูลและบันทึกตำแหน่งการเลื่อน
                        document.getElementById('randomButton').addEventListener('click', function (event) {
                            // บันทึกตำแหน่งการเลื่อนปัจจุบันใน sessionStorage
                            sessionStorage.setItem('scrollPosition', window.scrollY);

                            // อัพเดต URL ด้วยพารามิเตอร์เพื่อบอกว่ามีการกดปุ่ม
                            const url = new URL(window.location.href);
                            url.searchParams.set('submitted', 'true');
                            window.location.href = url.toString(); // ส่งไปยัง URL ใหม่เพื่อรีเฟรชหน้า
                        });
                    </script>


                    <!-- About End -->


                    <!-- Property List Start -->
                    <div class="container-xxl py-5">
                        <div class="container">
                            <div class="row g-0 gx-5 align-items-end">
                                <div class="col-lg-6">
                                    <div class="text-start mx-auto mb-5 wow slideInLeft" data-wow-delay="0.1s">
                                        <h1 class="mb-3">BoardGame List</h1>
                                        <p>List showing all the board games available in the system. Find
                                            the board games you like and choose to buy.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <?php
                                if (mysqli_num_rows($result) > 0) {
                                    $counter = 0; // ตัวนับ
                                    while ($row = $result->fetch_assoc()) {
                                        if ($counter == 6) { // ถ้าตัวนับถึง 6 หยุดการวนลูป
                                            break;
                                        }
                                        $imgData = base64_encode($row['bgimg']);
                                        $src = 'data:image/jpeg;base64,' . $imgData;

                                        // กำหนดความยาวสูงสุดสำหรับ bgdescript
                                        $maxlen = 210;
                                        $bgdescript = $row['bgdescript'];

                                        // จำกัดความยาวของ bgdescript
                                        if (strlen($bgdescript) > $maxlen) {
                                            $bgdescript = substr($bgdescript, 0, $maxlen) . '...'; // เพิ่ม '...' หากข้อมูลยาวเกิน 100 ตัว
                                        }

                                        echo '
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="property-item rounded overflow-hidden shadow">
                        <div class="position-relative overflow-hidden">
                            <a href="boardgame.php?bgid=' . intval($row['bgid']) . '"><img class="img-fluid w-100" style="height: 250px; object-fit: cover;" src="' . $src . '" alt="Property Image"></a>
                            <div class="bg-primary rounded text-white position-absolute start-0 top-0 m-4 py-1 px-3">
                                ' . htmlspecialchars($row['bgclass']) . '
                            </div>
                        </div>
                        <div class="p-4 pb-0">
                            <h5 class="text-primary mb-3">' . htmlspecialchars($row['bgprice']) . '฿</h5>
                            <a class="d-block h5 mb-2" href="boardgame.php?bgid=' . intval($row['bgid']) . '">' . htmlspecialchars($row['bgname']) . '</a>
                            <p><i class="fa fa-map-marker-alt text-primary me-2"></i>' . htmlspecialchars($bgdescript) . '</p>
                        </div>
                        <div class="d-flex border-top">
                            <small class="flex-fill text-center border-end py-2"><i class="fa fa-ruler-combined text-primary me-2"></i>' . htmlspecialchars($row['bgtype']) . '</small>
                        </div>
                    </div>
                </div>';
                                        $counter++; // เพิ่มตัวนับ
                                    }
                                } else {
                                    echo "No results";
                                }
                                $conn->close();
                                ?>
                                <div class="row g-4">
                                    <div class="col-12 text-center">
                                        <a id="morelist" class="btn btn-primary py-3 px-5 mt-3" href="category.php">More
                                            Lists</a>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Property List End -->

                        <!-- Footer Start -->
                        <div class="container-fluid bg-dark text-white-50 footer pt-5 mt-5 wow fadeIn"
                            data-wow-delay="0.1s">
                            <div class="container py-5">
                                <div class="row g-5">
                                    <div class="col-lg-6 col-md-6 ">
                                        <h5 class="text-white mb-4">Contact us</h5>
                                        <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>RajaRajamangala
                                            University of Technology Thanyaburi.</p>
                                        <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+660810126012</p>
                                        <p class="mb-2"><i class="fa fa-envelope me-3"></i>ugkongthong@gmail.com</p>
                                        <div class="d-flex pt-2">
                                            <a class="btn btn-outline-light btn-social"
                                                href="https://x.com/SuzuMiya__YuuGi"><i class="fab fa-twitter"></i></a>
                                            <a class="btn btn-outline-light btn-social"
                                                href="https://www.facebook.com/ug.kongthong"><i
                                                    class="fab fa-facebook-f"></i></a>
                                            <a class="btn btn-outline-light btn-social"
                                                href="https://www.youtube.com/@SuzuMiyaYuuGi"><i
                                                    class="fab fa-youtube"></i></a>
                                            <a class="btn btn-outline-light btn-social"
                                                href="https://github.com/SuzuMiyaYuuGi/webapp67"><i
                                                    class="fab fa-linkedin-in"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <h5 class="text-white mb-4">Quick Links</h5>
                                        <a class="btn btn-link text-white-50" href="mainmenu.php">Home</a>
                                        <a class="btn btn-link text-white-50" href="Category.php">Category</a>
                                        <a class="btn btn-link text-white-50" href="profile.php">Profile</a>
                                    </div>
                                </div>
                            </div>
                            <div class="container">
                                <div class="copyright">
                                    <div class="row">
                                        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                                            &copy; <a class="border-bottom"
                                                href="https://www.facebook.com/ug.kongthong">BGDice</a>, Mini Project
                                            Web-App.
                                            Designed By <a class="border-bottom"
                                                href="https://www.facebook.com/ug.kongthong">AGP</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Footer End -->


                        <!-- Back to Top -->
                        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i
                                class="bi bi-arrow-up"></i></a>
                    </div>

                    <!-- JavaScript Libraries -->
                    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js">
                    </script>
                    <script src="lib/wow/wow.min.js"></script>
                    <script src="lib/easing/easing.min.js"></script>
                    <script src="lib/waypoints/waypoints.min.js"></script>
                    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

                    <!-- Template Javascript -->
                    <script src="js/main.js"></script>
</body>

</html>