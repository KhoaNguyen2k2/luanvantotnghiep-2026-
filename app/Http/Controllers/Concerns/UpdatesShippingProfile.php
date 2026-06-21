<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

trait UpdatesShippingProfile
{
    /**
     * @return array<string, mixed>
     */
    protected function shippingProfileRules(?int $userId): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'mobile' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($userId)],
            'phone' => ['required', 'digits:10'],
            'zip' => ['required', 'digits:6'],
            'state' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'locality' => ['required', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function applyShippingProfile(User $user, Request $request): void
    {
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        if ($request->filled('password')) {
            $user->password = Hash::make((string) $request->password);
        }
        $user->save();

        Address::where('user_id', $user->id)->delete();

        Address::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'locality' => $request->locality,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->filled('country') ? (string) $request->country : 'Việt Nam',
            'landmark' => $request->landmark,
            'zip' => $request->zip,
            'type' => 'home',
            'isdefault' => true,
        ]);
    }
}
