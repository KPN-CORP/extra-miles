<!DOCTYPE html>
<html>
<head>
    <title>QR Attendance</title>
    <script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fdf7ef;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            display: flex;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            width: 75%;
        }

        .left {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .right {
            flex: 1;
            padding-left: 30px;
            font-size: 20px;
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
        }

        .label {
            font-weight: bold;
            color: #c10000;
        }

        .detail {
            margin-bottom: 15px;
        }

        .detail span {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="left">
            <div id="qrcode"></div>
        </div>
        <div class="right">
            <h3>Scan QR for Attendance</h3>
            <div class="detail">
                <div class="label">Event Name :</div>
                <span>{{ $event->title }}</span>
            </div>
            <div class="detail">
                <div class="label">Schedule :</div>
                <span>Start : {{ \Carbon\Carbon::parse($event->start_date)->format('j F Y').', '.$event->time_start }}</span>
                <span>End : {{ \Carbon\Carbon::parse($event->end_date)->format('j F Y').', '.$event->time_end }}</span>
            </div>
            <div class="detail">
                <div class="label">Description :</div>
                <span>{!! $event->description !!}</span>
            </div>
        </div>
    </div>

    <script>
        const qrCode = new QRCodeStyling({
            width: 400,
            height: 400,
            type: "canvas",
            data: "{{ $url }}", // Gantilah dengan URL Anda jika menggunakan Laravel
            image: "{{ asset('storage/img/logo-sm_ori.png') }}", // Ganti sesuai path gambar Anda
            dotsOptions: {
                color: "#000",
                type: "rounded"
            },
            backgroundOptions: {
                color: "#ffffff"
            },
            imageOptions: {
                crossOrigin: "anonymous",
                margin: 10
            }
        });

        qrCode.append(document.getElementById("qrcode"));
    </script>
</body>
</html>