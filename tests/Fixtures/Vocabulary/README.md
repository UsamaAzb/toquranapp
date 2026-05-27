# Vocabulary Test Fixtures

Use small in-memory arrays, model factories, or stubbed service results for vocabulary tests.

Do not require live shared database data for:

- `cambradge_words_api`
- legacy Cambridge, phonics, group, difficulty, or Hangman tables
- public audio folders

Fixture payloads should include:

- a word with primary US audio
- a word with missing primary audio and dictionary fallback
- a word with owner-recording fallback
- a word with no playable audio
- curated `wrong_spelling` values in JSON and delimiter formats
- compound words with spaces and hyphens
- custom typed words with and without resolver audio

DB-backed tests may run only after the owner executes the reviewed manual SQL patch and confirms the schema is available.
