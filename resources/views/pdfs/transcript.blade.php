<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lembar Kemajuan Akademik Mahasiswa - {{ $student->name }}</title>
  <style>
    @media print {
      body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 10mm;
        box-sizing: border-box;
      }
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 11pt;
      line-height: 1.3;
    }

    .info-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-gap: 15px;
      width: 100%;
      margin-top: 20px;
    }

    .info-item {
      display: flex;
      justify-content: space-between;
    }

    .info-label {
      font-weight: bold;
      width: 180px;
    }

    .info-value {
      max-width: calc(100% - 185px);
    }

    .semester-header {
      font-weight: bold;
      text-align: left;
      padding-left: 5px;
    }

    .left-align {
      text-align: left;
      padding-left: 5px;
    }

    h3 {
      text-align: center;
      font-size: 14pt;
      margin-bottom: 15px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th,
    td {
      border: 1px solid black;
      padding: 3px;
      text-align: center;
      font-size: 10pt;
    }

    th {
      background-color: #f2f2f2;
    }

    h3 {
      text-align: center;
      font-size: 14pt;
      margin-bottom: 15px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th,
    td {
      border: 1px solid black;
      padding: 3px;
      text-align: center;
      font-size: 10pt;
    }

    th {
      background-color: #f2f2f2;
    }

    @media print {
      .info-container {
        page-break-inside: avoid;
      }
    }

    @media screen and (max-width: 600px) {
      .info-container {
        grid-template-columns: 1fr;
      }
    }

  </style>
</head>
<body>
  <h3>Lembar Kemajuan Akademik Mahasiswa</h3>
  <div style="border-top: 1px solid #000000; margin: 1rem 0;"></div>

  <div class="info-container">
    <div class="info-item">
      <span class="info-label">NIM :</span>
      <span class="info-value">{{ $student->nim }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Nama :</span>
      <span class="info-value">{{ strtoupper($student->name) }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Alamat Mahasiswa :</span>
      <span class="info-value">{{ $student->domicileAddress->address ?: '--' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Kab/Kota :</span>
      <span class="info-value">{{ strtoupper($student->domicileAddress->village->district->regency->type->value) ?: '--' }} {{ strtoupper($student->domicileAddress->village->district->regency->name) ?: '--' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Kode Pos :</span>
      <span class="info-value">{{ $student->domicileAddress->village->pos_code ?: '--' }}/{{ strtoupper($student->domicileAddress->village->district->name) ?: '--' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">UPBJJ :</span>
      <span class="info-value">{{ strtoupper($student->upbjj) ?: '--' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Program Studi :</span>
      <span class="info-value">{{ $student->major->code }}/{{ strtoupper($student->major->name) }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Regis :</span>
      <span class="info-value">{{ $student->initial_registration_period ?: '--' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Jurusan Asal :</span>
      <span class="info-value">{{ strtoupper($student->origin_department) ?: '--' }}</span>
    </div>
    <div class="info-item">
      <span class="info-label">Tempat, Tanggal Lahir :</span>
      <span class="info-value">{{ $student->birth_place ?: '--' }}, {{ $student->formatted_birth_date }}</span>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>No.</th>
        <th>Kode Matakuliah</th>
        <th>Nama Matakuliah</th>
        <th>SKS</th>
        <th>Nilai</th>
        <th>Mutu</th>
        <th>Ket</th>
        <th>Masa</th>
        <th>ST</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($groupedSubjects as $semester => $subjects)
      <tr>
        <td colspan="9" class="semester-header">SEMESTER {{ strtoupper($semester) }}</td>
      </tr>
      @foreach ($subjects as $key => $value)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $value['subject']->code }}</td>
        <td class="left-align">{{ $value['subject']->name }}</td>
        <td>{{ $value['subject']->course_credit }}</td>
        <td>{{ $value['has_grade'] ? $value['grade']->grade : '-' }}</td>
        <td>{{ $value['mutu'] ?? '-' }}</td>
        <td>{{ $value['has_grade'] ? ($value['grade']->grade === "E" ? 'BL' : 'LL') : 'BL' }}</td>
        <td>{{ $value['exam_period'] ?? '-' }}</td>
        <td>{{ $value['subject']->subject_status }}</td>
      </tr>
      @endforeach
      @endforeach
    </tbody>
  </table>

  <ol>
    <li>Jumlah sks dalam Kurikulum yang ditempuh : {{ $academicInfo['credit_by_curriculum'] }} SKS</li>
    <li>Jumlah sks Alih Kredit : {{ $academicInfo['transfer_credit'] }} SKS</li>
    <li>Jumlah sks Total yang ditempuh untuk perhitungan IPK : {{ $academicInfo['credit_has_been_taken'] }} SKS</li>
    <li>Jumlah total nilai mutu : {{ $academicInfo['mutu'] }}</li>
    <li>Jumlah Total sks keseluruhan : {{ $academicInfo['total_course_credit'] }} SKS</li>
    <li>IPK : {{ $academicInfo['gpa'] }}</li>
  </ol>

</body>
</html>
