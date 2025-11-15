# HIMS Test Cases Update - Functionality Testing

## New Functionality Test Cases

### Admin Dashboard Module

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| FT_101 | Admin Dashboard Load Test | Admin Dashboard | Admin user logged in | 1. Login as admin<br>2. Navigate to /admin/home<br>3. Verify dashboard loads | Dashboard displays with all widgets, statistics, and navigation menu | | Pending | | | |
| FT_102 | Admin User Statistics Display | Admin Dashboard | Admin logged in, users exist in system | 1. Access admin dashboard<br>2. Check user statistics widget<br>3. Verify counts match database | Accurate count of users by role displayed | | Pending | | | |
| FT_103 | Admin Patient Statistics Display | Admin Dashboard | Admin logged in, patients exist | 1. Access admin dashboard<br>2. Check patient statistics<br>3. Verify active/discharged counts | Correct patient statistics shown | | Pending | | | |
| FT_104 | Admin Stock Summary Widget | Admin Dashboard | Admin logged in, stock data exists | 1. Access admin dashboard<br>2. Verify stock summary widget<br>3. Check low stock alerts | Stock summary with low stock indicators displayed | | Pending | | | |

### User Management Module

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| FT_105 | Create New User Account | User Management | Admin logged in | 1. Navigate to /admin/users<br>2. Click create user<br>3. Fill required fields<br>4. Submit form | New user created successfully with email notification | | Pending | | | |
| FT_106 | Edit Existing User | User Management | Admin logged in, user exists | 1. Go to user list<br>2. Click edit on a user<br>3. Modify user details<br>4. Save changes | User information updated successfully | | Pending | | | |
| FT_107 | Delete User Account | User Management | Admin logged in, deletable user exists | 1. Navigate to user list<br>2. Select user to delete<br>3. Confirm deletion | User removed from system | | Pending | | | |
| FT_108 | User Search Functionality | User Management | Admin logged in, multiple users exist | 1. Access user list<br>2. Enter search term<br>3. Verify results | Only matching users displayed | | Pending | | | |
| FT_109 | User Role Filter | User Management | Admin logged in, users with different roles exist | 1. Access user list<br>2. Select role filter<br>3. Verify filtering | Only users with selected role shown | | Pending | | | |
| FT_110 | User List Pagination | User Management | Admin logged in, more than 10 users exist | 1. Access user list<br>2. Navigate through pages<br>3. Verify pagination works | Pagination controls work correctly | | Pending | | | |

### Appointment Scheduling Module

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| FT_111 | Doctor Appointment View | Appointment Scheduling | Doctor logged in | 1. Login as doctor<br>2. Navigate to appointments<br>3. View appointment list | Doctor can view their appointments | | Pending | | | |
| FT_112 | Nurse Appointment View | Appointment Scheduling | Nurse logged in | 1. Login as nurse<br>2. Navigate to appointments<br>3. View appointment list | Nurse can view relevant appointments | | Pending | | | |
| FT_113 | Appointment Schedule Display | Appointment Scheduling | User logged in, appointments exist | 1. Access appointment page<br>2. Verify schedule display<br>3. Check time slots | Schedule displays correctly with time slots | | Pending | | | |

### Report Generation Module

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| FT_114 | Generate Patient Report | Report Generation | Admin logged in, patients exist | 1. Navigate to /admin/reports<br>2. Select patient report<br>3. Set parameters<br>4. Generate report | Patient report generated successfully | | Pending | | | |
| FT_115 | Generate Lab Report | Report Generation | Admin logged in, lab data exists | 1. Access reports page<br>2. Select lab report type<br>3. Set date range<br>4. Generate | Lab report created with accurate data | | Pending | | | |
| FT_116 | Generate Billing Report | Report Generation | Admin logged in, billing data exists | 1. Go to reports<br>2. Select billing report<br>3. Choose parameters<br>4. Generate | Billing report shows financial data | | Pending | | | |
| FT_117 | Export Report to PDF | Report Generation | Admin logged in, report generated | 1. Generate any report<br>2. Click export to PDF<br>3. Verify download | PDF file downloaded successfully | | Pending | | | |
| FT_118 | Export Report to Excel | Report Generation | Admin logged in, report generated | 1. Generate report<br>2. Click export to Excel<br>3. Verify download | Excel file downloaded with data | | Pending | | | |
| FT_119 | Delete Report | Report Generation | Admin logged in, report exists | 1. View report list<br>2. Select report to delete<br>3. Confirm deletion | Report removed from system | | Pending | | | |

### Role Management Module

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| FT_120 | Admin Role Access Control | Role Management | Admin user logged in | 1. Login as admin<br>2. Access admin features<br>3. Verify all admin routes accessible | Admin has access to all admin features | | Pending | | | |
| FT_121 | Doctor Role Restrictions | Role Management | Doctor logged in | 1. Login as doctor<br>2. Try to access admin routes<br>3. Verify restrictions | Doctor cannot access admin-only features | | Pending | | | |
| FT_122 | Nurse Role Permissions | Role Management | Nurse logged in | 1. Login as nurse<br>2. Access nurse features<br>3. Verify patient management access | Nurse can access patient management features | | Pending | | | |
| FT_123 | Lab Technician Role Access | Role Management | Lab tech logged in | 1. Login as lab technician<br>2. Access lab features<br>3. Try restricted areas | Lab tech can access lab features only | | Pending | | | |
| FT_124 | Cashier Role Permissions | Role Management | Cashier logged in | 1. Login as cashier<br>2. Access billing features<br>3. Verify limitations | Cashier can access billing module only | | Pending | | | |
| FT_125 | Role Assignment Notification | Role Management | Admin creating user | 1. Create new user with role<br>2. Check email notification<br>3. Verify role details | User receives role assignment notification | | Pending | | | |

### Patient Management Advanced Features

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| FT_126 | Patient Admission Process | Patient Management | Nurse logged in, patient exists | 1. Access patient list<br>2. Select patient<br>3. Create admission<br>4. Assign room | Patient admitted with room assignment | | Pending | | | |
| FT_127 | Patient Discharge Process | Patient Management | Nurse logged in, admitted patient exists | 1. Access patient with active admission<br>2. Initiate discharge<br>3. Complete discharge form | Patient discharged successfully | | Pending | | | |
| FT_128 | Doctor Patient View | Patient Management | Doctor logged in, patients exist | 1. Login as doctor<br>2. Access patient list<br>3. View patient details | Doctor can view patient information | | Pending | | | |
| FT_129 | Patient Medicine History | Patient Management | User logged in, patient with medicines exists | 1. Access patient details<br>2. View medicine history<br>3. Verify data accuracy | Medicine history displayed correctly | | Pending | | | |
| FT_130 | Patient Lab Results View | Patient Management | User logged in, patient with lab results exists | 1. Access patient details<br>2. View lab results<br>3. Check result details | Lab results displayed with PDF links | | Pending | | | |

### Lab Management Module

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| FT_131 | Create Lab Order | Lab Management | Nurse logged in, patient exists | 1. Access patient<br>2. Create lab order<br>3. Select tests<br>4. Submit order | Lab order created successfully | | Pending | | | |
| FT_132 | Lab Order Status Update | Lab Management | Lab tech logged in, orders exist | 1. Login as lab tech<br>2. View lab orders<br>3. Update order status | Order status updated correctly | | Pending | | | |
| FT_133 | Lab Result PDF Generation | Lab Management | Lab tech logged in, completed order exists | 1. Access completed lab order<br>2. Generate result PDF<br>3. Verify PDF content | PDF generated with test results | | Pending | | | |
| FT_134 | Lab Template Selection | Lab Management | Lab tech logged in | 1. Access lab templates<br>2. Select template<br>3. Generate results using template | Template applied correctly to results | | Pending | | | |

### FHIR Integration Module

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| FT_135 | FHIR Patient Export | FHIR Integration | Admin logged in, patients exist | 1. Navigate to FHIR page<br>2. Export patients to FHIR<br>3. Verify output format | FHIR-compliant patient data exported | | Pending | | | |
| FT_136 | FHIR Capability Statement | FHIR Integration | System accessible | 1. Access /api/fhir/metadata<br>2. Verify capability statement<br>3. Check FHIR compliance | Valid FHIR capability statement returned | | Pending | | | |
| FT_137 | FHIR Patient Search | FHIR Integration | System accessible, patients exist | 1. Access /api/fhir/Patient<br>2. Perform patient search<br>3. Verify results format | FHIR-formatted patient search results | | Pending | | | |