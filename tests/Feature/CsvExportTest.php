<?php

namespace Tests\Feature;

use App\Helpers\Csv;
use App\Models\Admin;
use App\Models\PropertyTaxRecord;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvExportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'type' => 'superadmin',
            'is_active' => true,
        ]);

        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    public function test_export_starts_with_a_utf8_bom_so_excel_detects_the_encoding(): void
    {
        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.property-tax.export'));

        $response->assertOk();

        $body = $response->streamedContent();

        $this->assertStringStartsWith(
            Csv::BOM,
            $body,
            'Without a BOM Excel reads the file as Windows-1252 and mangles Devanagari names.'
        );
    }

    public function test_devanagari_names_survive_the_export_intact(): void
    {
        PropertyTaxRecord::create([
            'a_no' => 1,
            'customer_no' => 'C-001',
            'property_no' => '3500/1',
            'property_type' => 'Default',
            'customer_name' => 'सोहम तारे',
            'phone' => '9425551234',
            'aadhaar_no' => '855012345678',
        ]);

        $body = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.property-tax.export'))
            ->streamedContent();

        $this->assertStringContainsString('सोहम तारे', $body);
        $this->assertStringNotContainsString('à¤', $body, 'Mojibake means the bytes were re-encoded.');
    }

    public function test_phone_and_aadhaar_are_forced_to_text_not_scientific_notation(): void
    {
        PropertyTaxRecord::create([
            'a_no' => 1,
            'customer_no' => 'C-001',
            'property_no' => '3500/1',
            'property_type' => 'Default',
            'customer_name' => 'Test',
            'phone' => '9425551234',
            'aadhaar_no' => '855012345678',
        ]);

        $body = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.property-tax.export'))
            ->streamedContent();

        // fputcsv escapes the quotes on the way out, so compare parsed fields
        // rather than raw bytes. Excel unescapes then evaluates ="..." as text.
        $rows = array_map('str_getcsv', array_filter(explode("\n", trim($body))));
        $dataRow = $rows[1];

        $this->assertContains('="9425551234"', $dataRow, 'Phone must stay text, not become 9.43E+09.');
        $this->assertContains('="855012345678"', $dataRow, 'Aadhaar must stay text, not become 8.55E+11.');
    }

    public function test_blank_identifiers_stay_blank_rather_than_becoming_empty_formulas(): void
    {
        $this->assertSame('', Csv::text(null));
        $this->assertSame('', Csv::text(''));
        $this->assertSame('', Csv::text('   '));
    }

    public function test_embedded_quotes_are_escaped(): void
    {
        $this->assertSame('="a""b"', Csv::text('a"b'));
    }
}
