<?php
// Check an order: an endpoint for Bit's "checkOrder" tool.
// Add this to routes/api.php (it's served at /api/bit/check-order), TOOL_CHECK_ORDER_SECRET=... to your .env, and to
// config/services.php: 'bit' => ['check_order_secret' => env('TOOL_CHECK_ORDER_SECRET')],
// Laravel's api routes skip CSRF, which is right here: Bit's signature is the check.

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('bit/check-order', function (Request $request) {
    $body = $request->getContent();
    if (! fromBit($body, (string) $request->header('x-bit-signature'))) {
        return response()->json(['error' => 'Bad signature'], 401);
    }
    $input = json_decode($body, true)['input'] ?? [];
    $orderNumber = $input['orderNumber'] ?? null; // The order number from the confirmation email

    // TODO: look it up in your own system (an Eloquent model, your order system, a courier API).
    $result = [
        'found' => true,
        'status' => 'Ready',
        'detail' => 'Whatever the visitor needs to hear, in plain field names',
    ];

    // Nothing matched? Say so, and the assistant tells the visitor: return response()->json(['found' => false]);
    return response()->json($result);
});

if (! function_exists('fromBit')) {
    /** True when the request really comes from Bit: a valid signature from the last 5 minutes. */
    function fromBit(string $body, string $header): bool
    {
        $secret = config('services.bit.check_order_secret');
        parse_str(str_replace(',', '&', $header), $parts);
        if (! $secret || ! isset($parts['t'], $parts['v1']) || abs(time() - (int) $parts['t']) > 300) {
            return false;
        }
        $want = hash_hmac('sha256', $parts['t'].'.'.$body, $secret);
        return hash_equals($want, (string) $parts['v1']);
    }
}
