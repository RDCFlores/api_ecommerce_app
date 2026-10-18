<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Usuario Administrador
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. Crear Usuario Cliente Regular
        User::updateOrCreate(
            ['email' => 'user@user.com'],
            [
                'name' => 'Cliente Pruebas',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        // 3. Crear Productos de Prueba Iniciales
        $products = [
            [
                'name' => 'Teclado Mecánico RGB',
                'description' => 'Teclado gamer con switches blue y retroiluminación RGB configurable.',
                'price' => 49.99,
                'stock' => 15,
                'image_url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500',
            ],
            [
                'name' => 'Ratón Inalámbrico Ergonómico',
                'description' => 'Mouse óptico ergonómico de alta precisión con conectividad Bluetooth.',
                'price' => 25.50,
                'stock' => 20,
                'image_url' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=500',
            ],
            [
                'name' => 'Monitor Gaming 144Hz 27"',
                'description' => 'Monitor FHD con panel IPS, 1ms de tiempo de respuesta y compatibilidad FreeSync.',
                'price' => 199.99,
                'stock' => 8,
                'image_url' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}