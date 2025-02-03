<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GradeImport implements ToCollection, WithHeadingRow
{
  /**
   * Holds any errors that occurred during the grade import process.
   *
   * @var array
   */
  protected $errors = [];

  /**
   * Holds the student identification number (NIM) for the current grade import.
   *
   * @var string
   */
  protected $nim;

  /**
   * Holds the student's major for the current grade import.
   *
   * @var string
   */
  protected $major;

  /**
   * Holds the courses for the current grade import.
   *
   * @var array
   */
  protected $courses = [];

  /**
   * @param Collection $collection
   */
  public function collection(Collection $collection)
  {
    DB::beginTransaction();
    try {
      /**
       * Holds an array of subjects for the current grade import.
       */
      $subjects = [];

      /**
       * Holds the current semester for the grade import process.
       *
       * @var int
       */
      $currentSemester = 0;

      $collection->each(function ($row) use (&$subjects, &$currentSemester) {
        /**
         * Checks if the current row's 'kode_matakuliah' column matches the pattern 'Semester (\d+)' and updates the $currentSemester variable accordingly.
         * This is used to track the current semester during the grade import process.
         */
        if (isset($row['kode_matakuliah']) && preg_match('/^Semester (\d+)$/', $row['kode_matakuliah'], $matches)) {
          $currentSemester = (int) $matches[1];
          return;
        }

        /**
         * Extracts the student identification number (NIM) from the 'kode_matakuliah' column of the current row.
         * This is used to track the NIM for the current grade import process.
         */
        if (isset($row['kode_matakuliah']) && strpos($row['kode_matakuliah'], 'NIM :') !== false) {
          $this->nim = trim(str_replace('NIM :', '', $row['kode_matakuliah']));
          return;
        }

        /**
         * Extracts the program of study (major) from the 'kode_matakuliah' column of the current row.
         * This is used to track the major for the current grade import process.
         */
        if (isset($row['kode_matakuliah']) && strpos($row['kode_matakuliah'], 'PROGRAM STUDI :') !== false) {
          $this->major = trim(str_replace('PROGRAM STUDI :', '', $row['kode_matakuliah']));
          return;
        }

        /**
         * Checks if the NIM (student identification number) or major is empty, and adds an error message if either is empty.
         * The error message includes the expected format for the NIM and major.
         */
        if (empty($this->nim) || empty($this->major)) {
          $this->errors = [
            'message' => 'Import File Gagal.',
            'errors' => [
              'file' => [
                "Nim atau Program Studi tidak boleh dikosongkan. Format yang benar adalah \nNIM : NIM MAHASISWA ||\nPROGRAM STUDI : JURUSAN MAHASISWA"
              ]
            ]
          ];
        }

        /**
         * Checks if the current row has valid data for the grade import process.
         * The valid data includes the course code, course name, credits, grade, grade points, passing status, and exam period.
         */
        $hasValidData = isset($row['kode_matakuliah']) || isset($row['matakuliah']) || isset($row['sks']) ||
          isset($row['nilai']) || isset($row['nilai_mutu']) || isset($row['kelulusan']) || isset($row['masa_ujian']);

        /**
         * Adds a new subject record to the $subjects array for the current semester, if the current row has valid data.
         * The subject record includes the course code, course name, credits, grade, grade points, passing status, and exam period.
         */
        if ($hasValidData) {
          $subjects[$currentSemester][] = [
            'kode_matakuliah' => isset($row['kode_matakuliah']) ? trim($row['kode_matakuliah']) : null,
            'matakuliah' => isset($row['matakuliah']) ? trim($row['matakuliah']) : null,
            'sks' => isset($row['sks']) ? (int) trim($row['sks']) : null,
            'nilai' => isset($row['nilai']) ? trim($row['nilai']) : null,
            'nilai_mutu' => isset($row['nilai_mutu']) ? trim($row['nilai_mutu']) : null,
            'kelulusan' => isset($row['kelulusan']) ? trim($row['kelulusan']) : null,
            'masa_ujian' => isset($row['masa_ujian']) ? trim($row['masa_ujian']) : null,
          ];
        }
      });

      /**
       * Assigns the $subjects array to the $courses property of the current instance.
       * This is used to store the grade data imported from the input file.
       */
      $this->courses = $subjects;

      DB::commit();
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error importing grade datas: ' . $e->getMessage());
      $this->errors[] = 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage();
    }
  }

  public function addError($message)
  {
    $this->errors = $message;
  }

  public function getErrors()
  {
    // return array_filter($this->errors);
    return $this->errors;
  }

  public function getNim()
  {
    return $this->nim;
  }

  public function getMajor()
  {
    return $this->major;
  }

  public function getCourses()
  {
    return $this->courses;
  }
}
