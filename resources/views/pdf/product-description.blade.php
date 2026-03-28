<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 13px;
            line-height: 1.6;
            margin: 32px;
        }

        .eyebrow {
            color: #6366f1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        h1 {
            font-size: 26px;
            margin: 0 0 8px;
        }

        .subtitle {
            color: #6b7280;
            margin: 0 0 24px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }

        .meta td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
        }

        .meta .label {
            width: 150px;
            font-weight: 700;
            background: #f9fafb;
        }

        .content {
            white-space: pre-line;
        }
    </style>
</head>
<body>
    <div class="eyebrow">Describr Export</div>
    <h1>{{ $title }}</h1>
    <p class="subtitle">{{ $subtitle }}</p>

    <table class="meta">
        @foreach ($meta as $label => $value)
            @continue(blank($value))
            <tr>
                <td class="label">{{ $label }}</td>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
    </table>

    <div class="content">{{ $content }}</div>
</body>
</html>
