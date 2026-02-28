<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalLead;
use Illuminate\Http\Request;

class PersonalLeadController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonalLead::query();
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category_name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('website', 'like', "%{$search}%")
                  ->orWhere('review', 'like', "%{$search}%");
            });
        }
        
        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $leads = $query->latest()->paginate($request->get('per_page', 100));
        
        return view('admin.personal_leads.index', compact('leads'));
    }

    public function create()
    {
        return view('admin.personal_leads.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'review' => 'nullable|string',
        ]);

        PersonalLead::create($validated);

        return redirect()->route('admin.personal-leads.index')
            ->with('success', 'Personal lead created successfully!');
    }

    public function edit(PersonalLead $personalLead)
    {
        return view('admin.personal_leads.edit', compact('personalLead'));
    }

    public function update(Request $request, PersonalLead $personalLead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'review' => 'nullable|string',
        ]);

        $personalLead->update($validated);

        return redirect()->route('admin.personal-leads.index')
            ->with('success', 'Personal lead updated successfully!');
    }

    public function destroy(PersonalLead $personalLead)
    {
        $personalLead->delete();

        return redirect()->route('admin.personal-leads.index')
            ->with('success', 'Personal lead deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('admin.personal-leads.index')
                ->with('error', 'No personal leads selected for deletion.');
        }

        PersonalLead::whereIn('id', $ids)->delete();

        return redirect()->route('admin.personal-leads.index')
            ->with('success', count($ids) . ' personal leads deleted successfully!');
    }
}
