<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Models\Room;
use App\Models\Patient;
use App\Traits\SecurityHelpers;
use Illuminate\Http\Request;
use App\Notifications\RoleAssigned;
use App\Notifications\NewUserCredentials;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Icd10Import;

class AdminController extends Controller
{
    use SecurityHelpers;

    public function __construct()
    {
        // Middleware is applied at route level, no need to apply here
        // $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        // Verify admin access with enhanced security
        $this->verifyAdminAccess();
        
        $adminName = $this->getAdminName();
        
        // Get stocks summary data
        try {
            $totalItems = \App\Models\StockPrice::count();
            $lowStock = \App\Models\StockPrice::where('quantity', '<=', 10)->count();
            $outOfStock = \App\Models\StockPrice::where('quantity', 0)->count();
            $totalValue = (float)\App\Models\StockPrice::sum(\DB::raw('CAST(price AS DECIMAL(10,2)) * CAST(quantity AS DECIMAL(10,2))'));
        } catch (\Exception $e) {
            // Default values if there's an error
            $totalItems = 0;
            $lowStock = 0;
            $outOfStock = 0;
            $totalValue = 0.0;
        }
        
        $stocksSummary = [
            'total_items' => $totalItems,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'total_value' => $totalValue
        ];
        
        return view('admin.admin_home', compact('adminName', 'stocksSummary'));
    }
    public function getAdminName()
    {
        $adminName = auth()->user()->name ?? 'Admin';
        return $adminName;
    }
    // Removed pending user approval methods as they are no longer needed
    // Roles are now assigned directly at user creation
    
    public function showCreateUserForm()
    {
        return view('admin.create_user');
    }

    public function createUser(Request $request)
    {
        // Log request data for debugging
        \Log::info('Create user request', [
            'email' => $request->email,
            'content_type' => $request->header('Content-Type'),
            'is_json' => $request->isJson(),
            'wants_json' => $request->wantsJson(),
            'ajax' => $request->ajax()
        ]);
        
        // Validate inputs
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => [
                'required',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z\s]+$/', // Only letters and spaces
            ],
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/', // Only .com domains allowed
                'unique:users,email'
            ],
            'role' => ['required', 'in:doctor,nurse,lab_technician,cashier,admin,inventory,pharmacy,billing']
        ], [
            'name.required' => 'Please enter a name.',
            'name.min' => 'Name must be 3-20 letters.',
            'name.max' => 'Name must be 3-20 letters.',
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.regex' => 'Email must end with .com and be valid.',
            'email.unique' => 'This email is already registered.',
            'role.required' => 'Please select a role.',
            'role.in' => 'Invalid role selected.'
        ]);
        
        if ($validator->fails()) {
            \Log::info('Validation failed', ['errors' => $validator->errors()]);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('Content-Type') == 'application/json') {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }
        
        // Double-check if email exists (extra validation)
        if (User::where('email', $request->email)->exists()) {
            \Log::info('Email already exists', ['email' => $request->email]);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('Content-Type') == 'application/json') {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'email' => ['This email is already registered.']
                    ]
                ], 422);
            }
            
            return back()->withErrors(['email' => 'This email is already registered.'])->withInput();
        }
        
        // Generate password reset token
        $token = \Illuminate\Support\Str::random(60);
        
        // Log token for debugging
        \Log::info('Generated token for new user', [
            'email' => $request->email,
            'token' => $token
        ]);
        
        // Create user with a temporary password
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(\Illuminate\Support\Str::random(16)), // Random temporary password
            'role' => $request->role
        ]);
        
        // Set the password reset token
        $user->password_reset_token = $token;
        $user->save();
        
        // Log user creation
        Report::log(
            'New User Created',
            Report::TYPE_USER_REGISTRATION,
            "Admin created new user: {$user->name} with role {$user->role}",
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'created_by' => auth()->user()->name,
                'created_by_id' => auth()->id(),
                'creation_time' => now()->toISOString()
            ],
            auth()->id()
        );
        
        // Double check the token was saved
        $savedUser = User::find($user->id);
        \Log::info('Saved user token check', [
            'email' => $savedUser->email,
            'token_saved' => $savedUser->password_reset_token
        ]);

        try {
            // Send notification with credentials
            try {
                $user->notify(new NewUserCredentials($token, $request->role));
                $notificationSent = true;
            } catch (\Exception $e) {
                \Log::error('Email notification failed: ' . $e->getMessage());
                $notificationSent = false;
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $notificationSent 
                        ? 'User created and notified successfully!'
                        : 'User created successfully but email notification failed. Please note their email and temporary password: ' . $tempPassword
                ]);
            }

            $message = $notificationSent 
                ? 'User created and notified successfully!'
                : 'User created successfully but email notification failed. Please note their email and temporary password: ' . $tempPassword;

            return redirect()->route('admin.home')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('User creation error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
        return redirect('/admin/users')->with('success', 'User created and notified successfully!');
    }

    /**
     * Get user data for editing
     */
    public function editUser($id)
    {
        try {
            $user = User::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at->format('M d, Y')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }
    }

    /**
     * Update user information
     */
    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Debug logging
            \Log::info('Update user request', [
                'user_id' => $id,
                'request_data' => $request->all(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method()
            ]);

            // Validate inputs
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name' => [
                    'required',
                    'min:3',
                    'max:20',
                    'regex:/^[a-zA-Z\s]+$/'
                ],
                'email' => [
                    'required',
                    'email',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/',
                    \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)
                ],
                'role' => ['required', 'in:admin,doctor,nurse,lab_technician,cashier,inventory,pharmacy,billing']
            ], [
                'name.required' => 'Please enter the user\'s name.',
                'name.min' => 'Name must be 3-20 letters.',
                'name.max' => 'Name must be 3-20 letters.',
                'name.regex' => 'Name can only contain letters and spaces.',
                'name.unique' => 'This name is already taken by another user.',
                'email.required' => 'Please enter the user\'s email address.',
                'email.email' => 'Please enter a valid email address.',
                'email.regex' => 'Email must end with .com and be valid.',
                'email.unique' => 'This email is already registered to another user.',
                'role.required' => 'Please select a role for the user.',
                'role.in' => 'Please select a valid role.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Store old data for logging
            $oldData = [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ];

            // Update user
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->save();

            // Log user update
            Report::log(
                'User Updated',
                Report::TYPE_USER_ACTIVITY,
                "Admin updated user: {$user->name}",
                [
                    'user_id' => $user->id,
                    'updated_by' => auth()->user()->name,
                    'updated_by_id' => auth()->id(),
                    'old_data' => $oldData,
                    'new_data' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role
                    ],
                    'update_time' => now()->toISOString()
                ],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at->format('M d, Y')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('User update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deletion of the current admin user
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
            }

            // Store user data for logging before deletion
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at->toISOString()
            ];

            // Delete the user
            $user->delete();

            // Log user deletion
            Report::log(
                'User Deleted',
                Report::TYPE_USER_ACTIVITY,
                "Admin deleted user: {$userData['name']}",
                [
                    'deleted_user' => $userData,
                    'deleted_by' => auth()->user()->name,
                    'deleted_by_id' => auth()->id(),
                    'deletion_time' => now()->toISOString()
                ],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('User deletion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importIcd10(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new Icd10Import, $request->file('file'));

        return back()->with('success', 'ICD-10 data imported.');
    }

    // Room Management Methods
    public function rooms(Request $request)
    {
        try {
            $query = \DB::table('roomlist');
            
            // Skip header row and empty entries
            $query->whereNotNull('COL 1')
                  ->where('COL 1', '!=', '')
                  ->where('COL 1', '!=', 'Room Name')  // Skip header
                  ->where('COL 1', 'NOT LIKE', '%Room Name%');
            
            // Get search and sort parameters
            $search = $request->get('q', '');
            $sortBy = $request->get('sort', 'COL 1');
            $sortDirection = $request->get('direction', 'asc');
            
            // Validate sort column
            $allowedSortColumns = ['COL 1', 'COL 2'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'COL 1';
            }
            
            // Validate sort direction
            $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('COL 1', 'like', "%{$search}%")
                      ->orWhere('COL 2', 'like', "%{$search}%");
                });
            }
            
            // Apply sorting and paginate for all results (with or without search)
            $query->orderBy($sortBy, $sortDirection);
            $rooms = $query->paginate(10);
            
            // Preserve search parameters in pagination links
            $rooms->appends(request()->query());
            return view('admin.admin_rooms', compact('rooms'));
            
        } catch (\Exception $e) {
            \Log::error('Room management error: ' . $e->getMessage());
            $emptyCollection = collect();
            $rooms = new \Illuminate\Pagination\LengthAwarePaginator(
                $emptyCollection,
                0,
                10,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            return view('admin.admin_rooms', compact('rooms'))->with('error', 'Error loading rooms data.');
        }
    }

    public function createRoom(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'room_name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\s\-]+$/'],
            'room_price' => ['required', 'string']
        ], [
            'room_name.required' => 'Room name is required.',
            'room_name.regex' => 'Room name can only contain letters, numbers, spaces, and hyphens.',
            'room_price.required' => 'Room price is required.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if room name already exists (excluding header row)
        $existingRoom = \DB::table('roomlist')
            ->where('COL 1', $request->room_name)
            ->whereNotIn('COL 1', ['Room Name', ''])
            ->first();
            
        if ($existingRoom) {
            return response()->json([
                'success' => false,
                'errors' => ['room_name' => ['A room with this name already exists.']]
            ], 422);
        }

        // Clean and validate price - remove commas and convert to float
        $cleanPrice = str_replace(',', '', $request->room_price);
        if (!is_numeric($cleanPrice) || $cleanPrice < 0) {
            return response()->json([
                'success' => false,
                'errors' => ['room_price' => ['Room price must be a valid positive number.']]
            ], 422);
        }

        try {
            \DB::table('roomlist')->insert([
                'COL 1' => $request->room_name,  // Room Name
                'COL 2' => $cleanPrice  // Price (cleaned of commas)
            ]);

            Report::log(
                'Room Management',
                Report::TYPE_USER_REPORT,
                "Admin created new room: {$request->room_name}",
                [
                    'room_name' => $request->room_name,
                    'room_price' => $cleanPrice,
                    'admin_id' => auth()->id()
                ],
                auth()->id()
            );

            return response()->json(['success' => true, 'message' => 'Room created successfully.']);
            
        } catch (\Exception $e) {
            \Log::error('Create room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error creating room.'], 500);
        }
    }

    public function editRoom(Request $request)
    {
        try {
            // Get room name from request body
            $roomName = $request->input('room_name');
            
            if (!$roomName) {
                return response()->json(['success' => false, 'message' => 'Room name is required.']);
            }
            
            $room = \DB::table('roomlist')->where('COL 1', $roomName)->first();
            
            if (!$room) {
                return response()->json(['success' => false, 'message' => 'Room not found.']);
            }

            return response()->json(['success' => true, 'room' => $room]);
            
        } catch (\Exception $e) {
            \Log::error('Edit room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading room data.'], 500);
        }
    }

    public function updateRoom(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'room_name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\s\-]+$/'],
            'room_price' => ['required', 'string'],
            'id' => ['required', 'string'] // This will contain the original room name
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Clean and validate price - remove commas and convert to float
        $cleanPrice = str_replace(',', '', $request->room_price);
        if (!is_numeric($cleanPrice) || $cleanPrice < 0) {
            return response()->json([
                'success' => false,
                'errors' => ['room_price' => ['Room price must be a valid positive number.']]
            ], 422);
        }

        // Use the original room name (from id field) to find the room
        $originalRoomName = $request->id;
        
        // Check if the new room name already exists (only if it's different from the original)
        if ($request->room_name !== $originalRoomName) {
            $existingRoom = \DB::table('roomlist')
                ->where('COL 1', $request->room_name)
                ->whereNotIn('COL 1', ['Room Name', ''])
                ->first();
                
            if ($existingRoom) {
                return response()->json([
                    'success' => false,
                    'errors' => ['room_name' => ['A room with this name already exists.']]
                ], 422);
            }
        }

        try {
            $room = \DB::table('roomlist')->where('COL 1', $originalRoomName)->first();
            if (!$room) {
                return response()->json(['success' => false, 'message' => 'Room not found.']);
            }

            \DB::table('roomlist')->where('COL 1', $originalRoomName)->update([
                'COL 1' => $request->room_name,  // Room Name (new name)
                'COL 2' => $cleanPrice  // Price (cleaned of commas)
            ]);

            Report::log(
                'Room Management',
                Report::TYPE_USER_REPORT,
                "Admin updated room: {$originalRoomName} to {$request->room_name}",
                [
                    'original_room_name' => $originalRoomName,
                    'new_room_name' => $request->room_name,
                    'admin_id' => auth()->id()
                ],
                auth()->id()
            );

            return response()->json(['success' => true, 'message' => 'Room updated successfully.']);
            
        } catch (\Exception $e) {
            \Log::error('Update room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating room.'], 500);
        }
    }

    // Patient Records Management
    public function patients(Request $request)
    {
        try {
            $query = \DB::table('patients');
            
            // Get search and sort parameters
            $search = $request->get('q', '');
            $status = $request->get('status', '');
            $sortBy = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            // Validate sort column to prevent SQL injection
            $allowedSortColumns = ['patient_no', 'first_name', 'last_name', 'status', 'room_no', 'created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'created_at';
            }
            
            // Validate sort direction
            $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('patient_no', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            }
            
            if ($status) {
                $query->where('status', ucfirst($status)); // Convert to match database format
            }
            
            // Apply sorting
            if ($sortBy === 'first_name') {
                // For name sorting, sort by full name
                $query->orderByRaw("CONCAT(first_name, ' ', last_name) {$sortDirection}");
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }
            
            // Apply pagination for all results (with or without search/filter)
            $patients = $query->paginate(10);
            
            // Preserve search and filter parameters in pagination links
            $patients->appends(request()->query());
            return view('admin.admin_patients', compact('patients'));
            
        } catch (\Exception $e) {
            \Log::error('Patient management error: ' . $e->getMessage());
            $emptyCollection = collect();
            $patients = new \Illuminate\Pagination\LengthAwarePaginator(
                $emptyCollection,
                0,
                10,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            return view('admin.admin_patients', compact('patients'))->with('error', 'Error loading patient data.');
        }
    }

    public function updatePatient(Request $request, $id)
    {
        try {
            $this->verifyAdminAccess();

            // Validate the input based on actual database structure
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'date_of_birth' => 'nullable|date',
                'nationality' => 'nullable|string|max:255',
                'province' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'barangay' => 'nullable|string|max:255',
                'room_no' => 'nullable|string|max:50',
                'admission_type' => 'nullable|in:Emergency,Elective,Urgent',
                'service' => 'nullable|in:Inpatient,Outpatient,Emergency Room',
                'doctor_name' => 'nullable|string|max:255',
                'doctor_type' => 'nullable|in:Attending,Consultant,Resident',
                'admission_diagnosis' => 'nullable|string|max:1000',
                'status' => 'nullable|in:Active,Discharged,Deceased'
            ]);

            // Update the patient record
            $updated = \DB::table('patients')
                ->where('id', $id)
                ->update(array_merge($validated, [
                    'updated_at' => now()
                ]));

            if ($updated) {
                \Log::info("Patient {$id} updated successfully by admin");
                return response()->json([
                    'success' => true,
                    'message' => 'Patient information updated successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found or no changes made.'
                ], 404);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Update patient error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating patient information.'
            ], 500);
        }
    }

    public function updatePatientStatus(Request $request, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => ['required', 'in:active,discharged,deceased']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid status.'], 422);
        }

        try {
            $patient = \DB::table('patients')->where('id', $id)->first();
            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Patient not found.']);
            }

            \DB::table('patients')->where('id', $id)->update([
                'status' => ucfirst($request->status), // Capitalize to match database format
                'updated_at' => now()
            ]);

            Report::log(
                'Patient Management',
                Report::TYPE_USER_REPORT,
                "Admin updated patient status: {$patient->first_name} {$patient->last_name} - {$request->status}",
                [
                    'patient_id' => $id,
                    'patient_name' => "{$patient->first_name} {$patient->last_name}",
                    'new_status' => $request->status,
                    'admin_id' => auth()->id()
                ],
                auth()->id()
            );

            return response()->json(['success' => true, 'message' => 'Patient status updated successfully.']);
            
        } catch (\Exception $e) {
            \Log::error('Update patient status error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating patient status.'], 500);
        }
    }

    public function getPatientDetails($id)
    {
        try {
            $this->verifyAdminAccess();

            \Log::info("Fetching patient details for ID: {$id}");

            $patient = \DB::table('patients')->where('id', $id)->first();
            
            if (!$patient) {
                \Log::warning("Patient not found with ID: {$id}");
                return response()->json([
                    'success' => false, 
                    'message' => 'Patient not found.'
                ], 404);
            }

            \Log::info("Patient found successfully");

            // Generate patient details form HTML
            $html = $this->generatePatientDetailsHTML($patient);
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get patient details error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generatePatientDetailsHTML($patient)
    {
        // Calculate age display
        $ageDisplay = 'N/A';
        if ($patient->age_years || $patient->age_months || $patient->age_days) {
            $ageDisplay = ($patient->age_years ?? 0) . ' years';
            if ($patient->age_months) {
                $ageDisplay .= ', ' . $patient->age_months . ' months';
            }
            if ($patient->age_days) {
                $ageDisplay .= ', ' . $patient->age_days . ' days';
            }
        }

        $html = '
        <div class="patient-details-form">
            <div class="form-header">
                <h3>Patient Details - ' . htmlspecialchars($patient->first_name . ' ' . $patient->last_name) . '</h3>
                <div class="form-actions">
                    <button type="button" id="editBtn" class="btn btn-primary">Edit</button>
                    <button type="button" id="saveBtn" class="btn btn-success" style="display: none;">Save</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary" style="display: none;">Cancel</button>
                </div>
            </div>
            
            <form id="patientDetailsForm" class="patient-form">
                <input type="hidden" name="patient_id" value="' . $patient->id . '">
                
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h4>Personal Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Patient Number</label>
                            <input type="text" name="patient_no" value="' . htmlspecialchars($patient->patient_no ?? '') . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" disabled>
                                <option value="Active"' . (($patient->status ?? '') === 'Active' ? ' selected' : '') . '>Active</option>
                                <option value="Discharged"' . (($patient->status ?? '') === 'Discharged' ? ' selected' : '') . '>Discharged</option>
                                <option value="Deceased"' . (($patient->status ?? '') === 'Deceased' ? ' selected' : '') . '>Deceased</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="' . htmlspecialchars($patient->first_name ?? '') . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" value="' . htmlspecialchars($patient->middle_name ?? '') . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="' . htmlspecialchars($patient->last_name ?? '') . '" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" value="' . htmlspecialchars($patient->date_of_birth ?? '') . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>Age</label>
                            <input type="text" value="' . htmlspecialchars($ageDisplay) . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>Nationality</label>
                            <input type="text" name="nationality" value="' . htmlspecialchars($patient->nationality ?? '') . '" readonly>
                        </div>
                    </div>
                </div>
                
                <!-- Address Information Section -->
                <div class="form-section">
                    <h4>Address Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Province</label>
                            <input type="text" name="province" value="' . htmlspecialchars($patient->province ?? '') . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" value="' . htmlspecialchars($patient->city ?? '') . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>Barangay</label>
                            <input type="text" name="barangay" value="' . htmlspecialchars($patient->barangay ?? '') . '" readonly>
                        </div>
                    </div>
                </div>
                
                <!-- Medical Information Section -->
                <div class="form-section">
                    <h4>Medical & Admission Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Room Number</label>
                            <input type="text" name="room_no" value="' . htmlspecialchars($patient->room_no ?? '') . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>Admission Type</label>
                            <select name="admission_type" disabled>
                                <option value="Emergency"' . (($patient->admission_type ?? '') === 'Emergency' ? ' selected' : '') . '>Emergency</option>
                                <option value="Elective"' . (($patient->admission_type ?? '') === 'Elective' ? ' selected' : '') . '>Elective</option>
                                <option value="Urgent"' . (($patient->admission_type ?? '') === 'Urgent' ? ' selected' : '') . '>Urgent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Service</label>
                            <select name="service" disabled>
                                <option value="Inpatient"' . (($patient->service ?? '') === 'Inpatient' ? ' selected' : '') . '>Inpatient</option>
                                <option value="Outpatient"' . (($patient->service ?? '') === 'Outpatient' ? ' selected' : '') . '>Outpatient</option>
                                <option value="Emergency Room"' . (($patient->service ?? '') === 'Emergency Room' ? ' selected' : '') . '>Emergency Room</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Doctor Name</label>
                            <input type="text" name="doctor_name" value="' . htmlspecialchars($patient->doctor_name ?? '') . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>Doctor Type</label>
                            <select name="doctor_type" disabled>
                                <option value="Attending"' . (($patient->doctor_type ?? '') === 'Attending' ? ' selected' : '') . '>Attending</option>
                                <option value="Consultant"' . (($patient->doctor_type ?? '') === 'Consultant' ? ' selected' : '') . '>Consultant</option>
                                <option value="Resident"' . (($patient->doctor_type ?? '') === 'Resident' ? ' selected' : '') . '>Resident</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Admission Diagnosis</label>
                            <textarea name="admission_diagnosis" rows="3" readonly>' . htmlspecialchars($patient->admission_diagnosis ?? '') . '</textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Timestamps Section -->
                <div class="form-section">
                    <h4>Record Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Created Date</label>
                            <input type="text" value="' . htmlspecialchars($patient->created_at ?? 'N/A') . '" readonly>
                        </div>
                        <div class="form-group">
                            <label>Last Updated</label>
                            <input type="text" value="' . htmlspecialchars($patient->updated_at ?? 'N/A') . '" readonly>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <style>
        .patient-details-form {
            max-width: 100%;
            font-family: Arial, sans-serif;
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #6f42c1;
        }
        
        .form-header h3 {
            margin: 0;
            color: #6f42c1;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
        }
        
        .form-section {
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 8px;
            background-color: #fafafa;
        }
        
        .form-section h4 {
            margin: 0 0 15px 0;
            color: #6f42c1;
            border-bottom: 1px solid #6f42c1;
            padding-bottom: 5px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group.full-width {
            flex: none;
            width: 100%;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: #f8f9fa;
        }
        
        .form-group input:not([readonly]):not([disabled]),
        .form-group select:not([disabled]),
        .form-group textarea:not([readonly]) {
            background-color: white;
            border-color: #6f42c1;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        
        .btn-primary {
            background-color: #6f42c1;
            color: white;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        </style>

        <script>
        document.getElementById("editBtn").addEventListener("click", function() {
            // Enable all form fields
            const form = document.getElementById("patientDetailsForm");
            const inputs = form.querySelectorAll("input[readonly], select[disabled], textarea[readonly]");
            
            inputs.forEach(input => {
                if (input.name !== "patient_no") { // Keep patient number readonly
                    input.removeAttribute("readonly");
                    input.removeAttribute("disabled");
                }
            });
            
            // Show save/cancel buttons, hide edit button
            document.getElementById("editBtn").style.display = "none";
            document.getElementById("saveBtn").style.display = "inline-block";
            document.getElementById("cancelBtn").style.display = "inline-block";
        });
        
        document.getElementById("cancelBtn").addEventListener("click", function() {
            // Disable all form fields
            const form = document.getElementById("patientDetailsForm");
            const inputs = form.querySelectorAll("input, select, textarea");
            
            inputs.forEach(input => {
                if (input.type !== "hidden") {
                    if (input.tagName === "SELECT") {
                        input.setAttribute("disabled", "disabled");
                    } else {
                        input.setAttribute("readonly", "readonly");
                    }
                }
            });
            
            // Show edit button, hide save/cancel buttons
            document.getElementById("editBtn").style.display = "inline-block";
            document.getElementById("saveBtn").style.display = "none";
            document.getElementById("cancelBtn").style.display = "none";
        });
        
        document.getElementById("saveBtn").addEventListener("click", function() {
            // Collect form data
            const form = document.getElementById("patientDetailsForm");
            const formData = new FormData(form);
            const patientId = formData.get("patient_id");
            
            // Show loading state
            this.textContent = "Saving...";
            this.disabled = true;
            
            // Send update request
            fetch(`/admin/patients/${patientId}/update`, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(\'meta[name="csrf-token"]\').getAttribute("content")
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    adminSuccess("Patient details updated successfully!");
                    
                    // Disable all form fields
                    const inputs = form.querySelectorAll("input, select, textarea");
                    inputs.forEach(input => {
                        if (input.type !== "hidden") {
                            if (input.tagName === "SELECT") {
                                input.setAttribute("disabled", "disabled");
                            } else {
                                input.setAttribute("readonly", "readonly");
                            }
                        }
                    });
                    
                    // Show edit button, hide save/cancel buttons
                    document.getElementById("editBtn").style.display = "inline-block";
                    document.getElementById("saveBtn").style.display = "none";
                    document.getElementById("cancelBtn").style.display = "none";
                } else {
                    adminError("Error updating patient: " + (data.message || "Unknown error"));
                }
            })
            .catch(error => {
                console.error("Error:", error);
                adminError("An error occurred while updating the patient details.");
            })
            .finally(() => {
                // Reset button state
                document.getElementById("saveBtn").textContent = "Save";
                document.getElementById("saveBtn").disabled = false;
            });
        });
        </script>';
        
        return $html;
    }
}
