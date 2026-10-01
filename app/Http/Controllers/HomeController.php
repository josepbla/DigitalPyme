<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->keyBy('slug');

        return view('home', [
            'serviceCards' => [
                [
                    'slug' => 'paginas-web',
                    'title' => 'Páginas Web',
                    'description' => 'Sitios profesionales, rápidos y adaptados a la identidad de tu negocio.',
                    'serviceId' => $services->get('paginas-web')?->id,
                       'price' => $services->get('paginas-web')?->price,
                ],
                [
                    'slug' => 'tiendas-online',
                    'title' => 'Tiendas Online',
                    'description' => 'Vende tus productos con un catálogo claro y una experiencia de compra sencilla.',
                    'serviceId' => $services->get('tiendas-online')?->id,
                       'price' => $services->get('tiendas-online')?->price,
                ],
                [
                    'slug' => 'automatizacion',
                    'title' => 'Automatización de Procesos',
                       'description' => 'Reduce tareas repetitivas y conecta las herramientas que ya utilizas.',
                    'serviceId' => $services->get('automatizacion-de-procesos')?->id,
                       'price' => $services->get('automatizacion-de-procesos')?->price,
                ],
                [
                    'slug' => 'redes-sociales',
                    'title' => 'Gestión de Redes Sociales',
                    'description' => 'Mantén una presencia activa con contenido alineado con tu marca.',
                    'serviceId' => $services->get('gestion-de-redes-sociales')?->id,
                       'price' => $services->get('gestion-de-redes-sociales')?->price,
                ],
            ],
        ]);
    }
}