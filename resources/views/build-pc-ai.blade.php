@extends('layouts.app')
@section('content')
@php
  $vnd = fn ($n) => number_format((float) $n, 0, ',', '.') . ' ₫';
@endphp
<main class="pt-90">
  <div class="mb-4 pb-4"></div>
  <section class="container" style="max-width: 1200px;">
    <h1 class="page-title h3 mb-2">Build PC</h1>
    <p class="text-secondary mb-4">Sử dụng trợ lý AI để tìm cấu hình PC phù hợp với yêu cầu của bạn.</p>

    <div class="row g-4">
      <!-- AI Chat Section -->
      <div class="col-lg-6">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="height: 600px; display: flex; flex-direction: column;">
          <h5 class="mb-3">💬 Tư vấn AI</h5>
          
          <!-- Chat Messages Container -->
          <div id="chatMessages" class="flex-grow-1 overflow-y-auto mb-3 border rounded-2 p-3 bg-light" style="min-height: 300px;">
            <div class="text-center text-secondary mb-3">
              <p class="small mb-0">Nhập ngân sách của bạn ở dưới để bắt đầu...</p>
            </div>
          </div>

          <!-- Input Section -->
          <div class="border-top pt-3" style="flex-shrink: 0;">
            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label small">Ngân sách (₫)</label>
                <input type="number" class="form-control" id="budgetInput" min="0" step="1000" placeholder="Ví dụ: 20000000" value="20000000">
              </div>
              <div class="col-md-6">
                <label class="form-label small">Mục đích</label>
                <select class="form-select" id="purposeInput">
                  <option value="balance" selected>Cân bằng</option>
                  <option value="gaming">Gaming</option>
                  <option value="work">Công việc</option>
                </select>
              </div>
            </div>

            <!-- Chat Input -->
            <div class="input-group">
              <input type="text" class="form-control" id="chatInput" placeholder="Nhập câu hỏi về PC..." autocomplete="off">
              <button class="btn btn-primary" type="button" id="sendBtn">
                <i class="fa fa-paper-plane"></i>
              </button>
            </div>
            <button class="btn btn-outline-primary btn-sm w-100 mt-2" id="quickRecommendBtn">
              🚀 Gợi ý nhanh dựa trên ngân sách
            </button>
          </div>
        </div>
      </div>

      <!-- Build Configuration Display -->
      <div class="col-lg-6">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="max-height: 600px; overflow-y: auto;">
          <h5 class="mb-3">🖥️ Cấu hình được chọn</h5>
          
          <div id="buildConfig">
            <div class="text-center text-secondary py-5">
              <p>Chọn các sản phẩm để xây dựng cấu hình...</p>
            </div>
          </div>

          <!-- Total and Actions -->
          <div id="configSummary" class="border-top pt-3 mt-3" style="display: none;">
            <div class="row g-2 mb-3">
              <div class="col-6">
                <div class="p-2 border rounded-2 bg-light">
                  <div class="small text-secondary">Tổng giá</div>
                  <div class="fw-semibold" id="totalPrice">0 ₫</div>
                </div>
              </div>
              <div class="col-6">
                <div class="p-2 border rounded-2 bg-light">
                  <div class="small text-secondary">Linh kiện</div>
                  <div class="fw-semibold" id="productCount">0</div>
                </div>
              </div>
            </div>

            <button class="btn btn-primary w-100" id="checkoutBtn" style="display: none;">
              🛒 Thêm vào giỏ & Thanh toán
            </button>
            <p class="small text-secondary mt-2 mb-0">
              @if (!auth()->check())
                <i class="fa fa-info-circle"></i> Bạn sẽ được yêu cầu đăng nhập để thanh toán.
              @endif
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Manual Build Section (Old Interface) -->
    <div class="mt-5">
      <h3 class="h5 mb-4">📋 Hoặc xây dựng thủ công</h3>
      <form method="POST" action="{{ route('build.pc.ai.analyze') }}" class="border rounded-3 p-4 bg-white shadow-sm">
        @csrf
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label">Giá dự kiến (₫)</label>
            <input type="number" class="form-control" name="budget" min="0" step="1000" value="{{ old('budget') }}" placeholder="...">
          </div>
          <div class="col-md-4">
            <label class="form-label">Mục đích</label>
            <select class="form-select" name="purpose">
              <option value="balance" @selected(old('purpose', 'balance') === 'balance')>Cân bằng</option>
              <option value="gaming" @selected(old('purpose') === 'gaming')>Gaming</option>
              <option value="work" @selected(old('purpose') === 'work')>Công việc / học tập</option>
            </select>
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Phân tích cấu hình</button>
          </div>
        </div>

        <div class="row g-3">
          @foreach ($slotsMeta as $key => $meta)
            <div class="col-md-6">
              <label class="form-label fw-medium">{{ $meta['label'] }}</label>
              <select class="form-select" name="{{ $key }}">
                <option value="">— Chưa chọn —</option>
                @foreach (($productsBySlot[$key] ?? collect()) as $p)
                  @php $price = ($p->sale_price !== null && $p->sale_price !== '') ? $p->sale_price : $p->regular_price; @endphp
                  <option value="{{ $p->id }}" @selected((string) old($key) === (string) $p->id)>
                    {{ $p->name }} — {{ $vnd($price) }}
                  </option>
                @endforeach
              </select>
              @if (($productsBySlot[$key] ?? collect())->isEmpty())
                <div class="form-text text-warning">Không tìm thấy danh mục khớp trong DB — thử đặt tên danh mục chứa từ khóa (RAM, GPU, …).</div>
              @endif
            </div>
          @endforeach
        </div>
      </form>

      @if (!empty($analysis))
        <div class="mt-5 border rounded-3 p-4 bg-white shadow-sm">
          <h2 class="h4 mb-3">Kết quả phân tích</h2>

          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="fw-medium">Hiệu năng tổng thể (ước lượng)</span>
              <span class="badge bg-primary">{{ (int) ($analysis['overall_score'] ?? 0) }}/100</span>
            </div>
            <div class="progress" style="height: 28px;">
              <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: {{ min(100, max(0, (int) ($analysis['overall_score'] ?? 0))) }}%;">
                {{ (int) ($analysis['overall_score'] ?? 0) }}%
              </div>
            </div>
            <p class="text-secondary small mt-2 mb-0">Điểm dựa trên mức giá của linh kiện so với mặt hàng đắt nhất trong cùng danh mục (proxy hiệu năng khi không có spec chi tiết).</p>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="p-3 border rounded-2 h-100">
                <div class="text-secondary small">Tổng giá linh kiện đã chọn</div>
                <div class="fs-4 fw-semibold">{{ $vnd($analysis['total'] ?? 0) }}</div>
                @if (!empty($analysis['budget']))
                  <div class="small mt-2">Ngân sách nhập: {{ $vnd($analysis['budget']) }}</div>
                @endif
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 border rounded-2 h-100">
                <div class="text-secondary small">Mục đích</div>
                <div class="fw-medium">{{ $analysis['purpose'] ?? '' }}</div>
              </div>
            </div>
          </div>

          @if (!empty($analysis['picked']))
            <h3 class="h6">Linh kiện đã chọn</h3>
            <ul class="list-group list-group-flush mb-4">
              @foreach ($analysis['picked'] as $slot => $product)
                @if ($product)
                  @php $pr = ($product->sale_price !== null && $product->sale_price !== '') ? $product->sale_price : $product->regular_price; @endphp
                  <li class="list-group-item d-flex justify-content-between align-items-start">
                    <span>{{ $slotsMeta[$slot]['label'] ?? $slot }}: {{ $product->name }}</span>
                    <span class="text-nowrap ms-2">{{ $vnd($pr) }}</span>
                  </li>
                @endif
              @endforeach
            </ul>
          @endif

          @if (!empty($analysis['compat_issues']))
            <div class="alert alert-danger">
              <strong>Cảnh báo / thiếu linh kiện</strong>
              <ul class="mb-0 mt-2">
                @foreach ($analysis['compat_issues'] as $line)
                  <li>{{ $line }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if (!empty($analysis['warnings']))
            <div class="alert alert-warning">
              <strong>Lưu ý</strong>
              <ul class="mb-0 mt-2">
                @foreach ($analysis['warnings'] as $line)
                  <li>{{ $line }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if (!empty($analysis['advantages']))
            <div class="alert alert-success">
              <strong>Ưu điểm</strong>
              <ul class="mb-0 mt-2">
                @foreach ($analysis['advantages'] as $line)
                  <li>{{ $line }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if (!empty($analysis['drawbacks']))
            <div class="alert alert-secondary">
              <strong>Nhược điểm / hạn chế</strong>
              <ul class="mb-0 mt-2">
                @foreach ($analysis['drawbacks'] as $line)
                  <li>{{ $line }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if (!empty($analysis['external_suggestions']))
            <h3 class="h6 mt-4">Gợi ý ngoài database (tham khảo thị trường)</h3>
            <ul class="mb-0">
              @foreach ($analysis['external_suggestions'] as $row)
                <li><strong>{{ $row['slot'] ?? '' }}:</strong> {{ $row['hint'] ?? '' }}</li>
              @endforeach
            </ul>
          @endif
        </div>
      @endif
    </div>

    <!-- Selected Products List (for checkout) -->
    <div id="selectedProducts" class="d-none"></div>
  </section>
</main>

<style>
  #chatMessages {
    scrollbar-width: thin;
    overflow: hidden auto;
    word-break: break-word;
  }

  .chat-message {
    margin-bottom: 1rem;
    animation: slideIn 0.3s ease-in-out;
    word-wrap: break-word;
    overflow-wrap: break-word;
    min-width: 0;
  }

  .chat-message.user {
    text-align: right;
  }

  .chat-message.user .message-bubble {
    background-color: #0d6efd;
    color: white;
    border-radius: 18px 18px 4px 18px;
  }

  .chat-message.ai .message-bubble {
    background-color: #e9ecef;
    color: #212529;
    border-radius: 18px 18px 18px 4px;
  }

  .message-bubble {
    display: inline-block;
    padding: 10px 14px;
    max-width: 85%;
    word-break: break-word;
    overflow-wrap: break-word;
    white-space: pre-wrap;
    font-size: 0.95rem;
    line-height: 1.4;
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .product-item {
    padding: 12px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 0;
    overflow: hidden;
  }

  .product-item:hover {
    background-color: #e7f1ff;
    border-color: #0d6efd;
  }

  .product-item.selected {
    background-color: #cfe2ff;
    border-color: #0d6efd;
    border-width: 2px;
  }

  .product-item-name {
    font-weight: 600;
    font-size: 0.95rem;
    word-break: break-word;
    overflow-wrap: break-word;
  }

  .product-item-price {
    color: #0d6efd;
    font-weight: bold;
    word-break: break-word;
  }

  .product-item-category {
    color: #6c757d;
    font-size: 0.85rem;
    word-break: break-word;
  }

  .product-item.out-of-stock {
    opacity: 0.6;
    background-color: #f5f5f5;
    cursor: not-allowed;
  }

  .product-item.out-of-stock:hover {
    background-color: #f5f5f5;
    border-color: #dee2e6;
  }

  .ai-product-section {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-top: 10px;
    overflow: hidden;
    min-width: 0;
  }

  /* Đổi màu chữ warning sang xanh dương */
  .alert-warning {
      color: #0d6efd !important;
  }
  .text-warning {
      color: #0d6efd !important;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let selectedProducts = {};
  const chatMessagesDiv = document.getElementById('chatMessages');
  const chatInput = document.getElementById('chatInput');
  const budgetInput = document.getElementById('budgetInput');
  const purposeInput = document.getElementById('purposeInput');
  const sendBtn = document.getElementById('sendBtn');
  const quickRecommendBtn = document.getElementById('quickRecommendBtn');
  const buildConfigDiv = document.getElementById('buildConfig');
  const configSummaryDiv = document.getElementById('configSummary');
  const checkoutBtn = document.getElementById('checkoutBtn');
  const selectedProductsDiv = document.getElementById('selectedProducts');
  let isLoading = false;

  // Add message to chat
  function addChatMessage(text, isUser = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${isUser ? 'user' : 'ai'}`;
    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    bubble.innerHTML = text.replace(/\n/g, '<br>');
    messageDiv.appendChild(bubble);
    chatMessagesDiv.appendChild(messageDiv);
    chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
  }

  // Send chat message
  async function sendMessage() {
    const message = chatInput.value.trim();
    if (!message || isLoading) return;

    const budget = parseFloat(budgetInput.value) || 10000000;
    const purpose = purposeInput.value;

    addChatMessage(message, true);
    chatInput.value = '';
    isLoading = true;

    try {
      const response = await fetch('{{ route("build.pc.ai.chat") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          message: message,
          budget: budget,
          purpose: purpose
        })
      });

      const data = await response.json();
      const chatText = data.response || (data.error ? '❌ Lỗi: ' + data.error : 'Không thể nhận phản hồi');
      addChatMessage(chatText);
      if (data.products) {
        displayProductsFromResponse(data.products);
      }
    } catch (error) {
      addChatMessage('❌ Lỗi: ' + error.message);
    } finally {
      isLoading = false;
    }
  }

  // Get quick recommendation
  async function getQuickRecommend() {
    const budget = parseFloat(budgetInput.value) || 10000000;
    const purpose = purposeInput.value;

    if (isLoading) return;
    isLoading = true;

    addChatMessage(`Bạn muốn cấu hình PC với ngân sách ${new Intl.NumberFormat('vi-VN').format(budget)}₫ cho mục đích ${purpose}. Đang tìm gợi ý...`, true);

    try {
      const response = await fetch('{{ route("build.pc.ai.recommend") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          budget: budget,
          purpose: purpose
        })
      });

      const data = await response.json();
      if (data.success) {
        addChatMessage(data.recommendations);
        // Try to extract and display products
        displayRecommendedProducts();
      } else {
        addChatMessage('❌ ' + (data.error || 'Không thể lấy gợi ý'));
      }
    } catch (error) {
      addChatMessage('❌ Lỗi: ' + error.message);
    } finally {
      isLoading = false;
    }
  }

  // Display recommended products (you can enhance this based on AI response)
  function displayRecommendedProducts() {
    // This would ideally parse the AI response and display actual products from DB
    // For now, showing a placeholder
    buildConfigDiv.innerHTML = '<div class="alert alert-info">💡 Nhấp vào các sản phẩm trong tìm kiếm dưới để thêm vào cấu hình.</div>';
  }

  // Hiển thị sản phẩm từ phản hồi của AI
  function displayProductsFromResponse(productsData) {
    let html = '<div class="ai-product-section"><strong>💾 Sản phẩm gợi ý từ Kho hàng:</strong></div>';
    
    for (const [componentType, items] of Object.entries(productsData)) {
      html += `<div class="ai-product-section">
        <div class="text-uppercase small fw-bold text-muted mb-2">${componentType}</div>`;
      
      if (items.has_available && items.in_stock.length > 0) {
        items.in_stock.forEach(product => {
          const price = new Intl.NumberFormat('vi-VN').format(product.price);
          const selectedClass = selectedProducts[product.id] ? 'selected' : '';
          html += `
            <div class="product-item ${selectedClass} d-flex justify-content-between align-items-center" 
                 data-product-id="${product.id}" 
                 data-product-name="${product.name}" 
                 data-product-price="${product.price}"
                 onclick="toggleProductFromData(this)"
                 style="cursor: pointer;">
              <div class="flex-grow-1">
                <div class="product-item-name">✅ ${product.name}</div>
                <div class="small text-muted">Còn: ${product.quantity} chiếc</div>
                <div class="product-item-price">${price}₫</div>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); toggleProductFromData(this.closest('.product-item'))">Thêm</button>
            </div>
          `;
        });
      } else if (items.out_of_stock && items.out_of_stock.length > 0) {
        html += '<div class="alert alert-warning py-2 mb-0">❌ Hết hàng</div>';
        items.out_of_stock.slice(0, 3).forEach(product => {
          const price = new Intl.NumberFormat('vi-VN').format(product.price);
          html += `<div class="small text-muted ms-2">• ${product.name} - ${price}₫</div>`;
        });
      } else {
        html += '<div class="alert alert-info py-2 mb-0">ℹ️ Không có sản phẩm</div>';
      }
      
      html += '</div>';
    }
    
    // Thêm HTML vào phần chat message
    const productDiv = document.createElement('div');
    productDiv.className = 'mt-3 mb-3';
    productDiv.innerHTML = html;
    chatMessagesDiv.appendChild(productDiv);
    chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
  }

  // Xử lý toggle product từ data attribute
  window.toggleProductFromData = function(element) {
    const productId = parseInt(element.dataset.productId);
    const productName = element.dataset.productName;
    const productPrice = parseFloat(element.dataset.productPrice);
    
    toggleProduct(productId, productName, productPrice);
  }

  // Toggle product selection
  window.toggleProduct = function(productId, productName, price) {
    if (selectedProducts[productId]) {
      delete selectedProducts[productId];
    } else {
      selectedProducts[productId] = {
        id: productId,
        name: productName,
        price: price
      };
    }
    updateConfigDisplay();
  }

  // Update config display
  function updateConfigDisplay() {
    const products = Object.values(selectedProducts);
    
    if (products.length === 0) {
      buildConfigDiv.innerHTML = '<div class="text-center text-secondary py-5"><p>Chọn các sản phẩm để xây dựng cấu hình...</p></div>';
      configSummaryDiv.style.display = 'none';
      checkoutBtn.style.display = 'none';
      return;
    }

    let html = '';
    let total = 0;

    products.forEach(product => {
      const formattedPrice = new Intl.NumberFormat('vi-VN').format(product.price);
      html += `
        <div class="product-item selected d-flex justify-content-between align-items-center" onclick="toggleProduct(${product.id}, '${product.name}', ${product.price})">
          <div class="flex-grow-1">
            <div class="product-item-name">${product.name}</div>
            <div class="product-item-price">${formattedPrice}₫</div>
          </div>
          <button class="btn btn-sm btn-outline-danger">✕</button>
        </div>
      `;
      total += parseFloat(product.price);
    });

    buildConfigDiv.innerHTML = html;
    
    // Update summary
    document.getElementById('totalPrice').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' ₫';
    document.getElementById('productCount').textContent = products.length + ' linh kiện';
    configSummaryDiv.style.display = 'block';
    checkoutBtn.style.display = 'block';

    // Update hidden form
    selectedProductsDiv.innerHTML = products.map(p => `<input type="hidden" name="products[]" value="${p.id}">`).join('');
  }

  // Checkout
  checkoutBtn.addEventListener('click', async function() {
    const productIds = Object.keys(selectedProducts).map(id => parseInt(id));
    
    if (productIds.length === 0) {
      alert('Vui lòng chọn ít nhất một sản phẩm');
      return;
    }

    try {
      const response = await fetch('{{ route("cart.add.from.build") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          products: productIds
        })
      });

      const data = await response.json();
      if (data.success) {
        alert(data.message);
        window.location.href = data.redirect;
      } else {
        alert('❌ ' + (data.error || 'Có lỗi xảy ra'));
      }
    } catch (error) {
      alert('❌ Lỗi: ' + error.message);
    }
  });

  // Event listeners
  sendBtn.addEventListener('click', sendMessage);
  chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !isLoading) {
      sendMessage();
    }
  });
  quickRecommendBtn.addEventListener('click', getQuickRecommend);

  // Initial greeting
  setTimeout(() => {
    addChatMessage('👋 Xin chào! Tôi là trợ lý AI của bạn. Hãy nhập ngân sách và mục đích sử dụng, sau đó tôi sẽ giúp bạn tìm cấu hình PC tốt nhất!');
  }, 500);
});
</script>

@endsection