<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileUpload
{
  protected $maxFileSize = 1024; // KB

  protected function validateFileSize(UploadedFile $file): bool
  {
    return $file->getSize() <= ($this->maxFileSize * 1024);
  }

  /**
   * Uploads a file to the specified path and returns the upload result.
   *
   * @param UploadedFile $file The file to be uploaded.
   * @param string $path The path to upload the file to.
   * @param string|null $currentFile The path of the current file to be replaced, if any.
   * @return array An array containing the upload result, with keys 'success', 'path', and 'url'.
   */
  protected function upload(
    UploadedFile $file,
    string $path,
    string $currentFile = null
  ): array {
    try {
      // Validasi ukuran file
      if (!$this->validateFileSize($file)) {
        return [
          'success' => false,
          'message' => 'Ukuran file melebihi ' . $this->maxFileSize . 'KB'
        ];
      }

      // Proses upload yang sudah ada
      if ($currentFile && Storage::exists($currentFile)) {
        Storage::delete($currentFile);
      }

      $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
      $filePath = $file->storeAs($path, $filename);

      return [
        'success' => true,
        'path' => $filePath,
        'url' => Storage::url($filePath)
      ];
    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  /**
   * Uploads multiple files to the specified path.
   *
   * @param array $files The files to be uploaded.
   * @param string $path The path to upload the files to.
   * @return array An array of upload results, each containing 'success', 'path', and 'url' keys.
   */
  protected function uploads(
    array $files,
    string $path
  ): array {
    $uploadedFiles = [];

    foreach ($files as $file) {
      $result = $this->uploadFile($file, $path);
      if ($result['success']) {
        $uploadedFiles[] = $result;
      }
    }

    return $uploadedFiles;
  }
}
