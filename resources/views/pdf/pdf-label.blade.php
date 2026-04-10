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
            font-family: DejaVu Sans, sans-serif;
            margin: 0px;
            padding: 0px;
        }
        .circle-table {
            width: 23px;
            height: 23px;
            border-radius: 50%;
            overflow: hidden;
            background-color: #000;
            align-items: center;
            justify-content: center;
            display: flex;
        }
        .circle-table td {
            text-align: center;
            vertical-align: middle;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            padding: 0;
        }
    </style>
</head>
<body>
    <table style="width: 100%; border-collapse: collapse; padding-top: 7px; padding-left: 5px;">
        <tr>
            <td style="width: 30%; vertical-align: middle; text-align: left; padding: 0; padding-right: 4px">
                <img  src="data:image/svg+xml;base64,{{ $qrCode }}" style="width: 89px; height: 89px;" alt="QR">
            </td>
            <td style="width: 70%; vertical-align: top; text-align: left; padding-left: 3px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0; display: inline">
                            <div style="font-size: 28px; font-weight: bold; line-height: 0.9;">{{ $mpsProjekt }}</div>

                        </td>
                        <td style="padding-right: 5px; padding-top: 2px; text-align: left; vertical-align: top;">
                            @if($mistrCislo)
                                <div class="circle-table" style=" text-align: center; vertical-align: middle; font-size: 14px; font-weight: bold; color: #fff; padding-top: -2px"><span>{{ $mistrCislo }}</span></div>
                            @endif
                        </td>
                    </tr>
                </table>
                <div style="font-size: 15px; font-weight: bold; line-height: 0.9;">{{ $klicDokla }}</div>
                <div style="font-size: 18px; font-weight: bold; line-height: 0.9;">{{ $pozice }}@if(!empty($cisloPodsestavy))/{{ $cisloPodsestavy}}@endif</div>
                <div style="font-size: 18px; font-weight: bold; line-height: 0.9;">{{ $mnozstvi }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
