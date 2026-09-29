<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f2f2f2;
        /* Màu nền nhẹ nhàng */
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        /* Chiều cao toàn bộ màn hình */
        margin: 0;
        text-align: center;
        /* Căn giữa nội dung */
    }

    .context {
        margin: 30px;
        text-align: center;
        background: #ffffff;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .context:hover {
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
    }

    h1 {
        font-size: 60px;
        color: #ff4d4d;
        margin-bottom: 20px;
    }

    p {
        font-size: 20px;
        color: #555;
        margin-bottom: 30px;
    }

    a.redirect {
        text-decoration: none;
        background-color: #007bff;
        color: #ffffff;
        padding: 15px 25px;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        transition: background-color 0.3s, transform 0.2s;
    }

    a.redirect:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
    }

    a.redirect:active {
        transform: translateY(1px);
    }
    </style>
</head>

<body>
    <main class="container">
        <div class="context">
            <h1>404</h1>
            <p>Trang không tìm thấy!</p>
            <p>Xin lỗi, nhưng trang bạn đang truy cập không tồn tại.</p>
            <a class="redirect" href="/bandoannhanh/catalog/">Quay về trang chủ</a>
        </div>
    </main>
</body>

</html>