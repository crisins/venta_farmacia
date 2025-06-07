<?php

use Illuminate\Support\Facades\Route;

/*
usuario
*/
use App\Http\Controllers\UsuarioController;

Route::get('usuarios', [UsuarioController::class, 'index']);
Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
Route::post('usuarios', [UsuarioController::class, 'store']);
Route::put('usuarios/{id}', [UsuarioController::class, 'update']); 
/*
producto
*/
use App\Http\Controllers\ProductoController;

Route::get('productos', [ProductoController::class, 'index']);
Route::get('productos/{id}', [ProductoController::class, 'show']);
Route::post('productos', [ProductoController::class, 'store']);
Route::put('productos/{id}', [ProductoController::class, 'update']);  // PUT
Route::delete('productos/{id}', [ProductoController::class, 'destroy']);  // DELETE

/*
proveedor
*/
use App\Http\Controllers\ProveedorController;

Route::get('proveedores', [ProveedorController::class, 'index']);
Route::get('proveedores/{id}', [ProveedorController::class, 'show']);
Route::post('proveedores', [ProveedorController::class, 'store']);
Route::put('proveedores/{id}', [ProveedorController::class, 'update']);
Route::delete('proveedores/{id}', [ProveedorController::class, 'destroy']);

/*
pedidos
*/
use App\Http\Controllers\PedidoController;

Route::get('pedidos', [PedidoController::class, 'index']);
Route::get('pedidos/{id}', [PedidoController::class, 'show']);
Route::post('pedidos', [PedidoController::class, 'store']);
Route::put('pedidos/{id}', [PedidoController::class, 'update']);

/*
DetallePedidoController
*/
use App\Http\Controllers\DetallePedidoController;

Route::get('detalle-pedido', [DetallePedidoController::class, 'index']);
Route::get('detalle-pedido/{id}', [DetallePedidoController::class, 'show']);
Route::post('detalle-pedido', [DetallePedidoController::class, 'store']);
Route::put('detalle-pedido/{id}', [DetallePedidoController::class, 'update']);
Route::delete('detalle-pedido/{id}', [DetallePedidoController::class, 'destroy']);



/*
ProductoProveedor
*/
use App\Http\Controllers\ProductoProveedorController;

Route::get('productos_proveedores', [ProductoProveedorController::class, 'index']);
Route::get('productos_proveedores/{id}', [ProductoProveedorController::class, 'show']);
Route::post('productos_proveedores', [ProductoProveedorController::class, 'store']);
Route::put('productos_proveedores/{id}', [ProductoProveedorController::class, 'update']);
Route::delete('productos_proveedores/{id}', [ProductoProveedorController::class, 'destroy']);



/*
RecetaMedica
*/

use App\Http\Controllers\RecetaMedicaController;

Route::get('/recetas_medicas', [RecetaMedicaController::class, 'index']);
Route::get('/recetas_medicas/{id}', [RecetaMedicaController::class, 'show']);
Route::post('/recetas_medicas', [RecetaMedicaController::class, 'store']);
Route::put('/recetas_medicas/{id}', [RecetaMedicaController::class, 'update']);

/*
pago
*/

use App\Http\Controllers\PagoController;

Route::prefix('pagos')->group(function() {
    Route::get('/', [PagoController::class, 'index']); // Mostrar todos los pagos
    Route::get('{id}', [PagoController::class, 'show']); // Mostrar pago por ID
    Route::post('/', [PagoController::class, 'store']); // Crear nuevo pago
    Route::put('{id}', [PagoController::class, 'update']); // Actualizar pago
});

/*
empresalogistica
*/

use App\Http\Controllers\EmpresaLogisticaController;

Route::prefix('empresas_logisticas')->group(function() {
    Route::get('/', [EmpresaLogisticaController::class, 'index']); // Mostrar todas las empresas logísticas
    Route::get('{id}', [EmpresaLogisticaController::class, 'show']); // Mostrar empresa logística por ID
    Route::post('/', [EmpresaLogisticaController::class, 'store']); // Crear nueva empresa logística
    Route::put('{id}', [EmpresaLogisticaController::class, 'update']); // Actualizar empresa logística
    Route::delete('{id}', [EmpresaLogisticaController::class, 'destroy']); // Eliminar empresa logística
});

/*
envio
*/

use App\Http\Controllers\EnvioController;


Route::prefix('envios')->group(function() {
    Route::get('/', [EnvioController::class, 'index']); // Mostrar todos los envíos
    Route::get('{id}', [EnvioController::class, 'show']); // Mostrar envío por ID
    Route::post('/', [EnvioController::class, 'store']); // Crear nuevo envío
    Route::put('{id}', [EnvioController::class, 'update']); // Actualizar envío
    Route::delete('{id}', [EnvioController::class, 'destroy']); // Eliminar envío
});

/*
atencioncliente
*/

use App\Http\Controllers\AtencionClienteController;


Route::prefix('atencion_cliente')->group(function() {
    Route::get('/', [AtencionClienteController::class, 'index']); // Mostrar todas las atenciones
    Route::get('{id}', [AtencionClienteController::class, 'show']); // Mostrar atención por ID
    Route::post('/', [AtencionClienteController::class, 'store']); // Crear nueva atención
    Route::put('{id}', [AtencionClienteController::class, 'update']); // Actualizar atención
    Route::delete('{id}', [AtencionClienteController::class, 'destroy']); // Eliminar atención
});


