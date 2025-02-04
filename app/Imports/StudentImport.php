<?php

namespace App\Imports;

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
  /**
   * Stores a cache of villages for use in the import process.
   */
  protected $villageCache;

  /**
   * Stores any errors that occur during the import process.
   */
  protected $errors = [];

  /**
   * Stores the number of students that have been imported.
   */
  protected $imported = 0;

  /**
   * Stores the number of students that have been skipped during the import process.
   */
  protected $skipped = 0;

  /**
   * Stores the records that have been skipped during the import process.
   */
  protected $skippedRecords = [];

  /**
   * Initializes the `$villageCache` property to a new collection.
   */
  public function __construct()
  {
    $this->villageCache = collect();
  }

  protected function getVillageId($villageName)
  {
    // Jika village name kosong, return null
    if (empty($villageName)) {
      return null;
    }

    // Cek apakah village name sudah ada di cache
    if (!$this->villageCache->has($villageName)) {
      // Jika tidak ada di cache, cari di database
      $village = Village::where('name', $villageName)->first();

      if ($village) {
        // Jika ditemukan, simpan id ke cache
        $this->villageCache->put($villageName, $village->id);
      } else {
        // Jika tidak ditemukan, simpan null ke cache
        $this->villageCache->put($villageName, null);
        $this->errors = [
          'message' => 'Import File Gagal.',
          'errors' => [
            'file' => [
              "Data Desa atau Kelurahan dengan nama '$villageName' tidak ditemukan. Mohon periksa kembali."
            ]
          ]
        ];
      }
    }

    // Return id village dari cache (bisa berupa id atau null)
    return $this->villageCache->get($villageName);
  }

  protected function convertDate($date)
  {
    if ($date) {
      $dateTime = Date::excelToDateTimeObject($date);
      return $dateTime->format('Y-m-d');
    }

    return null;
  }

  /**
   * @param Collection $collection
   */
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
        $villageName = trim($row['kelurahan']);
        $nim = trim($row['nim']);
        $nik = trim($row['nik']) ?: null;
        $name = trim($row['nama']);
        $email = trim($row['email']) ?: null;
        $birthDate = trim($row['tanggal_lahir']);
        $birthPlace = trim($row['tempat_lahir']);
        $gender = trim($row['jenis_kelamin']) ?: 'unknown';
        $phoneNumber = trim($row['nomor_wa']) ?: null;
        $religion = trim($row['agama']) ?: 'unknown';
        $initialRegistrationPeriod = trim($row['regis']);
        $originDepartment = trim($row['jurusan_asal']);
        $upbjj = trim($row['upbjj']);
        $address = trim($row['alamat_lengkap']);
        $studentStatus = trim($row['status_kemahasiswaan']) ?: 1;
        $regisStatus = trim($row['status_pendaftaran']) ?: 'unknown';
        $parentName = trim($row['nama_wali']);
        $parentPhoneNumber = trim($row['nomor_telepon_wali']) ?: null;

        if ($existingNims->has($nim)) {
          $this->skipped++;
          $this->skippedRecords[] = "NIM: $nim sudah ada di database";
          continue;
        }

        if (empty($majorName) || empty($nim) || empty($name)) {
          $this->errors = [
            'message' => 'Import File Gagal.',
            'errors' => [
              'file' => [
                "Kolom 'JURUSAN' atau 'NIM' tidak boleh dikosongkan."
              ]
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
                'file' => [
                  "Program Studi '$majorName' tidak ditemukan. Mohon periksa kembali."
                ]
              ]
            ];
            throw new \Exception("Major not found");
          }
          $majorCache->put($majorName, $major->id);
        }

        $villageId = $this->getVillageId($villageName);

        try {
          $checkBirthDate = $this->convertDate($birthDate);
        } catch (\Exception $e) {
          $this->errors[] = "Format tanggal lahir tidak valid untuk NIM $nim: " . $birthDate;
          continue;
        }

        $gender = !empty($gender) ? match ($gender) {
          'Laki - Laki' => GenderType::Male->value,
          'Perempuan' => GenderType::Female->value,
          default => GenderType::Unknown->value,
        } : $gender;

        $studentStatus = !empty($studentStatus) ? match ($studentStatus) {
          'Aktif' => 1,
          'Tidak Aktif' => 0,
          default => 1,
        } : $studentStatus;

        $regisStatus = !empty($regisStatus) ? match ($regisStatus) {
          'RPL' => StudentRegistrationStatus::Rpl->value,
          'Non RPL' => StudentRegistrationStatus::NonRpl->value,
          default => StudentRegistrationStatus::Unknown->value,
        } : $regisStatus;

        $student = Student::create([
          'major_id' => $majorCache->get($majorName),
          'nim' => $nim,
          'nik' => $nik,
          'name' => $name,
          'email' => $email,
          'birth_date' => $checkBirthDate,
          'birth_place' => strtoupper($birthPlace),
          'gender' => $gender,
          'phone' => $phoneNumber,
          'religion' => strtolower($religion),
          'initial_registration_period' => strtoupper($initialRegistrationPeriod),
          'origin_department' => strtoupper($originDepartment),
          'upbjj' => strtoupper($upbjj),
          'status_registration' => $regisStatus,
          'status_activity' => $studentStatus,
          'parent_name' => $parentName,
          'parent_phone_number' => $parentPhoneNumber,
        ]);

        // Create domicile address if villageId exists
        if ($villageId) {
          $student->addresses()->create([
            'uuid' => Str::uuid(),
            'type' => AddressType::Domicile,
            'village_id' => $villageId,
            'address' => strtoupper($address)
          ]);

          // Create ID card address with same data
          $student->addresses()->create([
            'uuid' => Str::uuid(),
            'type' => AddressType::IdCard,
            'village_id' => $villageId,
            'address' => strtoupper($address)
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
