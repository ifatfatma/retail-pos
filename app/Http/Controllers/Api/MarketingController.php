<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomatedRule;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\CustomerNotificationPreference;
use App\Models\MarketingCampaign;
use App\Models\MarketingSegment;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\Setting;
use App\Services\MarketingService;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketingController extends Controller
{
    public function __construct(
        protected MarketingService $marketingService
    ) {}

    // -------------------------------------------------------------
    // 1. DASHBOARD METRICS
    // -------------------------------------------------------------
    public function dashboard(): JsonResponse
    {
        $metrics = $this->marketingService->getDashboardMetrics();
        return response()->json($metrics);
    }

    // -------------------------------------------------------------
    // 2. CAMPAIGN MANAGEMENT
    // -------------------------------------------------------------
    public function campaigns(Request $request): JsonResponse
    {
        $this->marketingService->ensureDefaults();

        $query = MarketingCampaign::with(['segment', 'coupon', 'creator'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('campaign_type', $request->type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $campaigns = $query->paginate($request->input('per_page', 10));

        return response()->json($campaigns);
    }

    public function scheduledCampaigns(Request $request): JsonResponse
    {
        $campaigns = MarketingCampaign::with(['segment', 'coupon'])
            ->whereIn('status', ['scheduled', 'active'])
            ->whereNotNull('start_date')
            ->orderBy('start_date', 'asc')
            ->paginate($request->input('per_page', 10));

        return response()->json($campaigns);
    }

    public function showCampaign(MarketingCampaign $campaign): JsonResponse
    {
        $campaign->load(['segment', 'coupon', 'creator']);
        return response()->json($campaign);
    }

    public function storeCampaign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'title_ar' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'banner_url' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:255',
            'campaign_type' => 'required|string|in:promotional,flash_sale,festival,seasonal,clearance,retargeting',
            'target_type' => 'required|string|in:all,segment,custom',
            'segment_id' => 'nullable|exists:marketing_segments,id',
            'channels' => 'required|array|min:1',
            'channels.*' => 'string|in:in_app,email,sms,whatsapp,push',
            'discount_type' => 'required|string|in:none,percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'coupon_id' => 'nullable|exists:coupons,id',
            'target_product_ids' => 'nullable|array',
            'target_category_ids' => 'nullable|array',
            'target_brand_ids' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|string|in:draft,scheduled,active,paused',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['created_by'] = $request->user()?->id;

        $campaign = MarketingCampaign::create($validated);

        // If status is active, dispatch immediately
        if ($campaign->status === 'active') {
            $this->marketingService->sendCampaign($campaign);
        }

        return response()->json([
            'message' => 'Promotional campaign created successfully.',
            'campaign' => $campaign->load(['segment', 'coupon']),
        ], 201);
    }

    public function updateCampaign(Request $request, MarketingCampaign $campaign): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'title_ar' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'banner_url' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:255',
            'campaign_type' => 'required|string|in:promotional,flash_sale,festival,seasonal,clearance,retargeting',
            'target_type' => 'required|string|in:all,segment,custom',
            'segment_id' => 'nullable|exists:marketing_segments,id',
            'channels' => 'required|array|min:1',
            'channels.*' => 'string|in:in_app,email,sms,whatsapp,push',
            'discount_type' => 'required|string|in:none,percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'coupon_id' => 'nullable|exists:coupons,id',
            'target_product_ids' => 'nullable|array',
            'target_category_ids' => 'nullable|array',
            'target_brand_ids' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|string|in:draft,scheduled,active,paused,completed,cancelled',
        ]);

        $campaign->update($validated);

        return response()->json([
            'message' => 'Campaign updated successfully.',
            'campaign' => $campaign->load(['segment', 'coupon']),
        ]);
    }

    public function destroyCampaign(MarketingCampaign $campaign): JsonResponse
    {
        $campaign->delete();
        return response()->json(['message' => 'Campaign deleted successfully.']);
    }

    public function sendCampaignNow(MarketingCampaign $campaign): JsonResponse
    {
        $result = $this->marketingService->sendCampaign($campaign);

        return response()->json([
            'message' => 'Campaign notifications dispatched successfully.',
            'result' => $result,
            'campaign' => $campaign->fresh(['segment', 'coupon']),
        ]);
    }

    public function toggleCampaignStatus(Request $request, MarketingCampaign $campaign): JsonResponse
    {
        $newStatus = match ($campaign->status) {
            'active' => 'paused',
            'paused' => 'active',
            'draft' => 'active',
            default => 'active',
        };

        $campaign->update(['status' => $newStatus]);

        if ($newStatus === 'active' && $campaign->total_sent === 0) {
            $this->marketingService->sendCampaign($campaign);
        }

        return response()->json([
            'message' => "Campaign status changed to {$newStatus}.",
            'campaign' => $campaign->fresh(['segment', 'coupon']),
        ]);
    }

    // -------------------------------------------------------------
    // 3. CUSTOMER SEGMENTS
    // -------------------------------------------------------------
    public function segments(): JsonResponse
    {
        $this->marketingService->ensureDefaults();

        $segments = MarketingSegment::withCount('campaigns')
            ->orderBy('is_system', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($segments as $segment) {
            $segment->recalculateCount();
        }

        return response()->json($segments);
    }

    public function storeSegment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'name_ar' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'criteria' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['is_system'] = false;

        $segment = MarketingSegment::create($validated);
        $segment->recalculateCount();

        return response()->json([
            'message' => 'Customer segment created successfully.',
            'segment' => $segment,
        ], 201);
    }

    public function updateSegment(Request $request, MarketingSegment $segment): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'name_ar' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'criteria' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $segment->update($validated);
        $segment->recalculateCount();

        return response()->json([
            'message' => 'Customer segment updated successfully.',
            'segment' => $segment,
        ]);
    }

    public function destroySegment(MarketingSegment $segment): JsonResponse
    {
        if ($segment->is_system) {
            return response()->json(['message' => 'System default segments cannot be deleted.'], 422);
        }

        $segment->delete();
        return response()->json(['message' => 'Customer segment deleted successfully.']);
    }

    public function segmentCustomers(MarketingSegment $segment): JsonResponse
    {
        $customers = $segment->getCustomersQuery()->take(50)->get();
        return response()->json([
            'total_count' => $segment->recalculateCount(),
            'customers' => $customers,
        ]);
    }

    // -------------------------------------------------------------
    // 4. AUTOMATED NOTIFICATION RULES
    // -------------------------------------------------------------
    public function rules(): JsonResponse
    {
        $this->marketingService->ensureDefaults();

        $rules = AutomatedRule::with(['template', 'segment'])
            ->latest('id')
            ->get();

        return response()->json($rules);
    }

    public function storeRule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'event_type' => 'required|string',
            'description' => 'nullable|string',
            'segment_id' => 'nullable|exists:marketing_segments,id',
            'template_id' => 'nullable|exists:notification_templates,id',
            'channels' => 'required|array|min:1',
            'channels.*' => 'string|in:in_app,email,sms,whatsapp,push',
            'conditions' => 'nullable|array',
            'frequency' => 'required|string|in:immediate,daily,weekly',
            'run_time' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $rule = AutomatedRule::create($validated);

        return response()->json([
            'message' => 'Automated rule created successfully.',
            'rule' => $rule->load(['template', 'segment']),
        ], 201);
    }

    public function updateRule(Request $request, AutomatedRule $rule): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'event_type' => 'required|string',
            'description' => 'nullable|string',
            'segment_id' => 'nullable|exists:marketing_segments,id',
            'template_id' => 'nullable|exists:notification_templates,id',
            'channels' => 'required|array|min:1',
            'channels.*' => 'string|in:in_app,email,sms,whatsapp,push',
            'conditions' => 'nullable|array',
            'frequency' => 'required|string|in:immediate,daily,weekly',
            'run_time' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);

        return response()->json([
            'message' => 'Automated rule updated successfully.',
            'rule' => $rule->load(['template', 'segment']),
        ]);
    }

    public function toggleRuleActive(AutomatedRule $rule): JsonResponse
    {
        $rule->update(['is_active' => !$rule->is_active]);

        return response()->json([
            'message' => $rule->is_active ? 'Rule activated successfully.' : 'Rule paused.',
            'rule' => $rule->load(['template', 'segment']),
        ]);
    }

    public function runRuleNow(AutomatedRule $rule): JsonResponse
    {
        $result = $this->marketingService->executeAutomatedRule($rule);

        return response()->json([
            'message' => 'Automated rule executed successfully.',
            'result' => $result,
            'rule' => $rule->fresh(['template', 'segment']),
        ]);
    }

    public function destroyRule(AutomatedRule $rule): JsonResponse
    {
        $rule->delete();
        return response()->json(['message' => 'Automated rule deleted successfully.']);
    }

    // -------------------------------------------------------------
    // 5. NOTIFICATION TEMPLATES
    // -------------------------------------------------------------
    public function templates(): JsonResponse
    {
        $this->marketingService->ensureDefaults();

        $templates = NotificationTemplate::latest('id')->get();
        return response()->json($templates);
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'key' => 'required|string|max:100|unique:notification_templates,key',
            'category' => 'required|string|in:promotional,retention,transactional,seasonal',
            'subject' => 'nullable|string|max:200',
            'subject_ar' => 'nullable|string|max:200',
            'title' => 'required|string|max:200',
            'title_ar' => 'nullable|string|max:200',
            'body' => 'required|string',
            'body_ar' => 'nullable|string',
            'action_url' => 'nullable|string|max:255',
            'action_text' => 'nullable|string|max:100',
            'banner_url' => 'nullable|string|max:255',
            'channels' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $template = NotificationTemplate::create($validated);

        return response()->json([
            'message' => 'Notification template created successfully.',
            'template' => $template,
        ], 201);
    }

    public function updateTemplate(Request $request, NotificationTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'key' => "required|string|max:100|unique:notification_templates,key,{$template->id}",
            'category' => 'required|string|in:promotional,retention,transactional,seasonal',
            'subject' => 'nullable|string|max:200',
            'subject_ar' => 'nullable|string|max:200',
            'title' => 'required|string|max:200',
            'title_ar' => 'nullable|string|max:200',
            'body' => 'required|string',
            'body_ar' => 'nullable|string',
            'action_url' => 'nullable|string|max:255',
            'action_text' => 'nullable|string|max:100',
            'banner_url' => 'nullable|string|max:255',
            'channels' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'message' => 'Notification template updated successfully.',
            'template' => $template,
        ]);
    }

    public function destroyTemplate(NotificationTemplate $template): JsonResponse
    {
        if ($template->is_system) {
            return response()->json(['message' => 'System templates cannot be deleted.'], 422);
        }

        $template->delete();
        return response()->json(['message' => 'Notification template deleted successfully.']);
    }

    // -------------------------------------------------------------
    // 6. DELIVERY REPORTS & LOGS
    // -------------------------------------------------------------
    public function deliveryReports(Request $request): JsonResponse
    {
        $query = NotificationLog::with(['campaign', 'rule', 'customer'])
            ->latest('id');

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('recipient', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('content', 'like', "%{$s}%");
            });
        }

        $logs = $query->paginate($request->input('per_page', 15));

        return response()->json($logs);
    }

    // -------------------------------------------------------------
    // 7. SETTINGS & TEST NOTIFICATION
    // -------------------------------------------------------------
    public function getSettings(): JsonResponse
    {
        $all = Setting::getAll();
        $marketingSettings = [
            'marketing_test_mode' => (bool)$all['marketing_test_mode'],
            'marketing_email_enabled' => (bool)$all['marketing_email_enabled'],
            'marketing_email_provider' => $all['marketing_email_provider'],
            'marketing_email_from_name' => $all['marketing_email_from_name'],
            'marketing_email_from_address' => $all['marketing_email_from_address'],
            'marketing_email_smtp_host' => $all['marketing_email_smtp_host'],
            'marketing_email_smtp_port' => $all['marketing_email_smtp_port'],
            'marketing_email_smtp_user' => $all['marketing_email_smtp_user'],
            'marketing_email_smtp_pass' => $all['marketing_email_smtp_pass'] ? '••••••••' : '',

            'marketing_sms_enabled' => (bool)$all['marketing_sms_enabled'],
            'marketing_sms_provider' => $all['marketing_sms_provider'],
            'marketing_sms_sender_id' => $all['marketing_sms_sender_id'],
            'marketing_sms_api_key' => $all['marketing_sms_api_key'] ? '••••••••' : '',

            'marketing_whatsapp_enabled' => (bool)$all['marketing_whatsapp_enabled'],
            'marketing_whatsapp_provider' => $all['marketing_whatsapp_provider'],
            'marketing_whatsapp_phone_number_id' => $all['marketing_whatsapp_phone_number_id'],
            'marketing_whatsapp_token' => $all['marketing_whatsapp_token'] ? '••••••••' : '',

            'marketing_push_enabled' => (bool)$all['marketing_push_enabled'],
            'marketing_push_provider' => $all['marketing_push_provider'],
            'marketing_push_public_key' => $all['marketing_push_public_key'],

            'marketing_in_app_enabled' => (bool)$all['marketing_in_app_enabled'],
        ];

        return response()->json($marketingSettings);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $data = $request->all();

        foreach ($data as $key => $val) {
            // Ignore masked passwords if not changed
            if (in_array($key, ['marketing_email_smtp_pass', 'marketing_sms_api_key', 'marketing_whatsapp_token']) && $val === '••••••••') {
                continue;
            }

            if (is_bool($val)) {
                $val = $val ? '1' : '0';
            }

            Setting::set($key, (string)$val, 'marketing');
        }

        return response()->json([
            'message' => 'Marketing & notification settings saved successfully.',
        ]);
    }

    public function sendTestMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel' => 'required|string|in:in_app,email,sms,whatsapp,push',
            'recipient' => 'required|string|max:150',
            'title' => 'required|string|max:200',
            'message' => 'required|string',
        ]);

        $res = $this->marketingService->sendTestMessage(
            $validated['channel'],
            $validated['recipient'],
            $validated['title'],
            $validated['message']
        );

        return response()->json($res);
    }

    // -------------------------------------------------------------
    // 8. STOREFRONT & CUSTOMER APIS
    // -------------------------------------------------------------
    public function activeStorefrontBanners(): JsonResponse
    {
        $campaigns = MarketingCampaign::active()
            ->whereIn('campaign_type', ['flash_sale', 'festival', 'seasonal', 'promotional'])
            ->latest('id')
            ->take(3)
            ->get();

        return response()->json($campaigns);
    }

    // Resolve authenticated customer from session, Sanctum, or Bearer token
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

    public function customerNotifications(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);
        if (!$customer) {
            return response()->json(['notifications' => ['data' => []], 'unread_count' => 0]);
        }

        $notifications = CustomerNotification::where('customer_id', $customer->id)
            ->latest('id')
            ->paginate($request->input('per_page', 10));

        $unreadCount = CustomerNotification::where('customer_id', $customer->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markNotificationRead(Request $request, CustomerNotification $notification): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);
        if (!$customer || $notification->customer_id !== $customer->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $notification->markAsRead();
        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);
        if (!$customer) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        CustomerNotification::where('customer_id', $customer->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function getCustomerPreferences(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);
        if (!$customer) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $pref = CustomerNotificationPreference::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'promotional_enabled' => true,
                'channel_in_app' => true,
                'channel_email' => true,
                'channel_sms' => true,
                'channel_whatsapp' => true,
                'channel_push' => true,
                'category_flash_sales' => true,
                'category_back_in_stock' => true,
                'category_rewards' => true,
                'category_new_arrivals' => true,
                'category_festivals' => true,
            ]
        );

        return response()->json($pref);
    }

    public function updateCustomerPreferences(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);
        if (!$customer) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'promotional_enabled' => 'boolean',
            'channel_in_app' => 'boolean',
            'channel_email' => 'boolean',
            'channel_sms' => 'boolean',
            'channel_whatsapp' => 'boolean',
            'channel_push' => 'boolean',
            'category_flash_sales' => 'boolean',
            'category_back_in_stock' => 'boolean',
            'category_rewards' => 'boolean',
            'category_new_arrivals' => 'boolean',
            'category_festivals' => 'boolean',
        ]);

        if (isset($validated['promotional_enabled']) && !$validated['promotional_enabled']) {
            $validated['unsubscribed_at'] = now();
        } else {
            $validated['unsubscribed_at'] = null;
        }

        $pref = CustomerNotificationPreference::updateOrCreate(
            ['customer_id' => $customer->id],
            $validated
        );

        return response()->json([
            'message' => 'Notification preferences updated successfully.',
            'preferences' => $pref,
        ]);
    }

    public function unsubscribeAll(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);
        if (!$customer) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $pref = CustomerNotificationPreference::updateOrCreate(
            ['customer_id' => $customer->id],
            [
                'promotional_enabled' => false,
                'unsubscribed_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'You have been unsubscribed from all marketing and promotional communications.',
            'preferences' => $pref,
        ]);
    }
}
