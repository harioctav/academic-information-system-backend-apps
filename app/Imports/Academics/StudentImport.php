<?php

namespace App\Imports\Academics;

use App\Enums\Academics\AddressType;
use App\Enums\Academics\StudentRegistrationStatus;
use App\Enums\GenderType;
use App\Models\Major;
use App\Models\Student;
use App\Models\Village;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Str;

class StudentImport implements ToCollection, WithHeadingRow
{
  protected $villageCache;
  protected $errors = [];
  protected $imported = 0;
  protected $skipped = 0;
  protected $skippedRecords = [];

  public function __construct()
  {
    $this->villageCache = collect();
  }

  protected function convertDate($date)
  {
    if ($date) {
      $dateTime = Date::excelToDateTimeObject($date);
      return $dateTime->format('Y-m-d');
    }
    return null;
  }

  protected function getVillageData($villageName, $districtName, $regencyName, $provinceName)
  {
    // Handle empty case
    if (empty($villageName) || empty($districtName) || empty($regencyName) || empty($provinceName)) {
      return null;
    }

    $cacheKey = "{$provinceName}|{$regencyName}|{$districtName}|{$villageName}";

    if (!$this->villageCache->has($cacheKey)) {
      // Clean and standardize input
      $villageName = str_replace(['Desa ', 'Kelurahan ', 'Desa/Kelurahan '], '', $villageName);
      $districtName = str_replace(['Kecamatan ', 'Kec. '], '', $districtName);
      $regencyName = str_replace(['Kabupaten ', 'Kab. ', 'Kota '], '', $regencyName);

      // More flexible search query
      $village = Village::query()
        ->where(function ($query) use ($villageName) {
          $query->where('name', 'LIKE', $villageName)
            ->orWhere('name', 'LIKE', "DESA $villageName")
            ->orWhere('name', 'LIKE', "KELURAHAN $villageName");
        })
        ->whereHas('district', function ($query) use ($districtName) {
          $query->where(function ($q) use ($districtName) {
            $q->where('name', 'LIKE', $districtName)
              ->orWhere('name', 'LIKE', "KECAMATAN $districtName");
          });
        })
        ->whereHas('district.regency', function ($query) use ($regencyName) {
          $query->where(function ($q) use ($regencyName) {
            $q->where('name', 'LIKE', "%$regencyName%");
          });
        })
        ->whereHas('district.regency.province', function ($query) use ($provinceName) {
          $query->where('name', 'LIKE', "%$provinceName%");
        })
        ->first();

      Log::info("Village search", [
        'input' => [
          'village' => $villageName,
          'district' => $districtName,
          'regency' => $regencyName,
          'province' => $provinceName
        ],
        'found' => $village ? true : false,
        'village_id' => $village ? $village->id : null
      ]);

      $this->villageCache->put($cacheKey, $village ? $village->id : null);
    }

    return $this->villageCache->get($cacheKey);
  }

  public function collection(Collection $collection)
  {
    DB::beginTransaction();
    try {
      $majorCache = collect();
      $existingNims = Student::pluck('nim')->flip();

      foreach ($collection as $row) {
        if ($row->filter()->isEmpty()) {
          continue;
        }

        $majorName = trim($row['jurusan']);
        $nim = trim($row['nim']);
        $name = trim($row['nama_mahasiswa']);
        $phoneNumber = trim($row['nomor_telepon']) ?: null;
        $birthPlace = trim($row['tempat_lahir']);
        $birthDate = trim($row['tanggal_lahir']);
        $gender = trim($row['jenis_kelamin']) ?: 'unknown';
        $religion = trim($row['agama']) ?: 'unknown';
        $initialRegistrationPeriod = trim($row['registrasi']);
        $originDepartment = trim($row['jurusan_asal']);
        $regisStatus = trim($row['status_pendaftaran']) ?: 'unknown';
        $villageName = trim($row['desa_atau_kelurahan']);
        $districtName = trim($row['kecamatan']);
        $regencyName = trim($row['kabupaten_atau_kota']);
        $provinceName = trim($row['provinsi']);
        $fullAddress = trim($row['alamat_lengkap']);

        if ($existingNims->has($nim)) {
          $this->skipped++;
          $this->skippedRecords[] = "NIM: $nim sudah ada di database";
          continue;
        }

        if (empty($majorName) || empty($nim) || empty($name)) {
          $this->errors = [
            'message' => 'Import File Gagal.',
            'errors' => [
              'file' => ["Kolom 'JURUSAN', 'NIM', atau 'NAMA MAHASISWA' tidak boleh dikosongkan."]
            ]
          ];
          throw new \Exception("Required fields missing");
        }

        if (!$majorCache->has($majorName)) {
          $major = Major::where('name', $majorName)->first();
          if (!$major) {
            $this->errors = [
              'message' => 'Import File Gagal.',
              'errors' => [
                'file' => ["Program Studi '$majorName' tidak ditemukan."]
              ]
            ];
            throw new \Exception("Major not found");
          }
          $majorCache->put($majorName, $major->id);
        }

        $villageId = $this->getVillageData($villageName, $districtName, $regencyName, $provinceName);

        $gender = match (strtolower($gender)) {
          'laki - laki' => GenderType::Male->value,
          'perempuan' => GenderType::Female->value,
          default => GenderType::Unknown->value,
        };

        $regisStatus = match (strtoupper($regisStatus)) {
          'RPL' => StudentRegistrationStatus::Rpl->value,
          'NON RPL' => StudentRegistrationStatus::NonRpl->value,
          default => StudentRegistrationStatus::Unknown->value,
        };

        $student = Student::create([
          'uuid' => Str::uuid(),
          'major_id' => $majorCache->get($majorName),
          'nim' => $nim,
          'name' => $name,
          'phone' => $phoneNumber,
          'birth_place' => strtoupper($birthPlace),
          'birth_date' => $this->convertDate($birthDate),
          'gender' => $gender,
          'religion' => strtolower($religion),
          'initial_registration_period' => strtoupper($initialRegistrationPeriod),
          'origin_department' => strtoupper($originDepartment),
          'status_registration' => $regisStatus,
          'status_activity' => 1,
        ]);

        if ($villageId) {
          $addresses = [
            [
              'uuid' => Str::uuid(),
              'type' => AddressType::Domicile->value,
              'village_id' => $villageId,
              'address' => strtoupper($fullAddress)
            ],
            [
              'uuid' => Str::uuid(),
              'type' => AddressType::IdCard->value,
              'village_id' => $villageId,
              'address' => strtoupper($fullAddress)
            ]
          ];

          foreach ($addresses as $addressData) {
            $address = $student->addresses()->create($addressData);
            Log::info("Address created", [
              'student_id' => $student->id,
              'address_id' => $address->id,
              'village_id' => $villageId
            ]);
          }
        } else {
          Log::warning("Address not created - Village not found", [
            'student_id' => $student->id,
            'village_name' => $villageName,
            'district_name' => $districtName,
            'regency_name' => $regencyName,
            'province_name' => $provinceName
          ]);
        }

        $this->imported++;
        $existingNims->put($nim, true);
      }

      DB::commit();
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error importing students: ' . $e->getMessage());
      throw $e;
    }
  }

  public function getErrors()
  {
    return $this->errors;
  }

  public function getImportedCount()
  {
    return $this->imported;
  }

  public function getSkippedCount()
  {
    return $this->skipped;
  }

  public function getSkippedRecords()
  {
    return $this->skippedRecords;
  }
}
