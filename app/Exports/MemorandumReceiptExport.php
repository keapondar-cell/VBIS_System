<?php

namespace App\Exports;

use App\Models\InventoryTransaction;
use App\Support\SchoolTime;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemorandumReceiptExport
{
    private const TABLE_WIDTH = 10500;

    public function download(InventoryTransaction $transaction): StreamedResponse
    {
        $document = new PhpWord();
        $document->setDefaultFontName('Arial');
        $document->setDefaultFontSize(9);

        $section = $document->addSection([
            'paperSize' => 'A4',
            'orientation' => 'portrait',
            'marginLeft' => 680,
            'marginRight' => 680,
            'marginTop' => 560,
            'marginBottom' => 560,
        ]);
        $borderedTable = [
            'borderColor' => '222222',
            'borderSize' => 6,
            'cellMargin' => 60,
        ];
        $center = ['alignment' => 'center'];
        $bold = ['bold' => true];

        $heading = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $heading->addRow(1000);
        $logoCell = $heading->addCell(1300, ['borderSize' => 0, 'valign' => 'center']);
        $logoCell->addImage(public_path('images/school-logo.jpg'), [
            'width' => 58,
            'height' => 58,
            'alignment' => 'center',
        ]);
        $headingCell = $heading->addCell(self::TABLE_WIDTH - 2600, ['borderSize' => 0, 'valign' => 'center']);
        foreach ([
            ['Republic of the Philippines', ['size' => 8]],
            ['Department of Education', ['bold' => true, 'size' => 11, 'name' => 'Georgia']],
            ['Region V', ['size' => 9]],
            ['DIVISION OF CAMARINES SUR', ['size' => 9]],
            ['VICTOR BERNAL PROVINCIAL HIGH SCHOOL', ['bold' => true, 'size' => 10]],
            ['MEMORANDUM RECEIPT', ['bold' => true, 'size' => 12, 'underline' => 'single']],
        ] as [$text, $fontStyle]) {
            $headingCell->addText($text, $fontStyle, $center);
        }
        $heading->addCell(1300, ['borderSize' => 0]);
        $section->addTextBreak(1);

        $metadata = $section->addTable(['borderSize' => 0, 'cellMargin' => 70]);
        $metadata->addRow();
        $metadata->addCell(3500, ['borderSize' => 0])->addText('School: VICTOR BERNAL PHS');
        $metadata->addCell(3500, ['borderSize' => 0])->addText('Accountable Officer: ____________________', $bold, $center);
        $metadata->addCell(3500, ['borderSize' => 0])->addText('MR Num.: MR-'.now()->format('Y').'-'.$transaction->id, null, $center);
        $metadata->addRow();
        $metadata->addCell(3500, ['borderSize' => 0])->addText('Classification: '.($transaction->item?->category ?? ''));
        $metadata->addCell(3500, ['borderSize' => 0])->addText('School Year: ____________________', null, $center);
        $metadata->addCell(3500, ['borderSize' => 0])->addText('');

        $identity = $section->addTable($borderedTable);
        $identity->addRow();
        $identity->addCell(3500)->addText('Agency/Office', $bold, $center);
        $identity->addCell(3500)->addText('Address', $bold, $center);
        $identity->addCell(3500)->addText('Date', $bold, $center);
        $identity->addRow();
        $identity->addCell(3500)->addText('VICTOR BERNAL PHS', null, $center);
        $identity->addCell(3500)->addText('San Vicente Ogbon, Nabua, Camarines Sur', null, $center);
        $identity->addCell(3500)->addText(SchoolTime::format($transaction->requested_for_at ?? $transaction->created_at, 'F j, Y'), null, $center);

        $acknowledgement = $section->addTable($borderedTable);
        $acknowledgement->addRow();
        $acknowledgement->addCell(self::TABLE_WIDTH)->addText(
            'I acknowledge to have received from ________________________________, Property Custodian of Victor Bernal Provincial High School, the following properties which shall be used in '.
            ($transaction->department?->name ?? '____________________').
            ' and for which I am Accountable.'
        );

        $items = $section->addTable($borderedTable);
        $items->addRow();
        $items->addCell(650, ['vMerge' => 'restart', 'valign' => 'center'])->addText('Qty.', $bold, $center);
        $items->addCell(650, ['vMerge' => 'restart', 'valign' => 'center'])->addText('Unit', $bold, $center);
        $items->addCell(2600, ['vMerge' => 'restart', 'valign' => 'center'])->addText('NAME OF ITEM/DESCRIPTION', $bold, $center);
        $items->addCell(2000, ['gridSpan' => 4, 'valign' => 'center'])->addText('CONDITION', $bold, $center);
        $items->addCell(1000, ['vMerge' => 'restart', 'valign' => 'center'])->addText('Date Acquired', $bold, $center);
        $items->addCell(950, ['vMerge' => 'restart', 'valign' => 'center'])->addText('Source of Fund', $bold, $center);
        $items->addCell(1000, ['vMerge' => 'restart', 'valign' => 'center'])->addText('Property Number', $bold, $center);
        $items->addCell(1650, ['vMerge' => 'restart', 'valign' => 'center'])->addText('Remarks', $bold, $center);
        $items->addRow();
        foreach ([
            [650, 'continue', ''],
            [650, 'continue', ''],
            [2600, 'continue', ''],
            [500, null, 'A'],
            [500, null, 'B'],
            [500, null, 'C'],
            [500, null, 'D'],
            [1000, 'continue', ''],
            [950, 'continue', ''],
            [1000, 'continue', ''],
            [1650, 'continue', ''],
        ] as [$width, $verticalMerge, $label]) {
            $cellStyle = ['valign' => 'center'];
            if ($verticalMerge !== null) {
                $cellStyle['vMerge'] = $verticalMerge;
            }
            $items->addCell($width, $cellStyle)->addText($label, $bold, $center);
        }
        $items->addRow(1150);
        $items->addCell(650)->addText((string) $transaction->quantity, null, $center);
        $items->addCell(650)->addText('');
        $description = $items->addCell(2600, ['valign' => 'center']);
        $description->addText($transaction->item?->name ?? 'Inventory item', $bold);
        if ($transaction->item?->sku) {
            $description->addText($transaction->item->sku);
        }
        if ($transaction->item?->description) {
            $description->addText($transaction->item->description);
        }
        foreach ([500, 500, 500, 500] as $width) {
            $items->addCell($width)->addText('');
        }
        $items->addCell(1000)->addText(SchoolTime::format($transaction->requested_for_at ?? $transaction->created_at, 'n/j/Y'), null, $center);
        $items->addCell(950)->addText('');
        $items->addCell(1000)->addText('');
        $remarks = [];
        if ($transaction->condition) {
            $remarks[] = 'Condition: '.$transaction->condition;
        }
        if ($transaction->expected_return_at) {
            $remarks[] = 'Expected return: '.SchoolTime::format($transaction->expected_return_at, 'n/j/Y');
        }
        if ($transaction->notes) {
            $remarks[] = $transaction->notes;
        }
        $items->addCell(1650)->addText(implode("\n", $remarks));

        $section->addTextBreak(1);
        $section->addText('Legend: A - (New), B - (Used in Good Condition), C - (Used in Fair Condition), D - (Used in Poor Condition)', ['bold' => true, 'italic' => true, 'size' => 8]);
        $section->addText('Remarks: ____________________________________________________________________________________', null, ['spaceAfter' => 300]);

        $signatures = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $signatures->addRow(1200);
        $signatures->addCell(3500)->addText('__________________________________', null, $center);
        $signatures->addCell(3500)->addText('__________________________________', null, $center);
        $signatures->addCell(3500)->addText('__________________________________', null, $center);
        $signatures->addRow();
        $signatures->addCell(3500)->addText(($transaction->user?->name ?? '')."\nSignature over Printed Name\nof Accountable Officer", null, $center);
        $signatures->addCell(3500)->addText(($transaction->user?->role ? ucwords(str_replace('_', ' ', $transaction->user->role)) : '')."\nPosition/Designation", null, $center);
        $signatures->addCell(3500)->addText(SchoolTime::format($transaction->requested_for_at ?? $transaction->created_at, 'n/j/Y')."\nDate", null, $center);

        $approvals = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $approvals->addRow();
        $approvals->addCell(5250)->addText('Recommending Approval:', $bold);
        $approvals->addCell(5250)->addText('Approved:', $bold);
        $approvals->addRow(1200);
        $approvals->addCell(5250)->addText('__________________________________', null, $center);
        $approvals->addCell(5250)->addText('__________________________________', null, $center);
        $approvals->addRow();
        $approvals->addCell(5250)->addText('Supply Officer', null, $center);
        $approvals->addCell(5250)->addText('School Head', null, $center);

        $writer = IOFactory::createWriter($document, 'Word2007');
        $filename = 'memorandum_receipt_MR-'.now()->format('Y').'-'.$transaction->id.'.docx';

        return response()->streamDownload(
            static fn () => $writer->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        );
    }
}
