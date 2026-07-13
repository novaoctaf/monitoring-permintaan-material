<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:staff']);
    }

    /**
     * Tampilkan seluruh riwayat aktivitas (audit trail) sistem.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('causer')->latest();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('causer')) {
            $query->where('causer_id', $request->causer);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', "%{$request->search}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        $events = ActivityLog::query()->distinct()->orderBy('event')->pluck('event');
        $logNames = ActivityLog::query()->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name');
        $causers = User::orderBy('name')->get();

        return view('admin.activity-logs.index', compact('logs', 'events', 'logNames', 'causers'));
    }
}
