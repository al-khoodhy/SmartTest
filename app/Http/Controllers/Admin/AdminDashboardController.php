<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }
    
    /**
     * Display admin dashboard with system statistics
     */
    public function index()
    {
        // User statistics
        $totalUsers = User::count();
        $totalAdmin = User::where('role_id', 'admin')->count();
        $totalDosen = User::where('role_id', 'dosen')->count();
        $totalMahasiswa = User::where('role_id', 'mahasiswa')->count();
        $activeUsers = User::where('is_active', true)->count();
        
        // Academic statistics
        $totalMataKuliah = MataKuliah::count();
        $activeMataKuliah = MataKuliah::where('is_active', true)->count();
        $totalEnrollments = Enrollment::where('status', 'active')->count();
        
        // Assignment statistics
        $totalTugas = Tugas::count();
        $activeTugas = Tugas::where('is_active', true)->count();
        $tugasWithAutoGrade = Tugas::where('auto_grade', true)->count();
        
        // Submission statistics
        $totalJawaban = JawabanMahasiswa::count();
        $jawabanSubmitted = JawabanMahasiswa::whereIn('status', ['submitted', 'graded'])->count();
        $jawabanDraft = JawabanMahasiswa::where('status', 'draft')->count();
        
        // Grading statistics
        $totalPenilaian = Penilaian::count();
        $penilaianAI = Penilaian::where('graded_by_ai', true)->count();
        $penilaianManual = Penilaian::where('graded_by_ai', false)->count();
        $avgNilai = Penilaian::avg('nilai_final');
        
        // Recent activities
        $recentTugas = Tugas::with(['kelas.mataKuliah'])
            ->latest()
            ->take(5)
            ->get();
            
        $recentJawaban = JawabanMahasiswa::with(['tugas.kelas.mataKuliah', 'mahasiswa'])
            ->whereIn('status', ['submitted', 'graded'])
            ->latest()
            ->take(5)
            ->get();
            
        $recentPenilaian = Penilaian::with(['jawaban.tugas', 'jawaban.mahasiswa'])
            ->latest('graded_at')
            ->take(5)
            ->get();
        
        // Monthly statistics for charts
        $monthlyTugas = $this->getMonthlyStatistics(Tugas::class);
        $monthlyJawaban = $this->getMonthlyStatistics(JawabanMahasiswa::class);
        $monthlyPenilaian = $this->getMonthlyStatistics(Penilaian::class, 'graded_at');
        
        // System health
        $systemHealth = [
            'database_connection' => $this->checkDatabaseConnection(),
            'storage_writable' => is_writable(storage_path()),
            'queue_working' => $this->checkQueueStatus(),
            'gemini_api' => $this->checkGeminiAPI()
        ];
        
        return view('admin.dashboard', compact(
            'totalUsers', 'totalAdmin', 'totalDosen', 'totalMahasiswa', 'activeUsers',
            'totalMataKuliah', 'activeMataKuliah', 'totalEnrollments',
            'totalTugas', 'activeTugas', 'tugasWithAutoGrade',
            'totalJawaban', 'jawabanSubmitted', 'jawabanDraft',
            'totalPenilaian', 'penilaianAI', 'penilaianManual', 'avgNilai',
            'recentTugas', 'recentJawaban', 'recentPenilaian',
            'monthlyTugas', 'monthlyJawaban', 'monthlyPenilaian',
            'systemHealth'
        ));
    }
    
    /**
     * Get monthly statistics for charts
     */
    private function getMonthlyStatistics($model, $dateColumn = 'created_at')
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = $model::whereYear($dateColumn, $date->year)
                ->whereMonth($dateColumn, $date->month)
                ->count();
            
            $data[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
        
        return $data;
    }
    
    /**
     * Check database connection
     */
    private function checkDatabaseConnection()
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check queue status
     */
    private function checkQueueStatus()
    {
        // Simple check - in production you might want to check actual queue workers
        return true;
    }
    
    /**
     * Check Gemini API status
     */
    private function checkGeminiAPI()
    {
        $apiKey = config('services.gemini.api_key');
        return !empty($apiKey) && $apiKey !== 'your_gemini_api_key_here';
    }
    
    /**
     * Get system reports
     */
    public function reports(Request $request)
    {
        $type = $request->get('type', 'overview');
        
        switch ($type) {
            case 'users':
                return $this->getUsersReport();
            case 'assignments':
                return $this->getAssignmentsReport();
            case 'grading':
                return $this->getGradingReport();
            default:
                return $this->getOverviewReport();
        }
    }
    
    private function getUsersReport()
    {
        $users = User::with(['enrollments.kelas.mataKuliah'])
            ->withCount(['jawabanMahasiswa', 'tugasDibuat'])
            ->get()
            ->groupBy('role_id');
            
        return view('admin.reports.users', compact('users'));
    }
    
    private function getAssignmentsReport()
    {
        $tugas = Tugas::with(['kelas.mataKuliah', 'dosen'])
            ->withCount(['jawabanMahasiswa'])
            ->get();
            
        return view('admin.reports.assignments', compact('tugas'));
    }
    
    private function getGradingReport()
    {
        $penilaian = Penilaian::with(['jawaban.tugas.kelas.mataKuliah', 'jawaban.mahasiswa'])
            ->get();
            
        $avgByMataKuliah = $penilaian->groupBy('jawaban.tugas.kelas.mata_kuliah_id')
            ->map(function ($group) {
                return [
                    'mata_kuliah' => $group->first()->jawaban->tugas->mataKuliah->nama_mk,
                    'avg_nilai' => $group->avg('nilai_final'),
                    'total_penilaian' => $group->count()
                ];
            });
            
        return view('admin.reports.grading', compact('penilaian', 'avgByMataKuliah'));
    }
    
    private function getOverviewReport()
    {
        return redirect()->route('admin.dashboard');
    }
}
