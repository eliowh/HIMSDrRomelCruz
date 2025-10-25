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
use App\Services\FHIR\FhirService;

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
            'title' => [
                'nullable',
                'min:1',
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
            'title.min' => 'Title must be at least 1 character.',
            'title.max' => 'Title must be 20 characters or less.',
            'title.regex' => 'Title can only contain letters and spaces.',
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
        
        // Generate temporary password
        $tempPassword = \Illuminate\Support\Str::random(16);
        
        // Log token for debugging
        \Log::info('Generated token for new user', [
            'email' => $request->email,
            'token' => $token,
            'temp_password' => $tempPassword
        ]);
        
        // Create user with a temporary password
        $user = User::create([
            'name' => $request->name,
            'title' => $request->title,
            'email' => $request->email,
            'password' => Hash::make($tempPassword), // Use the stored temporary password
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
     * Archive a user (soft delete)
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent archiving of the current admin user
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot archive your own account.'
                ], 403);
            }

            // Store user data for logging before archiving
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at->toISOString()
            ];

            // Archive the user (soft delete)
            $user->delete();

            // Log user archiving
            Report::log(
                'User Archived',
                Report::TYPE_USER_ACTIVITY,
                "Admin archived user: {$userData['name']}",
                [
                    'archived_user' => $userData,
                    'archived_by' => auth()->user()->name,
                    'archived_by_id' => auth()->id(),
                    'archive_time' => now()->toISOString(),
                    'note' => 'User account archived to preserve data integrity'
                ],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'User archived successfully! Account data has been preserved.'
            ]);

        } catch (\Exception $e) {
            \Log::error('User archiving error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error archiving user: ' . $e->getMessage()
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
            // Select explicit columns including contact_number and sex for consistent view rendering
            $query = \DB::table('patients')->select('id','patient_no','first_name','last_name','date_of_birth','status','created_at','contact_number','sex');
            
            // Get search and sort parameters
            $search = $request->get('q', '');
            $status = $request->get('status', '');
            $sortBy = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            // Validate sort column to prevent SQL injection
            $allowedSortColumns = ['patient_no', 'first_name', 'last_name', 'status', 'room_no', 'created_at', 'contact_number'];
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

            // Validate the input based on simplified form fields
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'date_of_birth' => 'nullable|date|before_or_equal:today',
                'nationality' => 'nullable|string|max:255',
                'province' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'barangay' => 'nullable|string|max:255',
                'status' => 'nullable|in:Active,Discharged,Deceased',
                // Support both new and legacy field names
                'phone_number' => 'nullable|string|max:50',
                'contact_number' => 'nullable|string|max:50',
                'gender' => 'nullable|in:male,female,other',
                'sex' => 'nullable|in:male,female,other'
            ]);

            // Update the patient record
            // Normalize field names: prefer contact_number and sex as DB columns
            $updateData = $validated;
            if (isset($validated['phone_number']) && !isset($validated['contact_number'])) {
                $updateData['contact_number'] = $validated['phone_number'];
            }
            if (isset($validated['gender']) && !isset($validated['sex'])) {
                $updateData['sex'] = $validated['gender'];
            }

            // Keep only columns that actually exist in the patients table to avoid SQL errors
            $permitted = array_intersect_key($updateData, array_flip(['first_name','middle_name','last_name','date_of_birth','nationality','province','city','barangay','status','contact_number','sex']));

            $updated = \DB::table('patients')
                ->where('id', $id)
                ->update(array_merge($permitted, [
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
        // Calculate age display (years only) from date_of_birth
        $ageDisplay = 'N/A';
        if (!empty($patient->date_of_birth)) {
            try {
                $dob = \Carbon\Carbon::parse($patient->date_of_birth);
                $ageDisplay = intval($dob->diffInYears(now())) . ' years';
            } catch (\Exception $e) {
                $ageDisplay = 'N/A';
            }
        }

        $html = '
        <div class="simple-patient-form">
            <form id="patientForm">
                <input type="hidden" name="patient_id" value="' . $patient->id . '">
                
                <div class="form-grid">
                    <div class="form-field">
                        <label>Patient No.</label>
                        <input type="text" name="patient_no" value="' . htmlspecialchars($patient->patient_no ?? '') . '" readonly>
                    </div>
                    
                    <div class="form-field">
                        <label>Status</label>
                        <select name="status">
                            <option value="Active"' . (($patient->status ?? '') === 'Active' ? ' selected' : '') . '>Active</option>
                            <option value="Discharged"' . (($patient->status ?? '') === 'Discharged' ? ' selected' : '') . '>Discharged</option>
                            <option value="Deceased"' . (($patient->status ?? '') === 'Deceased' ? ' selected' : '') . '>Deceased</option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="' . htmlspecialchars($patient->first_name ?? '') . '">
                    </div>
                    
                    <div class="form-field">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="' . htmlspecialchars($patient->middle_name ?? '') . '">
                    </div>
                    
                    <div class="form-field">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="' . htmlspecialchars($patient->last_name ?? '') . '">
                    </div>
                    
                    <div class="form-field">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" value="' . htmlspecialchars($patient->date_of_birth ?? '') . '" max="' . date('Y-m-d') . '">
                    </div>
                    
                    <div class="form-field">
                        <label>Age</label>
                        <input type="text" value="' . htmlspecialchars($ageDisplay) . '" readonly>
                    </div>
                    
                    <div class="form-field">
                        <label>Nationality</label>
                        <input type="text" name="nationality" value="' . htmlspecialchars($patient->nationality ?? '') . '">
                    </div>

                    <div class="form-field">
                        <label>Contact Number</label>
                        <input id="edit_contact_number" type="number" name="contact_number" placeholder="Enter contact number" min="1000000000" max="99999999999" maxlength="11" oninput="if(this.value.length > 11) this.value = this.value.slice(0, 11);" value="' . htmlspecialchars($patient->contact_number ?? '') . '">
                    </div>

                    <div class="form-field">
                        <label>Sex</label>
                        <select name="sex">
                            <option value="">Select</option>
                            <option value="male"' . (strtolower($patient->sex ?? '') === 'male' ? ' selected' : '') . '>Male</option>
                            <option value="female"' . (strtolower($patient->sex ?? '') === 'female' ? ' selected' : '') . '>Female</option>
                            <option value="other"' . (strtolower($patient->sex ?? '') === 'other' ? ' selected' : '') . '>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label>Province</label>
                        <input type="text" name="province" value="' . htmlspecialchars($patient->province ?? '') . '">
                    </div>
                    
                    <div class="form-field">
                        <label>City</label>
                        <input type="text" name="city" value="' . htmlspecialchars($patient->city ?? '') . '">
                    </div>
                    
                    <div class="form-field">
                        <label>Barangay</label>
                        <input type="text" name="barangay" value="' . htmlspecialchars($patient->barangay ?? '') . '">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="savePatientData()" class="save-btn">Save Changes</button>
                    <button type="button" onclick="closePatientDetailsModal()" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>

        <style>
        .simple-patient-form {
            padding: 20px;
            max-width: 100%;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-field {
            display: flex;
            flex-direction: column;
        }
        
        .form-field.full-width {
            grid-column: 1 / -1;
        }
        
        .form-field label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .form-field input,
        .form-field select,
        .form-field textarea {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-field input:focus,
        .form-field select:focus,
        .form-field textarea:focus {
            outline: none;
            border-color: #6f42c1;
            box-shadow: 0 0 0 2px rgba(111, 66, 193, 0.2);
        }
        
        .form-field input[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .save-btn, .cancel-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }
        
        .save-btn {
            background-color: #28a745;
            color: white;
        }
        
        .save-btn:hover {
            background-color: #218838;
        }
        
        .cancel-btn {
            background-color: #6c757d;
            color: white;
        }
        
        .cancel-btn:hover {
            background-color: #5a6268;
        }
        </style>

        <script>
        // Auto-calculate age when date of birth changes
        document.addEventListener("DOMContentLoaded", function() {
            const dobInput = document.querySelector("input[name=\"date_of_birth\"]");
            const ageDisplay = dobInput?.closest(".form-grid")?.querySelector("input[readonly]");
            
            if (dobInput && ageDisplay) {
                dobInput.addEventListener("change", function() {
                    if (this.value) {
                        const selectedDate = this.value; // Get the date string directly
                        const today = new Date().toISOString().split(\"T\")[0]; // Get today in YYYY-MM-DD format
                        
                        // Check if date is in the future
                        if (selectedDate > today) {
                            alert(\"Date of Birth cannot be in the future.\");
                            this.setCustomValidity(\"Date of Birth cannot be in the future.\");
                            this.value = \"\"; // Clear the invalid date
                            ageDisplay.value = \"N/A\";
                            return;
                        } else {
                            this.setCustomValidity(\"\"); // Clear any previous custom validity
                        }
                        
                        const birthDate = new Date(this.value);
                        const todayForAge = new Date();
                        let age = todayForAge.getFullYear() - birthDate.getFullYear();
                        const monthDiff = todayForAge.getMonth() - birthDate.getMonth();
                        
                        if (monthDiff < 0 || (monthDiff === 0 && todayForAge.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        
                        ageDisplay.value = age >= 0 ? age + " years" : "N/A";
                    } else {
                        ageDisplay.value = "N/A";
                    }
                });
                
                dobInput.addEventListener(\"input\", function() {
                    if (this.value) {
                        const selectedDate = this.value; // Get the date string directly
                        const today = new Date().toISOString().split(\"T\")[0]; // Get today in YYYY-MM-DD format
                        
                        if (selectedDate > today) {
                            this.setCustomValidity(\"Date of Birth cannot be in the future.\");
                        } else {
                            this.setCustomValidity(\"\"); // Clear any previous custom validity
                        }
                    }
                });
            }
        });
        </script>

';
        
        return $html;
    }

    /**
     * Show FHIR export interface
     */
    public function fhir()
    {
        // Verify admin access
        $this->verifyAdminAccess();

        // Get counts for the interface
        $stats = [
            'total_patients' => Patient::count(),
            'total_admissions' => \App\Models\Admission::count(),
            'total_lab_orders' => \App\Models\LabOrder::count(),
            'total_medications' => \App\Models\PatientMedicine::count() + \App\Models\PharmacyRequest::count(),
        ];

        return view('admin.admin_fhir', compact('stats'));
    }

    /**
     * Export patient data as FHIR JSON
     */
    public function exportPatientFhir(Request $request, $patientId = null)
    {
        $this->verifyAdminAccess();

        try {
            $fhirService = new FhirService();
            
            // Check if patient_no is provided in request (from form)
            $patientNo = $request->get('patient_no');
            
            if ($patientNo) {
                // Find patient by patient number
                $patient = Patient::where('patient_no', $patientNo)->first();
                
                if (!$patient) {
                    return back()->with('error', "Patient with number {$patientNo} not found.");
                }
                
                // Export specific patient by their database ID
                $bundle = $fhirService->getPatientBundle($patient->id);
                $filename = "patient_{$patientNo}_fhir_" . date('Y-m-d_H-i-s') . '.json';
            } elseif ($patientId) {
                // Export specific patient by ID (from route parameter)
                $bundle = $fhirService->getPatientBundle($patientId);
                $filename = "patient_{$patientId}_fhir_" . date('Y-m-d_H-i-s') . '.json';
            } else {
                // Export all patients (limited to avoid memory issues)
                $patients = Patient::with(['admissions', 'labOrders', 'medicines', 'pharmacyRequests'])
                    ->limit(50) // Limit to prevent memory issues
                    ->get();
                
                $bundle = [
                    'resourceType' => 'Bundle',
                    'id' => 'bulk-export-' . uniqid(),
                    'meta' => [
                        'lastUpdated' => now()->toISOString()
                    ],
                    'type' => 'collection',
                    'total' => 0,
                    'entry' => []
                ];

                foreach ($patients as $patient) {
                    $patientBundle = $fhirService->getPatientBundle($patient->id);
                    $bundle['entry'] = array_merge($bundle['entry'], $patientBundle['entry']);
                }

                $bundle['total'] = count($bundle['entry']);
                $filename = "all_patients_fhir_" . date('Y-m-d_H-i-s') . '.json';
            }

            // Return as downloadable JSON file
            return response()->json($bundle, 200, [
                'Content-Type' => 'application/fhir+json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            \Log::error('FHIR Export Error', [
                'patient_id' => $patientId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error exporting FHIR data: ' . $e->getMessage());
        }
    }

    /**
     * Export all encounters as FHIR JSON
     */
    public function exportEncountersFhir()
    {
        $this->verifyAdminAccess();

        try {
            $fhirService = new FhirService();
            $admissions = \App\Models\Admission::with('patient')->limit(100)->get();
            
            $bundle = [
                'resourceType' => 'Bundle',
                'id' => 'encounters-export-' . uniqid(),
                'meta' => [
                    'lastUpdated' => now()->toISOString()
                ],
                'type' => 'collection',
                'total' => $admissions->count(),
                'entry' => []
            ];

            foreach ($admissions as $admission) {
                $encounter = $fhirService->transformToFhir($admission);
                $bundle['entry'][] = [
                    'fullUrl' => config('app.url') . "/api/fhir/Encounter/{$admission->id}",
                    'resource' => $encounter
                ];
            }

            $filename = "encounters_fhir_" . date('Y-m-d_H-i-s') . '.json';

            return response()->json($bundle, 200, [
                'Content-Type' => 'application/fhir+json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            \Log::error('FHIR Encounters Export Error', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error exporting encounters: ' . $e->getMessage());
        }
    }

    /**
     * Export lab observations as FHIR JSON
     */
    public function exportObservationsFhir()
    {
        $this->verifyAdminAccess();

        try {
            $fhirService = new FhirService();
            $labOrders = \App\Models\LabOrder::with(['patient', 'labTech'])->limit(100)->get();
            
            $bundle = [
                'resourceType' => 'Bundle',
                'id' => 'observations-export-' . uniqid(),
                'meta' => [
                    'lastUpdated' => now()->toISOString()
                ],
                'type' => 'collection',
                'total' => $labOrders->count(),
                'entry' => []
            ];

            foreach ($labOrders as $labOrder) {
                $observation = $fhirService->transformToFhir($labOrder);
                $bundle['entry'][] = [
                    'fullUrl' => config('app.url') . "/api/fhir/Observation/{$labOrder->id}",
                    'resource' => $observation
                ];
            }

            $filename = "observations_fhir_" . date('Y-m-d_H-i-s') . '.json';

            return response()->json($bundle, 200, [
                'Content-Type' => 'application/fhir+json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            \Log::error('FHIR Observations Export Error', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error exporting observations: ' . $e->getMessage());
        }
    }

    /**
     * Export medication statements as FHIR JSON
     */
    public function exportMedicationsFhir()
    {
        $this->verifyAdminAccess();

        try {
            $fhirService = new FhirService();
            
            // Get both patient medicines and pharmacy requests
            $patientMedicines = \App\Models\PatientMedicine::with('patient')->limit(50)->get();
            $pharmacyRequests = \App\Models\PharmacyRequest::with('patient')->limit(50)->get();
            
            $bundle = [
                'resourceType' => 'Bundle',
                'id' => 'medications-export-' . uniqid(),
                'meta' => [
                    'lastUpdated' => now()->toISOString()
                ],
                'type' => 'collection',
                'total' => $patientMedicines->count() + $pharmacyRequests->count(),
                'entry' => []
            ];

            // Add patient medicines
            foreach ($patientMedicines as $medicine) {
                $medicationStatement = $fhirService->transformToFhir($medicine);
                $bundle['entry'][] = [
                    'fullUrl' => config('app.url') . "/api/fhir/MedicationStatement/pm-{$medicine->id}",
                    'resource' => $medicationStatement
                ];
            }

            // Add pharmacy requests
            foreach ($pharmacyRequests as $pharmacy) {
                $medicationStatement = $fhirService->transformToFhir($pharmacy);
                $bundle['entry'][] = [
                    'fullUrl' => config('app.url') . "/api/fhir/MedicationStatement/pr-{$pharmacy->id}",
                    'resource' => $medicationStatement
                ];
            }

            $filename = "medications_fhir_" . date('Y-m-d_H-i-s') . '.json';

            return response()->json($bundle, 200, [
                'Content-Type' => 'application/fhir+json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            \Log::error('FHIR Medications Export Error', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error exporting medications: ' . $e->getMessage());
        }
    }

    /**
     * Get FHIR capability statement
     */
    public function getFhirCapability()
    {
        $this->verifyAdminAccess();

        try {
            $fhirService = new FhirService();
            $capability = $fhirService->getCapabilityStatement();

            return response()->json($capability, 200, [
                'Content-Type' => 'application/fhir+json',
                'Content-Disposition' => 'attachment; filename="fhir_capability_' . date('Y-m-d_H-i-s') . '.json"'
            ], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            return back()->with('error', 'Error getting FHIR capability: ' . $e->getMessage());
        }
    }

    /**
     * Get archived users
     */
    public function archivedUsers(Request $request)
    {
        try {
            // Get only archived users (soft deleted)
            $query = User::onlyTrashed();
            
            // Get search and sort parameters
            $search = $request->get('search', '');
            $sortBy = $request->get('sort', 'deleted_at');
            $sortDirection = $request->get('direction', 'desc');
            
            // Validate sort column
            $allowedSortColumns = ['name', 'email', 'role', 'deleted_at', 'created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'deleted_at';
            }
            
            // Validate sort direction
            $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%");
                });
            }
            
            // Apply sorting and pagination
            $query->orderBy($sortBy, $sortDirection);
            $archivedUsers = $query->paginate(10);
            
            // Preserve search parameters in pagination links
            $archivedUsers->appends(request()->query());
            
            return view('admin.archived_users', compact('archivedUsers'));
            
        } catch (\Exception $e) {
            \Log::error('Archived users error: ' . $e->getMessage());
            $emptyCollection = collect();
            $archivedUsers = new \Illuminate\Pagination\LengthAwarePaginator(
                $emptyCollection,
                0,
                10,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            return view('admin.archived_users', compact('archivedUsers'))->with('error', 'Error loading archived users.');
        }
    }

    /**
     * Restore an archived user
     */
    public function restoreUser($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);

            // Store user data for logging
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'archived_at' => $user->deleted_at->toISOString()
            ];

            // Restore the user
            $user->restore();

            // Log user restoration
            Report::log(
                'User Restored',
                Report::TYPE_USER_ACTIVITY,
                "Admin restored archived user: {$userData['name']}",
                [
                    'restored_user' => $userData,
                    'restored_by' => auth()->user()->name,
                    'restored_by_id' => auth()->id(),
                    'restore_time' => now()->toISOString(),
                    'note' => 'User account restored from archived status'
                ],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'User account restored successfully! The user can now log in again.'
            ]);

        } catch (\Exception $e) {
            \Log::error('User restoration error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error restoring user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete an archived user (use with extreme caution)
     */
    public function permanentlyDeleteUser($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);

            // Prevent permanent deletion of the current admin user
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot permanently delete your own account.'
                ], 403);
            }

            // Store user data for logging before permanent deletion
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at->toISOString(),
                'archived_at' => $user->deleted_at->toISOString()
            ];

            // Permanently delete the user
            $user->forceDelete();

            // Log permanent deletion
            Report::log(
                'User Permanently Deleted',
                Report::TYPE_USER_ACTIVITY,
                "Admin permanently deleted user: {$userData['name']}",
                [
                    'permanently_deleted_user' => $userData,
                    'deleted_by' => auth()->user()->name,
                    'deleted_by_id' => auth()->id(),
                    'permanent_deletion_time' => now()->toISOString(),
                    'warning' => 'User data permanently removed - cannot be recovered'
                ],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'User permanently deleted. This action cannot be undone.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Permanent user deletion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting user: ' . $e->getMessage()
            ], 500);
        }
    }
}
