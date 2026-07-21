<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Migrate legacy hypothesis_1/2/3 keys to the Repeater format
        if (empty($data['meta']['hypotheses'])) {
            $legacy = array_filter([
                $data['meta']['hypothesis_1'] ?? null,
                $data['meta']['hypothesis_2'] ?? null,
                $data['meta']['hypothesis_3'] ?? null,
            ]);

            if ($legacy) {
                $data['meta']['hypotheses'] = array_map(
                    fn ($content) => ['content' => $content],
                    array_values($legacy)
                );
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['slug'] ?? '', ['hard-copies', 'online-classes', 'feature-homeschooling', 'audio-stories', 'feature-teachers-training', 'feature-counselling-therapy', 'about', 'methodology', 'contact'])) {
            $this->enforceMaxLength(
                'Page Title',
                $data['title'] ?? '',
                100,
                stripHtml: false,
            );
        }

        if (($data['slug'] ?? '') === 'feature-homeschooling') {
            $this->enforceMaxLength('Intro Paragraph 1', $data['meta']['intro_para_1'] ?? '', 400, stripHtml: false);
            $this->enforceMaxLength('Intro Paragraph 2', $data['meta']['intro_para_2'] ?? '', 250, stripHtml: false);
            $this->enforceMaxLength('Laptop Text', $data['meta']['laptop_text'] ?? '', 150, stripHtml: false);
        }

        if (($data['slug'] ?? '') === 'methodology') {
            $this->enforceMaxLength('Preface Heading', $data['meta']['preface_heading'] ?? '', 100, stripHtml: false);
            $this->enforceMaxLength('Preface Subtext', $data['meta']['preface_subtext'] ?? '', 350, stripHtml: false);
        }

        if (($data['slug'] ?? '') === 'hard-copies') {
            $this->enforceMaxLength('Hard Copies Section Heading', $data['meta']['list_heading']   ?? '', 100, stripHtml: false);
            $this->enforceMaxLength('Alert Box Text',              $data['meta']['alert_text']     ?? '', 200, stripHtml: false);
            $this->enforceMaxLength('Shipping Notice Text',        $data['meta']['shipping_text']  ?? '',  80, stripHtml: false);
        }

        if (($data['slug'] ?? '') === 'ebooks') {
            $this->enforceMaxLength('eBooks Section Heading', $data['meta']['recommend_heading'] ?? '', 100, stripHtml: false);
        }

        if (($data['slug'] ?? '') === 'online-classes') {
            $this->enforceMaxLength('Intro Paragraph',          $data['meta']['intro_text']              ?? '', 534, stripHtml: false);
            $this->enforceMaxLength('Launching Text',           $data['meta']['launching_text']          ?? '',  30, stripHtml: false);
            $this->enforceMaxLength('Online Teachers Heading',  $data['meta']['online_teachers_heading'] ?? '',  30, stripHtml: false);
            $this->enforceMaxLength('Heading',                  $data['meta']['teach_with_us_heading']   ?? '',  33, stripHtml: false);
        }

        if (($data['slug'] ?? '') === 'contact') {
            $this->enforceMaxLength('Email Line',       $data['meta']['email_line']  ?? '',  80, stripHtml: false);
            $this->enforceMaxLength('Form Prompt Text', $data['meta']['form_prompt'] ?? '',  60, stripHtml: false);
            $this->enforceMaxLength('Column 1 Heading', $data['meta']['col1_heading'] ?? '', 50, stripHtml: false);
            $this->enforceMaxLength('Column 1 Text',    $data['meta']['col1_text']    ?? '', 80, stripHtml: false);
            $this->enforceMaxLength('Column 2 Heading', $data['meta']['col2_heading'] ?? '', 50, stripHtml: false);
            $this->enforceMaxLength('Column 3 Heading', $data['meta']['col3_heading'] ?? '', 50, stripHtml: false);
            $this->enforceMaxLength('Form Intro Text',  $data['meta']['form_intro']   ?? '', 150, stripHtml: false);
        }

        if (($data['slug'] ?? '') === 'feature-counselling-therapy') {
            $this->enforceMaxLength('Quote',            $data['meta']['quote']          ?? '', 200, stripHtml: false);
            $this->enforceMaxLength('Paragraph',        $data['meta']['therapy_para']   ?? '', 450, stripHtml: false);
            $this->enforceMaxLength('Faith Button Text', $data['meta']['faith_btn_text'] ?? '',  80, stripHtml: false);
        }

        if (($data['slug'] ?? '') === 'feature-teachers-training') {
            $this->enforceMaxLength('Intro Paragraph',        $data['meta']['intro_para']       ?? '', 350, stripHtml: false);
            $this->enforceMaxLength('Offer Heading',          $data['meta']['offer_heading']     ?? '',  60, stripHtml: false);
            $this->enforceMaxLength('Offer Point 1',          $data['meta']['offer_point_1']     ?? '', 150, stripHtml: false);
            $this->enforceMaxLength('Offer Point 2',          $data['meta']['offer_point_2']     ?? '', 400, stripHtml: false);
            $this->enforceMaxLength('Section 1 Heading',      $data['meta']['section1_heading']  ?? '', 100, stripHtml: false);
            $this->enforceMaxLength('Section 1 Paragraph 1',  $data['meta']['section1_para_1']   ?? '', 400, stripHtml: false);
            $this->enforceMaxLength('Section 1 Paragraph 2',  $data['meta']['section1_para_2']   ?? '', 200, stripHtml: false);
            $this->enforceMaxLength('List Heading',           $data['meta']['list_heading']       ?? '', 200, stripHtml: false);
            $this->enforceMaxLength('Custom Courses Paragraph', $data['meta']['custom_courses_para'] ?? '', 150, stripHtml: false);
            $this->enforceMaxLength('SCA Contact Paragraph',  $data['meta']['sca_contact_para']  ?? '', 200, stripHtml: false);
            $this->enforceMaxLength('Section 2 Heading',      $data['meta']['section2_heading']  ?? '', 100, stripHtml: false);
            $this->enforceMaxLength('License Paragraph',      $data['meta']['license_para']      ?? '', 350, stripHtml: false);
        }

        if (($data['slug'] ?? '') === 'audio-stories') {
            $this->enforceMaxLength('Intro Paragraph',          $data['meta']['intro_para']       ?? '', 300, stripHtml: false);
            $this->enforceMaxLength('Audio Label',              $data['meta']['audio_label']      ?? '',  60, stripHtml: false);
            $this->enforceMaxLength('Audio Title',              $data['meta']['audio_title']      ?? '',  50, stripHtml: false);
            $this->enforceMaxLength('Quote',                    $data['meta']['quote']            ?? '', 500, stripHtml: false);
            $this->enforceMaxLength('Citation',                 $data['meta']['citation_1']       ?? '', 150, stripHtml: false);
            $this->enforceMaxLength('Narrative Paragraph',      $data['meta']['narrative_para']   ?? '', 750, stripHtml: false);
            $this->enforceMaxLength('Citation 2',               $data['meta']['citation_2']       ?? '', 300, stripHtml: false);
            $this->enforceMaxLength('Imagine Paragraph',        $data['meta']['imagine_para']     ?? '', 300, stripHtml: false);
            $this->enforceMaxLength('Blue Quote',               $data['meta']['blue_quote']       ?? '', 350, stripHtml: false);
            $this->enforceMaxLength('Citation 3',               $data['meta']['citation_3']       ?? '',  50, stripHtml: false);
            $this->enforceMaxLength('Availability Line',        $data['meta']['availability_line'] ?? '', 100, stripHtml: false);
        }

        foreach ($data['meta']['sections'] ?? [] as $index => $section) {
            $this->enforceMaxLength(
                'Section Heading #' . ($index + 1),
                $section['heading'] ?? '',
                50,
                stripHtml: false,
            );
        }

        if ($this->record?->content) {
            $this->enforceMaxLength(
                'Body Content',
                $data['content'] ?? '',
                mb_strlen(strip_tags($this->record->content)),
            );
        }

        $savedAuthor = $this->record?->meta['author_content'] ?? null;
        if ($savedAuthor) {
//            $this->enforceMaxLength(
//                'Author Content',
//                $data['meta']['author_content'] ?? '',
//                mb_strlen(strip_tags($savedAuthor)),
//            );
        }

        return $data;
    }

    private function enforceMaxLength(string $label, mixed $value, int $max, bool $stripHtml = true): void
    {
        $text  = is_string($value) ? $value : '';
        $count = mb_strlen($stripHtml ? strip_tags($text) : $text);

        if ($count <= $max) {
            return;
        }

        Notification::make()
            ->title("{$label} too long")
            ->body("Must not exceed {$max} characters (current: {$count}).")
            ->danger()
            ->persistent()
            ->send();

        throw new Halt();
    }
}
