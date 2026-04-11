<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Location;
use App\Models\Room;

class UploadRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rooms'            => ['required', 'array', 'min:1', $this->noDuplicatesInFileRule()],
            'rooms.*.name'     => ['required', 'string', 'max:255'],
            'rooms.*.location' => ['required', 'string', $this->locationExistsAndUniqueRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'rooms.*.name.required'     => 'name is required.',
            'rooms.*.location.required' => 'location is required.',
        ];
    }

    // Only checks duplicates within the uploaded file itself
    private function noDuplicatesInFileRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $seen = [];

            foreach ($value as $index => $room) {
                $name     = strtolower(trim($room['name'] ?? ''));
                $location = strtolower(trim($room['location'] ?? ''));
                $combo    = "{$name}|{$location}";

                if (in_array($combo, $seen)) {
                    $fail("Duplicate room '{$room['name']}' at location '{$room['location']}' found at row " . ($index + 1) . ".");
                }

                $seen[] = $combo;
            }
        };
    }

    // Fires per row — errors land on rooms.0.location, rooms.1.location, etc.
    private function locationExistsAndUniqueRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            // $attribute is e.g. "rooms.0.location" — extract index and name
            preg_match('/rooms\.(\d+)\.location/', $attribute, $matches);
            $index    = isset($matches[1]) ? (int) $matches[1] : null;
            $roomName = $this->input("rooms.{$index}.name");

            $location = Location::where('name', $value)->first();

            if (!$location) {
                $fail("location '{$value}' does not exist.");
                return;
            }

            if (Room::where('name', $roomName)->where('location_id', $location->id)->exists()) {
                $fail("room '{$roomName}' already exists at location '{$value}'.");
            }
        };
    }
}