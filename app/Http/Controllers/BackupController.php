<?php

namespace App\Http\Controllers;

use App\Response\AllResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    use AllResponse;
    public function backupDatabase()
    {
        if (auth('admin')->user() !== null) {
            $backup = Artisan::call('db:backup');
            if ($backup === 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Database backup successfully'
                ], 200);
            } else {
                return $this->Response(false, 'Database backup failed', 400);
            }
        }
    }
    public function ShowDatabase()
    {
        $files = Storage::files('backup/');
        $fileDetails = [];
        foreach ($files as $file) {
            $fileName = pathinfo($file, PATHINFO_BASENAME);
            $fileSize = Storage::size($file);
            $formattedSize = $this->formatFileSize($fileSize);
            $fileDetails[] = [
                'name' => $fileName,
                'size' => $formattedSize,
            ];
        }
        return response()->json(['data' => $fileDetails]);
    }
    private function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
    public function DeleteDatabase(Request $request)
    {
        $dynamicFileName = $request->input('file_name');
        $fileName = 'backup/' . $dynamicFileName;
        if (Storage::exists($fileName)) {
            Storage::delete($fileName);
            return $this->Response(true, 'Database file deleted successfully', 200);
        } else {
            return $this->Response(false, 'File not Found', 404);
        }
    }
    public function RestoreDatabase(Request $request)
    {
        $dynamicFileName = $request->input('file_name');
        $fileName = 'backup/' . $dynamicFileName;
        if (Storage::exists($fileName)) {
            DB::unprepared(Storage::path($fileName));
            // Get the full path to the file
            // $filePath = Storage::path($fileName);

            // // Perform the database restore using the custom command
            // Artisan::call('db:restore', ['file' => $filePath]);

            return response()->json(['message' => 'Database restored successfully']);
        } else {
            return response()->json(['message' => 'File not found'], 404);
        }
    }
}
