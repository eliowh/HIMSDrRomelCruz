<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        // Define allowed sort columns for security
        $allowedSortColumns = ['id', 'title', 'type', 'status', 'generated_by', 'generated_at'];
        
        // Get sort parameters with validation
        $sortBy = $request->get('sort', 'generated_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validate sort column
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'generated_at';
        }
        
        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query = Report::with('generatedBy');
        
        // Apply sorting with special handling for user names
        if ($sortBy === 'generated_by') {
            $query->leftJoin('users', 'reports.generated_by', '=', 'users.id')
                  ->select('reports.*')
                  ->orderBy('users.name', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        $reports = $query->paginate(15)->appends($request->query());

        $stats = [
            'total_reports' => Report::count(),
            'pending_reports' => Report::where('status', Report::STATUS_PENDING)->count(),
            'completed_reports' => Report::where('status', Report::STATUS_COMPLETED)->count(),
            'failed_reports' => Report::where('status', Report::STATUS_FAILED)->count(),
        ];

        // Additional simple analytics
        try {
            $totalAdmittedPatients = DB::table('admissions')->distinct('patient_id')->count('patient_id');
            $currentlyAdmitted = DB::table('admissions')->where('status', 'active')->distinct('patient_id')->count('patient_id');
        } catch (\Exception $e) {
            $totalAdmittedPatients = 0;
            $currentlyAdmitted = 0;
        }

        $stats['total_admitted_patients'] = $totalAdmittedPatients;
        $stats['currently_admitted'] = $currentlyAdmitted;

        return view('admin.admin_reports', compact('reports', 'stats'));
    }

    /**
     * Generate a new report
     */
    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        try {
            $data = $this->generateReportData($request->type, $request->date_from, $request->date_to);
            
            $report = Report::create([
                'title' => $request->title,
                'type' => $request->type,
                'description' => $this->getReportDescription($request->type, $request->date_from, $request->date_to),
                'data' => $data,
                'generated_by' => auth()->id(),
                'status' => Report::STATUS_COMPLETED,
                'generated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully!',
                'report_id' => $report->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a specific report
     */
    public function show(Report $report)
    {
        $report->load('generatedBy');
        return view('admin.reports.show', compact('report'));
    }

    /**
     * Delete a report
     */
    public function destroy(Report $report)
    {
        try {
            $report->delete();
            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate report data based on type
     */
    private function generateReportData($type, $dateFrom = null, $dateTo = null)
    {
        $from = $dateFrom ? Carbon::parse($dateFrom) : Carbon::now()->subMonth();
        $to = $dateTo ? Carbon::parse($dateTo) : Carbon::now();

        switch ($type) {
            case Report::TYPE_USER_ACTIVITY:
                return $this->getUserActivityData($from, $to);
            
            case Report::TYPE_LOGIN_REPORT:
                return $this->getLoginReportData($from, $to);
            
            case Report::TYPE_USER_REGISTRATION:
                return $this->getUserRegistrationData($from, $to);
            
            case Report::TYPE_SYSTEM_LOG:
                return $this->getSystemLogData($from, $to);

            case Report::TYPE_ADMISSIONS:
                return $this->getAdmissionsData($from, $to);
            
            default:
                return [];
        }
    }

    /**
     * Get admissions / admitted patients data for a given period
     */
    private function getAdmissionsData($from, $to)
    {
        // Fetch admissions in range with patient data
        $admissions = Admission::with('patient')
            ->whereBetween('admission_date', [$from, $to])
            ->orderBy('admission_date', 'desc')
            ->get();

        // Unique patient count
        $uniquePatientCount = $admissions->pluck('patient_id')->unique()->count();

        $items = $admissions->map(function($admission) {
            return [
                'admission_id' => $admission->id,
                'admission_number' => $admission->admission_number,
                'admission_date' => optional($admission->admission_date)->format('Y-m-d H:i:s'),
                'discharge_date' => optional($admission->discharge_date)->format('Y-m-d H:i:s'),
                'room_no' => $admission->room_no,
                'status' => $admission->status,
                'patient' => $admission->patient ? [
                    'id' => $admission->patient->id,
                    'patient_no' => $admission->patient->patient_no ?? null,
                    'name' => trim(($admission->patient->first_name ?? '') . ' ' . ($admission->patient->last_name ?? '')),
                    'date_of_birth' => $admission->patient->date_of_birth ?? null,
                    'contact_number' => $admission->patient->contact_number ?? null,
                ] : null
            ];
        })->toArray();

        return [
            'period' => ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')],
            'admitted_patient_count' => $uniquePatientCount,
            'admissions_count' => $admissions->count(),
            'admissions' => $items
        ];
    }

    /**
     * Get user activity data
     */
    private function getUserActivityData($from, $to)
    {
        $users = User::whereBetween('created_at', [$from, $to])
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();

        $totalUsers = User::count();
        $newUsers = User::whereBetween('created_at', [$from, $to])->count();

        return [
            'period' => ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')],
            'total_users' => $totalUsers,
            'new_users' => $newUsers,
            'users_by_role' => $users->toArray(),
            'summary' => [
                'doctors' => User::where('role', 'doctor')->count(),
                'nurses' => User::where('role', 'nurse')->count(),
                'lab_technicians' => User::where('role', 'lab_technician')->count(),
                'cashiers' => User::where('role', 'cashier')->count(),
                'admins' => User::where('role', 'admin')->count(),
            ]
        ];
    }

    /**
     * Get login report data
     */
    private function getLoginReportData($from, $to)
    {
        // This would require login tracking - for now return basic user data
        return [
            'period' => ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')],
            'note' => 'Login tracking would require additional implementation',
            'active_users' => User::whereBetween('updated_at', [$from, $to])->count(),
        ];
    }

    /**
     * Get user registration data
     */
    private function getUserRegistrationData($from, $to)
    {
        $registrations = User::whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return [
            'period' => ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')],
            'total_registrations' => $registrations->count(),
            'registrations' => $registrations->toArray(),
        ];
    }

    /**
     * Get system log data
     */
    private function getSystemLogData($from, $to)
    {
        $reports = Report::whereBetween('generated_at', [$from, $to])
            ->orderBy('generated_at', 'desc')
            ->get();

        return [
            'period' => ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')],
            'total_reports' => $reports->count(),
            'reports_by_type' => $reports->groupBy('type')->map->count(),
            'recent_reports' => $reports->take(10)->toArray(),
        ];
    }

    /**
     * Get report description based on type and date range
     */
    private function getReportDescription($type, $dateFrom, $dateTo)
    {
        $from = $dateFrom ? Carbon::parse($dateFrom)->format('M d, Y') : Carbon::now()->subMonth()->format('M d, Y');
        $to = $dateTo ? Carbon::parse($dateTo)->format('M d, Y') : Carbon::now()->format('M d, Y');

        $typeMap = [
            Report::TYPE_USER_ACTIVITY => "User activity report from {$from} to {$to}",
            Report::TYPE_LOGIN_REPORT => "Login activity report from {$from} to {$to}",
            Report::TYPE_USER_REGISTRATION => "User registration report from {$from} to {$to}",
            Report::TYPE_SYSTEM_LOG => "System log report from {$from} to {$to}",
            Report::TYPE_ADMISSIONS => "Admissions report (patients admitted) from {$from} to {$to}",
        ];

        return $typeMap[$type] ?? "Custom report from {$from} to {$to}";
    }

    /**
     * Export report data
     */
    public function export(Report $report)
    {
        $format = request()->get('format', 'json');

        if ($format === 'print') {
            // Render a simple printable HTML view
            return view('admin.reports.print', compact('report'));
        }

        $filename = "report_{$report->id}_{$report->type}_" . now()->format('Y_m_d') . ".json";
        return response()->json($report->data)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}
