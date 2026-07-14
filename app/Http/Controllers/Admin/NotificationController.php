<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Store;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function index()
    {
        $stores = Store::whereNotNull('mobile_number')->where('mobile_number', '!=', '')->get();
        $notifications = Notification::with('recipients.store')->latest()->paginate(20);
        
        return view('admin.notifications.index', compact('stores', 'notifications'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'store_ids' => 'required|array',
            'store_ids.*' => 'exists:stores,id',
            'message' => 'required|string',
        ]);

        $stores = Store::whereIn('id', $request->store_ids)->get();
        
        $message = $request->message;
        
        // Use league/html-to-markdown for robust conversion
        $converter = new \League\HTMLToMarkdown\HtmlConverter([
            'bold_style' => '*',
            'italic_style' => '_',
            'strip_tags' => true,
            'hard_break' => true,
            'remove_nodes' => '',
        ]);
        
        // Add custom converter for strikethrough tags (WhatsApp uses ~)
        $converter->getEnvironment()->addConverter(new class implements \League\HTMLToMarkdown\Converter\ConverterInterface {
            public function getSupportedTags(): array {
                return ['s', 'strike', 'del'];
            }
            public function convert(\League\HTMLToMarkdown\ElementInterface $element): string {
                return '~' . trim($element->getValue()) . '~';
            }
        });
        
        // Convert the HTML to Markdown
        $message = $converter->convert($message);
        
        // Decode HTML entities (like &amp; or emojis encoded by the editor)
        $message = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Replace non-breaking spaces with normal spaces
        $message = str_replace(["\xc2\xa0", "&nbsp;"], ' ', $message);
        // Clean up excessive empty lines
        $message = trim(preg_replace("/\n{3,}/", "\n\n", $message));
        
        $notification = Notification::create([
            'message' => $message
        ]);
        
        $sentCount = 0;
        foreach ($stores as $store) {
            if (!$store->country_code || !$store->mobile_number) {
                continue;
            }
            
            $number = $store->country_code . $store->mobile_number;
            $number = preg_replace('/[^0-9]/', '', $number);
            
            try {
                $response = $this->whatsappService->sendTextMessage($number, $message);
                
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'store_id' => $store->id,
                    'status' => 'sent'
                ]);
                
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Failed to send notification to store ' . $store->id . ': ' . $e->getMessage());
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'store_id' => $store->id,
                    'status' => 'failed'
                ]);
            }
        }

        return redirect()->back()->with('success', "Message sent to {$sentCount} stores successfully.");
    }
}
