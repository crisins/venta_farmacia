<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\DetallePedidoController;
use App\Http\Controllers\ProductoProveedorController;
use App\Http\Controllers\RecetaMedicaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\EmpresaLogisticaController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\AtencionClienteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| **IMPORTANTE:** El prefijo '/api' ya lo añade automáticamente RouteServiceProvider.
| NO necesitamos añadir Route::prefix('api') aquí dentro.
|
*/

// Usuarios
Route::get('usuarios', [UsuarioController::class, 'index']);
Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
Route::post('usuarios', [UsuarioController::class, 'store']);
Route::put('usuarios/{id}', [UsuarioController::class, 'update']);
Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy']); // Asumiendo que existe

// Productos
Route::get('productos', [ProductoController::class, 'index']);
Route::get('productos/{id}', [ProductoController::class, 'show']);
Route::post('productos', [ProductoController::class, 'store']);
Route::put('productos/{id}', [ProductoController::class, 'update']);
Route::delete('productos/{id}', [ProductoController::class, 'destroy']);

// Proveedores
Route::get('proveedores', [ProveedorController::class, 'index']);
Route::get('proveedores/{id}', [ProveedorController::class, 'show']);
Route::post('proveedores', [ProveedorController::class, 'store']);
Route::put('proveedores/{id}', [ProveedorController::class, 'update']);
Route::delete('proveedores/{id}', [ProveedorController::class, 'destroy']);

// **VENTAS - AHORA SIN DOBLE PREFIJO 'api'**
Route::post('ventas', [VentaController::class, 'store']);
Route::delete('ventas/{id}', [VentaController::class, 'destroy']);
Route::get('ventas', [VentaController::class, 'index']);
Route::get('ventas/{id}', [VentaController::class, 'show']);
Route::put('ventas/{id}', [VentaController::class, 'update']);

// Clientes
Route::get('clientes', [ClienteController::class, 'index']);
Route::post('clientes', [ClienteController::class, 'store']);
Route::get('clientes/{id}', [ClienteController::class, 'show']);
Route::put('clientes/{id}', [ClienteController::class, 'update']);
Route::delete('clientes/{id}', [ClienteController::class, 'destroy']);

// Pedidos
Route::get('pedidos', [PedidoController::class, 'index']);
Route::get('pedidos/{id}', [PedidoController::class, 'show']);
Route::post('pedidos', [PedidoController::class, 'store']);
Route::put('pedidos/{id}', [PedidoController::class, 'update']);

// Detalle pedido
Route::get('detalle-pedido', [DetallePedidoController::class, 'index']);
Route::get('detalle-pedido/{id}', [DetallePedidoController::class, 'show']);
Route::post('detalle-pedido', [DetallePedidoController::class, 'store']);
Route::put('detalle-pedido/{id}', [DetallePedidoController::class, 'update']);
Route::delete('detalle-pedido/{id}', [DetallePedidoController::class, 'destroy']);

// ProductoProveedor
Route::get('productos_proveedores', [ProductoProveedorController::class, 'index']);
Route::get('productos_proveedores/{id}', [ProductoProveedorController::class, 'show']);
Route::post('productos_proveedores', [ProductoProveedorController::class, 'store']);
Route::put('productos_proveedores/{id}', [ProductoProveedorController::class, 'update']);
Route::delete('productos_proveedores/{id}', [ProductoProveedorController::class, 'destroy']);

// RecetaMedica
Route::get('recetas_medicas', [RecetaMedicaController::class, 'index']);
Route::get('recetas_medicas/{id}', [RecetaMedicaController::class, 'show']);
Route::post('recetas_medicas', [RecetaMedicaController::class, 'store']);
Route::put('recetas_medicas/{id}', [RecetaMedicaController::class, 'update']);

// Pagos - Si estas rutas específicas de pagos necesitan un prefijo adicional (ej. /api/pagos/id)
// entonces mantener el prefix 'pagos' es correcto, pero debe estar directamente aquí, no anidado.
Route::prefix('pagos')->group(function() {
    Route::get('/', [PagoController::class, 'index']);
    Route::get('{id}', [PagoController::class, 'show']);
    Route::post('/', [PagoController::class, 'store']);
    Route::put('{id}', [PagoController::class, 'update']);
});

// EmpresaLogistica
Route::prefix('empresas_logisticas')->group(function() {
    Route::get('/', [EmpresaLogisticaController::class, 'index']);
    Route::get('{id}', [EmpresaLogisticaController::class, 'show']);
    Route::post('/', [EmpresaLogisticaController::class, 'store']);
    Route::put('{id}', [EmpresaLogisticaController::class, 'update']);
    Route::delete('{id}', [EmpresaLogisticaController::class, 'destroy']);
});

// Envios
Route::prefix('envios')->group(function() {
    Route::get('/', [EnvioController::class, 'index']);
    Route::get('{id}', [EnvioController::class, 'show']);
    Route::post('/', [EnvioController::class, 'store']);
    Route::put('{id}', [EnvioController::class, 'update']);
    Route::delete('{id}', [EnvioController::class, 'destroy']);
});

// AtencionCliente
Route::prefix('atencion_cliente')->group(function() {
    Route::get('/', [AtencionClienteController::class, 'index']);
    Route::get('{id}', [AtencionClienteController::class, 'show']);
    Route::post('/', [AtencionClienteController::class, 'store']);
    Route::put('{id}', [AtencionClienteController::class, 'update']);
    Route::delete('{id}', [AtencionClienteController::class, 'destroy']);
});