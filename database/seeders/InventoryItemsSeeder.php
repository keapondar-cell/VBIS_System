<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class InventoryItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['VBIS-1006', 'USB Headset', 'Wired headset with microphone for language and multimedia lessons.', 'Computer Accessories', 12, 'Computer Lab'],
            ['VBIS-1007', 'Ethernet Cable 3m', 'Network cable for connecting computers to the laboratory network.', 'Networking', 15, 'Computer Lab'],
            ['VBIS-1008', 'Surge Protector', 'Multi-outlet surge protector for computer workstation equipment.', 'Computer Accessories', 6, 'Computer Lab'],
            ['VBIS-1009', 'Webcam', 'USB webcam for online lessons and classroom demonstrations.', 'Computer Accessories', 8, 'Computer Lab'],
            ['VBIS-1010', 'Wireless Presentation Remote', 'Presentation remote for classroom instruction.', 'Computer Accessories', 5, 'Computer Lab'],
            ['VBIS-1002', 'USB Keyboard', 'Standard wired keyboard for computer laboratory workstations.', 'Computer Accessories', 12, 'Com Lab'],
            ['VBIS-1003', 'Optical Mouse', 'Wired optical mouse for computer laboratory workstations.', 'Computer Accessories', 15, 'Com Lab'],
            ['VBIS-1004', 'HDMI Cable', 'Two-meter cable for connecting computers to classroom displays.', 'Computer Accessories', 8, 'Com Lab'],
            ['VBIS-1005', 'USB Flash Drive 32GB', 'USB storage device for instructional files and lab activities.', 'Computer Accessories', 10, 'Com Lab'],
            ['VBIS-2010', 'Children\'s Storybook Set', 'Illustrated storybooks for student reading and library borrowing.', 'Books', 20, 'Library'],
            ['VBIS-2011', 'Filipino Dictionary', 'Filipino-language dictionary for student reference.', 'Reference Books', 10, 'Library'],
            ['VBIS-2012', 'Literary Anthology', 'Collection of poems and short stories for classroom reading.', 'Books', 16, 'Library'],
            ['VBIS-2013', 'Encyclopedia Volume Set', 'General knowledge reference volumes for library research.', 'Reference Books', 5, 'Library'],
            ['VBIS-2014', 'Bookends Pair', 'Pair of bookends for organizing library shelves.', 'Library Supplies', 8, 'Library'],
            ['VBIS-2001', 'General Science Textbook', 'Reference textbook available for student library use.', 'Books', 24, 'Library'],
            ['VBIS-2002', 'English Dictionary', 'Student reference dictionary for reading and writing activities.', 'Reference Books', 12, 'Library'],
            ['VBIS-2003', 'World Atlas', 'Printed atlas for geography and social studies reference.', 'Reference Books', 8, 'Library'],
            ['VBIS-2004', 'Reading Workbook', 'Supplemental reading workbook for classroom lending.', 'Books', 30, 'Library'],
            ['VBIS-3010', 'Graduated Cylinder 100mL', 'Graduated cylinder for measuring liquid volume during experiments.', 'Laboratory Glassware', 12, 'Science Lab'],
            ['VBIS-3011', 'Digital Weighing Scale', 'Electronic balance for measuring samples in science activities.', 'Laboratory Equipment', 4, 'Science Lab'],
            ['VBIS-3012', 'Laboratory Apron', 'Protective apron for student laboratory activities.', 'Laboratory Safety', 20, 'Science Lab'],
            ['VBIS-3013', 'Test Tube Set', 'Reusable glass test tubes for classroom experiments.', 'Laboratory Glassware', 30, 'Science Lab'],
            ['VBIS-3014', 'Magnifying Glass', 'Handheld magnifier for specimen and materials observation.', 'Laboratory Equipment', 10, 'Science Lab'],
            ['VBIS-3001', 'Safety Goggles', 'Protective eyewear for laboratory activities.', 'Laboratory Safety', 20, 'Science Lab'],
            ['VBIS-3002', 'Glass Beaker 250mL', 'Graduated borosilicate glass beaker for experiments.', 'Laboratory Glassware', 16, 'Science Lab'],
            ['VBIS-3003', 'Digital Thermometer', 'Digital thermometer for classroom science experiments.', 'Laboratory Equipment', 6, 'Science Lab'],
            ['VBIS-3004', 'Microscope Slides', 'Reusable glass slides for microscope activities.', 'Laboratory Supplies', 40, 'Science Lab'],
            ['VBIS-5001', 'Geometry Set', 'Student geometry tools for mathematics lessons.', 'Mathematics Supplies', 30, 'JHS Department'],
            ['VBIS-5002', 'Scientific Calculator', 'Student calculator for junior high mathematics activities.', 'Mathematics Supplies', 20, 'JHS Department'],
            ['VBIS-5003', 'Periodic Table Chart', 'Classroom wall chart of the chemical elements.', 'Classroom Materials', 4, 'JHS Department'],
            ['VBIS-5004', 'Filipino Literature Textbook', 'Textbook for junior high Filipino language and literature lessons.', 'Books', 25, 'JHS Department'],
            ['VBIS-5005', 'Student Art Set', 'Drawing pencils, erasers, and colored pencils for classroom art activities.', 'Art Supplies', 15, 'JHS Department'],
            ['VBIS-6001', 'Advanced Scientific Calculator', 'Scientific calculator for senior high mathematics and science coursework.', 'Mathematics Supplies', 20, 'SHS Department'],
            ['VBIS-6002', 'Laboratory Notebook', 'Bound notebook for recording senior high science observations.', 'Laboratory Supplies', 30, 'SHS Department'],
            ['VBIS-6003', 'Technical Drawing Set', 'Drawing tools for senior high technical and mathematics activities.', 'Classroom Supplies', 12, 'SHS Department'],
            ['VBIS-6004', 'Research Methods Textbook', 'Reference textbook for senior high research and academic writing.', 'Books', 15, 'SHS Department'],
            ['VBIS-6005', 'Presentation Clicker', 'Wireless clicker for senior high classroom presentations.', 'Classroom Equipment', 6, 'SHS Department'],
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