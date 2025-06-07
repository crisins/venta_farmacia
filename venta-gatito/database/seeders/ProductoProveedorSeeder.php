<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductoProveedor;
use App\Models\Producto;
use App\Models\Proveedor;

class ProductoProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = Producto::all();
        $proveedores = Proveedor::all();

        foreach ($productos as $producto) {
            ProductoProveedor::factory()->count(1)->create([
                'producto_id' => $producto->id,
                'proveedor_id' => $proveedores->random()->id,
            ]);
        }
    }
}
