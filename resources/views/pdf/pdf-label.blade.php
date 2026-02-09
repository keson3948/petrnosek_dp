<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 0px; 
            padding: 0px;
        }
        body {
            font-family: DejaVu Sans, sans-serif; /* Supports UTF-8 */
            margin: 0px;
            padding: 0px;
            text-align: center;
        }
        .container {
            width: 100%;
            display: block;
            text-align: center;
            padding-left: 5px;
            padding-top: 5px; 
        }
        .qr {
            width: 80px; /* Adjust visual size on paper */
            height: auto;
            margin: 0 auto;
        }
        .info {
            font-size: 11px;
            font-weight: bold;
        }
        .date {
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="container">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <!-- QR Code Column -->
                <td style="width: 35%; vertical-align: middle; text-align: center; padding: 0;">
                    <img src="data:image/png;base64, {{ $qrCode }}" class="qr" alt="QR Code" style="min-width: 90px; max-width: 110px; height: auto;">
                </td>
                
                <!-- Text Info Column -->
                <td style="width: 65%; vertical-align: middle; text-align: left; padding-left: 5px;">
                    <div class="info" style="font-size: 30px; line-height: 1; margin: 0;">
                        {{ $projekt }}
                    </div>
                    <div class="info" style="font-size: 15px; line-height: 1; margin: 0;">
                        {{ $id }}
                    </div>
                    <div class="info" style="font-size: 15px; line-height: 1; margin: 0;">
                        1 ks
                    </div>
                    @if(isset($author))
                        <div class="date" style="font-size: 10px; line-height: 1; margin: 0;">
                            G: {{ $author }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>