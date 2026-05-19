<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TxtExport;

class TxtToExcelController extends Controller
{
    public function index()
    {
        $exports = $this->getExportHistory();
        return view('index', compact('exports'));
    }


    public function upload(Request $request)
    {
        $request->validate([
            'txt_file'  => 'required|file|mimes:txt,csv|max:10240', 
            'delimiter' => 'required|in:comma,tab,semicolon,pipe,space',
            'has_header' => 'nullable|boolean',
            'sheet_name' => 'nullable|string|max:31',
        ]);

        $file      = $request->file('txt_file');
        $delimiter = $this->resolveDelimiter($request->input('delimiter'));
        $hasHeader = $request->boolean('has_header', true);
        $sheetName = $request->input('sheet_name', 'Sheet1') ?: 'Sheet1';
        $original  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $content = file_get_contents($file->getRealPath());
        $rows    = $this->parseTxt($content, $delimiter);

        if (empty($rows)) {
            return back()->withErrors(['txt_file' => 'The file appears to be empty or could not be parsed.']);
        }

        
        $exportFilename = $original . '_' . now()->format('Ymd_His') . '.xlsx';
        $exportPath     = 'exports/' . $exportFilename;

        Excel::store(
            new TxtExport($rows, $hasHeader, $sheetName),
            $exportPath,
            'public'
        );

        $this->pushHistory($request, $exportFilename, $file->getClientOriginalName(), count($rows));

        return redirect()->route('home')->with([
            'success'         => 'File converted successfully!',
            'export_filename' => $exportFilename,
            'row_count'       => count($rows),
            'original_name'   => $file->getClientOriginalName(),
        ]);
    }

    public function download(string $filename)
    {
        $path = storage_path('app/public/exports/' . $filename);

        abort_unless(file_exists($path), 404, 'File not found.');

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Delete a previously generated Excel file.
     */
    public function delete(string $filename)
    {
        Storage::disk('public')->delete('exports/' . $filename);

        // Remove from history
        $history = session('export_history', []);
        $history = array_filter($history, fn($e) => $e['filename'] !== $filename);
        session(['export_history' => array_values($history)]);

        return redirect()->route('home')->with('deleted', 'Export deleted.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function resolveDelimiter(string $key): string
    {
        return match ($key) {
            'comma'     => ',',
            'tab'       => "\t",
            'semicolon' => ';',
            'pipe'      => '|',
            'space'     => ' ',
            default     => ',',
        };
    }

    /**
     * Parse TXT content into a 2-D array of rows.
     * Handles Windows (\r\n), Unix (\n), and old Mac (\r) line endings.
     */
    private function parseTxt(string $content, string $delimiter): array
    {
        // Normalise line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines   = explode("\n", $content);

        $rows = [];
        foreach ($lines as $line) {
            // Skip completely blank lines
            if (trim($line) === '') {
                continue;
            }

            if ($delimiter === ',') {
                // Use str_getcsv so quoted fields are handled correctly
                $rows[] = str_getcsv($line, ',', '"', '\\');
            } else {
                $rows[] = explode($delimiter, $line);
            }
        }

        return $rows;
    }

    private function getExportHistory(): array
    {
        return session('export_history', []);
    }

    private function pushHistory(Request $request, string $filename, string $original, int $rows): void
    {
        $history = session('export_history', []);

        array_unshift($history, [
            'filename'      => $filename,
            'original_name' => $original,
            'rows'          => $rows,
            'delimiter'     => $request->input('delimiter'),
            'created_at'    => now()->format('Y-m-d H:i:s'),
            'size'          => Storage::disk('public')->size('exports/' . $filename),
        ]);

        // Keep last 20 entries
        session(['export_history' => array_slice($history, 0, 20)]);
    }
}