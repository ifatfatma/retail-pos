<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class CustomerAuthController extends Controller
{
    // Resolve customer from request, Sanctum guard, or direct bearer token
    protected function getAuthenticatedCustomer(Request $request): ?Customer
    {
        $user = $request->user();
        if ($user instanceof Customer) {
            return $user;
        }

        $sanctumUser = auth('sanctum')->user();
        if ($sanctumUser instanceof Customer) {
            return $sanctumUser;
        }

        $bearer = $request->bearerToken();
        if ($bearer) {
            $token = PersonalAccessToken::findToken($bearer);
            if ($token && $token->tokenable instanceof Customer) {
                return $token->tokenable;
            }
        }

        return null;
    }

    // Customer Registration
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
        ]);

        $email = trim(strtolower($request->email));
        $phone = trim($request->phone);

        // Check if an existing guest customer record exists
        $existing = Customer::where('email', $email)
            ->orWhere('phone', $phone)
            ->first();

        if ($existing) {
            if ($existing->password) {
                throw ValidationException::withMessages([
                    'email' => ['An account with this email or phone number already exists. Please log in.'],
                ]);
            }

            // Upgrade guest checkout record to registered account
            $existing->name = $request->name;
            $existing->email = $email;
            $existing->phone = $phone;
            $existing->password = Hash::make($request->password);
            if ($request->filled('address')) $existing->address = $request->address;
            if ($request->filled('city')) $existing->city = $request->city;
            if ($request->filled('state')) $existing->state = $request->state;
            if ($request->filled('pincode')) $existing->pincode = $request->pincode;
            $existing->save();
            $customer = $existing;
        } else {
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'status' => true,
            ]);
        }

        $token = $customer->createToken('customer-access-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful! Welcome to our store.',
            'token' => $token,
            'customer' => $this->formatCustomer($customer),
        ], 201);
    }

    // Customer Login (supports either email or phone)
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->login);

        $customer = Customer::where('email', strtolower($login))
            ->orWhere('phone', $login)
            ->first();

        if (!$customer || !$customer->password || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'login' => ['Invalid email/phone or password. Please verify your credentials.'],
            ]);
        }

        $token = $customer->createToken('customer-access-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful. Welcome back!',
            'token' => $token,
            'customer' => $this->formatCustomer($customer),
        ]);
    }

    // Get current authenticated customer details
    public function me(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'customer' => $this->formatCustomer($customer),
        ]);
    }

    // Update customer profile & default address
    public function updateProfile(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:30|unique:customers,phone,' . $customer->id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'customer' => $this->formatCustomer($customer),
        ]);
    }

    // Change customer password
    public function changePassword(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password entered is incorrect.'],
            ]);
        }

        $customer->password = Hash::make($request->password);
        $customer->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    // Customer Order History
    public function orders(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $orders = Sale::where('customer_id', $customer->id)
            ->with(['items.product', 'items.variant'])
            ->latest()
            ->paginate($request->get('per_page', 10));

        $transformed = $orders->getCollection()->map(function ($o) {
            return [
                'id' => $o->id,
                'order_number' => $o->invoice_number,
                'sale_date' => $o->sale_date,
                'status' => $o->status,
                'payment_status' => $o->payment_status,
                'payment_method' => $o->payment_method,
                'shipping_address' => $o->shipping_address,
                'delivery_partner' => $o->delivery_partner,
                'tracking_number' => $o->tracking_number,
                'estimated_delivery_date' => $o->estimated_delivery_date,
                'subtotal' => (float) $o->subtotal,
                'shipping_cost' => (float) $o->shipping_cost,
                'tax_amount' => (float) $o->tax_amount,
                'total_amount' => (float) $o->total_amount,
                'items_count' => $o->items->sum('quantity'),
                'items' => $o->items->map(function ($it) {
                    return [
                        'name' => $it->product->name ?? 'Product',
                        'sku' => $it->variant->sku ?? '',
                        'quantity' => (float) $it->quantity,
                        'unit_price' => (float) $it->unit_price,
                        'total' => (float) $it->total,
                    ];
                }),
            ];
        });

        $orders->setCollection($transformed);

        return response()->json($orders);
    }

    // Single order detail for customer
    public function orderDetails(Request $request, $orderNumber)
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $order = Sale::where('customer_id', $customer->id)
            ->where('invoice_number', trim($orderNumber))
            ->with(['items.product', 'items.variant'])
            ->firstOrFail();

        return response()->json([
            'data' => [
                'order_number' => $order->invoice_number,
                'sale_date' => $order->sale_date,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'shipping_address' => $order->shipping_address,
                'delivery_partner' => $order->delivery_partner,
                'tracking_number' => $order->tracking_number,
                'estimated_delivery_date' => $order->estimated_delivery_date,
                'subtotal' => (float) $order->subtotal,
                'shipping_cost' => (float) $order->shipping_cost,
                'tax_amount' => (float) $order->tax_amount,
                'total_amount' => (float) $order->total_amount,
                'items' => $order->items->map(function ($it) {
                    return [
                        'name' => $it->product->name ?? 'Product',
                        'sku' => $it->variant->sku ?? '',
                        'quantity' => (float) $it->quantity,
                        'unit_price' => (float) $it->unit_price,
                        'total' => (float) $it->total,
                    ];
                }),
            ]
        ]);
    }

    // Customer Logout
    public function logout(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if ($customer) {
            $customer->currentAccessToken()?->delete();
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }

    // Request Password Reset Code
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
        ]);

        $login = trim($request->login);

        $customer = Customer::where('email', strtolower($login))
            ->orWhere('phone', $login)
            ->first();

        if (!$customer || !$customer->password) {
            throw ValidationException::withMessages([
                'login' => ['No registered customer account with a password was found for this email or phone.'],
            ]);
        }

        // Invalidate prior active reset tokens for this customer
        DB::table('customer_password_resets')
            ->where('customer_id', $customer->id)
            ->delete();

        // Generate 6-digit OTP code and secure token
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $token = bin2hex(random_bytes(32));

        DB::table('customer_password_resets')->insert([
            'customer_id' => $customer->id,
            'identifier' => $login,
            'token' => $token,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        // Mask destination for user feedback
        $targetDesc = '';
        if ($customer->email && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $customer->email);
            $targetDesc = substr($parts[0], 0, 3) . '***@' . $parts[1];
        } elseif ($customer->phone) {
            $targetDesc = substr($customer->phone, 0, 3) . '****' . substr($customer->phone, -2);
        } else {
            $targetDesc = 'your registered contact';
        }

        Log::info("Customer Password Reset Code for {$customer->email}/{$customer->phone}: {$code}");

        return response()->json([
            'message' => "A 6-digit password reset code has been generated for {$targetDesc}. Valid for 15 minutes.",
            'identifier' => $login,
            'token' => $token,
            'debug_code' => config('app.debug') || app()->environment('local') ? $code : null,
        ]);
    }

    // Reset Password with Code
    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'code' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $identifier = trim($request->identifier);
        $customer = Customer::where('email', strtolower($identifier))
            ->orWhere('phone', $identifier)
            ->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'identifier' => ['Customer account not found.'],
            ]);
        }

        $code = trim($request->code);

        $reset = DB::table('customer_password_resets')
            ->where('customer_id', $customer->id)
            ->where('code', $code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$reset) {
            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or has expired. Please request a new code.'],
            ]);
        }

        // Update password
        $customer->password = Hash::make($request->password);
        $customer->save();

        // Clear used code
        DB::table('customer_password_resets')
            ->where('customer_id', $customer->id)
            ->delete();

        // Revoke prior customer Sanctum tokens for security
        $customer->tokens()->delete();

        // Issue new authenticated token for seamless sign-in
        $newToken = $customer->createToken('customer-access-token')->plainTextToken;

        return response()->json([
            'message' => 'Password reset successfully! You are now signed in.',
            'token' => $newToken,
            'customer' => $this->formatCustomer($customer),
        ]);
    }

    protected function formatCustomer(Customer $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'email' => $c->email,
            'phone' => $c->phone,
            'address' => $c->address,
            'city' => $c->city,
            'state' => $c->state,
            'pincode' => $c->pincode,
            'total_spent' => (float) $c->total_spent,
            'total_orders' => (int) $c->total_orders,
            'created_at' => $c->created_at?->format('d M Y'),
        ];
    }
}
