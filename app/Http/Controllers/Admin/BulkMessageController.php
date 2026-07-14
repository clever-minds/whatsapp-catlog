<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreGroup;
use App\Models\Store;
use App\Services\WhatsAppService;

class BulkMessageController extends Controller
{
    public function index()
    {
        $groups = StoreGroup::with('stores')->get();
        $stores = Store::all();
        return view('admin.bulk_messages.index', compact('groups', 'stores'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:group,single',
            'store_group_id' => 'required_if:target_type,group',
            'store_id' => 'required_if:target_type,single',
            'message' => 'required|string',
        ]);

        $message = $request->message;
        $whatsAppService = new WhatsAppService();
        $successCount = 0;
        $failCount = 0;

        if ($request->target_type === 'group') {
            $group = StoreGroup::with('stores')->findOrFail($request->store_group_id);
            $stores = $group->stores;
            
            if ($stores->isEmpty()) {
                return redirect()->back()->with('error', 'The selected group has no stores.');
            }

            foreach ($stores as $store) {
                $phone = $store->mobile_number;
                if (empty($phone)) {
                    $failCount++;
                    continue;
                }
                $fullPhone = $store->country_code ? $store->country_code . $store->mobile_number : $store->mobile_number;
                if ($whatsAppService->sendTextMessage($fullPhone, $message)) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
            return redirect()->back()->with('success', "Bulk message sent! Success: {$successCount}, Failed: {$failCount}.");
        } else {
            $store = Store::findOrFail($request->store_id);
            if (empty($store->mobile_number)) {
                return redirect()->back()->with('error', 'The selected store has no mobile number.');
            }
            $fullPhone = $store->country_code ? $store->country_code . $store->mobile_number : $store->mobile_number;
            if ($whatsAppService->sendTextMessage($fullPhone, $message)) {
                return redirect()->back()->with('success', 'Message sent successfully to the store.');
            } else {
                return redirect()->back()->with('error', 'Failed to send the message.');
            }
        }
    }
}
