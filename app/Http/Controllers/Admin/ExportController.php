<?php

namespace App\Http\Controllers\Admin;

use App\Models\Lead;
use App\Models\PersonalLead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        
        $type = $request->input('type', 'general');
        $columns = $request->input('columns', ['id', 'name', 'address', 'phone', 'website', 'review']);
        $format = $request->input('format', 'xlsx'); // xlsx or pdf
        
        // Get filtered leads
        $model = ($type === 'personal') ? PersonalLead::class : Lead::class;
        $query = $model::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('website', 'like', "%{$search}%")
                  ->orWhere('review', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $leads = $query->get();
        $isPdf = ($format === 'pdf');
        
        $export = new LeadsExport($leads, $columns, $isPdf);
        
        $filename = ($type === 'personal' ? 'personal-' : '') . 'leads-' . date('Y-m-d') . '.' . $format;
        
        if ($format === 'pdf') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
        }
        
        return Excel::download($export, $filename);
    }
}

