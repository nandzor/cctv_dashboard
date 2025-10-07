<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetectionRequest extends FormRequest {
    public function authorize(): bool {
        // Authorization handled by ApiKeyAuth middleware
        return true;
    }

    public function rules(): array {
        return [
            're_id' => ['required', 'string', 'max:100'],
            'branch_id' => ['required', 'exists:company_branches,id'],
            'device_id' => ['required', 'exists:device_masters,device_id'],
            'detected_count' => ['required', 'integer', 'min:1'],
            'detection_data' => ['nullable', 'array'],
            'detection_data.confidence' => ['nullable', 'numeric', 'between:0,1'],
            'detection_data.bounding_box' => ['nullable', 'array'],
            'detection_data.appearance_features' => ['nullable', 'array'],
            'image' => ['nullable', 'image', 'max:10240'], // 10MB max
        ];
    }

    public function messages(): array {
        return [
            're_id.required' => 'Re-identification ID is required',
            'branch_id.required' => 'Branch ID is required',
            'branch_id.exists' => 'Branch does not exist',
            'device_id.required' => 'Device ID is required',
            'device_id.exists' => 'Device does not exist',
            'detected_count.required' => 'Detected count is required',
            'detected_count.min' => 'Detected count must be at least 1',
            'image.image' => 'File must be an image',
            'image.max' => 'Image size must not exceed 10MB',
        ];
    }
}
