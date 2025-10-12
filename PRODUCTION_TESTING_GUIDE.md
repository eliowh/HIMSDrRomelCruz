# FHIR System Testing Guide for Production

## 🌐 **Production URL**: `https://romelcruz.up.railway.app`

---

## 🔧 **Testing Tools & Methods**

### 1. **🌐 Built-in Browser Tester (Recommended)**
**URL**: `https://romelcruz.up.railway.app/fhir-tester.html`

**Why Use This**:
- ✅ Pre-configured for your FHIR endpoints
- ✅ No setup required
- ✅ Visual interface with color-coded results
- ✅ Tests all endpoints automatically
- ✅ Shows example requests/responses

**How to Use**:
1. Open the URL in any browser
2. Click "Test All Endpoints" 
3. View results (green = success, red = error)
4. Click individual test buttons for specific endpoints

---

### 2. **📮 Postman (Professional Testing)**

#### **Setup Collection**:
1. **Create New Collection**: "Dr. Romel Cruz FHIR API"
2. **Base URL Variable**: `{{base_url}}` = `https://romelcruz.up.railway.app`

#### **Essential Requests**:

**A) FHIR Metadata**
```
GET {{base_url}}/api/fhir/metadata
Headers:
- Accept: application/fhir+json
```

**B) Search Patients** 
```
GET {{base_url}}/api/fhir/Patient
Headers:
- Accept: application/fhir+json
```

**C) Get Specific Patient**
```
GET {{base_url}}/api/fhir/Patient/1
Headers:
- Accept: application/fhir+json
```

**D) Patient Everything Bundle**
```
GET {{base_url}}/api/fhir/Patient/1/$everything
Headers:
- Accept: application/fhir+json
```

**E) Validate FHIR Resource**
```
POST {{base_url}}/api/fhir/$validate
Headers:
- Content-Type: application/fhir+json
- Accept: application/fhir+json

Body (raw JSON):
{
  "resourceType": "Patient",
  "id": "test-patient-123",
  "name": [
    {
      "family": "Doe",
      "given": ["John"]
    }
  ]
}
```

**F) Get Lab Results**
```
GET {{base_url}}/api/fhir/Observation/1
Headers:
- Accept: application/fhir+json
```

#### **Expected Responses**:
- **Status**: 200 OK (or 400 for validation errors)
- **Content-Type**: `application/fhir+json`
- **Body**: Valid FHIR JSON with `resourceType` field

---

### 3. **🔥 HAPI FHIR Test Client**

**URL**: `http://hapi.fhir.org/resource`

#### **Configuration**:
1. **Server Base URL**: `https://romelcruz.up.railway.app/api/fhir`
2. **FHIR Version**: R4
3. **Format**: JSON

#### **Test Operations**:
- **Capabilities**: Click "Conformance" to test `/metadata` endpoint
- **Patient Search**: Resource Type = "Patient", Operation = "Search"
- **Read Patient**: Resource Type = "Patient", Operation = "Read", ID = "1"
- **Validation**: Use "$validate" operation with test resource

---

### 4. **📱 curl/HTTP Commands**

#### **Basic Tests**:

**Metadata (Capability Statement)**:
```bash
curl -H "Accept: application/fhir+json" \
     https://romelcruz.up.railway.app/api/fhir/metadata
```

**Patient Search**:
```bash
curl -H "Accept: application/fhir+json" \
     https://romelcruz.up.railway.app/api/fhir/Patient
```

**Specific Patient**:
```bash
curl -H "Accept: application/fhir+json" \
     https://romelcruz.up.railway.app/api/fhir/Patient/1
```

**Validation Test**:
```bash
curl -X POST \
     -H "Content-Type: application/fhir+json" \
     -H "Accept: application/fhir+json" \
     -d '{"resourceType":"Patient","id":"test-123","name":[{"text":"Test"}]}' \
     https://romelcruz.up.railway.app/api/fhir/\$validate
```

---

### 5. **🏥 HL7 FHIR Official Tools**

#### **FHIR Validator**:
- **URL**: `https://validator.fhir.org/`
- **Use**: Validate downloaded FHIR resources
- **Setup**: Paste JSON from your API responses

#### **Crucible Testing**:
- **URL**: `https://projectcrucible.org/`
- **Use**: Comprehensive FHIR server testing
- **Setup**: Register your server endpoint

---

## 📋 **Admin Interface Testing**

### **FHIR Data Export**:
**URL**: `https://romelcruz.up.railway.app/admin/fhir`

#### **Test Cases**:
1. **Individual Patient Export**:
   - Enter patient number: `250001`
   - Click "Export Patient FHIR"
   - Verify JSON download

2. **Bulk Exports**:
   - Click "Export All Patients"
   - Click "Export Encounters" 
   - Click "Export Lab Results"
   - Click "Export Medications"

3. **FHIR Capability**:
   - Click "Download Capability"
   - Verify OperationOutcome JSON

---

## 🧪 **Test Data Available**

### **Patient Numbers**: `250001` - `250020` (20 patients)
### **Sample Tests**:
- **Patient 250001**: Juan Cruz (11 FHIR resources)
- **Patient 250002**: Juan Cruz (multiple encounters)
- **Patient 250004**: John Doe (lab results available)

---

## ✅ **Expected FHIR Compliance**

### **Headers**:
- ✅ `Content-Type: application/fhir+json`
- ✅ `Cache-Control: no-cache, no-store, must-revalidate`

### **Response Structure**:
```json
{
  "resourceType": "Patient|Bundle|OperationOutcome",
  "id": "unique-identifier",
  "meta": {
    "lastUpdated": "2025-10-12T10:00:00Z"
  }
  // ... resource-specific fields
}
```

### **Bundle Format** (for multiple resources):
```json
{
  "resourceType": "Bundle",
  "type": "searchset|collection",
  "total": 20,
  "entry": [
    {"resource": {"resourceType": "Patient", ...}},
    {"resource": {"resourceType": "Encounter", ...}}
  ]
}
```

---

## 🚨 **Troubleshooting**

### **Common Issues**:

**1. CORS Errors**:
- ✅ **Fixed**: All endpoints support CORS
- Test with browser console open

**2. 404 Errors**:
- Check URL format: `/api/fhir/` prefix required
- Verify endpoint exists in capability statement

**3. 405 Method Not Allowed**:
- Verify HTTP method (GET vs POST)
- Check route registration

**4. Invalid JSON**:
- Validate with online JSON validator
- Check FHIR resource structure

---

## 📊 **Performance Benchmarks**

### **Expected Response Times**:
- **Metadata**: < 500ms
- **Single Patient**: < 1000ms  
- **Patient Bundle**: < 2000ms
- **Search Results**: < 1500ms
- **Validation**: < 300ms

### **Load Testing** (using Apache Bench):
```bash
ab -n 100 -c 10 https://romelcruz.up.railway.app/api/fhir/metadata
```

---

## 🎯 **Recommended Testing Workflow**

### **For Development/QA**:
1. ✅ Use built-in browser tester for quick validation
2. ✅ Use Postman for detailed API testing
3. ✅ Test admin interface exports
4. ✅ Validate downloaded resources with HL7 tools

### **For Production Monitoring**:
1. ✅ Setup Postman collection with automated tests
2. ✅ Monitor `/metadata` endpoint for uptime
3. ✅ Regular FHIR validation checks
4. ✅ Performance monitoring with response time alerts

---

**Last Updated**: October 12, 2025  
**FHIR Version**: R4 (4.0.1)  
**Production Status**: ✅ Ready for Testing