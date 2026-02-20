<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بيت الديكور - الستائر والأقمشة الفاخرة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }
        body {
            background-color: #0f172a;
            color: #e2e8f0;
        }
        .hero-gradient {
            background: linear-gradient(135deg, #B8860B 0%, #D4AF37 100%);
        }
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: #1e293b;
            border-color: #334155;
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(184, 134, 11, 0.3);
            background-color: #334155;
        }
        .fabric-card {
            background: linear-gradient(135deg, #334155 0%, #475569 100%);
            transition: all 0.3s ease;
        }
        .fabric-card:hover {
            transform: scale(1.05);
            background: linear-gradient(135deg, #B8860B 0%, #D4AF37 100%);
            color: white;
        }
        .section-title {
            color: #f1f5f9;
        }
        .section-subtitle {
            color: #cbd5e1;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="bg-slate-900 shadow-lg sticky top-0 z-50 border-b border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <img src="/images/logo.jpeg" alt="بيت الديكور" class="h-12 w-12 rounded-lg">
                    <h1 class="text-2xl font-bold" style="color: #D4AF37;">بيت الديكور</h1>
                </div>
                <div class="hidden md:flex space-x-8 space-x-reverse">
                    <a href="#products" class="text-slate-300 hover:text-yellow-500 transition">المنتجات</a>
                    <a href="#fabrics" class="text-slate-300 hover:text-yellow-500 transition">الأقمشة والمفروشات</a>
                    <a href="#contact" class="text-slate-300 hover:text-yellow-500 transition">تواصل معنا</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-5xl md:text-6xl font-bold mb-6">ستائر وأقمشة ومفروشات فاخرة</h2>
            <p class="text-xl md:text-2xl mb-8 text-yellow-100">اختر من مجموعة واسعة من الستائر والمفروشات الحريرية والطبيعية عالية الجودة</p>
            <button class="bg-white px-8 py-3 rounded-full font-bold text-lg hover:bg-yellow-100 transition transform hover:scale-105" style="color: #B8860B;">
                اكتشف مجموعتنا
            </button>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-16 bg-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h3 class="section-title text-4xl font-bold text-center mb-4" style="color: #D4AF37;">منتجاتنا المميزة</h3>
            <p class="section-subtitle text-center mb-12 text-lg">اختر من أفضل مجموعاتنا من الستائر والمفروشات الفاخرة</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($products as $product)
                    <div class="product-card rounded-lg overflow-hidden shadow-lg border">
                        <!-- Product Image -->
                        <div class="relative overflow-hidden h-64">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover hover:scale-110 transition duration-300">
                        </div>

                        <!-- Product Info -->
                        <div class="p-6">
                            <h4 class="text-xl font-bold text-slate-100 mb-2">{{ $product['name'] }}</h4>
                            <p class="text-slate-400 mb-4">{{ $product['description'] }}</p>

                            <!-- Colors -->
                            <div class="mb-4">
                                <p class="text-sm font-semibold text-slate-300 mb-2">الألوان المتاحة:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($product['colors'] as $color)
                                        <span class="text-sm px-3 py-1 rounded-full border" style="background-color: rgba(212, 175, 55, 0.2); color: #D4AF37; border-color: #B8860B;">
                                            {{ $color }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold" style="color: #D4AF37;">{{ $product['price_range'] }} ر.س</span>
                                <button class="text-white px-4 py-2 rounded-lg transition font-semibold hover:opacity-90" style="background-color: #B8860B;">
                                    اطلب الآن
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Fabrics Section -->
    <section id="fabrics" class="py-16 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h3 class="section-title text-4xl font-bold text-center mb-4" style="color: #D4AF37;">أنواع الأقمشة والمفروشات</h3>
            <p class="section-subtitle text-center mb-12 text-lg">تعرف على أنواع الأقمشة والمفروشات المستخدمة في منتجاتنا</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($fabrics as $fabric)
                    <div class="fabric-card rounded-lg p-8 text-center shadow-lg">
                        <div class="text-5xl mb-4">{{ $fabric['icon'] }}</div>
                        <h4 class="text-2xl font-bold mb-3 text-slate-100">{{ $fabric['name'] }}</h4>
                        <p class="text-slate-300 text-lg">{{ $fabric['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h3 class="section-title text-4xl font-bold text-center mb-12" style="color: #D4AF37;">لماذا تختار بيت الديكور؟</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl mb-4">⭐</div>
                    <h4 class="text-xl font-bold text-slate-100 mb-2">جودة عالية</h4>
                    <p class="text-slate-400">أقمشة مختارة بعناية من أفضل الموردين</p>
                </div>

                <div class="text-center">
                    <div class="text-4xl mb-4">💰</div>
                    <h4 class="text-xl font-bold text-slate-100 mb-2">أسعار منافسة</h4>
                    <p class="text-slate-400">أفضل الأسعار مع جودة مضمونة</p>
                </div>

                <div class="text-center">
                    <div class="text-4xl mb-4">🚚</div>
                    <h4 class="text-xl font-bold text-slate-100 mb-2">توصيل سريع</h4>
                    <p class="text-slate-400">توصيل مجاني للطلبات داخل المدينة</p>
                </div>

                <div class="text-center">
                    <div class="text-4xl mb-4">👥</div>
                    <h4 class="text-xl font-bold text-slate-100 mb-2">خدمة عملاء</h4>
                    <p class="text-slate-400">فريق متخصص لمساعدتك 24/7</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="py-16 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h3 class="section-title text-4xl font-bold text-center mb-12" style="color: #D4AF37;">معرض الستائر والمفروشات</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="h-64 rounded-lg overflow-hidden shadow-lg border border-slate-700">
                    <img src="https://images.unsplash.com/photo-1578500494198-246f612d03b3?w=300&h=400&fit=crop" alt="Gallery" class="w-full h-full object-cover hover:scale-110 transition duration-300">
                </div>
                <div class="h-64 rounded-lg overflow-hidden shadow-lg border border-slate-700">
                    <img src="https://images.unsplash.com/photo-1578149102327-636521ce3b57?w=300&h=400&fit=crop" alt="Gallery" class="w-full h-full object-cover hover:scale-110 transition duration-300">
                </div>
                <div class="h-64 rounded-lg overflow-hidden shadow-lg border border-slate-700">
                    <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=300&h=400&fit=crop" alt="Gallery" class="w-full h-full object-cover hover:scale-110 transition duration-300">
                </div>
                <div class="h-64 rounded-lg overflow-hidden shadow-lg border border-slate-700">
                    <img src="https://images.unsplash.com/photo-1589939705066-5ec7037ceaff?w=300&h=400&fit=crop" alt="Gallery" class="w-full h-full object-cover hover:scale-110 transition duration-300">
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="hero-gradient text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h3 class="text-4xl font-bold mb-4">هل أنت مستعد للبدء؟</h3>
            <p class="text-xl mb-8 text-yellow-100">اختر ستائرك ومفروشاتك الفاخرة اليوم واستمتع بخصم 20% على أول طلبية</p>
            <button class="bg-white px-8 py-3 rounded-full font-bold text-lg hover:bg-yellow-100 transition transform hover:scale-105" style="color: #B8860B;">
                اطلب الآن
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-slate-950 text-slate-100 py-12 border-t border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="/images/logo.jpeg" alt="بيت الديكور" class="h-12 w-12 rounded-lg">
                        <h4 class="text-xl font-bold" style="color: #D4AF37;">بيت الديكور</h4>
                    </div>
                    <p class="text-slate-400">متجرك الأول للستائر والأقمشة والمفروشات الفاخرة</p>
                </div>

                <div>
                    <h4 class="font-bold text-slate-100 mb-4">الروابط السريعة</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#products" class="hover:text-yellow-400 transition">المنتجات</a></li>
                        <li><a href="#fabrics" class="hover:text-yellow-400 transition">الأقمشة والمفروشات</a></li>
                        <li><a href="#" class="hover:text-yellow-400 transition">من نحن</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-100 mb-4">التواصل</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="https://wa.me/970592490007" target="_blank" class="hover:text-yellow-400 transition">💬 WhatsApp: +970592490007</a></li>
                        <li>📱 Tel: 0592490007</li>
                        <li>📍 غزة فلسطين، شارع الشفاء</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-100 mb-4">تابعنا</h4>
                    <div class="flex gap-4">
                        <a href="#" class="text-slate-400 hover:text-yellow-400 transition">📘 Facebook</a>
                        <a href="#" class="text-slate-400 hover:text-yellow-400 transition">📷 Instagram</a>
                    </div>
                </div>
            </div>

            <hr class="border-slate-700 mb-8">
            <div class="text-center text-slate-400">
                <p>&copy; 2026 بيت الديكور. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

</body>
</html>
