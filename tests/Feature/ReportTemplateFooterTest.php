<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReportTemplateFooterTest extends TestCase
{
    public function test_all_pdf_report_templates_include_remarks_and_signature_lines(): void
    {
        $templates = [
            ['inventory.pdf_report', ['title' => 'Inventory report', 'headings' => [], 'rows' => []]],
            ['inventory.pdf_items', ['items' => collect()]],
            ['inventory.pdf_transactions', ['transactions' => collect()]],
            ['admin.pdf_users', ['users' => collect()]],
        ];

        foreach ($templates as [$template, $data]) {
            $html = view($template, $data)->render();

            $this->assertStringContainsString('Remarks:', $html, $template);
            $this->assertStringContainsString('Office In Charge', $html, $template);
            $this->assertStringContainsString('Property Custodian', $html, $template);
        }
    }
}
