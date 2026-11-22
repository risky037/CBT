<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'siswa')->orderBy('kelas')->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        $students = $query->paginate(20);
        $classes = User::where('role', 'siswa')
            ->whereNotNull('kelas')
            ->distinct()
            ->pluck('kelas');

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'kelas' => 'required|string|max:50',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'kelas' => $request->kelas,
            'role' => 'siswa',
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Akun siswa berhasil ditambahkan.');
    }

    public function update(Request $request, User $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kelas' => 'required|string|max:50',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $request->name,
            'kelas' => $request->kelas,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $student->update($data);

        return back()->with('success', "Data siswa {$student->name} berhasil diperbarui.");
    }

    public function destroy(User $student)
    {
        $student->delete();
        return back()->with('success', 'Akun siswa berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Template_Import_Siswa.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 agar kolom otomatis terpisah rapi saat dibuka di Microsoft Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Nama Lengkap', 'Email Login', 'Kelas', 'Password']);
            fputcsv($file, ['Ahmad Fauzi', 'ahmad.fauzi@siswa.sch.id', 'Kls 10', 'siswa123']);
            fputcsv($file, ['Siti Nurhaliza', 'siti.nurhaliza@siswa.sch.id', 'Kls 10', 'siswa123']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('file')->getRealPath();
        $firstLine = fgets(fopen($path, 'r'));
        // Deteksi otomatis pemisah koma (,) atau titik koma (;) dari Excel
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';

        $handle = fopen($path, 'r');
        fgetcsv($handle, 0, $delimiter); // Lewati baris header pertama
        $imported = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) < 3) continue;
            [$name, $email, $kelas, $password] = array_pad($row, 4, 'password');

            $cleanEmail = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $email));
            if (!filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) continue;

            User::updateOrCreate(
                ['email' => $cleanEmail],
                [
                    'name' => trim($name),
                    'kelas' => trim($kelas),
                    'role' => 'siswa',
                    'password' => Hash::make(trim($password) ?: 'password'),
                ]
            );
            $imported++;
        }
        fclose($handle);

        return back()->with('success', "{$imported} data siswa berhasil diimpor.");
    }
}