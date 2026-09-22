<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Certificado</title>
  <style>
    @page { margin: 0; }

    html, body {
      height: 100%;
    }

    body {
      font-family: "DejaVu Sans", sans-serif;
      margin: 0;
      padding: 0;
      color: #1a1410;
    }

    .page {
      display: table;
      width: 100%;
      height: 100%;
    }

    .page-cell {
      display: table-cell;
      vertical-align: middle;
    }

    .frame {
      margin: 150px 24px 24px;
      border: 3px solid #cf9a2e;
      padding: 6px;
    }

    .frame-inner {
      border: 1px solid #cf9a2e;
      padding: 60px 60px;
      text-align: center;
    }

    .brand {
      font-size: 13px;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: #cf9a2e;
      font-weight: bold;
    }

    .title {
      font-family: Georgia, "Times New Roman", serif;
      font-size: 34px;
      font-weight: bold;
      margin: 18px 0 6px;
      color: #1a1410;
    }

    .subtitle {
      font-size: 14px;
      color: #6b5f4f;
      margin-bottom: 30px;
    }

    .lead {
      font-size: 13px;
      color: #6b5f4f;
    }

    .student-name {
      font-family: Georgia, "Times New Roman", serif;
      font-size: 30px;
      font-weight: bold;
      color: #1a1410;
      margin: 10px 0 26px;
      border-bottom: 1px solid #cf9a2e;
      display: inline-block;
      padding-bottom: 8px;
    }

    .course-name {
      font-family: Georgia, "Times New Roman", serif;
      font-size: 20px;
      font-style: italic;
      color: #1a1410;
      margin: 8px 0 40px;
    }

    .footer-table {
      width: 100%;
      margin-top: 30px;
    }

    .footer-table td {
      width: 33%;
      text-align: center;
      font-size: 12px;
      color: #6b5f4f;
      vertical-align: bottom;
    }

    .signature-line {
      border-top: 1px solid #1a1410;
      width: 200px;
      margin: 0 auto 6px;
      padding-top: 6px;
      font-weight: bold;
      color: #1a1410;
    }

    .cert-number {
      font-size: 10px;
      color: #a89a80;
      letter-spacing: 1px;
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="page-cell">
      <div class="frame">
        <div class="frame-inner">
          <div class="brand">{{ __('app.brand_name') }}</div>

          <div class="title">Certificado de Finalización</div>
          <div class="subtitle">Formación profesional en odontología estética</div>

          <div class="lead">Se otorga el presente certificado a</div>
          <div class="student-name">{{ $user->name }}</div>

          <div class="lead">por haber completado satisfactoriamente el curso</div>
          <div class="course-name">{{ $course->title }}</div>

          <table class="footer-table">
            <tr>
              <td>{{ $issuedAt->translatedFormat('d \d\e F \d\e Y') }}<br>Fecha de emisión</td>
              <td>
                <div class="signature-line">{{ __('app.brand_name') }}</div>
                Odontóloga
              </td>
              <td class="cert-number">N.º {{ $certificateNumber }}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
