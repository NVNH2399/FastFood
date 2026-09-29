<link rel="stylesheet" href="../pages/css/style_banner.css">

<!-- Banner Slideshow -->
<section class="banner-slideshow">
    <?php
    include '../connect.php';
    // Lấy banner từ database
    $sql = "SELECT * FROM banners WHERE status = 'active' ORDER BY sort_order ASC";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        while($banner = $result->fetch_assoc()) {
            ?>
    <div class="slide fade">
        <a href="<?php echo $banner['link']; ?>">
            <img src="<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>">
        </a>
    </div>
    <?php
        }
    } else {
        // Hiển thị banner mặc định nếu không có banner trong database
        ?>
    <div class="slide fade">
        <img src="../upload/banner/banner1.jpg" alt="Banner Default">
    </div>
    <?php
    }
    ?>

    <!-- Nút điều hướng -->
    <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
    <a class="next" onclick="plusSlides(1)">&#10095;</a>

    <!-- Chấm tròn điều hướng -->
    <div class="dots" style="text-align:center">
        <?php
        if($result->num_rows > 0) {
            $result->data_seek(0); // Reset con trỏ về đầu result set
            $i = 1;
            while($banner = $result->fetch_assoc()) {
                echo "<span class='dot' onclick='currentSlide($i)'></span>";
                $i++;
            }
        } else {
            echo "<span class='dot' onclick='currentSlide(1)'></span>";
        }
        ?>
    </div>
</section>

<script src="../pages/js/script.js"></script>