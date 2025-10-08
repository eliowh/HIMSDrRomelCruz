<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class LabTemplateController extends Controller
{
    private $templatePath;
    
    public function __construct()
    {
        $this->templatePath = resource_path('views/labtech/templates');
    }
    
    /**
     * Get available laboratory templates
     */
    public function getAvailableTemplates()
    {
        $templates = [
            'hematology' => [
                'name' => 'Hematology Laboratory Result Form',
                'file' => 'hematology.html',
                'description' => 'Complete blood count with differential count, includes WBC, RBC, HGB, HCT, PLT, clotting and bleeding time'
            ],
            'blood_typing' => [
                'name' => 'Blood Typing Result Form',
                'file' => 'blood_typing.html',
                'description' => 'Blood type determination (A, B, AB, O) and Rh factor (positive/negative)'
            ],
            'fecal_occult_blood' => [
                'name' => 'Fecal Occult Blood Test Result Form',
                'file' => 'fecal_occult_blood.html',
                'description' => 'Stool test for hidden blood (positive/negative result)'
            ],
            'pregnancy_test' => [
                'name' => 'Pregnancy Test Result Form',
                'file' => 'pregnancy_test.html',
                'description' => 'Pregnancy test result (positive/negative)'
            ],
            'urinalysis' => [
                'name' => 'Urine Analysis Result Form',
                'file' => 'urinalysis.html',
                'description' => 'Complete urinalysis including physical characteristics and microscopic findings'
            ],
            'clinical_chemistry' => [
                'name' => 'Clinical Chemistry Laboratory Result Form',
                'file' => 'clinical_chemistry.html',
                'description' => 'Blood chemistry panel including glucose, cholesterol, liver and kidney function tests'
            ],
            'coagulation_test' => [
                'name' => 'Coagulation Test Result Form',
                'file' => 'coagulation_test.html',
                'description' => 'Blood clotting tests including PT, PTT, INR with medical signatures'
            ]
        ];
        
        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }
    
    /**
     * Get a specific template HTML content
     */
    public function getTemplate($templateType, Request $request)
    {
        $templateFile = $this->getTemplateFile($templateType);
        
        if (!$templateFile) {
            return response()->json([
                'success' => false,
                'error' => 'Template not found'
            ], 404);
        }
        
        $filePath = $this->templatePath . '/' . $templateFile;
        
        if (!File::exists($filePath)) {
            return response()->json([
                'success' => false,
                'error' => 'Template file not found'
            ], 404);
        }
        
        $htmlContent = File::get($filePath);
        
        // Replace placeholders with actual data if provided
        $patientData = $request->get('patient_data', []);
        $htmlContent = $this->replacePlaceholders($htmlContent, $patientData);
        
        return response()->json([
            'success' => true,
            'html' => $htmlContent,
            'template_type' => $templateType
        ]);
    }
    
    /**
     * Serve template as viewable HTML page
     */
    public function viewTemplate($templateType, Request $request)
    {
        $templateFile = $this->getTemplateFile($templateType);
        
        if (!$templateFile) {
            abort(404, 'Template not found');
        }
        
        $filePath = $this->templatePath . '/' . $templateFile;
        
        if (!File::exists($filePath)) {
            abort(404, 'Template file not found');
        }
        
        $htmlContent = File::get($filePath);
        
        // Replace placeholders with actual data if provided
        $patientData = $request->get('patient_data', []);
        $htmlContent = $this->replacePlaceholders($htmlContent, $patientData);
        
        return Response::make($htmlContent, 200, [
            'Content-Type' => 'text/html'
        ]);
    }
    
    /**
     * Generate filled template for a specific lab order
     */
    public function generateFilledTemplate($templateType, $orderId, Request $request)
    {
        // Get lab order data
        $order = \App\Models\LabOrder::with(['patient', 'requestedBy'])->find($orderId);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Lab order not found'
            ], 404);
        }
        
        $templateFile = $this->getTemplateFile($templateType);
        
        if (!$templateFile) {
            return response()->json([
                'success' => false,
                'error' => 'Template not found'
            ], 404);
        }
        
        $filePath = $this->templatePath . '/' . $templateFile;
        
        if (!File::exists($filePath)) {
            return response()->json([
                'success' => false,
                'error' => 'Template file not found'
            ], 404);
        }
        
        $htmlContent = File::get($filePath);
        
        // Prepare patient data from order
        $patientData = [
            'PATIENT_NAME' => $order->patient_name ?? 'N/A',
            'AGE_SEX' => ($order->patient->age ?? 'N/A') . '/' . ($order->patient->gender ?? 'N/A'),
            'WARD' => $order->patient->room ?? 'N/A',
            'DATE' => now()->format('l, d F Y')
        ];
        
        // Replace placeholders with actual patient data
        $htmlContent = $this->replacePlaceholders($htmlContent, $patientData);
        
        return response()->json([
            'success' => true,
            'html' => $htmlContent,
            'template_type' => $templateType,
            'order_id' => $orderId,
            'patient_data' => $patientData
        ]);
    }
    
    /**
     * Get template file name based on type
     */
    private function getTemplateFile($templateType)
    {
        $templates = [
            'hematology' => 'hematology.html',
            'blood_typing' => 'blood_typing.html',
            'fecal_occult_blood' => 'fecal_occult_blood.html',
            'pregnancy_test' => 'pregnancy_test.html',
            'urinalysis' => 'urinalysis.html',
            'clinical_chemistry' => 'clinical_chemistry.html',
            'coagulation_test' => 'coagulation_test.html'
        ];
        
        return $templates[$templateType] ?? null;
    }
    
    /**
     * Replace placeholders in HTML content with actual data
     */
    private function replacePlaceholders($htmlContent, $data)
    {
        foreach ($data as $key => $value) {
            $htmlContent = str_replace('[' . $key . ']', $value, $htmlContent);
        }
        
        // Replace any remaining placeholders with empty strings or default values
        $htmlContent = preg_replace('/\[([A-Z_]+)\]/', '', $htmlContent);
        
        return $htmlContent;
    }
    
    /**
     * List all available templates with their details
     */
    public function listTemplates()
    {
        $templates = [];
        $templateFiles = File::files($this->templatePath);
        
        foreach ($templateFiles as $file) {
            if ($file->getExtension() === 'html') {
                $filename = $file->getFilenameWithoutExtension();
                $templates[] = [
                    'name' => ucwords(str_replace('_', ' ', $filename)),
                    'filename' => $file->getFilename(),
                    'type' => $filename,
                    'size' => $file->getSize(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime())
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'templates' => $templates,
            'total' => count($templates)
        ]);
    }
}