# Laboratory Templates Documentation

## Overview
This directory contains HTML templates for laboratory result forms used by Romel Cruz Hospital. Each template is designed to match official medical laboratory standards and can be used for generating lab results.

## Available Templates

### 1. Hematology Laboratory Result Form (`hematology.html`)
- **Purpose**: Complete blood count with differential
- **Tests Included**: WBC, Lymph#, Mid#, Gran#, Lymph%, Mid%, Gran%, RBC, HGB, HCT, PLT, Clotting Time, Bleeding Time
- **Reference Values**: Included for all parameters
- **Features**: Professional hospital header, patient info section, structured test results table

### 2. Blood Typing Result Form (`blood_typing.html`)
- **Purpose**: ABO blood group and Rh factor determination
- **Result Format**: Large text display (e.g., "A" RH POSITIVE)
- **Usage**: Simple positive/negative or blood type determination

### 3. Fecal Occult Blood Test (`fecal_occult_blood.html`)
- **Purpose**: Stool examination for hidden blood
- **Result Format**: Large POSITIVE/NEGATIVE display
- **Usage**: Screening for gastrointestinal bleeding

### 4. Pregnancy Test (`pregnancy_test.html`)
- **Purpose**: Pregnancy detection test
- **Result Format**: Large POSITIVE/NEGATIVE display
- **Usage**: Urine or blood-based pregnancy testing

### 5. Urine Analysis (`urinalysis.html`)
- **Purpose**: Comprehensive urinalysis
- **Sections**: 
  - Physical Characteristics (color, transparency, pH, specific gravity)
  - Microscopic Findings (RBC, WBC, epithelial cells, bacteria, casts)
- **Features**: Two-column layout for microscopic findings

### 6. Clinical Chemistry (`clinical_chemistry.html`)
- **Purpose**: Blood chemistry panel
- **Features**: 
  - Normal values in both SI and traditional units
  - Tests for glucose, cholesterol, liver function, kidney function
  - Comprehensive reference ranges

### 7. Coagulation Test (`coagulation_test.html`)
- **Purpose**: Blood clotting function tests
- **Tests**: PT, PTT, INR, APTT
- **Features**: Medical signatures section (pathologist and technologist)

## How to Use Templates

### 1. Direct Access
Templates can be accessed directly via URLs:
```
/labtech/templates/{template_type}/view
```

### 2. API Access
Get template HTML via API:
```
GET /labtech/templates/{template_type}
```

### 3. Filled Templates
Generate templates with patient data:
```
GET /labtech/templates/{template_type}/order/{order_id}
```

### 4. Available Templates List
Get list of all available templates:
```
GET /labtech/templates
```

## Template Structure

All templates follow a consistent structure:
1. **Hospital Header**: Logo, hospital name, address, license
2. **Form Title**: Specific test name
3. **Patient Information**: Name, age/sex, ward, date
4. **Test Results**: Organized by test type
5. **Signatures** (where applicable): Medical professionals

## CSS Features

- **Print Optimized**: All templates are designed for printing
- **Responsive**: Works on different screen sizes
- **Professional Styling**: Matches medical document standards
- **Input Fields**: Ready for data entry
- **Focus States**: Enhanced user experience

## Integration with Laravel

Templates are managed by `LabTemplateController`:
- Dynamic data replacement
- Patient information population
- API endpoints for template access
- Validation and error handling

## Customization

Templates can be customized by:
1. Editing HTML files directly
2. Modifying CSS styles
3. Adding new input fields
4. Updating reference values
5. Adding new sections

## File Locations

```
resources/views/labtech/templates/
├── hematology.html
├── blood_typing.html
├── fecal_occult_blood.html
├── pregnancy_test.html
├── urinalysis.html
├── clinical_chemistry.html
├── coagulation_test.html
├── index.html
└── README.md
```

## Browser Compatibility

Templates are tested and compatible with:
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Printing Guidelines

For optimal printing results:
- Use A4 paper size
- Set margins to 1.5cm
- Enable background graphics
- Use portrait orientation

## Security Considerations

- Input validation is handled by the backend
- No JavaScript execution in templates
- XSS protection through proper escaping
- Access control via Laravel middleware

## Support

For template issues or customization requests, contact the IT department or system administrator.

---

**Last Updated**: October 2025  
**Version**: 1.0  
**Maintained by**: Hospital IT Department