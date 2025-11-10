<?php

namespace App\Http\Controllers;

use App\Models\ShareToken;
use App\Models\SharedAccess;
use App\Models\user;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShareTokenController extends Controller
{
    public function state(Request $request)
    {
        $authUser = Auth::user();

        $activeToken = ShareToken::where('owner_user_id', $authUser->id)
            ->where('expires_at', '>', Carbon::now())
            ->orderByDesc('id')
            ->first();

        $sharedFrom = SharedAccess::where('shared_user_id', $authUser->id)
            ->whereNull('revoked_at')
            ->with('owner')
            ->first();

        $sharedUsers = SharedAccess::where('owner_user_id', $authUser->id)
            ->whereNull('revoked_at')
            ->with('sharedUser')
            ->get();

        return response()->json([
            'activeToken' => $activeToken,
            'sharedFrom' => $sharedFrom,
            'sharedUsers' => $sharedUsers,
        ]);
    }

    public function generate(Request $request)
    {
        $authUser = Auth::user();

        $existingShared = SharedAccess::where('shared_user_id', $authUser->id)
            ->whereNull('revoked_at')
            ->first();

        if ($existingShared) {
            return response()->json(['message' => 'Users with shared access cannot generate tokens.'], 403);
        }

        $token = Str::random(100);
        $shareToken = ShareToken::create([
            'owner_user_id' => $authUser->id,
            'token' => $token,
            'max_uses' => 5,
            'uses' => 0,
            'expires_at' => Carbon::now()->addHours(3),
        ]);

        return response()->json($shareToken, 201);
    }

    public function paste(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $authUser = Auth::user();

        $token = ShareToken::where('token', $request->input('token'))
            ->orderByDesc('id')
            ->first();

        if (!$token) {
            return response()->json(['message' => 'Invalid token'], 404);
        }

        if ($token->isExpired()) {
            return response()->json(['message' => 'Token expired'], 410);
        }

        if ($token->uses >= $token->max_uses) {
            return response()->json(['message' => 'Token use limit reached'], 429);
        }

        if ($token->owner_user_id === $authUser->id) {
            return response()->json(['message' => 'Cannot use your own token'], 400);
        }

        $alreadyShared = SharedAccess::where('owner_user_id', $token->owner_user_id)
            ->where('shared_user_id', $authUser->id)
            ->whereNull('revoked_at')
            ->exists();

        if ($alreadyShared) {
            return response()->json(['message' => 'Already has access'], 409);
        }

        SharedAccess::create([
            'owner_user_id' => $token->owner_user_id,
            'shared_user_id' => $authUser->id,
            'share_token_id' => $token->id,
        ]);

        $token->increment('uses');

        return response()->json(['message' => 'Access granted']);
    }

    public function revoke(Request $request, user $sharedUser)
    {
        $authUser = Auth::user();

        $access = SharedAccess::where('owner_user_id', $authUser->id)
            ->where('shared_user_id', $sharedUser->id)
            ->whereNull('revoked_at')
            ->first();

        if (!$access) {
            return response()->json(['message' => 'No active access'], 404);
        }

        $access->update(['revoked_at' => Carbon::now()]);
        return response()->json(['message' => 'Access revoked']);
    }
}


