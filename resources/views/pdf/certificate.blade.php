<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }

        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", sans-serif;
            color: #0D0D0D;
        }

        .sheet   { padding: 26px; }
        .frame   { border: 7px solid #4B2E83; padding: 5px; }
        .inner   { border: 2px solid #C9A227; padding: 28px 34px 24px 34px; text-align: center; }

        /* Logo lives at public/img/crest.png. dompdf needs an absolute
           filesystem path via public_path(), never a URL. */
        .logo { max-height: 86px; margin-bottom: 10px; }

        .crest {
            width: 74px;
            height: 74px;
            margin: 0 auto 10px auto;
            border: 3px solid #C9A227;
            border-radius: 37px;
            background-color: #4B2E83;
            color: #C9A227;
            font-size: 15px;
            font-weight: bold;
            line-height: 68px;
            letter-spacing: 1px;
        }

        .org      { font-size: 11.5px; letter-spacing: 1.4px; color: #4B2E83; font-weight: bold; }
        .orgsub   { font-size: 8px;  letter-spacing: 1.6px; color: #6b7280; margin-top: 3px; }
        .rule     { border-top: 1px solid #C9A227; width: 180px; margin: 14px auto; }
        .kicker   { font-size: 12px; letter-spacing: 5px; color: #6b7280; margin-top: 4px; }
        .heading  { font-size: 30px; letter-spacing: 2px; color: #4B2E83; font-weight: bold; margin-top: 6px; }
        .presented{ font-size: 11px; color: #4b5563; margin-top: 18px; }
        .name     { font-size: 38px; font-weight: bold; margin-top: 6px; color: #0D0D0D; }
        .namerule { border-top: 2px solid #C9A227; width: 400px; margin: 8px auto 0 auto; }
        .body     { font-size: 11px; color: #4b5563; margin-top: 16px; }
        .title    { font-size: 20px; font-weight: bold; color: #4B2E83; margin-top: 6px; }
        .detail   { font-size: 11px; color: #4b5563; margin-top: 10px; }

        .footer      { margin-top: 30px; }
        .footer td   { font-size: 9px; color: #6b7280; vertical-align: bottom; }
        .sigline     { border-top: 1px solid #9ca3af; padding-top: 4px; }
        .serial      { font-size: 8px; color: #9ca3af; letter-spacing: 1px; }
    </style>
</head>
<body>
<div class="sheet">
    <div class="frame">
        <div class="inner">

            @php $crest = public_path('img/crest.png'); @endphp
            @if (file_exists($crest))
                <img src="{{ $crest }}" class="logo" alt="IPABOA">
            @else
                <div class="crest">IPABOA</div>
            @endif

            <div class="org">INTERNATIONAL PRO-AM BASKETBALL OFFICIALS ASSOCIATION</div>
            <div class="orgsub">BASKETBALL OFFICIATING &middot; TRAINING &amp; DEVELOPMENT</div>

            <div class="rule"></div>

            <div class="kicker">CERTIFICATE OF COMPLETION</div>
            <div class="heading">{{ strtoupper($certificate->sourceLabel()) }}</div>

            <div class="presented">This certifies that</div>
            <div class="name">{{ $certificate->user->name }}</div>
            <div class="namerule"></div>

            <div class="body">has successfully completed</div>
            <div class="title">{{ $certificate->title }}</div>

            <div class="detail">{{ $detail }}</div>

            <table class="footer" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="34%">
                        <div class="sigline">Camp President</div>
                    </td>
                    <td width="32%" align="center">
                        <div class="serial">{{ $certificate->serial }}</div>
                    </td>
                    <td width="34%" align="right">
                        <div class="sigline">Issued {{ $certificate->issued_at->format('F j, Y') }}</div>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>
</body>
</html>
