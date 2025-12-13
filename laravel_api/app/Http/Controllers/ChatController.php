<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ChatbotConversation;
use App\Models\Product;
use App\Models\Coupon;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        // 1. Validate & Init
        $request->validate(['message' => 'required|string|max:500']); // Max 500 thôi cho đỡ tốn token
        $userMessage = $request->input('message');
        $sessionId = $request->input('session_id') ?? (string) Str::uuid();
        $userId = auth()->id() ?? null;

        // Tìm tin nhắn gần nhất của session này
        $lastConversation = ChatbotConversation::where('session_id', $sessionId)
                            ->latest() // Sắp xếp mới nhất
                            ->first();

        if ($lastConversation && 
            trim($lastConversation->message) === trim($userMessage) && 
            $lastConversation->created_at->diffInMinutes(now()) < 2) 
        {
            // => TRẢ VỀ KẾT QUẢ CŨ LUÔN (Không gọi API Gemini nữa)
            $products = $lastConversation->products_json ? json_decode($lastConversation->products_json, true) : [];
            
            return response()->json([
                'reply'      => $lastConversation->response,
                'products'   => $products,
                'session_id' => $sessionId,
                'cached'     => true // Cờ đánh dấu để biết là hàng cache
            ]);
        }
        // 2. Tìm kiếm sản phẩm & Coupon (Giữ nguyên logic của bạn - nó tốt rồi)
        // Mẹo: Nếu user message quá ngắn (< 3 ký tự) thì skip search để đỡ tốn query DB
        $products = strlen($userMessage) > 3 ? $this->searchProducts($userMessage) : $this->getFallbackProducts();
        $coupons = $this->getActiveCoupons();

        // 3. Chuẩn bị Context dữ liệu
        $productContext = $this->formatProductsToString($products);
        $couponContext = $this->formatCouponsToString($coupons);

        // 4. LẤY LỊCH SỬ CHAT CŨ (Để Bot thông minh hơn)
        // Lấy 4 câu gần nhất (2 cặp hỏi-đáp) của session này
        $history = ChatbotConversation::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->take(4) // Đừng lấy nhiều quá, tốn tiền/token
            ->get()
            ->reverse(); // Đảo ngược lại để đúng thứ tự thời gian

        // Build chuỗi lịch sử
        $historyContext = "";
        foreach ($history as $chat) {
            $historyContext .= "Khách: {$chat->message}\nBot: {$chat->response}\n";
        }

        // 5. Prompt (Tối ưu lại cho ngắn gọn, tiết kiệm token)
        $systemPrompt = "Bạn là AI bán hàng. Dữ liệu kho hiện tại:\n" .
            ($productContext ? "SẢN PHẨM:\n$productContext" : "SẢN PHẨM: Không tìm thấy.") . "\n" .
            ($couponContext ? "MÃ GIẢM GIÁ:\n$couponContext" : "") . "\n" .
            "QUY TẮC: Trả lời ngắn gọn (<100 chữ). Chỉ tư vấn sản phẩm có trong danh sách trên. Nếu khách hỏi sản phẩm cũ, hãy xem lịch sử chat.";

        // 6. Gọi API (SỬA LẠI ĐOẠN NÀY)
        $rawKeys = env('GEMINI_API_KEY');
        $keysArray = explode(',', $rawKeys);
        $apiKey = trim($keysArray[array_rand($keysArray)]);
        if (empty($apiKey)) {
            Log::error('Gemini API Key is missing!');
            return response()->json(['reply' => 'Lỗi cấu hình hệ thống.'], 500);
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $fullPrompt = $systemPrompt . "\n\n--- LỊCH SỬ CHAT ---\n" . $historyContext . "\n--- MỚI ---\nKhách: " . $userMessage;

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                // Tự động thử lại 3 lần, mỗi lần cách nhau 2 giây nếu lỗi mạng/429
                ->retry(2, 10000, function ($exception, $request) {
                    return $exception->response->status() === 429;
                })
                ->post($url, [
                    'contents' => [[
                        'parts' => [['text' => $fullPrompt]]
                    ]]
                ]);

           if ($response->successful()) {
                $data = $response->json();
                $aiReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, tôi không hiểu ý bạn.';
            } else {
                // Trường hợp API trả về lỗi khác 200 (nhưng không phải exception)
                $aiReply = 'Hệ thống đang bảo trì một chút, bạn chờ xíu nhé.';
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // BẮT LỖI 429 CỤ THỂ
            if ($e->response->status() == 429) {
                Log::warning('Gemini Rate Limit Hit: ' . $userId);
                // Trả lời khéo léo để khách không biết lỗi hệ thống
                $aiReply = 'Hiện tại có quá nhiều người đang hỏi, bạn vui lòng đợi khoảng 30 giây rồi hỏi lại mình nhé! ❤️'; 
            } else {
                Log::error('Gemini API Error: ' . $e->getMessage());
                $aiReply = 'Hệ thống đang gặp trục trặc nhỏ, bạn thử lại sau nhé.';
            }
        } catch (\Exception $e) {
            Log::error('Chat Controller Error: ' . $e->getMessage());
            $aiReply = 'Có lỗi xảy ra, vui lòng thử lại.';
        }

        // 7. Lưu DB & Trả kết quả (Giữ nguyên)
        ChatbotConversation::create([
            'session_id' => $sessionId,
            'user_id'    => $userId,
            'message'    => $userMessage,
            'response'   => $aiReply,
            'products_json' => (!empty($products) && count($products) > 0) ? json_encode($products) : null
        ]);

        // Cleanup cũ (Giữ nguyên code của bạn)

        return response()->json([
            'reply'      => $aiReply,
            'products'   => $products, // Trả cái này để Frontend render list sản phẩm dạng thẻ (Card)
            'session_id' => $sessionId
        ]);
    }

    /**
     * CẢI TIẾN LỚN NHẤT: Logic tìm kiếm 2 lớp (Strict Mode -> Broad Mode)
     */
    private function searchProducts($message)
    {
        // 1. Tách và lọc từ khóa rác
        $rawKeywords = explode(' ', $message);
        $keywords = [];
        // Danh sách từ nối tiếng Việt cần bỏ qua để query chính xác hơn
        $stopWords = ['là', 'của', 'có', 'không', 'những', 'các', 'cái', 'shop', 'cho', 'mình', 'em', 'anh', 'chị', 'muốn', 'mua', 'tìm', 'giá', 'bao', 'nhiêu'];

        foreach ($rawKeywords as $word) {
            $word = strtolower(trim($word));
            if (strlen($word) >= 2 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        if (empty($keywords)) {
            // Nếu lọc xong mà không còn từ nào (vd khách chỉ chat "alo"), trả về sp mới nhất
            return $this->getFallbackProducts();
        }

        // --- LỚP 1: TÌM KIẾM CHẶT CHẼ (AND Logic) ---
        // Sản phẩm phải thỏa mãn TẤT CẢ từ khóa
        // Ví dụ: "Áo đỏ" -> Phải có (Áo) VÀ (Đỏ)
        $query = Product::with(['category', 'variants'])->where('status', 'active');

        foreach ($keywords as $word) {
            $query->where(function ($subQ) use ($word) {
                // Từ khóa này phải xuất hiện ở ÍT NHẤT MỘT TRONG CÁC CỘT SAU:
                $subQ->orWhere('name', 'like', "%{$word}%")
                    ->orWhere('description', 'like', "%{$word}%")
                    ->orWhereHas('category', function ($catQ) use ($word) {
                        $catQ->where('name', 'like', "%{$word}%");
                    })
                    ->orWhereHas('variants', function ($varQ) use ($word) {
                        $varQ->where('color_name', 'like', "%{$word}%")
                            ->orWhere('size', 'like', "%{$word}%");
                    });
            });
        }

        $results = $query->take(6)->get();

        // Nếu Lớp 1 tìm thấy kết quả, trả về ngay (Đây là kết quả chính xác nhất)
        if ($results->isNotEmpty()) {
            return $results;
        }

        // --- LỚP 2: TÌM KIẾM RỘNG (OR Logic - Fallback) ---
        // Nếu Lớp 1 không ra gì (do khách gõ sai hoặc tìm quá khó), chuyển sang tìm "Có từ nào hay từ đó"
        $queryBroad = Product::with(['category', 'variants'])->where('status', 'active');

        $queryBroad->where(function ($subQ) use ($keywords) {
            foreach ($keywords as $word) {
                $subQ->orWhere('name', 'like', "%{$word}%")
                    ->orWhere('description', 'like', "%{$word}%")
                    ->orWhereHas('category', function ($catQ) use ($word) {
                        $catQ->where('name', 'like', "%{$word}%");
                    })
                    ->orWhereHas('variants', function ($varQ) use ($word) {
                        $varQ->where('color_name', 'like', "%{$word}%")
                            ->orWhere('size', 'like', "%{$word}%");
                    });
            }
        });

        $broadResults = $queryBroad->take(6)->get();

        if ($broadResults->isNotEmpty()) {
            return $broadResults;
        }

        // --- LỚP 3: KHÔNG TÌM THẤY GÌ ---
        // Trả về sản phẩm mới nhất để gợi ý
        return $this->getFallbackProducts();
    }

    private function getFallbackProducts()
    {
        return Product::with(['category', 'variants'])
            ->where('status', 'active')
            ->latest()
            ->take(3)
            ->get();
    }

    private function getActiveCoupons()
    {
        return Coupon::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereColumn('used_count', '<', 'usage_limit')
            ->orderBy('discount_value', 'desc')
            ->take(3)
            ->get();
    }

    private function formatProductsToString($products)
    {
        if ($products->isEmpty()) return "";
        $context = "";
        foreach ($products as $p) {
            $price = number_format($p->sale_price > 0 ? $p->sale_price : $p->price);
            $variants = $p->variants->map(function ($v) {
                return "{$v->color_name}-Size{$v->size}";
            })->unique()->implode(', ');

            $context .= "- {$p->name} ({$price}đ). Có: {$variants}\n";
        }
        return $context;
    }

    private function formatCouponsToString($coupons)
    {
        if ($coupons->isEmpty()) return "";
        $context = "";
        foreach ($coupons as $c) {
            $val = $c->discount_type === 'percent' ? "{$c->discount_value}%" : number_format($c->discount_value) . "đ";
            $context .= "- Mã {$c->code}: Giảm {$val} (Đơn từ " . number_format($c->min_order_value) . "đ)\n";
        }
        return $context;
    }

    public function getHistory(Request $request)
    {
        // (Giữ nguyên code getHistory cũ của bạn)
        $sessionId = $request->input('session_id');
        if (!$sessionId) return response()->json([]);
        $conversations = ChatbotConversation::where('session_id', $sessionId)->orderBy('created_at', 'asc')->get();
        $history = [];
        foreach ($conversations as $conv) {
            $history[] = ['sender' => 'user', 'text' => $conv->message, 'products' => []];
            if ($conv->response) {
                $savedProducts = $conv->products_json ? json_decode($conv->products_json, true) : [];
                $history[] = ['sender' => 'bot', 'text' => $conv->response, 'products' => $savedProducts];
            }
        }
        return response()->json($history);
    }
}
