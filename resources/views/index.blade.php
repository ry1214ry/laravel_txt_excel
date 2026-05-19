@php
    $hasViteAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));

    $exportCount = count($exports);

    $totalRows = array_reduce($exports, function ($carry, $export) {
        return $carry + (int) ($export['rows'] ?? 0);
    }, 0);

    $latestExport = $exports[0]['created_at'] ?? 'No exports yet';

    $delimiterLabels = [
        'comma' => 'Comma',
        'tab' => 'Tab',
        'semicolon' => 'Semicolon',
        'pipe' => 'Pipe',
        'space' => 'Space',
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TXT to Excel Converter</title>

    @if ($hasViteAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, sans-serif;">

    <!-- Main Container -->
    <div style="max-width:1100px; margin:30px auto; padding:20px;">

        <!-- Header -->
        <div style="
            background:white;
            padding:25px;
            border-radius:10px;
            margin-bottom:25px;
            box-shadow:0 2px 10px rgba(0,0,0,0.1);
        ">
            <h1 style="margin:0; color:#333;">
                TXT to Excel Converter
            </h1>

            <p style="margin-top:10px; color:#666;">
                Upload TXT or CSV file and convert to Excel easily.
            </p>

            <!-- Stats -->
            <div style="
                display:flex;
                gap:20px;
                margin-top:20px;
                flex-wrap:wrap;
            ">

                <div style="
                    background:#007bff;
                    color:white;
                    padding:15px;
                    border-radius:8px;
                    min-width:180px;
                ">
                    <small>Total Exports</small>
                    <h2 style="margin:5px 0;">
                        {{ $exportCount }}
                    </h2>
                </div>

                <div style="
                    background:#28a745;
                    color:white;
                    padding:15px;
                    border-radius:8px;
                    min-width:180px;
                ">
                    <small>Total Rows</small>
                    <h2 style="margin:5px 0;">
                        {{ number_format($totalRows) }}
                    </h2>
                </div>

                <div style="
                    background:#6c757d;
                    color:white;
                    padding:15px;
                    border-radius:8px;
                    min-width:220px;
                ">
                    <small>Latest Export</small>
                    <h4 style="margin:5px 0;">
                        {{ $latestExport }}
                    </h4>
                </div>

            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div style="
                background:#d4edda;
                color:#155724;
                padding:20px;
                border-radius:8px;
                margin-bottom:20px;
            ">

                <strong>{{ session('success') }}</strong>

                <div style="margin-top:15px;">

                    <a href="{{ route('download', session('export_filename')) }}"
                       style="
                            background:#28a745;
                            color:white;
                            text-decoration:none;
                            padding:10px 18px;
                            border-radius:5px;
                            display:inline-block;
                       ">
                        Download Excel
                    </a>

                </div>

            </div>
        @endif

        <!-- Delete Message -->
        @if (session('deleted'))
            <div style="
                background:#fff3cd;
                color:#856404;
                padding:15px;
                border-radius:8px;
                margin-bottom:20px;
            ">
                {{ session('deleted') }}
            </div>
        @endif

        <!-- Error Message -->
        @if ($errors->any())
            <div style="
                background:#f8d7da;
                color:#721c24;
                padding:20px;
                border-radius:8px;
                margin-bottom:20px;
            ">

                <strong>Please fix the following errors:</strong>

                <ul style="margin-top:10px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif

        <!-- Upload Form -->
        <div style="
            background:white;
            padding:25px;
            border-radius:10px;
            margin-bottom:30px;
            box-shadow:0 2px 10px rgba(0,0,0,0.1);
        ">

            <h2 style="margin-top:0; color:#333;">
                Upload File
            </h2>

            <form action="{{ route('upload') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <!-- File Input -->
                <div style="margin-bottom:20px;">

                    <label style="display:block; margin-bottom:8px;">
                        Select TXT or CSV File
                    </label>

                    <input type="file"
                           name="txt_file"
                           accept=".txt,.csv"
                           required
                           style="
                                width:100%;
                                padding:10px;
                                border:1px solid #ccc;
                                border-radius:5px;
                           ">

                </div>

                <!-- Delimiter -->
                <div style="margin-bottom:20px;">

                    <label style="display:block; margin-bottom:8px;">
                        Select Delimiter
                    </label>

                    <select name="delimiter"
                            style="
                                width:100%;
                                padding:10px;
                                border:1px solid #ccc;
                                border-radius:5px;
                            ">

                        <option value="comma">Comma (,)</option>
                        <option value="tab">Tab</option>
                        <option value="semicolon">Semicolon (;)</option>
                        <option value="pipe">Pipe (|)</option>
                        <option value="space">Space</option>

                    </select>

                </div>

                <!-- Sheet Name -->
                <div style="margin-bottom:20px;">

                    <label style="display:block; margin-bottom:8px;">
                        Sheet Name
                    </label>

                    <input type="text"
                           name="sheet_name"
                           value="{{ old('sheet_name', 'Sheet1') }}"
                           maxlength="31"
                           style="
                                width:100%;
                                padding:10px;
                                border:1px solid #ccc;
                                border-radius:5px;
                           ">

                </div>

                <!-- Header -->
                <div style="margin-bottom:25px;">

                    <label>
                        <input type="checkbox"
                               name="has_header"
                               value="1"
                               checked>

                        First row is header
                    </label>

                </div>

                <!-- Submit Button -->
                <button type="submit"
                        style="
                            background:#007bff;
                            color:white;
                            border:none;
                            padding:12px 25px;
                            border-radius:5px;
                            cursor:pointer;
                            font-size:16px;
                        ">
                    Convert to Excel
                </button>

            </form>

        </div>

        <!-- Export History -->
        <div style="
            background:white;
            padding:25px;
            border-radius:10px;
            box-shadow:0 2px 10px rgba(0,0,0,0.1);
        ">

            <h2 style="margin-top:0;">
                Recent Exports
            </h2>

            @if ($exportCount > 0)

                <div style="overflow-x:auto;">

                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        margin-top:20px;
                    ">

                        <thead>

                            <tr style="background:#007bff; color:white;">

                                <th style="padding:12px; text-align:left;">
                                    File Name
                                </th>

                                <th style="padding:12px; text-align:left;">
                                    Delimiter
                                </th>

                                <th style="padding:12px; text-align:left;">
                                    Rows
                                </th>

                                <th style="padding:12px; text-align:left;">
                                    Size
                                </th>

                                <th style="padding:12px; text-align:left;">
                                    Date
                                </th>

                                <th style="padding:12px; text-align:left;">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($exports as $export)

                                <tr style="border-bottom:1px solid #ddd;">

                                    <td style="padding:12px;">
                                        {{ $export['original_name'] }}
                                    </td>

                                    <td style="padding:12px;">
                                        {{ $delimiterLabels[$export['delimiter']] ?? ucfirst($export['delimiter']) }}
                                    </td>

                                    <td style="padding:12px;">
                                        {{ number_format($export['rows']) }}
                                    </td>

                                    <td style="padding:12px;">
                                        {{ number_format($export['size'] / 1024, 1) }} KB
                                    </td>

                                    <td style="padding:12px;">
                                        {{ $export['created_at'] }}
                                    </td>

                                    <td style="padding:12px;">

                                        <!-- Download -->
                                        <a href="{{ route('download', $export['filename']) }}"
                                           style="
                                                background:#28a745;
                                                color:white;
                                                text-decoration:none;
                                                padding:8px 12px;
                                                border-radius:5px;
                                                display:inline-block;
                                                margin-bottom:5px;
                                           ">
                                            Download
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('delete', $export['filename']) }}"
                                              method="POST"
                                              style="display:inline-block;"
                                              onsubmit="return confirm('Delete this export?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    style="
                                                        background:#dc3545;
                                                        color:white;
                                                        border:none;
                                                        padding:8px 12px;
                                                        border-radius:5px;
                                                        cursor:pointer;
                                                    ">
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div style="
                    background:#f8f9fa;
                    padding:20px;
                    border-radius:8px;
                    text-align:center;
                    color:#666;
                ">
                    No exports yet.
                </div>

            @endif

        </div>

    </div>

</body>
</html>