<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function apply(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => 'required|in:sympathisant,membre,militant,responsable,dirigeant',
            'department' => 'nullable|string|max:100',
            'commune' => 'nullable|string|max:100',
            'section' => 'nullable|string|max:100',
            'motivation' => 'nullable|string|max:1000',
            'referral_code' => 'nullable|string|max:100',
        ]);

        $membership = Membership::updateOrCreate(
            ['user_id' => $user->id],
            [
                ...$validated,
                'status' => 'pending',
                'approved_at' => null,
                'approved_by' => null,
            ]
        );

        return $this->success($membership, 'Demande d’adhésion enregistrée', 201);
    }

    public function me(Request $request)
    {
        $membership = Membership::where('user_id', $request->user()->id)->first();

        return $this->success($membership, 'Adhésion');
    }
}
