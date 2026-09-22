<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Existing virtual (online) courses — backfill the new display fields.
        Course::updateOrCreate(
            ['title' => 'Blanqueamiento Dental Profesional'],
            [
                'description' => 'Aprende las técnicas más avanzadas en blanqueamiento dental, uso de peróxidos y manejo de sensibilidad.',
                'price' => 150000,
                'image_url' => '/images/teeth-whitening.svg',
                'bold_payment_link' => 'https://bold.co/pago/test1',
                'features' => ['Certificado de asistencia', 'Acceso de por vida', 'Material de apoyo PDF'],
                'type' => 'virtual',
                'category' => 'Blanqueamiento',
                'rating' => 4.8,
                'duration_hours' => 12,
                'level' => 'Principiante',
            ]
        );

        Course::updateOrCreate(
            ['title' => 'Estética Dental Avanzada'],
            [
                'description' => 'Domina las carillas de porcelana y diseño de sonrisa en este curso intensivo 100% online.',
                'price' => 250000,
                'image_url' => '/images/cosmetic-dentistry.svg',
                'bold_payment_link' => 'https://bold.co/pago/test2',
                'features' => ['Certificado internacional', 'Soporte vía WhatsApp', 'Casos clínicos reales'],
                'type' => 'virtual',
                'category' => 'Estética',
                'rating' => 4.9,
                'duration_hours' => 25,
                'level' => 'Intermedio',
            ]
        );

        Course::updateOrCreate(
            ['title' => 'Manejo de Pacientes y Odontopediatría'],
            [
                'description' => 'Técnicas de manejo de ansiedad y procedimientos sin dolor para los más pequeños.',
                'price' => 120000,
                'image_url' => '/images/painless-dentistry.svg',
                'bold_payment_link' => 'https://bold.co/pago/test3',
                'features' => ['Certificado de asistencia', 'Acceso de por vida', 'Material descargable'],
                'type' => 'virtual',
                'category' => 'Odontopediatría',
                'rating' => 4.7,
                'duration_hours' => 10,
                'level' => 'Principiante',
            ]
        );

        // Método AV — flagship virtual course, styled after the reference design.
        Course::updateOrCreate(
            ['title' => 'Natural Layering'],
            [
                'description' => 'Carillas estratificadas a mano alzada. El método propio de la Dra. Andrea Vargas para lograr sonrisas naturales sin desgaste dental.',
                'price' => 1800000,
                'image_url' => null,
                'features' => ['Certificado del Método AV', 'Acceso de por vida', 'Casos clínicos paso a paso'],
                'type' => 'virtual',
                'category' => 'Resina',
                'rating' => 4.9,
                'duration_hours' => 20,
                'level' => 'Principiante',
            ]
        );

        // In-person (presencial) workshops — sold out, matching the reference design.
        Course::updateOrCreate(
            ['title' => 'Arte & Naturalidad', 'event_date' => '2026-07-29'],
            [
                'description' => 'Carillas en resina estratificadas a mano alzada',
                'price' => 3500000,
                'deposit_amount' => 500000,
                'features' => ['Certificado de asistencia', 'Kit de materiales incluido', 'Almuerzo incluido'],
                'type' => 'presencial',
                'category' => 'Resina',
                'rating' => 4.9,
                'location' => 'Medellín',
                'capacity' => 15,
                'seats_taken' => 15,
            ]
        );

        Course::updateOrCreate(
            ['title' => 'Arte & Naturalidad', 'event_date' => '2026-06-04'],
            [
                'description' => 'Carillas estratificadas a mano alzada',
                'price' => 3800000,
                'deposit_amount' => 500000,
                'features' => ['Certificado de asistencia', 'Kit de materiales incluido', 'Almuerzo incluido'],
                'type' => 'presencial',
                'category' => 'Resina',
                'rating' => 4.9,
                'location' => 'Medellín',
                'capacity' => 15,
                'seats_taken' => 15,
            ]
        );

        Course::updateOrCreate(
            ['title' => 'Arte & Resina', 'event_date' => '2026-11-12'],
            [
                'description' => 'Carillas estratificadas a mano alzada',
                'price' => 3800000,
                'deposit_amount' => 500000,
                'features' => ['Certificado de asistencia', 'Kit de materiales incluido', 'Almuerzo incluido'],
                'type' => 'presencial',
                'category' => 'Resina',
                'rating' => 4.9,
                'location' => 'Medellín',
                'capacity' => 15,
                'seats_taken' => 6,
            ]
        );
    }
}
