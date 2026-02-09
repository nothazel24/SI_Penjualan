<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #f3f4f6;
            margin: 0;
            padding: 40px;
        }

        .container {
            background: #ffffff;
            padding: 24px;
            border-radius: 6px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .mb-6 {
            margin-bottom: 24px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }

        th {
            background: #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="container">
        @yield('content')
    </div>
</body>

</html>
