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

    // store campaign data

   public function store(Request $request)
{
    try {
        // Check if the user has permission to create a campaign
        if (!auth()->user()->canCreateCampaign()) {
            \Log::warning('User does not have permission to create a campaign.');
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Validate the request data
        $data = $request->validate([
            'name' => 'required|string',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'total_budget' => 'required|numeric',
            'daily_budget' => 'required|numeric',
            'creatives' => 'nullable|array',
            'creatives.*' => '', // Validate file type and size
        ]);

        \Log::info('Validation passed. Proceeding with file upload and campaign creation.');

        $creatives = [];

        // Process and store uploaded image files
        if ($request->hasFile('creatives')) {
            foreach ($request->file('creatives') as $file) {
                // Check if the file is valid
                if ($file->isValid()) {
                    // Log the file size and type
                    \Log::info('Processing file: ' . $file->getClientOriginalName());
                    \Log::info('File size: ' . $file->getSize() . ' bytes');
                    \Log::info('File MIME type: ' . $file->getMimeType());

                    // Store the file in the 'public/creatives' directory
                    $path = $file->store('creatives', 'public');
                    \Log::info('File stored at: ' . $path);
                    // Add the public URL for the stored file
                    $creatives[] = url('storage/' . $path);
                } else {
                    \Log::warning('Invalid file: ' . $file->getClientOriginalName());
                }
            }
        } else {
            \Log::warning('No files found in the creatives array.');
        }

        // Merge creatives URLs into the data array
        $data['creatives'] = $creatives;

        // Save campaign data to the database
        $campaign = Campaign::create([
            'name' => $data['name'],
            'from' => $data['from'],
            'to' => $data['to'],
            'total_budget' => $data['total_budget'],
            'daily_budget' => $data['daily_budget'],
            'creatives' => json_encode($data['creatives']), // Store creatives as JSON
        ]);

        // Return a JSON response with the campaign details
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

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Log the validation errors and return them in the response
        \Log::error('Validation failed: ' . json_encode($e->errors()));
        return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        // Log any other exception that might occur
        \Log::error('Error storing campaign: ' . $e->getMessage());
        return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
    }
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
