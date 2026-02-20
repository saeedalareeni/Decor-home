<?php

namespace App\Http\Controllers;

class LandingPageController extends Controller
{
    public function index()
    {
        $products = [
            [
                'id' => 1,
                'name' => 'ستائر الساتان الفاخرة',
                'description' => 'ستائر وأقمشة حريرية ناعمة عالية الجودة',
                'image' => 'https://images.unsplash.com/photo-1595521624812-7ebb60f32e53?w=400&h=500&fit=crop',
                'colors' => ['أحمر فاخر', 'ذهبي', 'فضي'],
                'price_range' => '200-500'
            ],
            [
                'id' => 2,
                'name' => 'ستائر الكتان الطبيعية',
                'description' => 'ستائر ومفروشات من الكتان الطبيعي 100%',
                'image' => 'https://images.unsplash.com/photo-1597180041219-e2b1a5b6a9c2?w=400&h=500&fit=crop',
                'colors' => ['بيج', 'رمادي', 'أبيض'],
                'price_range' => '150-350'
            ],
            [
                'id' => 3,
                'name' => 'ستائر الملك الفيلفت',
                'description' => 'ستائر ومفروشات مخملية فاخرة للديكورات الراقية',
                'image' => 'https://images.unsplash.com/photo-1578500494198-246f612d03b3?w=400&h=500&fit=crop',
                'colors' => ['بنفسجي عميق', 'أسود', 'نبيتي'],
                'price_range' => '300-600'
            ],
            [
                'id' => 4,
                'name' => 'ستائر الدانتيل الراقية',
                'description' => 'ستائر دانتيل شفافة بتصاميم راقية',
                'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=500&fit=crop',
                'colors' => ['أبيض', 'فضي', 'كريمي'],
                'price_range' => '100-250'
            ],
            [
                'id' => 5,
                'name' => 'مفروشات البولي إستر المضادة للماء',
                'description' => 'ستائر ومفروشات عملية مقاومة للرطوبة والبقع',
                'image' => 'https://images.unsplash.com/photo-1589939705066-5ec7037ceaff?w=400&h=500&fit=crop',
                'colors' => ['رمادي', 'أزرق بحري', 'أسود'],
                'price_range' => '120-280'
            ],
            [
                'id' => 6,
                'name' => 'ستائر الكرتان الايطالية',
                'description' => 'ستائر ومفروشات حديثة بتصاميم معاصرة',
                'image' => 'https://images.unsplash.com/photo-1578149102327-636521ce3b57?w=400&h=500&fit=crop',
                'colors' => ['أبيض نقي', 'رمادي فاتح', 'تيراكوتا'],
                'price_range' => '180-420'
            ],
        ];

        $fabrics = [
            [
                'name' => 'الساتان',
                'description' => 'نسيج لامع وناعم يعطي مظهراً فاخراً',
                'icon' => '✨'
            ],
            [
                'name' => 'الكتان',
                'description' => 'ألياف طبيعية خفيفة وتهوية جيدة',
                'icon' => '🌾'
            ],
            [
                'name' => 'الفيلفت',
                'description' => 'مخمل سميك وناعم جداً فاخر',
                'icon' => '👑'
            ],
            [
                'name' => 'الدانتيل',
                'description' => 'أقمشة شفافة برقيقة بتصاميم حساسة',
                'icon' => '🎀'
            ],
            [
                'name' => 'البولي إستر',
                'description' => 'نسيج عملي ومقاوم للتجاعيد والماء',
                'icon' => '🛡️'
            ],
            [
                'name' => 'القطن والمفروشات',
                'description' => 'لين وطبيعي وممتص للرطوبة مريح جداً',
                'icon' => '🍃'
            ],
        ];

        return view('landing', compact('products', 'fabrics'));
    }
}
