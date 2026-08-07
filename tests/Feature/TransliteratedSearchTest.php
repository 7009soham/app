<?php

namespace Tests\Feature;

use App\Helpers\Transliterate;
use App\Models\Citizen;
use App\Models\PropertyTaxRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransliteratedSearchTest extends TestCase
{
    use RefreshDatabase;

    private function record(string $name, array $extra = []): PropertyTaxRecord
    {
        return PropertyTaxRecord::create(array_merge([
            'a_no' => 1,
            'customer_no' => 'C-001',
            'property_no' => '3500/1',
            'property_type' => 'Default',
            'customer_name' => $name,
            'phone' => '9425551234',
        ], $extra));
    }

    public function test_typing_english_finds_a_devanagari_name(): void
    {
        $this->record('सोहम तारे');

        // The whole point: staff type on an English keyboard.
        $this->assertSame(1, PropertyTaxRecord::search('soham')->count());
        $this->assertSame(1, PropertyTaxRecord::search('tare')->count());
    }

    public function test_typing_devanagari_still_works(): void
    {
        $this->record('सोहम तारे');

        $this->assertSame(1, PropertyTaxRecord::search('सोहम')->count());
    }

    public function test_search_is_case_insensitive_for_english_input(): void
    {
        $this->record('सोहम तारे');

        $this->assertSame(1, PropertyTaxRecord::search('SOHAM')->count());
        $this->assertSame(1, PropertyTaxRecord::search('Soham')->count());
    }

    public function test_an_unrelated_name_is_not_matched(): void
    {
        $this->record('सोहम तारे');

        $this->assertSame(0, PropertyTaxRecord::search('rajesh')->count());
    }

    public function test_non_name_columns_are_still_searchable(): void
    {
        $this->record('सोहम तारे', ['property_no' => '3500/7', 'phone' => '9876543210']);

        $this->assertSame(1, PropertyTaxRecord::search('3500/7')->count());
        $this->assertSame(1, PropertyTaxRecord::search('9876543210')->count());
        $this->assertSame(1, PropertyTaxRecord::search('C-001')->count());
    }

    public function test_a_blank_term_does_not_filter_anything_out(): void
    {
        $this->record('सोहम तारे');
        $this->record('राजेश पाटील', ['customer_no' => 'C-002', 'a_no' => 2]);

        $this->assertSame(2, PropertyTaxRecord::search('')->count());
        $this->assertSame(2, PropertyTaxRecord::search(null)->count());
        $this->assertSame(2, PropertyTaxRecord::search('   ')->count());
    }

    public function test_the_romanised_column_is_maintained_on_update(): void
    {
        $record = $this->record('सोहम तारे');
        $this->assertStringContainsString('soham', $record->customer_name_roman);

        $record->update(['customer_name' => 'राजेश पाटील']);

        $this->assertSame(0, PropertyTaxRecord::search('soham')->count());
        $this->assertSame(1, PropertyTaxRecord::search('rajesh')->count());
    }

    public function test_english_stored_names_are_indexed_too(): void
    {
        $this->record('Asha Kulkarni');

        $this->assertSame(1, PropertyTaxRecord::search('asha')->count());
        $this->assertSame(1, PropertyTaxRecord::search('kulkarni')->count());
    }

    public function test_search_works_across_other_models(): void
    {
        Citizen::create([
            'customer_no' => 'CIT-1',
            'name' => 'सोहम तारे',
            'phone' => '9425551234',
        ]);

        $this->assertSame(1, Citizen::search('soham')->count());
        $this->assertSame(1, Citizen::search('9425551234')->count());
    }

    public function test_transliteration_strips_punctuation_so_matching_is_stable(): void
    {
        $this->assertSame('tare soham', Transliterate::toRoman('Tare, Soham'));
        $this->assertSame('tare soham', Transliterate::toRoman('  Tare   Soham  '));
    }

    /**
     * ICU renders श as "ś" and Latin-ASCII drops the diacritic, so the "h" the
     * user types is not in the transliterated form.
     */
    public function test_sh_sounds_are_found_despite_the_lost_diacritic(): void
    {
        $this->record('राजेश पाटील');

        $this->assertSame(1, PropertyTaxRecord::search('rajesh')->count());
        $this->assertSame(1, PropertyTaxRecord::search('rajes')->count());
    }

    public function test_hand_typed_long_vowels_match_the_transliterated_form(): void
    {
        $this->record('नीता शिंदे');

        // ICU gives "nita"; people type "neeta".
        $this->assertSame(1, PropertyTaxRecord::search('neeta')->count());
        $this->assertSame(1, PropertyTaxRecord::search('nita')->count());
    }

    public function test_v_and_w_are_interchangeable(): void
    {
        $this->record('विशाल जाधव');

        $this->assertSame(1, PropertyTaxRecord::search('vishal')->count());
        $this->assertSame(1, PropertyTaxRecord::search('wishal')->count());
    }

    public function test_aspirated_consonants_fold_to_their_base(): void
    {
        $this->record('भारती ठाकूर');

        $this->assertSame(1, PropertyTaxRecord::search('bharati')->count());
        $this->assertSame(1, PropertyTaxRecord::search('thakur')->count());
    }

    public function test_devanagari_detection(): void
    {
        $this->assertTrue(Transliterate::isDevanagari('सोहम'));
        $this->assertFalse(Transliterate::isDevanagari('Soham'));
    }

    public function test_empty_input_romanises_to_an_empty_string(): void
    {
        $this->assertSame('', Transliterate::toRoman(null));
        $this->assertSame('', Transliterate::toRoman('   '));
    }
}
