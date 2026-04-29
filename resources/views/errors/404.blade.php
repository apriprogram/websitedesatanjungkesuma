<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Halaman Tidak Ditemukan - Desa Tanjung Kesuma</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --text-main: #111827;
            --text-muted: #6b7280;
            --bg-color: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Ambient Background Blobs */
        .ambient-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .blob {
            position: absolute;
            filter: blur(100px);
            border-radius: 50%;
            opacity: 0.3;
        }

        .blob-1 {
            width: 500px;
            height: 500px;
            background: #dbeafe; /* Light blue */
            top: -100px;
            right: -100px;
        }

        .blob-2 {
            width: 600px;
            height: 600px;
            background: #ede9fe; /* Light purple */
            bottom: -150px;
            left: -150px;
        }

        .blob-3 {
            width: 400px;
            height: 400px;
            background: #eff6ff; /* Very light blue */
            top: 40%;
            left: 10%;
        }

        .container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 2rem;
            max-width: 650px;
        }

        .error-code {
            font-size: 10rem;
            font-weight: 600; /* Semibold */
            line-height: 1;
            margin-bottom: 0.5rem;
            color: #1f2937;
            letter-spacing: -0.05em;
        }

        .error-title {
            font-size: 2.5rem;
            font-weight: 600; /* Semibold */
            margin-bottom: 1.25rem;
            color: #111827;
        }

        .error-desc {
            font-size: 1.05rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
            line-height: 1.6;
            padding: 0 1rem;
        }

        .button-group {
            display: flex;
            gap: 1.25rem;
            justify-content: center;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.9rem 2rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .btn--primary {
            background-color: #111827;
            color: #ffffff;
            border: 1px solid #111827;
        }

        .btn--primary:hover {
            background-color: #000000;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn--outline {
            background-color: transparent;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }

        .btn--outline:hover {
            border-color: #111827;
            color: #111827;
            background-color: #f9fafb;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .error-code { font-size: 7rem; }
            .error-title { font-size: 1.8rem; }
            .button-group { flex-direction: column; width: 100%; max-width: 300px; margin: 0 auto; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="ambient-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    
    <div class="container">
        <div class="error-code">404</div>
        <h1 class="error-title">Oops! Halaman tidak ditemukan.</h1>
        <p class="error-desc">
            Maaf, halaman atau data yang Anda cari di website Desa Tanjung Kesuma mungkin telah dihapus, dipindahkan, atau tautan yang Anda masukkan salah.
        </p>
        <div class="button-group">
            <a href="{{ url('/') }}" class="btn btn--primary">
                Kembali ke Beranda <i class="fas fa-arrow-right"></i>
            </a>
            <a href="javascript:history.back()" class="btn btn--outline">
                Halaman Sebelumnya
            </a>
        </div>
    </div>
</body>
</html>
