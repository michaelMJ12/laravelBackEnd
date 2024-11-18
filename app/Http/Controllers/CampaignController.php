<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;

class CampaignController extends Controller
{
    // Ensure the user is authorized before performing certain actions
    public function __construct()
    {
        $this->middleware('auth:api'); // Add authentication middleware for API routes
    }

    public function index()
    {
        // Check if the user has permission to view campaigns
        if (!auth()->user()->canViewCampaign()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(Campaign::all());
    }

    // Show a single campaign
    public function show($id)
    {
        $campaign = Campaign::findOrFail($id);

        // Check if the user has permission to view the campaign
        if (!auth()->user()->canViewCampaign()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($campaign);
    }

    public function store(Request $request)
    {
        // Check if the user has permission to create a campaign
        if (!auth()->user()->canCreateCampaign()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'total_budget' => 'required|numeric',
            'daily_budget' => 'required|numeric',
            'creatives' => 'nullable|array',
            'creatives.*' => 'nullable',
        ]);

        $creatives = [];

        // Process each creative entry in the array
        if ($request->filled('creatives')) {
            foreach ($request->creatives as $item) {
                if ($request->hasFile("creatives") && is_file($item)) {
                    $path = $item->store('creatives', 'public');
                    $creatives[] = url('storage/' . $path);
                } elseif (filter_var($item, FILTER_VALIDATE_URL)) {
                    $creatives[] = $item;
                }
            }
        }

        // Store creatives in the data array
        $data['creatives'] = $creatives;

        // Save the campaign data to the database
        $campaign = Campaign::create([
            'name' => $data['name'],
            'from' => $data['from'],
            'to' => $data['to'],
            'total_budget' => $data['total_budget'],
            'daily_budget' => $data['daily_budget'],
            'creatives' => json_encode($data['creatives']), 
        ]);

        return response()->json([
            'data' => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'from' => $campaign->from,
                'to' => $campaign->to,
                'total_budget' => $campaign->total_budget,
                'daily_budget' => $campaign->daily_budget,
                'creatives' => json_decode($campaign->creatives),  
                'created_at' => $campaign->created_at,
                'updated_at' => $campaign->updated_at,
            ]
        ], 201);
    }

    // Update a campaign
    public function update(Request $request, $id)
    {
        // Check if the user has permission to update the campaign
        if (!auth()->user()->canUpdateCampaign()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $campaign = Campaign::findOrFail($id);

        $data = $request->validate([
            'name' => 'string',
            'from' => 'date',
            'to' => 'date|after_or_equal:from',
            'total_budget' => 'numeric',
            'daily_budget' => 'numeric',
            'creatives.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('creatives')) {
            $data['creatives'] = [];
            foreach ($request->file('creatives') as $file) {
                $path = $file->store('creatives', 'public');
                $data['creatives'][] = $path;
            }
        }

        $campaign->update($data);
        return response()->json($campaign);
    }

    // Delete a campaign
    public function destroy($id)
    {
        // Check if the user has permission to delete the campaign
        if (!auth()->user()->canDeleteCampaign()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $campaign = Campaign::findOrFail($id);
        $campaign->delete();

        return response()->json(['message' => 'Campaign deleted successfully']);
    }
}
