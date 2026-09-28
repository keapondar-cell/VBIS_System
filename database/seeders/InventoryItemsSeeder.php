<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class InventoryItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['VBIS-1002', 'USB Keyboard', 'Standard wired keyboard for computer laboratory workstations.', 'Computer Accessories', 12, 'Com Lab'],
            ['VBIS-1003', 'Optical Mouse', 'Wired optical mouse for computer laboratory workstations.', 'Computer Accessories', 15, 'Com Lab'],
            ['VBIS-1004', 'HDMI Cable', 'Two-meter cable for connecting computers to classroom displays.', 'Computer Accessories', 8, 'Com Lab'],
            ['VBIS-1005', 'USB Flash Drive 32GB', 'USB storage device for instructional files and lab activities.', 'Computer Accessories', 10, 'Com Lab'],
            ['VBIS-2001', 'General Science Textbook', 'Reference textbook available for student library use.', 'Books', 24, 'Library'],
            ['VBIS-2002', 'English Dictionary', 'Student reference dictionary for reading and writing activities.', 'Reference Books', 12, 'Library'],
            ['VBIS-2003', 'World Atlas', 'Printed atlas for geography and social studies reference.', 'Reference Books', 8, 'Library'],
            ['VBIS-2004', 'Reading Workbook', 'Supplemental reading workbook for classroom lending.', 'Books', 30, 'Library'],
            ['VBIS-3001', 'Safety Goggles', 'Protective eyewear for laboratory activities.', 'Laboratory Safety', 20, 'Science Lab'],
            ['VBIS-3002', 'Glass Beaker 250mL', 'Graduated borosilicate glass beaker for experiments.', 'Laboratory Glassware', 16, 'Science Lab'],
            ['VBIS-3003', 'Digital Thermometer', 'Digital thermometer for classroom science experiments.', 'Laboratory Equipment', 6, 'Science Lab'],
            ['VBIS-3004', 'Microscope Slides', 'Reusable glass slides for microscope activities.', 'Laboratory Supplies', 40, 'Science Lab'],
            ['VBIS-4001', 'Whiteboard Marker Set', 'Assorted-color dry erase markers for teacher use.', 'Teacher Supplies', 18, 'Teacher Supplies'],
            ['VBIS-4002', 'Bond Paper Ream', 'A4 multipurpose paper for lesson materials and handouts.', 'Teacher Supplies', 12, 'Teacher Supplies'],
            ['VBIS-4003', 'Desktop Stapler', 'Standard desktop stapler for preparing classroom materials.', 'Teacher Supplies', 8, 'Teacher Supplies'],
            ['VBIS-4004', 'Whiteboard Eraser', 'Dry erase board eraser for classroom instruction.', 'Teacher Supplies', 10, 'Teacher Supplies'],
        ];

        foreach ($items as [$sku, $name, $description, $category, $quantity, $location]) {
            Item::firstOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'description' => $description,
                    'category' => $category,
                    'quantity' => $quantity,
                    'location' => $location,
                    'status' => 'available',
                ]
            );
        }
    }
}