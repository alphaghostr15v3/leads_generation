<?php

namespace App\Http\Controllers\Admin;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        $columns = $request->input('columns', ['id', 'name', 'address', 'phone', 'website', 'review']);
        $format = $request->input('format', 'xlsx'); // xlsx or pdf
        
        // Get filtered leads
        $query = Lead::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
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
        
        $filename = 'leads-' . date('Y-m-d') . '.' . $format;
        
        if ($format === 'pdf') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
        }
        
        return Excel::download($export, $filename);
    }
}

