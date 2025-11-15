# HIMS Test Cases Update - Performance Testing

## Performance Testing Cases

### Dashboard Performance

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| PT_101 | Admin Dashboard Load Time | Admin Dashboard | Admin logged in, system under normal load | 1. Clear browser cache<br>2. Login as admin<br>3. Navigate to dashboard<br>4. Measure load time | Dashboard loads within 3 seconds | | Pending | Target: ≤3s | | |
| PT_102 | Doctor Dashboard Response Time | Doctor Dashboard | Doctor logged in | 1. Clear cache<br>2. Access doctor dashboard<br>3. Measure response time | Dashboard responds within 2 seconds | | Pending | Target: ≤2s | | |
| PT_103 | Nurse Dashboard with Patient Data | Nurse Dashboard | Nurse logged in, 100+ patients in system | 1. Login as nurse<br>2. Access dashboard with patient list<br>3. Measure load time | Loads within 4 seconds with pagination | | Pending | Target: ≤4s | | |

### User Management Performance

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| PT_104 | User List Load with 500+ Users | User Management | Admin logged in, 500+ users exist | 1. Navigate to user list<br>2. Measure load time<br>3. Test pagination performance | Page loads within 5 seconds | | Pending | Target: ≤5s | | |
| PT_105 | User Search Performance | User Management | Admin logged in, large user dataset | 1. Perform user search<br>2. Measure search response time<br>3. Test with various queries | Search results within 2 seconds | | Pending | Target: ≤2s | | |
| PT_106 | User Creation Form Submission | User Management | Admin logged in | 1. Fill user creation form<br>2. Submit form<br>3. Measure processing time | User created within 3 seconds | | Pending | Target: ≤3s | | |

### Patient Management Performance

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| PT_107 | Patient List Load Performance | Patient Management | Large patient database (1000+ records) | 1. Access patient list<br>2. Measure load time<br>3. Test pagination | Loads within 4 seconds | | Pending | Target: ≤4s | | |
| PT_108 | Patient Details Load | Patient Management | Patient with extensive history | 1. Access patient with multiple admissions<br>2. Load patient details<br>3. Measure response time | Details load within 3 seconds | | Pending | Target: ≤3s | | |
| PT_109 | Patient Admission Process | Patient Management | Nurse logged in, patient selected | 1. Start admission process<br>2. Complete admission form<br>3. Measure total time | Admission completed within 5 seconds | | Pending | Target: ≤5s | | |

### Lab Management Performance

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| PT_110 | Lab Order Creation | Lab Management | Nurse logged in, patient selected | 1. Create new lab order<br>2. Select multiple tests<br>3. Submit order | Order created within 3 seconds | | Pending | Target: ≤3s | | |
| PT_111 | Lab Results PDF Generation | Lab Management | Lab tech logged in, completed order | 1. Generate lab result PDF<br>2. Measure generation time<br>3. Verify download speed | PDF generated within 10 seconds | | Pending | Target: ≤10s | | |
| PT_112 | Lab Order History Load | Lab Management | Patient with extensive lab history | 1. Access patient lab history<br>2. Load all previous orders<br>3. Measure load time | History loads within 4 seconds | | Pending | Target: ≤4s | | |

### Report Generation Performance

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| PT_113 | Patient Report Generation | Report Generation | Admin logged in, large patient dataset | 1. Generate patient report<br>2. Set date range (1 year)<br>3. Measure generation time | Report generated within 15 seconds | | Pending | Target: ≤15s | | |
| PT_114 | Lab Report Generation | Report Generation | Admin logged in, extensive lab data | 1. Generate lab report<br>2. Include all lab orders<br>3. Measure processing time | Report ready within 20 seconds | | Pending | Target: ≤20s | | |
| PT_115 | Report Export to PDF | Report Generation | Report generated and displayed | 1. Export report to PDF<br>2. Measure export time<br>3. Verify file size | PDF exported within 8 seconds | | Pending | Target: ≤8s | | |
| PT_116 | Report Export to Excel | Report Generation | Report generated and displayed | 1. Export report to Excel<br>2. Measure export time<br>3. Check file integrity | Excel exported within 10 seconds | | Pending | Target: ≤10s | | |

### Billing System Performance

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| PT_117 | Billing List Load | Billing System | Cashier logged in, many billing records | 1. Access billing list<br>2. Load all pending bills<br>3. Measure load time | Billing list loads within 5 seconds | | Pending | Target: ≤5s | | |
| PT_118 | Receipt Generation | Billing System | Cashier logged in, completed payment | 1. Generate receipt<br>2. Process receipt creation<br>3. Measure generation time | Receipt generated within 5 seconds | | Pending | Target: ≤5s | | |

### Database Query Performance

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| PT_119 | Complex Patient Query | Database Performance | Large dataset loaded | 1. Execute complex patient search<br>2. Include multiple filters<br>3. Measure query time | Query executes within 3 seconds | | Pending | Target: ≤3s | | |
| PT_120 | Lab Results Query | Database Performance | Extensive lab data | 1. Query lab results with date range<br>2. Include patient filters<br>3. Measure execution time | Query completes within 4 seconds | | Pending | Target: ≤4s | | |

### Concurrent User Performance

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| PT_121 | 10 Concurrent Users Login | Authentication System | 10 users attempt simultaneous login | 1. Simulate 10 concurrent logins<br>2. Measure response times<br>3. Check system stability | All users login within 5 seconds | | Pending | Target: ≤5s each | | |
| PT_122 | Multiple Dashboard Access | System Performance | 5 users accessing different dashboards | 1. Have 5 users access dashboards<br>2. Measure individual response times<br>3. Monitor system performance | No performance degradation | | Pending | Maintain normal speeds | | |
| PT_123 | Concurrent Report Generation | Report System | 3 admins generating large reports | 1. Initiate 3 report generations<br>2. Monitor processing times<br>3. Check system resources | Reports complete without errors | | Pending | Within expected times | | |

### Memory and Resource Usage

| Test Case ID | Test Description | Module | Preconditions | Test Steps | Expected Result | Actual Result | Status | Remarks | Tester | Date |
|-------------|------------------|---------|---------------|------------|----------------|---------------|--------|---------|--------|------|
| PT_124 | Memory Usage During Peak Load | System Performance | Multiple users active, large operations | 1. Monitor memory usage<br>2. Perform memory-intensive operations<br>3. Check for memory leaks | Memory usage stays within limits | | Pending | Target: <80% RAM | | |
| PT_125 | File Upload Performance | System Performance | Large file uploads (lab results, reports) | 1. Upload large files (10MB+)<br>2. Measure upload time<br>3. Verify processing | Files uploaded within acceptable time | | Pending | Target: <2min for 10MB | | |