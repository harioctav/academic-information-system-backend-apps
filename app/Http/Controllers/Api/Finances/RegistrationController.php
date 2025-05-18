<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentAddress;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function index()
    {
        $registrations = Registration::with(['student', 'address'])->latest()->paginate(10);
        return response()->json($registrations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'address_id' => 'required|exists:student_addresses,id',
            'student_category' => 'required|string',
            'payment_method' => 'required|string',
            'program_type' => 'required|string',
            'tutorial_service' => 'required|boolean',
            'semester' => 'required|string',
            'interested_spp' => 'required|boolean',
        ]);

        $validated['uuid'] = Str::uuid();

        $registration = Registration::create($validated);

        return response()->json([
            'message' => 'Registration created successfully.',
            'data' => $registration
        ], 201);
    }

    public function show($id)
    {
        $registration = Registration::with(['student', 'address'])->findOrFail($id);
        return response()->json($registration);
    }

    public function update(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'address_id' => 'sometimes|exists:student_addresses,id',
            'student_category' => 'sometimes|string',
            'payment_method' => 'sometimes|string',
            'program_type' => 'sometimes|string',
            'tutorial_service' => 'sometimes|boolean',
            'semester' => 'sometimes|string',
            'interested_spp' => 'sometimes|boolean',
        ]);

        $registration->update($validated);

        return response()->json([
            'message' => 'Registration updated successfully.',
            'data' => $registration
        ]);
    }

    public function destroy($id)
    {
        $registration = Registration::findOrFail($id);
        $registration->delete();

        return response()->json([
            'message' => 'Registration deleted successfully.'
        ]);
    }
}
