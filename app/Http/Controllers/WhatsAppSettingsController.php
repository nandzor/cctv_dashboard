<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppSettings;
use Illuminate\Http\Request;

class WhatsAppSettingsController extends Controller {
    public function index() {
        $whatsappSettings = WhatsAppSettings::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('whatsapp-settings.index', compact('whatsappSettings'));
    }

    public function create() {
        return view('whatsapp-settings.create');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:whatsapp_settings,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'phone_numbers' => ['required', 'string'],
            'message_template' => ['required', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        // Convert phone numbers string to array
        $phoneNumbers = array_map('trim', explode(',', $data['phone_numbers']));
        $data['phone_numbers'] = array_filter($phoneNumbers, function ($number) {
            return !empty($number);
        });

        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;

        WhatsAppSettings::create($data);

        return redirect()->route('whatsapp-settings.index')
            ->with('success', 'WhatsApp settings created successfully.');
    }

    public function show(WhatsAppSettings $whatsappSettings) {
        return view('whatsapp-settings.show', compact('whatsappSettings'));
    }

    public function edit(WhatsAppSettings $whatsappSettings) {
        return view('whatsapp-settings.edit', compact('whatsappSettings'));
    }

    public function update(Request $request, WhatsAppSettings $whatsappSettings) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:whatsapp_settings,name,' . $whatsappSettings->id],
            'description' => ['nullable', 'string', 'max:500'],
            'phone_numbers' => ['required', 'string'],
            'message_template' => ['required', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        // Convert phone numbers string to array
        $phoneNumbers = array_map('trim', explode(',', $data['phone_numbers']));
        $data['phone_numbers'] = array_filter($phoneNumbers, function ($number) {
            return !empty($number);
        });

        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;

        $whatsappSettings->update($data);

        return redirect()->route('whatsapp-settings.show', $whatsappSettings)
            ->with('success', 'WhatsApp settings updated successfully.');
    }

    public function destroy(WhatsAppSettings $whatsappSettings) {
        // Prevent deletion of default setting
        if ($whatsappSettings->is_default) {
            return redirect()->back()
                ->with('error', 'Cannot delete the default WhatsApp settings.');
        }

        $whatsappSettings->delete();

        return redirect()->route('whatsapp-settings.index')
            ->with('success', 'WhatsApp settings deleted successfully.');
    }

    public function setDefault(WhatsAppSettings $whatsappSettings) {
        $whatsappSettings->update(['is_default' => true]);

        return redirect()->back()
            ->with('success', 'Default WhatsApp settings updated successfully.');
    }
}
