<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Http;

class GeminiAIService
{
    private string $apiKey;
    private string $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    private string $model = 'mistralai/mistral-large';

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
        $this->model = config('services.openrouter.model', 'meta-llama/llama-2-70b-chat');
    }

    // ── helper gọi API ──────────────────────────────────────────────
    private function callApi(string $prompt, int $maxTokens = 1024): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($this->apiUrl, [
                'model'      => $this->model,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $maxTokens,
            ]);

            if ($response->failed()) {
                $err = $response->json();
                return [
                    'success' => false,
                    'error'   => data_get($err, 'error.message') ?? 'Lỗi kết nối API',
                ];
            }

            $data = $response->json();
            $text = data_get($data, 'choices.0.message.content');

            if ($text) {
                return ['success' => true, 'text' => $text];
            }

            return ['success' => false, 'error' => 'Phản hồi không hợp lệ từ API'];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── recommendBuild ──────────────────────────────────────────────
    public function recommendBuild(float $budget, string $purpose = 'balance', array $existingProducts = []): array
    {
        $prompt = $this->buildSystemPrompt($budget, $purpose, $existingProducts);
        $result = $this->callApi($prompt, 1024);

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error'], 'message' => 'Vui lòng thử lại sau.'];
        }

        return [
            'success'         => true,
            'recommendations' => $result['text'],
            'budget'          => $budget,
            'purpose'         => $purpose,
        ];
    }

    // ── chat ────────────────────────────────────────────────────────
    public function chat(string $message, float $budget, string $purpose = 'balance'): array
    {
        $budgetFmt = number_format($budget, 0, ',', '.');
        $purposeLabel = match($purpose) {
            'gaming' => 'gaming',
            'work'   => 'công việc',
            default  => 'cân bằng',
        };

        $prompt = "Bạn là chuyên gia tư vấn cấu hình PC tại Việt Nam. "
                . "Khách có ngân sách {$budgetFmt}đ, mục đích: {$purposeLabel}. "
                . "Trả lời bằng tiếng Việt.\n\nCâu hỏi: {$message}";

        $result = $this->callApi($prompt, 512);

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error']];
        }

        return ['success' => true, 'response' => $result['text']];
    }

    // ── buildSystemPrompt (giữ nguyên logic cũ) ─────────────────────
    private function buildSystemPrompt(float $budget, string $purpose, array $existingProducts): string
    {
        $budgetVnd = number_format($budget, 0, ',', '.');

        $existingList = '';
        if (!empty($existingProducts)) {
            $existingList = "\n\nLinh kiện đã chọn:\n";
            foreach ($existingProducts as $slot => $product) {
                if ($product) {
                    $price = number_format($product['price'], 0, ',', '.');
                    $existingList .= "- {$product['name']}: {$price}đ\n";
                }
            }
        }

        $purposeDescription = match($purpose) {
            'gaming' => 'chơi game cao cấp (mục tiêu 1440p 144fps hoặc 4K 60fps)',
            'work'   => 'công việc văn phòng, chỉnh sửa ảnh/video nhẹ, đa tab',
            default  => 'cân bằng giữa gaming và công việc',
        };

        return <<<PROMPT
Bạn là chuyên gia cấu hình PC gaming/work tại Việt Nam. Ngân sách: {$budgetVnd}đ. Mục đích: {$purposeDescription}.
{$existingList}

Gợi ý:
1. Danh sách linh kiện (CPU, Mainboard, RAM, GPU, PSU, Storage, Cooling, Case)
2. Khoảng giá từng linh kiện
3. Tổng giá dự kiến
4. Lý do chọn
5. Lưu ý tương thích

Trả lời tiếng Việt, rõ ràng, tập trung giá/hiệu năng.
PROMPT;
    }

    // ── các method còn lại GIỮ NGUYÊN ───────────────────────────────
    public function findProductsByComponent(string $componentType): array
    {
        $keywords = [
            'cpu'         => ['cpu', 'bộ xử lý', 'processor', 'ryzen', 'core i', 'intel', 'amd'],
            'motherboard' => ['main', 'board', 'motherboard', 'mainboard', 'b550', 'b650', 'x670', 'z790'],
            'ram'         => ['ram', 'ddr4', 'ddr5', 'bộ nhớ'],
            'gpu'         => ['gpu', 'vga', 'rtx', 'radeon', 'geforce', 'card màn hình', 'graphics'],
            'psu'         => ['psu', 'nguồn', 'power'],
            'storage'     => ['ssd', 'hdd', 'nvme', 'ổ cứng', 'storage'],
            'cooling'     => ['tản', 'cooler', 'cooling', 'fan', 'aio', 'water'],
            'case'        => ['case', 'vỏ máy', 'tower'],
        ];

        $searchKeywords = $keywords[$componentType] ?? [];
        if (empty($searchKeywords)) return [];

        $catIds = Category::query()
            ->where(function ($q) use ($searchKeywords) {
                foreach ($searchKeywords as $kw) {
                    $like = '%' . addcslashes($kw, '%_\\') . '%';
                    $q->orWhere('name', 'LIKE', $like)->orWhere('slug', 'LIKE', $like);
                }
            })->pluck('id');

        return Product::query()
            ->where(function ($query) use ($catIds, $searchKeywords) {
                if ($catIds->isNotEmpty()) {
                    $query->whereIn('category_id', $catIds);
                }
                $query->orWhere(function ($q) use ($searchKeywords) {
                    foreach ($searchKeywords as $kw) {
                        $like = '%' . addcslashes($kw, '%_\\') . '%';
                        $q->orWhere('name', 'LIKE', $like)->orWhere('slug', 'LIKE', $like);
                    }
                });
            })
            ->orderBy('quantity', 'desc')
            ->orderBy('regular_price', 'asc')
            ->take(5)->get()->toArray();
    }

    public function getProductsWithStockInfo(array $components): array
    {
        $result = [];
        foreach ($components as $componentType) {
            $products = $this->findProductsByComponent($componentType);
            $inStock = []; $outOfStock = [];
            foreach ($products as $product) {
                $product['price'] = ($product['sale_price'] && $product['sale_price'] !== '')
                    ? $product['sale_price'] : $product['regular_price'];
                $product['is_available'] = $product['stock_status'] === 'in_stock' && $product['quantity'] > 0;
                $product['is_available'] ? ($inStock[] = $product) : ($outOfStock[] = $product);
            }
            $result[$componentType] = [
                'in_stock'      => $inStock,
                'out_of_stock'  => $outOfStock,
                'has_available' => !empty($inStock),
            ];
        }
        return $result;
    }

    public function formatRecommendationWithProducts(string $aiResponse, array $components): array
    {
        $productInfo      = $this->getProductsWithStockInfo($components);
        $formattedResponse = $aiResponse . "\n\n=== 💾 SẢN PHẨM TỪ DATABASE ===\n\n";

        foreach ($productInfo as $componentType => $items) {
            $formattedResponse .= "**" . ucfirst($componentType) . ":**\n";
            if ($items['has_available']) {
                foreach ($items['in_stock'] as $product) {
                    $price = number_format($product['price'], 0, ',', '.');
                    $formattedResponse .= "✅ {$product['name']} - {$price}₫ (Còn: {$product['quantity']})\n";
                    $formattedResponse .= "   ID: {$product['id']}\n";
                }
            } else {
                $formattedResponse .= "❌ Hết hàng trong kho\n";
                foreach (($items['out_of_stock'] ?? []) as $product) {
                    $price = number_format($product['price'], 0, ',', '.');
                    $formattedResponse .= "   • {$product['name']} - {$price}₫\n";
                }
            }
            $formattedResponse .= "\n";
        }

        return ['success' => true, 'response' => $aiResponse, 'products' => $productInfo];
    }
}
