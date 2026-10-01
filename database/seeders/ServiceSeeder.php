<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        Service::query()
            ->where('slug', 'paginas-web-tiendas-online')
            ->update([
                'name' => 'Páginas web',
                'slug' => 'paginas-web',
                'description' => 'Sitios profesionales, rápidos y adaptados a la identidad de tu negocio.',
            ]);

        $services = [
            [
                'name' => 'Páginas web',
                'slug' => 'paginas-web',
                'description' => 'Sitios profesionales, rápidos y adaptados a la identidad de tu negocio.',
            ],
            [
                'name' => 'Tiendas online',
                'slug' => 'tiendas-online',
                'description' => 'Vende tus productos con un catálogo claro y una experiencia de compra sencilla.',
            ],
            [
                'name' => 'Automatización de procesos',
                'slug' => 'automatizacion-de-procesos',
                'description' => 'Automatiza tareas repetitivas y conecta las herramientas de tu negocio.',
            ],
            [
                'name' => 'Gestión de redes sociales',
                'slug' => 'gestion-de-redes-sociales',
                'description' => 'Planificación y gestión de contenidos para tus redes sociales.',
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['slug' => $service['slug']],
                $service,
            );
        }
    }
}