<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Categoria2controller;
use Illuminate\Http\Request;

class Categoria2ControllerTest extends TestCase
{
    public function testIndexReturnsJson()
    {
        $controller = new Categoria2controller();
        $response = $controller->index();

        $data = $response->getData(true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Listar todas las categorías', $data['message']);
    }

    public function testStoreReturnsJson()
    {
        $controller = new Categoria2controller();
        $request = new Request();
        $response = $controller->store($request);

        $data = $response->getData(true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Almacenar una nueva categoría', $data['message']);
    }

    public function testShowReturnsJsonWithId()
    {
        $controller = new Categoria2controller();
        $response = $controller->show(5);

        $data = $response->getData(true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Mostrar categoría con ID: 5', $data['message']);
    }

    public function testUpdateReturnsJsonWithId()
    {
        $controller = new Categoria2controller();
        $request = new Request();
        $response = $controller->update($request, 3);

        $data = $response->getData(true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Actualizar categoría con ID: 3', $data['message']);
    }

    public function testDestroyReturnsJsonWithId()
    {
        $controller = new Categoria2controller();
        $response = $controller->destroy(7);

        $data = $response->getData(true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Eliminar categoría con ID: 7', $data['message']);
    }
}
