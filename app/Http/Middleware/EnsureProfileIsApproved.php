<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks professional users (engineers / suppliers) whose profile is not yet
 * approved from performing professional actions.
 *
 * This is deliberately separate from authentication and from role/permission
 * checks: a user may legitimately HOLD the `engineer` or `supplier` role
 * (Access) while their professional profile is still `pending` or `rejected`
 * (Professional Status). Role != Approval.
 *
 * Customers and admins have no professional profile to gate and always pass.
 */
class EnsureProfileIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->isApprovedProfessional()) {
            return response()->json([
                'success' => false,
                'message' => 'Your professional profile is '
                    .$user->professionalApprovalStatus()
                    .'. This action is only available once your profile has been approved.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
