<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\GeminiAIService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class PcBuildController extends Controller
{
    /**
     * @return array<string, array{label: string, keywords: array<int, string>}>
     */
    private function slotDefinitions(): array
    {
        return [
            'cpu' => ['label' => 'CPU', 'keywords' => ['cpu', 'bộ xử lý', 'processor', 'ryzen', 'core i', 'intel', 'amd']],
            'motherboard' => ['label' => 'Mainboard', 'keywords' => ['main', 'board', 'motherboard', 'mainboard', 'b550', 'b650', 'x670', 'z790']],
            'ram' => ['label' => 'RAM', 'keywords' => ['ram', 'ddr4', 'ddr5', 'bộ nhớ']],
            'gpu' => ['label' => 'GPU / VGA', 'keywords' => ['gpu', 'vga', 'rtx', 'radeon', 'geforce', 'card màn hình', 'graphics']],
            'psu' => ['label' => 'Nguồn (PSU)', 'keywords' => ['psu', 'nguồn', 'power']],
            'storage' => ['label' => 'Ổ cứng / SSD', 'keywords' => ['ssd', 'hdd', 'nvme', 'ổ cứng', 'storage']],
            'cooling' => ['label' => 'Tản nhiệt', 'keywords' => ['tản', 'cooler', 'cooling', 'fan', 'aio', 'water']],
            'case' => ['label' => 'Case', 'keywords' => ['case', 'vỏ máy', 'tower']],
        ];
    }

    private function effectivePrice(Product $p): float
    {
        $sale = $p->sale_price;
        $regular = $p->regular_price;

        return (float) (($sale !== null && $sale !== '') ? $sale : $regular);
    }

    private function externalHintForSlot(string $slot, string $purpose): string
    {
        $p = $purpose;
        $map = [
            'cpu' => $p === 'gaming'
                ? 'Tham khảo AMD Ryzen 7 / Intel Core i7 (đời mới) nếu shop chưa có.'
                : 'Tham khảo AMD Ryzen 5 / Intel Core i5 cho công việc văn phòng & chỉnh sửa nhẹ.',
            'gpu' => $p === 'gaming'
                ? 'Tham khảo NVIDIA RTX 5070 / AMD RX 9070 series (thị trường ngoài).'
                : 'GPU tích hồ hoặc RTX 5060 class đủ cho work nhẹ.',
            'motherboard' => 'Chọn chipset/socket khớp CPU (ví dụ AM5 với Ryzen 7000+, LGA1700 với Intel 12–14th).',
            'ram' => 'Nên 32GB DDR5 cho gaming/work hiện đại; 16GB tối thiểu.',
            'psu' => '80 Plus Gold 650–850W cho build có GPU cao cấp.',
            'storage' => 'SSD NVMe Gen4 1TB là điểm cân bằng giá/hiệu năng.',
            'cooling' => 'Tower khí hoặc AIO 240mm cho CPU hiệu năng cao.',
            'case' => 'Case airflow mesh giúp GPU/CPU mát hơn.',
        ];

        return $map[$slot] ?? 'Tham khảo linh kiện cùng phân khúc trên thị trường.';
    }

    /**
     * @return array<string, \Illuminate\Database\Eloquent\Collection<int, Product>>
     */
    private function productsBySlots(): array
    {
        $slots = $this->slotDefinitions();
        $out = [];

        foreach ($slots as $key => $meta) {
            $catIds = Category::query()
                ->where(function ($q) use ($meta) {
                    foreach ($meta['keywords'] as $kw) {
                        $like = '%' . addcslashes($kw, '%_\\') . '%';
                        $q->orWhere('name', 'LIKE', $like)->orWhere('slug', 'LIKE', $like);
                    }
                })
                ->pluck('id');

            $out[$key] = Product::query()
                ->whereIn('category_id', $catIds)
                ->with('category')
                ->orderBy('name')
                ->get();
        }

        return $out;
    }

    public function index(Request $request): View
    {
        $slotsMeta = $this->slotDefinitions();
        $productsBySlot = $this->productsBySlots();
        $analysis = session('pc_build_analysis');

        return view('build-pc-ai', [
            'slotsMeta' => $slotsMeta,
            'productsBySlot' => $productsBySlot,
            'analysis' => $analysis,
        ]);
    }

    public function analyze(Request $request): RedirectResponse
    {
        $slotKeys = array_keys($this->slotDefinitions());
        $rules = [
            'budget' => ['nullable', 'numeric', 'min:0'],
            'purpose' => ['nullable', 'string', 'in:gaming,work,balance'],
        ];
        foreach ($slotKeys as $slot) {
            $rules[$slot] = ['nullable', 'integer', 'exists:products,id'];
        }
        $validated = $request->validate($rules);

        $budget = isset($validated['budget']) ? (float) $validated['budget'] : null;
        $purpose = $validated['purpose'] ?? 'balance';

        $picked = [];
        foreach ($slotKeys as $slot) {
            $id = $validated[$slot] ?? null;
            if ($id) {
                $picked[$slot] = Product::with('category')->find((int) $id);
            }
        }

        $analysis = $this->buildAnalysis($picked, $budget, (string) $purpose);
        $request->session()->put('pc_build_analysis', $analysis);

        return redirect()->route('build.pc.ai')->withInput($request->except('_token'));
    }

    /**
     * @param array<string, Product|null> $picked
     * @return array<string, mixed>
     */
    private function buildAnalysis(array $picked, ?float $budget, string $purpose): array
    {
        $slotsMeta = $this->slotDefinitions();

        $warnings = [];
        $compatIssues = [];
        $advantages = [];
        $drawbacks = [];
        $externalSuggestions = [];

        $total = 0.0;
        foreach ($picked as $product) {
            if ($product) {
                $total += $this->effectivePrice($product);
            }
        }

        $scores = [];
        foreach ($picked as $slot => $product) {
            if (! $product) {
                continue;
            }
            $catMax = (float) Product::query()
                ->where('category_id', $product->category_id)
                ->get()
                ->map(fn ($p) => $this->effectivePrice($p))
                ->max();

            $price = $this->effectivePrice($product);
            $scores[$slot] = $catMax > 0 ? (int) round(min(100, ($price / $catMax) * 100)) : 55;
        }

        $overallScore = count($scores) ? (int) round(array_sum($scores) / count($scores)) : 0;

        foreach ($slotsMeta as $key => $meta) {
            if (empty($picked[$key])) {
                $compatIssues[] = 'Chưa chọn ' . $meta['label'] . '.';
                $externalSuggestions[] = [
                    'slot' => $meta['label'],
                    'hint' => $this->externalHintForSlot($key, $purpose),
                ];
            }
        }

        $gpu = $picked['gpu'] ?? null;
        $psu = $picked['psu'] ?? null;
        $cpu = $picked['cpu'] ?? null;
        $mb = $picked['motherboard'] ?? null;
        $ram = $picked['ram'] ?? null;

        if ($gpu && $psu) {
            $g = $this->effectivePrice($gpu);
            $p = $this->effectivePrice($psu);
            if ($p < $g * 0.12 && $g > 5_000_000) {
                $warnings[] = 'Nguồn có thể chưa đủ dự phòng cho GPU — cân nhắc PSU công suất/grade cao hơn (gợi ý tham khảo: ~15–25% giá GPU).';
            }
        }

        if ($cpu && $gpu) {
            $cp = $this->effectivePrice($cpu);
            $gp = $this->effectivePrice($gpu);
            if ($gp > $cp * 4) {
                $warnings[] = 'GPU đắt hơn CPU rất nhiều — dễ nghẽn cổ chai CPU ở độ phân giải thấp / game CPU-bound.';
            }
            if ($cp > $gp * 3 && $purpose === 'gaming') {
                $warnings[] = 'CPU mạnh hơn GPU khá nhiều — có thể dư CPU cho gaming.';
            }
        }

        if ($cpu && $mb) {
            $nCpu = strtolower($cpu->name . ' ' . ($cpu->slug ?? ''));
            $nMb = strtolower($mb->name . ' ' . ($mb->slug ?? ''));
            $intelCpu = str_contains($nCpu, 'intel') || str_contains($nCpu, 'core');
            $amdCpu = str_contains($nCpu, 'amd') || str_contains($nCpu, 'ryzen');
            $intelMb = str_contains($nMb, 'z790') || str_contains($nMb, 'b760') || str_contains($nMb, 'intel') || str_contains($nMb, 'lga');
            $amdMb = str_contains($nMb, 'b650') || str_contains($nMb, 'x670') || str_contains($nMb, 'am5') || str_contains($nMb, 'amd');

            if (($intelCpu && $amdMb && ! str_contains($nMb, 'intel')) || ($amdCpu && $intelMb && ! str_contains($nMb, 'amd'))) {
                $compatIssues[] = 'CPU và mainboard có thể không cùng platform (Intel vs AMD) — kiểm tra lại socket/chipset.';
            }
        }

        if ($purpose === 'gaming' && $overallScore >= 70 && count($picked) >= 6) {
            $advantages[] = 'Cấu hình có điểm linh kiện khá cao trong từng danh mục → phù hợp game và đa nhiệm.';
        }

        if ($purpose === 'work' && $ram) {
            $advantages[] = 'Đã có RAM — phù hợp công việc đa tab / chỉnh ảnh nhẹ.';
        }

        if ($overallScore < 45) {
            $drawbacks[] = 'Một số linh kiện đang ở phân khúc thấp trong danh mục → hiệu năng tổng thể hạn chế.';
        }

        if ($budget !== null && $budget > 0 && $total > $budget) {
            $warnings[] = 'Tổng giá ước tính vượt ngân sách đã nhập.';
        }

        if ($total > 0 && ($budget === null || $budget <= 0)) {
            $warnings[] = 'Nên nhập ngân sách để hệ thống so sánh và gợi ý tối ưu hơn.';
        }

        return [
            'purpose' => $purpose,
            'budget' => $budget,
            'total' => $total,
            'overall_score' => $overallScore,
            'slot_scores' => $scores,
            'picked' => $picked,
            'warnings' => $warnings,
            'compat_issues' => $compatIssues,
            'advantages' => $advantages,
            'drawbacks' => $drawbacks,
            'external_suggestions' => $externalSuggestions,
        ];
    }

    /**
     * Trích xuất loại linh kiện từ message
     */
    private function extractComponentsFromMessage(string $message): array
    {
        $message = strtolower($message);
        $components = [];
        
        $patterns = [
            'cpu' => ['cpu', 'bộ xử lý', 'processor', 'ryzen', 'core i'],
            'motherboard' => ['mainboard', 'main board', 'motherboard', 'b550', 'b650', 'x670'],
            'ram' => ['ram', 'ddr4', 'ddr5', 'bộ nhớ', 'memory'],
            'gpu' => ['gpu', 'vga', 'rtx', 'radeon', 'geforce', 'graphics card'],
            'psu' => ['psu', 'nguồn', 'power supply'],
            'storage' => ['ssd', 'hdd', 'nvme', 'ổ cứng', 'storage'],
            'cooling' => ['tản nhiệt', 'cooler', 'cooling', 'aio'],
            'case' => ['case', 'vỏ máy', 'tower', 'chassis'],
        ];
        
        foreach ($patterns as $component => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    $components[] = $component;
                    break;
                }
            }
        }
        
        return array_unique($components);
    }

    /**
     * Handle AI chat for PC build recommendations
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'purpose' => ['nullable', 'string', 'in:gaming,work,balance'],
        ]);

        $gemini = new GeminiAIService();
        $budget = isset($validated['budget']) ? (float) $validated['budget'] : 10_000_000;
        $purpose = $validated['purpose'] ?? 'balance';

        // Xác định các loại linh kiện từ message
        $components = $this->extractComponentsFromMessage($validated['message']);
        if (empty($components)) {
            $components = array_keys($this->slotDefinitions());
        }

        // Gọi AI để lấy recommendation
        $aiResponse = $gemini->chat(
            $validated['message'],
            $budget,
            $purpose
        );

        if (!$aiResponse['success']) {
            return response()->json([
                'success' => false,
                'error' => $aiResponse['error'] ?? 'Không thể kết nối với Gemini API',
                'response' => 'Không thể lấy phản hồi AI. Dưới đây là các sản phẩm tham khảo trong kho:',
                'products' => $gemini->getProductsWithStockInfo($components),
            ]);
        }

        // Định dạng response với sản phẩm từ database
        $result = $gemini->formatRecommendationWithProducts(
            $aiResponse['response'],
            $components
        );

        return response()->json($result);
    }

    /**
     * Get AI recommendations for PC build based on budget
     */
    public function aiRecommend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'budget' => ['required', 'numeric', 'min:0'],
            'purpose' => ['nullable', 'string', 'in:gaming,work,balance'],
        ]);

        $gemini = new GeminiAIService();
        $budget = (float) $validated['budget'];
        $purpose = $validated['purpose'] ?? 'balance';

        $response = $gemini->recommendBuild($budget, $purpose);
        $response['products'] = $gemini->getProductsWithStockInfo(array_keys($this->slotDefinitions()));

        return response()->json($response);
    }

    /**
     * Add products from build config to cart
     */
    public function addFromBuild(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'products' => ['required', 'array'],
            'products.*' => ['integer', 'exists:products,id']
        ]);

        try {
            $products = Product::whereIn('id', $validated['products'])->get();

            foreach ($products as $product) {
                $price = ($product->sale_price !== null && $product->sale_price !== '') 
                    ? $product->sale_price 
                    : $product->regular_price;

                Cart::instance('cart')->add(
                    $product->id,
                    $product->name,
                    1,
                    $price,
                    ['product_slug' => $product->slug]
                );
            }

            if (Auth::check()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã thêm vào giỏ hàng. Chuyển đến thanh toán...',
                    'redirect' => route('cart.checkout')
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã thêm vào giỏ hàng. Vui lòng đăng nhập để thanh toán.',
                    'redirect' => route('login')
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Lỗi khi thêm vào giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }
}
        
