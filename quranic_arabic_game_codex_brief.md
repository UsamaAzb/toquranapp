# Quranic Arabic Reading & Spelling Games — Technical Findings, Report, and Implementation Recommendations

**Prepared for:** Laravel / MariaDB implementation using the existing English spelling-game design  
**Target user:** Codex / AI coding agent + project developer  
**Date:** 2026-06-14  
**Project context:** Build Arabic reading and spelling games related to Qur'anic Arabic meaning, using Uthmani script, Quranic symbols, tashkeel, and word-level pronunciation.

---

## 1. Executive Summary

The project is technically possible, but it must be implemented carefully because Qur'anic Arabic has three sensitive layers:

1. **Religious/textual accuracy**  
   The Qur'an text must remain unchanged. Any game variant such as a word with a missing letter, missing tashkeel, or wrong answer option must be treated as an *exercise*, not as Qur'an text.

2. **Unicode complexity**  
   Uthmani script contains normal Arabic letters, harakat, Qur'anic annotation marks, dagger alif, madd signs, special sukun-like marks, hamzat wasl, pause marks, sajdah signs, and other combining marks. Laravel/PHP/MariaDB can handle them, but only with correct UTF-8, normalization, and comparison rules.

3. **Licensing and storage**  
   Quran Foundation / Quran.com APIs are excellent for word-by-word data and word audio, but their developer terms restrict long-term caching/storage of QF Content unless expressly permitted. Tanzil is safer as a canonical local Qur'an-text source because it explicitly provides downloadable text under CC BY 3.0 with attribution and no modification.

**Recommended first implementation:**

- Use **Quran Foundation API** for a proof of concept and word-by-word audio.
- Use **Tanzil Uthmani / Uthmani Minimal** as the long-term canonical local text source.
- Store generated game variants separately from canonical Qur'an text.
- Use **QPC Hafs Unicode rendering** first, not QCF V2 page-based fonts.
- Build only three first game modes:
  1. Listen and choose the correct word.
  2. Missing tashkeel.
  3. Missing letter.
- Delay tajweed coloring, full Mushaf page rendering, ayah timing/highlighting, and advanced morphology until the base game is working.

---

## 2. Core Questions and Findings

### 2.1 Can we get ready Qur'anic words with tashkeel and without tashkeel?

**Yes.**

There are several possible sources:

| Source | What it gives | Best use | Main caution |
|---|---|---|---|
| Quran Foundation / Quran.com Content API | Word-level Uthmani, Imla'ei, translations, transliteration, audio paths | Proof of concept, word audio, word metadata | Do not store/cache QF Content for more than 1 week unless permitted |
| Tanzil | Downloadable Qur'an text in Simple, Uthmani, Uthmani Minimal, XML, SQL, text formats | Canonical local Qur'an text source | Must not modify the text; attribution required |
| Quranic Arabic Corpus | Morphology, syntax, grammar, word-level analysis | Later advanced meaning/grammar features | GPL and terms; not primarily an audio source |
| Quran-MD / research datasets | Word-level text/audio research dataset | Research exploration only | Must verify license before product use |

### 2.2 Can Laravel handle Uthmani symbols?

**Yes.**

Laravel/PHP can handle Uthmani script if:

- all files are UTF-8;
- database uses `utf8mb4`;
- exact text columns use a binary or exact collation when needed;
- PHP `intl` extension is enabled;
- text is normalized before comparison;
- `strlen()` and naive `substr()` are avoided for Arabic text;
- generated masked/missing-character versions are created using Unicode-aware methods.

### 2.3 Can we get word pronunciation like Quran.com?

**Yes.**

Quran Foundation word objects can include `audio_url`, for example:

```text
wbw/001_001_001.mp3
```

This is a relative word-by-word audio path. The SDK documentation says to resolve it against:

```text
https://audio.qurancdn.com/
```

So the full URL becomes:

```text
https://audio.qurancdn.com/wbw/001_001_001.mp3
```

This is suitable for the first game mode: **play word audio, then ask the learner to choose the correct spelling**.

---

## 3. Source Research and Links

### 3.1 Quran Foundation / Quran.com Content API

**Use for:** word-by-word API data, Uthmani word text, Imla'ei word text, translation, transliteration, word audio path, page/line metadata, QCF glyph codes if needed later.

Main docs:

- Verse by key endpoint:  
  https://api-docs.quran.foundation/docs/content_apis_versioned/4.0.0/verses-by-verse-key/

- JavaScript SDK / Verses API and word audio path explanation:  
  https://api-docs.quran.foundation/docs/sdk/javascript/verses/

- Font rendering guide:  
  https://api-docs.quran.com/docs/tutorials/fonts/font-rendering/

- Developer terms:  
  https://api-docs.quran.foundation/legal/developer-terms/

Important API capabilities:

- `words=true` includes word-level data.
- `word_fields` controls which word-level fields are returned.
- Word-level fields can include:
  - `text_uthmani`
  - `text_imlaei`
  - `text_qpc_hafs`
  - `text_indopak`
  - `audio_url`
  - `location`
  - `code_v1`
  - `code_v2`
  - translation object
  - transliteration object
- `char_type_name` may identify normal words, end markers, pause marks, etc.
- `audio_url` is relative, not a normal API endpoint.
- Production use should call the API from the backend, not expose credentials in frontend code.

### 3.2 Quran Foundation Licensing / Storage Warning

Important developer terms:

- QF Content may be displayed inside the application if the Qur'an text is not modified.
- QF Content must not be resold, sublicensed, or redistributed except as integral to the app user experience.
- Commercial redistribution or use of raw API data requires a separate written commercial license.
- Developer must not cache or store QF Content longer than 1 week unless expressly permitted.
- QF may suspend/revoke credentials if terms are breached.

**Practical recommendation:**

For a commercial or semi-commercial LMS product, do **not** treat Quran Foundation as the permanent local Qur'an database unless written permission is obtained. Use it for API-powered enrichment, testing, and word audio, or request permission for production storage.

### 3.3 Tanzil

**Use for:** permanent local canonical Qur'an text source.

Download page:

- https://tanzil.net/pub/download/v1.0/

Tanzil provides:

- Simple
- Simple Enhanced
- Simple Minimal
- Simple Clean
- Uthmani
- Uthmani Minimal
- Options for pause marks, sajdah signs, rub-el-hizb signs, superscript alefs, different tanween shapes
- Text, text with ayah numbers, XML, SQL/MySQL dump

Tanzil terms include:

- Creative Commons Attribution 3.0 License.
- Verbatim copying/distribution is allowed.
- Changing the Qur'an text is not allowed.
- Can be used in websites/applications with clear source indication and a link to Tanzil.
- Copyright notice must be included in substantial copies/derived files.

**Practical recommendation:**

Use Tanzil as the production canonical text table, then generate game exercises separately.

### 3.4 Quranic Arabic Corpus

**Use for:** later morphology, roots, lemmas, grammar, syntax, word-by-word meaning improvements.

Links:

- Main site:  
  https://corpus.quran.com/

- Download page:  
  https://corpus.quran.com/download/

Findings:

- Provides morphological annotation, syntactic treebank, and semantic ontology.
- Builds on Tanzil verified Arabic text.
- Data download page references GNU General Public License and usage terms.
- The annotation can be used in a website/application with source attribution and link back.

**Practical recommendation:**

Do not make this part of version 1. Use it later for advanced meaning, vocabulary categories, grammar games, roots, and lemmas.

### 3.5 PHP Unicode Normalization

**Use for:** consistent Arabic/Uthmani comparison and storage.

Docs:

- https://www.php.net/manual/en/class.normalizer.php

Key point:

PHP `Normalizer` transforms Unicode text into consistent normalization forms. This is important for comparison, searching, and storage.

Recommended form for this project:

```php
Normalizer::FORM_C // NFC
```

### 3.6 MariaDB Character Sets and Collations

Docs:

- https://mariadb.com/docs/server/reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations

Important points:

- MariaDB supports `utf8mb4`.
- `utf8mb4` supports up to 4 bytes per character.
- `ci` collations are case-insensitive.
- `cs` collations are case-sensitive.
- Accent-insensitive collations may treat accented/unaccented letters as identical for sorting/comparison.
- For exact Qur'anic text matching, prefer binary/exact comparisons for canonical columns.

Recommended practical default:

```text
utf8mb4
utf8mb4_bin
```

Use `utf8mb4_bin` for exact symbol-sensitive columns such as Uthmani words.

---

## 4. Recommended Product Scope

### 4.1 First Game Modes

#### Game Mode 1: Listen and Choose the Correct Word

Flow:

1. System selects a word.
2. Audio plays.
3. Student chooses from 3 or 4 word options.
4. Correct answer is checked against `quran_words.id`, not only raw string text.

Example:

```text
Audio: https://audio.qurancdn.com/wbw/001_001_001.mp3
Options:
- بِسْمِ
- ٱللَّهِ
- ٱلرَّحْمَـٰنِ
- ٱلرَّحِيمِ
```

#### Game Mode 2: Missing Tashkeel

Flow:

1. Show a word with missing or simplified diacritics.
2. Student chooses the correct fully marked word.

Example:

```text
Prompt: بسم
Correct: بِسْمِ
Wrong options:
- بَسْمُ
- بِسَمِ
- بُسْمَ
```

Important:

- Wrong options must be generated carefully.
- Do not accidentally create offensive or misleading Qur'anic fragments.
- Label them clearly as exercise options, not Qur'an text.

#### Game Mode 3: Missing Letter

Flow:

1. Show a word with a missing letter.
2. Student chooses the missing letter or the full correct word.

Example:

```text
Prompt: ٱلرَّحْمَـٰـ
Correct missing letter: ن
Correct full word: ٱلرَّحْمَـٰنِ
```

Important:

- Do not split combining marks incorrectly.
- Do not remove part of a letter + its marks in a way that breaks display.

### 4.2 Later Game Modes

Delay these until after the first version:

- meaning match;
- root/lemma match;
- tajweed-symbol recognition;
- madd/sukun recognition;
- listen and arrange words;
- ayah-level audio with word highlighting;
- full Mushaf page rendering;
- teacher/admin custom game builder.

---

## 5. Recommended First Content Scope

Do not start with the full Qur'an. Start with curated, safe, familiar, short content.

### Phase 1 Surahs

- Al-Fatihah
- Al-Ikhlas
- Al-Falaq
- An-Nas
- selected short, common words from Juz Amma

### Level Design

| Level | Focus | Example words |
|---|---|---|
| Level 1 | Short familiar words | قُلْ، رَبِّ، فِي، لَا، هُوَ |
| Level 2 | Common meaning words | شَمْس، قَمَر، لَيْل، نَهَار، مَاء، نَار |
| Level 3 | Tashkeel accuracy | مَلِكِ، يَوْمِ، نَعْبُدُ، نَسْتَعِينُ |
| Level 4 | Uthmani-specific symbols | ٱللَّه، ٱلرَّحْمَـٰن، هَـٰذَا، ذَٰلِكَ |
| Level 5 | Meaning and spelling together | word audio + meaning + correct spelling |

### Meaning Categories

Add a manually reviewed category layer:

- Allah's names / attributes
- People
- Places
- Nature
- Time
- Actions
- Commands
- Objects
- Emotions
- Worship terms
- Moral concepts

Do not rely only on automatic translation for children. Curate meanings manually.

---

## 6. Data Architecture

### 6.1 Core Principle

Separate:

1. **Canonical source text**
2. **Word metadata**
3. **Audio metadata**
4. **Game-generated variants**
5. **Student attempts**

Never overwrite or mutate canonical Qur'an text.

### 6.2 Recommended Tables

#### `quran_sources`

Tracks source and license.

```text
id
name
source_type                // tanzil, quran_foundation, corpus, manual
url
license_name
license_url
terms_summary
requires_attribution
cache_limit_days
notes
created_at
updated_at
```

#### `quran_surahs`

```text
id
surah_number
name_ar
name_en
ayah_count
revelation_place
created_at
updated_at
```

#### `quran_ayahs`

```text
id
surah_number
ayah_number
verse_key                  // 1:1
text_uthmani
text_uthmani_minimal
text_imlaei
text_plain
source_id
checksum_sha256
created_at
updated_at
```

#### `quran_words`

```text
id
surah_number
ayah_number
word_position
verse_key
location                   // e.g. 1:1:1
text_uthmani
text_qpc_hafs
text_imlaei
text_plain
text_skeleton
char_type_name             // word, pause, end, sajdah
page_number
line_number
difficulty_level
meaning_category
is_teachable
is_active
source_id
created_at
updated_at
```

#### `quran_word_audios`

```text
id
quran_word_id
audio_provider             // quran_foundation
audio_path                 // wbw/001_001_001.mp3
audio_url                  // generated full URL or stored remote URL
reciter_name
license_notes
source_id
expires_at                 // important if using QF cached data
created_at
updated_at
```

#### `quran_word_meanings`

```text
id
quran_word_id
language_code              // en, ar
meaning_short
meaning_child_friendly
translation_source
review_status              // draft, reviewed, approved
reviewed_by
created_at
updated_at
```

#### `quran_game_questions`

```text
id
question_type              // audio_choose_word, missing_tashkeel, missing_letter
quran_word_id
prompt_text
prompt_audio_url
correct_answer_text
correct_answer_value
difficulty_level
skill_focus                // listening, tashkeel, letter_recognition, meaning
created_by                 // system, admin
review_status
is_active
created_at
updated_at
```

#### `quran_game_options`

```text
id
quran_game_question_id
option_text
option_value
is_correct
sort_order
created_at
updated_at
```

#### `quran_game_attempts`

```text
id
user_id
quran_game_question_id
selected_option_id
is_correct
response_time_ms
attempted_at
created_at
updated_at
```

---

## 7. Minimal Phase 1 Table

For a very fast proof of concept, create one table first:

```text
quran_game_words
```

Fields:

```text
id
surah_number
ayah_number
word_position
verse_key
location
word_uthmani
word_qpc_hafs
word_imlaei
word_plain
word_translation_en
word_translation_ar
transliteration
audio_path
audio_url
difficulty_level
skill_type
meaning_category
is_active
created_at
updated_at
```

This lets the existing English game code be adapted quickly.

Later, refactor into normalized tables.

---

## 8. Laravel / MariaDB Implementation Recommendations

### 8.1 Database Charset and Collation

In Laravel migration:

```php
Schema::create('quran_words', function (Blueprint $table) {
    $table->id();

    $table->unsignedSmallInteger('surah_number');
    $table->unsignedSmallInteger('ayah_number');
    $table->unsignedSmallInteger('word_position');

    $table->string('verse_key', 10);
    $table->string('location', 20)->nullable();

    // Exact text fields: preserve every symbol.
    $table->text('text_uthmani')->charset('utf8mb4')->collation('utf8mb4_bin')->nullable();
    $table->text('text_qpc_hafs')->charset('utf8mb4')->collation('utf8mb4_bin')->nullable();
    $table->text('text_imlaei')->charset('utf8mb4')->collation('utf8mb4_bin')->nullable();

    // Generated normalized/search fields.
    $table->string('text_plain', 255)->charset('utf8mb4')->collation('utf8mb4_bin')->nullable();
    $table->string('text_skeleton', 255)->charset('utf8mb4')->collation('utf8mb4_bin')->nullable();

    $table->string('char_type_name', 30)->nullable();
    $table->unsignedSmallInteger('page_number')->nullable();
    $table->unsignedSmallInteger('line_number')->nullable();

    $table->unsignedTinyInteger('difficulty_level')->default(1);
    $table->string('meaning_category', 80)->nullable();

    $table->boolean('is_teachable')->default(true);
    $table->boolean('is_active')->default(true);

    $table->timestamps();

    $table->unique(['surah_number', 'ayah_number', 'word_position'], 'quran_word_unique_position');
    $table->index(['verse_key']);
    $table->index(['difficulty_level', 'is_active']);
});
```

### 8.2 Avoid These Mistakes

Do **not**:

```php
strlen($arabicWord)
substr($arabicWord, 0, 1)
str_replace(['َ','ُ','ِ'], '', $word) // too limited
```

Use:

```php
mb_strlen($word, 'UTF-8')
mb_substr($word, 0, 1, 'UTF-8')
Normalizer::normalize($word, Normalizer::FORM_C)
preg_replace('/.../u', '', $word)
```

For missing-letter games, ideally use grapheme-aware functions if available:

```php
grapheme_strlen($word)
grapheme_substr($word, $start, $length)
```

However, be careful: Arabic letters plus combining marks may not always behave exactly like pedagogical “letters.” For Qur'anic learning, a custom tokenizer may eventually be needed.

---

## 9. Unicode Handling Plan

### 9.1 Store Multiple Forms

For each word, store:

```text
text_uthmani      // full Uthmani script from source/API
text_qpc_hafs     // best for simple Quran.com-like Unicode rendering
text_imlaei       // modern Arabic writing style
text_plain        // generated: letters without marks
text_skeleton     // generated: optional form for matching
```

### 9.2 Normalize Before Comparing

Create a service:

```php
<?php

namespace App\Services\Quran;

use Normalizer;

class QuranTextService
{
    public function normalize(string $text): string
    {
        if (class_exists(Normalizer::class)) {
            $normalized = Normalizer::normalize($text, Normalizer::FORM_C);
            return $normalized === false ? $text : $normalized;
        }

        return $text;
    }

    public function stripTatweel(string $text): string
    {
        return preg_replace('/\x{0640}/u', '', $text) ?? $text;
    }

    /**
     * Removes common Arabic vowel marks, tanween, shadda, sukun, madd/hamza marks.
     * Use for normal Arabic spelling comparison.
     */
    public function stripBasicHarakat(string $text): string
    {
        $text = $this->normalize($text);

        return preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text) ?? $text;
    }

    /**
     * Removes broader Qur'anic annotation marks.
     * Use only when the game needs a simplified/plain version.
     * Do NOT use this to alter canonical Qur'an text.
     */
    public function stripQuranMarks(string $text): string
    {
        $text = $this->normalize($text);

        return preg_replace(
            '/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{08D3}-\x{08FF}]/u',
            '',
            $text
        ) ?? $text;
    }

    public function plainForSearch(string $text): string
    {
        $text = $this->stripQuranMarks($text);
        $text = $this->stripTatweel($text);

        return $this->normalize($text);
    }
}
```

### 9.3 Important Unicode Notes

- `ٱ` (alef wasla, U+0671) is a letter. Do not strip it by default.
- `ٰ` (dagger alif, U+0670) is a mark. Strip only when generating plain forms.
- `ـ` (tatweel/kashida, U+0640) is not a letter. Strip it in plain/search forms.
- Quranic pause signs and annotation marks are not normal letters. Do not include them in spelling-option answers unless the learning objective is specifically about Uthmani signs.
- `char_type_name != "word"` should usually be excluded from word-spelling questions.

---

## 10. Font and Rendering Plan

### 10.1 Recommended Version 1 Rendering

Use **QPC Hafs Unicode** / `text_qpc_hafs` first.

Reason:

- easiest implementation;
- no page-based font loading;
- works like normal Unicode text;
- suitable for word-level game options;
- fewer problems than QCF glyph codes.

Basic CSS:

```css
@font-face {
    font-family: 'UthmanicHafs';
    src: url('/fonts/UthmanicHafs1Ver18.woff2') format('woff2');
    font-display: swap;
}

.quran-word {
    font-family: 'UthmanicHafs', 'Traditional Arabic', serif;
    direction: rtl;
    unicode-bidi: isolate;
    font-size: 2.5rem;
    line-height: 2;
}
```

In Blade:

```blade
<span class="quran-word" dir="rtl" lang="ar">
    {{ $word->text_qpc_hafs ?? $word->text_uthmani }}
</span>
```

### 10.2 Later Rendering Options

| Rendering type | API field | Use when |
|---|---|---|
| QPC Hafs Unicode | `text_qpc_hafs` | simple word/ayah display |
| Uthmani Unicode | `text_uthmani` | standard Uthmani display |
| QCF V2 | `code_v2` + `page_number` | pixel-perfect Mushaf rendering |
| QCF V4 Tajweed | `code_v2` + `page_number` | colored tajweed display |

Do not use QCF V2 or V4 in version 1 unless absolutely necessary.

---

## 11. Quran Foundation API Integration in Laravel

### 11.1 Environment Variables

Add to `.env`:

```env
QF_API_BASE=https://apis.quran.foundation/content/api/v4
QF_AUDIO_BASE=https://audio.qurancdn.com
QF_CLIENT_ID=
QF_ACCESS_TOKEN=
```

Note: Proper production implementation should manage the token server-side. Do not expose secrets or client credentials in frontend JavaScript.

### 11.2 Laravel Service Example

```php
<?php

namespace App\Services\Quran;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class QuranFoundationClient
{
    public function __construct(
        private readonly string $apiBase = '',
        private readonly string $audioBase = ''
    ) {}

    private function baseUrl(): string
    {
        return config('services.quran_foundation.api_base', env('QF_API_BASE'));
    }

    private function audioBaseUrl(): string
    {
        return rtrim(config('services.quran_foundation.audio_base', env('QF_AUDIO_BASE')), '/');
    }

    public function verseByKey(string $verseKey): array
    {
        $response = Http::withHeaders([
                'x-client-id' => config('services.quran_foundation.client_id', env('QF_CLIENT_ID')),
                'x-auth-token' => config('services.quran_foundation.access_token', env('QF_ACCESS_TOKEN')),
            ])
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 500)
            ->get($this->baseUrl() . '/verses/by_key/' . $verseKey, [
                'words' => 'true',
                'word_fields' => 'text_uthmani,text_imlaei,text_qpc_hafs,text_uthmani_simple,audio_url,location,code_v1,code_v2',
                'fields' => 'text_uthmani,text_imlaei,text_uthmani_simple',
                'language' => 'en',
            ]);

        $response->throw();

        return $response->json();
    }

    public function resolveWordAudioUrl(?string $audioPath): ?string
    {
        if (!$audioPath) {
            return null;
        }

        $cleanPath = ltrim($audioPath, '/');

        return $this->audioBaseUrl() . '/' . $cleanPath;
    }
}
```

### 11.3 Import Command Example

```php
<?php

namespace App\Console\Commands;

use App\Models\QuranWord;
use App\Services\Quran\QuranFoundationClient;
use App\Services\Quran\QuranTextService;
use Illuminate\Console\Command;

class ImportQuranFoundationVerseWords extends Command
{
    protected $signature = 'quran:import-qf-verse {verseKey}';
    protected $description = 'Import Quran Foundation word data for one verse for proof-of-concept use.';

    public function handle(
        QuranFoundationClient $client,
        QuranTextService $textService
    ): int {
        $verseKey = $this->argument('verseKey');

        [$surahNumber, $ayahNumber] = array_map('intval', explode(':', $verseKey));

        $payload = $client->verseByKey($verseKey);
        $verse = $payload['verse'] ?? null;

        if (!$verse) {
            $this->error('Verse not found in API response.');
            return self::FAILURE;
        }

        foreach (($verse['words'] ?? []) as $word) {
            if (($word['char_type_name'] ?? null) !== 'word') {
                continue;
            }

            $position = (int) ($word['position'] ?? 0);
            $uthmani = $word['text_uthmani'] ?? null;
            $qpcHafs = $word['text_qpc_hafs'] ?? null;
            $imlaei = $word['text_imlaei'] ?? null;

            QuranWord::updateOrCreate(
                [
                    'surah_number' => $surahNumber,
                    'ayah_number' => $ayahNumber,
                    'word_position' => $position,
                ],
                [
                    'verse_key' => $verseKey,
                    'location' => $word['location'] ?? "{$surahNumber}:{$ayahNumber}:{$position}",
                    'text_uthmani' => $uthmani,
                    'text_qpc_hafs' => $qpcHafs,
                    'text_imlaei' => $imlaei,
                    'text_plain' => $textService->plainForSearch($imlaei ?: $uthmani ?: ''),
                    'text_skeleton' => $textService->plainForSearch($uthmani ?: $imlaei ?: ''),
                    'char_type_name' => $word['char_type_name'] ?? null,
                    'page_number' => $word['page_number'] ?? null,
                    'line_number' => $word['line_number'] ?? null,
                    'difficulty_level' => 1,
                    'is_teachable' => true,
                    'is_active' => true,
                ]
            );
        }

        $this->info("Imported words for verse {$verseKey}.");

        return self::SUCCESS;
    }
}
```

**Important:** This command is suitable for proof of concept. For production, check QF storage/caching permission first.

---

## 12. Audio Implementation Plan

### 12.1 First Version

Use remote word audio URLs:

```text
https://audio.qurancdn.com/wbw/001_001_001.mp3
```

Frontend:

```html
<audio id="word-audio" preload="none">
    <source src="https://audio.qurancdn.com/wbw/001_001_001.mp3" type="audio/mpeg">
</audio>
```

JavaScript:

```js
async function playWordAudio(audioUrl) {
    const audio = new Audio(audioUrl);
    audio.preload = 'auto';
    await audio.play();
}
```

### 12.2 Do Not Download Audio Permanently Without Permission

For production, confirm:

- Can the project stream QF/QR CDN audio directly?
- Can it cache audio?
- How long?
- Is commercial/paid LMS use allowed?
- Is attribution required?
- Is separate audio licensing needed?

Until written permission is clear, store only `audio_path` and resolve to remote URL dynamically.

---

## 13. Game Generation Logic

### 13.1 Question Type: Audio Choose Word

Generation rules:

1. Select active teachable word.
2. Use `quran_word_audios.audio_url` or resolved remote audio URL.
3. Correct answer = selected `quran_words.id`.
4. Distractors:
   - same difficulty level;
   - similar length;
   - ideally same surah or same lesson;
   - not identical after stripping marks;
   - avoid words with completely different difficulty;
   - avoid non-word markers.

Pseudo-code:

```php
$correct = QuranWord::active()->teachable()->inRandomOrder()->first();

$distractors = QuranWord::query()
    ->where('id', '!=', $correct->id)
    ->where('difficulty_level', $correct->difficulty_level)
    ->where('is_active', true)
    ->where('is_teachable', true)
    ->inRandomOrder()
    ->limit(3)
    ->get();
```

### 13.2 Question Type: Missing Tashkeel

Correct answer:

```php
$correctText = $word->text_qpc_hafs ?? $word->text_uthmani;
```

Prompt:

```php
$promptText = $quranTextService->stripBasicHarakat($correctText);
```

Distractors:

- use real words where possible;
- or generate controlled artificial variants only if clearly marked as answer options;
- never store generated distractors as canonical Qur'an text.

### 13.3 Question Type: Missing Letter

Do not naively remove a byte or code point.

Recommended safe first version:

- Use `text_plain` or `text_imlaei` for missing-letter exercises first.
- Keep full Uthmani for display-only option.
- Avoid words with complex Uthmani symbols in early missing-letter levels.

Example safe logic:

```php
$plain = $word->text_plain;

$length = mb_strlen($plain, 'UTF-8');

if ($length < 3) {
    // skip for missing-letter mode
}

$removeIndex = random_int(1, $length - 2);
$missingLetter = mb_substr($plain, $removeIndex, 1, 'UTF-8');

$prompt = mb_substr($plain, 0, $removeIndex, 'UTF-8')
    . 'ـ'
    . mb_substr($plain, $removeIndex + 1, null, 'UTF-8');
```

Later, improve this with grapheme/token clustering.

---

## 14. API Endpoints for the Game

### 14.1 Get Next Question

```http
GET /api/quran-games/next?mode=audio_choose_word&level=1
```

Response:

```json
{
  "question_id": 123,
  "mode": "audio_choose_word",
  "prompt_text": null,
  "prompt_audio_url": "https://audio.qurancdn.com/wbw/001_001_001.mp3",
  "options": [
    { "id": 1, "text": "بِسْمِ" },
    { "id": 2, "text": "ٱللَّهِ" },
    { "id": 3, "text": "ٱلرَّحْمَـٰنِ" },
    { "id": 4, "text": "ٱلرَّحِيمِ" }
  ]
}
```

### 14.2 Submit Answer

```http
POST /api/quran-games/answer
```

Request:

```json
{
  "question_id": 123,
  "selected_option_id": 1,
  "response_time_ms": 4200
}
```

Response:

```json
{
  "is_correct": true,
  "correct_option_id": 1,
  "feedback": "Correct"
}
```

---

## 15. Integration With Existing English Game

Since the English game already exists, reuse:

- page layout;
- game state machine;
- answer checking;
- scoring;
- attempt saving;
- levels/categories;
- audio playback UI;
- option buttons;
- feedback animations.

Change/add:

- RTL layout;
- Arabic font;
- larger word size;
- Unicode-safe answer generation;
- Quran-specific source tables;
- audio URL resolver;
- teacher/admin review before publishing a word set.

### Frontend CSS Additions

```css
.quran-game {
    direction: rtl;
    text-align: center;
}

.quran-options {
    direction: rtl;
}

.quran-option-button {
    font-family: 'UthmanicHafs', 'Traditional Arabic', serif;
    font-size: 2rem;
    line-height: 2;
    min-height: 80px;
}

.quran-prompt {
    font-family: 'UthmanicHafs', 'Traditional Arabic', serif;
    font-size: 3rem;
    line-height: 2.2;
}
```

---

## 16. Admin / Review Workflow

Because this is Qur'anic content, do not publish automatically generated content directly.

Recommended workflow:

1. Import/source words.
2. Generate proposed questions.
3. Admin/teacher reviews.
4. Optional religious/Qur'an teacher review for sensitive levels.
5. Publish question set.
6. Monitor reports and corrections.

Add fields:

```text
review_status: draft | reviewed | approved | rejected
review_notes
reviewed_by
reviewed_at
```

---

## 17. Quality Assurance Checklist

### 17.1 Text Accuracy

- [ ] Compare imported canonical text with source.
- [ ] Preserve exact Uthmani text.
- [ ] Do not modify canonical text.
- [ ] Generated exercises stored separately.
- [ ] Attribution shown in app.

### 17.2 Unicode

- [ ] Database uses `utf8mb4`.
- [ ] Exact columns use exact/binary collation where needed.
- [ ] PHP `intl` extension enabled.
- [ ] `Normalizer::normalize()` works.
- [ ] No `strlen()`/`substr()` used on Arabic text.
- [ ] Test dagger alif, hamzat wasl, shadda, sukun, madd, tanween, pause marks.

### 17.3 Audio

- [ ] Audio URL resolves correctly.
- [ ] Browser can play MP3.
- [ ] Handle 404/failed audio gracefully.
- [ ] Do not expose private API credentials.
- [ ] Do not permanently store audio without permission.

### 17.4 Game Logic

- [ ] Correct answer checks by ID.
- [ ] Distractors are not duplicates.
- [ ] Distractors are not identical after stripping marks.
- [ ] Missing-letter mode does not break Unicode.
- [ ] Wrong options are clearly exercise options, not Qur'an text.

### 17.5 UX

- [ ] RTL layout works on desktop/mobile.
- [ ] Arabic font loads.
- [ ] Word size is large enough for children.
- [ ] Audio replay button available.
- [ ] Accessibility labels included.
- [ ] Mistake feedback is gentle and educational.

---

## 18. Risks and Mitigations

| Risk | Severity | Mitigation |
|---|---:|---|
| Accidentally modifying Qur'an text | Very high | Separate canonical source text from generated game variants |
| Violating API/content license | Very high | Use Tanzil for local canonical text; get QF permission before long-term storage |
| Unicode splitting errors | High | Use normalization, `mb_*`, and later grapheme/token methods |
| Wrong audio-word mapping | High | Store surah/ayah/word position and verify sample manually |
| Poor Arabic rendering | Medium | Use QPC Hafs Unicode first; test fonts on mobile |
| Bad distractors | Medium | Use curated distractor rules and admin review |
| Children confused by Uthmani symbols too early | Medium | Level the content carefully |
| Commercial licensing uncertainty | High | Ask source providers for written permission before paid deployment |

---

## 19. Implementation Roadmap

### Sprint 1 — Proof of Concept

Goal: one working mode using a few words from Al-Fatihah.

Tasks:

- Create `quran_game_words` table.
- Add `QuranTextService`.
- Add `QuranFoundationClient`.
- Add command to fetch one verse.
- Store word text and audio paths.
- Create API endpoint to return one audio-choice question.
- Adapt existing English game UI to RTL Arabic.
- Test 10 questions manually.

Acceptance criteria:

- Audio plays.
- Student selects correct Uthmani spelling.
- Correct/incorrect result is saved.
- Arabic font renders properly.
- No API secret is exposed in browser.

### Sprint 2 — Curated Word Set

Goal: structured levels.

Tasks:

- Import selected short surahs.
- Add `difficulty_level`.
- Add `meaning_category`.
- Build admin review page.
- Add missing tashkeel game mode.
- Add missing letter game mode using `text_plain` first.
- Add attempt tracking.

Acceptance criteria:

- 50–100 reviewed words.
- At least 3 playable modes.
- Level 1 and Level 2 ready.
- Teacher/admin can deactivate bad words/questions.

### Sprint 3 — Production Source Strategy

Goal: licensing-safe production data.

Tasks:

- Import Tanzil canonical Qur'an text locally.
- Add source attribution page.
- Decide whether to request Quran Foundation commercial/storage permission.
- If permission granted, sync word metadata/audio under approved terms.
- If not, keep QF only as remote enrichment and avoid long-term storage of restricted content.
- Create correction/reporting mechanism.

Acceptance criteria:

- Sources and licenses documented in admin.
- Attribution displayed.
- Caching/storage policy implemented.
- Production data strategy approved.

### Sprint 4 — Advanced Learning

Goal: meaning-rich Qur'anic Arabic learning.

Tasks:

- Add child-friendly Arabic/English meanings.
- Add Quranic Arabic Corpus morphology/root fields if license-compatible.
- Add meaning-match games.
- Add root/word-family games.
- Add teacher-created playlists.
- Add learner analytics.

---

## 20. Codex Implementation Instructions

Codex should implement this incrementally.

### 20.1 First Prompt to Codex

Use this as the first instruction:

```text
Read this markdown file carefully. We already have an English spelling/audio choice game in Laravel. Implement a Quranic Arabic proof of concept without breaking the existing game.

Start with:
1. Laravel migration for quran_game_words.
2. QuranTextService for Unicode normalization and stripping marks.
3. QuranFoundationClient using Laravel Http facade.
4. Artisan command quran:import-qf-verse {verseKey}.
5. API endpoint GET /api/quran-games/next?mode=audio_choose_word&level=1.
6. API endpoint POST /api/quran-games/answer.
7. Minimal Blade/Livewire/Vue-compatible view changes for RTL Arabic rendering.

Do not implement advanced morphology, QCF V2 fonts, tajweed colors, or full Mushaf rendering yet.
Do not permanently cache Quran Foundation content beyond a proof-of-concept table unless the project owner confirms permission.
```

### 20.2 Codex Must Preserve Existing English Game

Codex must:

- avoid modifying existing English tables unless necessary;
- create new Quran-specific tables/services/controllers;
- reuse shared UI components where safe;
- keep Arabic-specific logic isolated;
- add tests for Unicode normalization.

### 20.3 Tests Codex Should Add

```php
test_it_preserves_uthmani_text_exactly()
test_it_strips_basic_harakat()
test_it_strips_quran_marks_for_plain_search()
test_it_does_not_strip_alef_wasla_by_default()
test_it_resolves_word_audio_url()
test_it_generates_audio_choice_question()
test_it_saves_correct_attempt()
test_it_saves_wrong_attempt()
```

---

## 21. Recommended Source Attribution Text

Display in an About/Sources page:

```text
Qur'an text source: Tanzil Project (https://tanzil.net), used under Creative Commons Attribution 3.0. The Qur'an text is copied verbatim and is not modified. Game exercises such as missing-letter or missing-tashkeel prompts are educational derivatives generated separately from the canonical Qur'an text.

Word-by-word metadata and/or audio may be powered by Quran Foundation / Quran.com APIs where permitted. See https://api-docs.quran.foundation/.
```

Adjust this depending on final source decisions.

---

## 22. Final Recommendation

Build the game, but do it in this order:

1. **Prototype with Quran Foundation API** because it gives exactly what is needed for word-by-word audio and metadata.
2. **Use QPC Hafs Unicode** for display because it is simple and good enough for a word-level game.
3. **Create a small curated word bank** instead of importing the whole Qur'an at first.
4. **Store canonical text separately** and never mutate it.
5. **Use Tanzil as the production canonical local text source** unless Quran Foundation gives written permission for long-term storage.
6. **Treat all masked/missing/wrong forms as exercise content**, not Qur'an text.
7. **Add review workflow** before publishing generated questions.
8. **Delay advanced rendering and morphology** until the base game proves useful.

---

## 23. Official / Useful Links

### Quran Foundation / Quran.com

- Content API — Verse by key:  
  https://api-docs.quran.foundation/docs/content_apis_versioned/4.0.0/verses-by-verse-key/

- SDK Verses API / Word audio paths:  
  https://api-docs.quran.foundation/docs/sdk/javascript/verses/

- Font rendering guide:  
  https://api-docs.quran.com/docs/tutorials/fonts/font-rendering/

- Developer terms:  
  https://api-docs.quran.foundation/legal/developer-terms/

- Word audio base URL:  
  https://audio.qurancdn.com/

### Tanzil

- Download page:  
  https://tanzil.net/pub/download/v1.0/

- Tanzil main site:  
  https://tanzil.net/

### Quranic Arabic Corpus

- Main site:  
  https://corpus.quran.com/

- Download page:  
  https://corpus.quran.com/download/

### PHP / Laravel / MariaDB

- PHP Normalizer:  
  https://www.php.net/manual/en/class.normalizer.php

- MariaDB character sets and collations:  
  https://mariadb.com/docs/server/reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations

- Laravel migrations:  
  https://laravel.com/docs/12.x/migrations

### Unicode Reference

- Unicode Arabic chart:  
  https://www.unicode.org/charts/PDF/U0600.pdf

- Unicode Arabic Supplement chart:  
  https://www.unicode.org/charts/PDF/U0750.pdf

- Unicode Arabic Extended-A chart:  
  https://www.unicode.org/charts/PDF/U08A0.pdf

---

## 24. Notes for the Project Owner

This project is more than a spelling game. It can become a serious Qur'anic Arabic learning module inside the LMS if designed carefully.

Best future positioning:

- not only “memorize spelling”;
- but “hear, read, recognize, and understand Qur'anic Arabic words”;
- with levels, meaning categories, pronunciation, Uthmani-script awareness, and teacher-reviewed content.

The strongest educational value will come from connecting:

```text
Audio → visual word → tashkeel → meaning → repeated recognition → Quranic context
```

This makes the game useful for children, non-Arabic speakers, and Arabic-speaking learners who can read Arabic but struggle with Qur'anic script and symbols.
