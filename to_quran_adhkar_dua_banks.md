# To Quran - Morning Adhkar, Evening Adhkar, and Dua Bank

Developer-ready starter bank for the **My Deen Journey** automated paths.

This file is designed for Codex / Laravel seeding. It is not a fiqh curriculum. The aim is to provide a safe, editable, child-friendly starter bank for Muslim families.

---

## 0. Important Product Rules

1. **Parent rating is 0-5 for the whole task**, not for each individual dua.
2. The LMS may show 1, 2, 3, 4, or a teacher-selected set depending on the learner's path version.
3. The order below is a **product learning order**, not a religious ranking.
4. Arabic text should be reviewed before launch by a qualified Islamic reviewer, especially tashkeel and source references.
5. English meanings are simple child-friendly meanings, not formal published translations.
6. Morning/evening adhkar should be selectable by teacher, parent, or routine version.
7. For Quranic passages, store `quran_ref` separately so the LMS can later use Quran.com / Quran Foundation API.
8. Avoid forcing all children to complete a full morning/evening wird at launch. Start small.

---

## 1. Suggested Database Fields

```php
// Suggested table: deen_supplication_bank
id
bank_type              // morning_adhkar, evening_adhkar, daily_dua, quranic_dua, salah_dhikr
code                   // MOR-001, EVE-001, DUA-001
level                  // L1, L2, L3, L4, L5, L6, L7
category               // protection, gratitude, sleep, food, wudu, masjid, salah, family, study, etc.
title_en
title_ar
arabic_text
english_meaning
transliteration        // optional
repeat_count_default   // 1, 3, 4, 7, 10, 33, 100, etc.
recurrence_hint        // morning, evening, bedtime, daily, specific_day, weekly, situation_based
quran_ref              // e.g. 2:255, 112:1-4
hadith_source          // e.g. Hisn al-Muslim 75; Abu Dawud 4/323
source_url
uploaded_source_note   // e.g. Morning-Adhkar PDF p.1
is_child_friendly      // true/false
min_readiness_level    // beginner, supported, independent, advanced
teacher_review_needed  // true/false
active
sort_order
```

---

## 2. Path Display Logic

### 2.1 Morning Adhkar Path

| Version | LMS Display |
|---|---|
| Morning V1 | Show 1 short item from L1. |
| Morning V2 | Show 2 items from L1-L2. |
| Morning V3 | Show 3 items from L1-L3. |
| Morning V4 | Show 4 items from L1-L4. |
| Morning V5 | Show 5 items, including one protection item. |
| Morning V6 | Show 6-7 teacher-selected items. |
| Morning V7 | Show a longer set for independent learners. |
| Morning V8 | Full teacher-selected routine. |

### 2.2 Evening Adhkar Path

| Version | LMS Display |
|---|---|
| Evening V1 | Show 1 short item from L1. |
| Evening V2 | Show 2 items from L1-L2. |
| Evening V3 | Show 3 items from L1-L3. |
| Evening V4 | Show 4 items from L1-L4. |
| Evening V5 | Show 5 items, including one protection item. |
| Evening V6 | Show 6-7 teacher-selected items. |
| Evening V7 | Show evening + bedtime items. |
| Evening V8 | Full teacher-selected routine. |

### 2.3 Dua Bank Path

| Version | LMS Display |
|---|---|
| Dua V1 | Show 1 very short daily-life dua. |
| Dua V2 | Show 2 daily-life duas. |
| Dua V3 | Show 3 daily-life duas. |
| Dua V4 | Show 4 daily-life duas. |
| Dua V5 | Show situation-based duas: food, sleep, bathroom, home, wudu. |
| Dua V6 | Add Quranic duas. |
| Dua V7 | Add salah-related dhikr and duas. |
| Dua V8 | Student uses correct duas in real life; parent rates from 0-5. |

---

# 3. Morning Adhkar Bank

> Use: morning automated path. Parent rates the whole task from 0-5.

## MOR-001 - Ayat al-Kursi

- **Level:** L1
- **Category:** protection / Quran
- **Repeat:** 1
- **Arabic:** اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ ۚ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ ۚ لَهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ ۗ مَنْ ذَا الَّذِي يَشْفَعُ عِنْدَهُ إِلَّا بِإِذْنِهِ ۚ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ ۖ وَلَا يُحِيطُونَ بِشَيْءٍ مِنْ عِلْمِهِ إِلَّا بِمَا شَاءَ ۚ وَسِعَ كُرْسِيُّهُ السَّمَاوَاتِ وَالْأَرْضَ ۖ وَلَا يَئُودُهُ حِفْظُهُمَا ۚ وَهُوَ الْعَلِيُّ الْعَظِيمُ
- **English meaning:** Allah is the Ever-Living and Sustainer. He owns and protects the heavens and the earth.
- **Quran ref:** Al-Baqarah 2:255
- **Source:** Hisn al-Muslim 75; uploaded Morning-Adhkar PDF p.1; Quran.com 2:255
- **Source URL:** https://sunnah.com/hisn:75 | https://quran.com/2/255
- **Sort order:** 1

## MOR-002 - Surah Al-Ikhlas

- **Level:** L1
- **Category:** protection / Quran
- **Repeat:** 3
- **Arabic:** بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ۝ قُلْ هُوَ اللَّهُ أَحَدٌ ۝ اللَّهُ الصَّمَدُ ۝ لَمْ يَلِدْ وَلَمْ يُولَدْ ۝ وَلَمْ يَكُنْ لَهُ كُفُوًا أَحَدٌ
- **English meaning:** Say: Allah is One, completely independent, not born and not giving birth, and none is like Him.
- **Quran ref:** Al-Ikhlas 112:1-4
- **Source:** Hisn al-Muslim 76; uploaded Morning-Adhkar PDF p.3
- **Source URL:** https://sunnah.com/hisn:76 | https://quran.com/112
- **Sort order:** 2

## MOR-003 - Surah Al-Falaq

- **Level:** L1
- **Category:** protection / Quran
- **Repeat:** 3
- **Arabic:** بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ۝ قُلْ أَعُوذُ بِرَبِّ الْفَلَقِ ۝ مِنْ شَرِّ مَا خَلَقَ ۝ وَمِنْ شَرِّ غَاسِقٍ إِذَا وَقَبَ ۝ وَمِنْ شَرِّ النَّفَّاثَاتِ فِي الْعُقَدِ ۝ وَمِنْ شَرِّ حَاسِدٍ إِذَا حَسَدَ
- **English meaning:** I seek protection with the Lord of daybreak from created evil, darkness, harmful magic, and envy.
- **Quran ref:** Al-Falaq 113:1-5
- **Source:** Hisn al-Muslim 76; uploaded Morning-Adhkar PDF p.3
- **Source URL:** https://sunnah.com/hisn:76 | https://quran.com/113
- **Sort order:** 3

## MOR-004 - Surah An-Nas

- **Level:** L1
- **Category:** protection / Quran
- **Repeat:** 3
- **Arabic:** بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ۝ قُلْ أَعُوذُ بِرَبِّ النَّاسِ ۝ مَلِكِ النَّاسِ ۝ إِلَٰهِ النَّاسِ ۝ مِنْ شَرِّ الْوَسْوَاسِ الْخَنَّاسِ ۝ الَّذِي يُوَسْوِسُ فِي صُدُورِ النَّاسِ ۝ مِنَ الْجِنَّةِ وَالنَّاسِ
- **English meaning:** I seek protection with the Lord, King, and God of people from whispering evil.
- **Quran ref:** An-Nas 114:1-6
- **Source:** Hisn al-Muslim 76; uploaded Morning-Adhkar PDF p.3
- **Source URL:** https://sunnah.com/hisn:76 | https://quran.com/114
- **Sort order:** 4

## MOR-005 - Morning Begins with Allah

- **Level:** L1
- **Category:** morning identity
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ بِكَ أَصْبَحْنَا، وَبِكَ أَمْسَيْنَا، وَبِكَ نَحْيَا، وَبِكَ نَمُوتُ، وَإِلَيْكَ النُّشُورُ
- **English meaning:** O Allah, by You we enter the morning and evening; by You we live and die, and to You is the return.
- **Source:** Hisn al-Muslim 78; uploaded Morning-Adhkar PDF p.4
- **Source URL:** https://sunnah.com/hisn:78
- **Sort order:** 5

## MOR-006 - I Am Pleased with Allah, Islam, and the Prophet ﷺ

- **Level:** L1
- **Category:** faith identity
- **Repeat:** 3
- **Arabic:** رَضِيتُ بِاللَّهِ رَبًّا، وَبِالإِسْلَامِ دِينًا، وَبِمُحَمَّدٍ ﷺ نَبِيًّا
- **English meaning:** I am pleased with Allah as my Lord, Islam as my religion, and Muhammad ﷺ as my Prophet.
- **Source:** Hisn al-Muslim 87; uploaded Morning-Adhkar PDF p.4
- **Source URL:** https://sunnah.com/hisn:87
- **Sort order:** 6

## MOR-007 - Protection by Allah's Name

- **Level:** L1
- **Category:** protection
- **Repeat:** 3
- **Arabic:** بِسْمِ اللَّهِ الَّذِي لَا يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الْأَرْضِ وَلَا فِي السَّمَاءِ، وَهُوَ السَّمِيعُ الْعَلِيمُ
- **English meaning:** In Allah's name, nothing can harm with His name in earth or heaven. He is the All-Hearing, All-Knowing.
- **Source:** Hisn al-Muslim 86
- **Source URL:** https://sunnah.com/hisn:86
- **Sort order:** 7

## MOR-008 - Ya Hayyu Ya Qayyum

- **Level:** L2
- **Category:** help / reliance
- **Repeat:** 1
- **Arabic:** يَا حَيُّ يَا قَيُّومُ، بِرَحْمَتِكَ أَسْتَغِيثُ، أَصْلِحْ لِي شَأْنِي كُلَّهُ، وَلَا تَكِلْنِي إِلَى نَفْسِي طَرْفَةَ عَيْنٍ
- **English meaning:** O Ever-Living, O Sustainer, by Your mercy I seek help. Set all my affairs right and do not leave me to myself even for a moment.
- **Source:** Hisn al-Muslim 88; StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://sunnah.com/hisn:88 | https://stepbystepsalah.com/duas/
- **Sort order:** 8

## MOR-009 - Sayyid al-Istighfar

- **Level:** L3
- **Category:** forgiveness
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ أَنْتَ رَبِّي لَا إِلَهَ إِلَّا أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ، وَأَنَا عَلَى عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ، أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ، أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ، وَأَبُوءُ بِذَنْبِي، فَاغْفِرْ لِي، فَإِنَّهُ لَا يَغْفِرُ الذُّنُوبَ إِلَّا أَنْتَ
- **English meaning:** O Allah, You are my Lord. I admit Your blessings and my sins, so forgive me; none forgives sins except You.
- **Source:** Hisn al-Muslim 79
- **Source URL:** https://sunnah.com/hisn:79
- **Sort order:** 9

## MOR-010 - Morning Dominion Belongs to Allah

- **Level:** L3
- **Category:** praise / protection
- **Repeat:** 1
- **Arabic:** أَصْبَحْنَا وَأَصْبَحَ الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ. رَبِّ أَسْأَلُكَ خَيْرَ مَا فِي هَذَا الْيَوْمِ وَخَيْرَ مَا بَعْدَهُ، وَأَعُوذُ بِكَ مِنْ شَرِّ مَا فِي هَذَا الْيَوْمِ وَشَرِّ مَا بَعْدَهُ. رَبِّ أَعُوذُ بِكَ مِنَ الْكَسَلِ وَسُوءِ الْكِبَرِ. رَبِّ أَعُوذُ بِكَ مِنْ عَذَابٍ فِي النَّارِ وَعَذَابٍ فِي الْقَبْرِ
- **English meaning:** We enter the morning while all dominion belongs to Allah. O Allah, I ask for the good of this day and seek refuge from its evil, laziness, old age, Hellfire, and grave punishment.
- **Source:** Hisn al-Muslim 77
- **Source URL:** https://sunnah.com/hisn:77
- **Sort order:** 10

## MOR-011 - Witness of Tawhid

- **Level:** L3
- **Category:** faith identity
- **Repeat:** 4
- **Arabic:** اللَّهُمَّ إِنِّي أَصْبَحْتُ أُشْهِدُكَ، وَأُشْهِدُ حَمَلَةَ عَرْشِكَ، وَمَلَائِكَتَكَ، وَجَمِيعَ خَلْقِكَ، أَنَّكَ أَنْتَ اللَّهُ لَا إِلَهَ إِلَّا أَنْتَ، وَحْدَكَ لَا شَرِيكَ لَكَ، وَأَنَّ مُحَمَّدًا عَبْدُكَ وَرَسُولُكَ
- **English meaning:** O Allah, I begin the morning calling You, Your angels, and all creation to witness that You alone are Allah and that Muhammad is Your servant and messenger.
- **Source:** Hisn al-Muslim 80
- **Source URL:** https://sunnah.com/hisn:80
- **Sort order:** 11

## MOR-012 - Thanking Allah for Blessings

- **Level:** L2
- **Category:** gratitude
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ مَا أَصْبَحَ بِي مِنْ نِعْمَةٍ أَوْ بِأَحَدٍ مِنْ خَلْقِكَ، فَمِنْكَ وَحْدَكَ لَا شَرِيكَ لَكَ، فَلَكَ الْحَمْدُ وَلَكَ الشُّكْرُ
- **English meaning:** O Allah, every blessing I or anyone has this morning is from You alone, so all praise and thanks belong to You.
- **Source:** Hisn al-Muslim 81
- **Source URL:** https://sunnah.com/hisn:81
- **Sort order:** 12

## MOR-013 - Health and Protection

- **Level:** L2
- **Category:** wellbeing
- **Repeat:** 3
- **Arabic:** اللَّهُمَّ عَافِنِي فِي بَدَنِي، اللَّهُمَّ عَافِنِي فِي سَمْعِي، اللَّهُمَّ عَافِنِي فِي بَصَرِي، لَا إِلَهَ إِلَّا أَنْتَ. اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْكُفْرِ وَالْفَقْرِ، وَأَعُوذُ بِكَ مِنْ عَذَابِ الْقَبْرِ، لَا إِلَهَ إِلَّا أَنْتَ
- **English meaning:** O Allah, grant wellbeing to my body, hearing, and sight, and protect me from disbelief, poverty, and grave punishment.
- **Source:** Hisn al-Muslim 82
- **Source URL:** https://sunnah.com/hisn:82
- **Sort order:** 13

## MOR-014 - Allah Is Sufficient for Me

- **Level:** L3
- **Category:** reliance
- **Repeat:** 7
- **Arabic:** حَسْبِيَ اللَّهُ لَا إِلَهَ إِلَّا هُوَ، عَلَيْهِ تَوَكَّلْتُ، وَهُوَ رَبُّ الْعَرْشِ الْعَظِيمِ
- **English meaning:** Allah is enough for me. There is no god but Him. I trust Him, and He is Lord of the Mighty Throne.
- **Source:** Hisn al-Muslim 83
- **Source URL:** https://sunnah.com/hisn:83
- **Sort order:** 14

## MOR-015 - Forgiveness and Wellbeing

- **Level:** L3
- **Category:** protection / wellbeing
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَفْوَ وَالْعَافِيَةَ فِي الدُّنْيَا وَالْآخِرَةِ. اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَفْوَ وَالْعَافِيَةَ فِي دِينِي وَدُنْيَايَ وَأَهْلِي وَمَالِي. اللَّهُمَّ اسْتُرْ عَوْرَاتِي، وَآمِنْ رَوْعَاتِي. اللَّهُمَّ احْفَظْنِي مِنْ بَيْنِ يَدَيَّ، وَمِنْ خَلْفِي، وَعَنْ يَمِينِي، وَعَنْ شِمَالِي، وَمِنْ فَوْقِي، وَأَعُوذُ بِعَظَمَتِكَ أَنْ أُغْتَالَ مِنْ تَحْتِي
- **English meaning:** O Allah, I ask You for forgiveness and wellbeing in this life and the next, and protection from every direction.
- **Source:** Hisn al-Muslim 84
- **Source URL:** https://sunnah.com/hisn:84
- **Sort order:** 15

## MOR-016 - Protection from Self and Shaytan

- **Level:** L4
- **Category:** protection / self-control
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ عَالِمَ الْغَيْبِ وَالشَّهَادَةِ، فَاطِرَ السَّمَاوَاتِ وَالْأَرْضِ، رَبَّ كُلِّ شَيْءٍ وَمَلِيكَهُ، أَشْهَدُ أَنْ لَا إِلَهَ إِلَّا أَنْتَ، أَعُوذُ بِكَ مِنْ شَرِّ نَفْسِي، وَمِنْ شَرِّ الشَّيْطَانِ وَشِرْكِهِ، وَأَنْ أَقْتَرِفَ عَلَى نَفْسِي سُوءًا، أَوْ أَجُرَّهُ إِلَى مُسْلِمٍ
- **English meaning:** O Allah, Knower of all things, I seek refuge in You from my own evil, the evil of Shaytan, and harming myself or another Muslim.
- **Source:** Hisn al-Muslim 85
- **Source URL:** https://sunnah.com/hisn:85
- **Sort order:** 16

## MOR-017 - Good of This Day

- **Level:** L4
- **Category:** guidance / protection
- **Repeat:** 1
- **Arabic:** أَصْبَحْنَا وَأَصْبَحَ الْمُلْكُ لِلَّهِ رَبِّ الْعَالَمِينَ. اللَّهُمَّ إِنِّي أَسْأَلُكَ خَيْرَ هَذَا الْيَوْمِ: فَتْحَهُ، وَنَصْرَهُ، وَنُورَهُ، وَبَرَكَتَهُ، وَهُدَاهُ، وَأَعُوذُ بِكَ مِنْ شَرِّ مَا فِيهِ وَشَرِّ مَا بَعْدَهُ
- **English meaning:** We enter the morning while dominion belongs to Allah. O Allah, I ask You for this day's good, victory, light, blessing, and guidance, and I seek refuge from its evil.
- **Source:** Hisn al-Muslim 89
- **Source URL:** https://sunnah.com/hisn:89
- **Sort order:** 17

## MOR-018 - Morning on Islam

- **Level:** L4
- **Category:** faith identity
- **Repeat:** 1
- **Arabic:** أَصْبَحْنَا عَلَى فِطْرَةِ الإِسْلَامِ، وَعَلَى كَلِمَةِ الإِخْلَاصِ، وَعَلَى دِينِ نَبِيِّنَا مُحَمَّدٍ ﷺ، وَعَلَى مِلَّةِ أَبِينَا إِبْرَاهِيمَ، حَنِيفًا مُسْلِمًا، وَمَا كَانَ مِنَ الْمُشْرِكِينَ
- **English meaning:** We begin the morning upon Islam, sincerity, the religion of Prophet Muhammad ﷺ, and the way of Ibrahim, upright and Muslim.
- **Source:** Hisn al-Muslim 90
- **Source URL:** https://sunnah.com/hisn:90
- **Sort order:** 18

## MOR-019 - Subhanallahi wa Bihamdih

- **Level:** L2
- **Category:** praise
- **Repeat:** 100, or teacher-adjusted 3/10 for children
- **Arabic:** سُبْحَانَ اللَّهِ وَبِحَمْدِهِ
- **English meaning:** Glory and praise belong to Allah.
- **Source:** Hisn al-Muslim 91
- **Source URL:** https://sunnah.com/hisn:91
- **Sort order:** 19

## MOR-020 - Tawhid Dhikr

- **Level:** L3
- **Category:** tawhid / praise
- **Repeat:** 10, or 1 when starting
- **Arabic:** لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ
- **English meaning:** There is no god but Allah alone. He has no partner. Dominion and praise belong to Him, and He can do all things.
- **Source:** Hisn al-Muslim 92 / 93
- **Source URL:** https://sunnah.com/hisn:92 | https://sunnah.com/hisn:93
- **Sort order:** 20

## MOR-021 - Praise by the Number of Creation

- **Level:** L4
- **Category:** praise
- **Repeat:** 3
- **Arabic:** سُبْحَانَ اللَّهِ وَبِحَمْدِهِ، عَدَدَ خَلْقِهِ، وَرِضَا نَفْسِهِ، وَزِنَةَ عَرْشِهِ، وَمِدَادَ كَلِمَاتِهِ
- **English meaning:** Glory and praise belong to Allah by the number of His creation, His pleasure, the weight of His Throne, and the extent of His words.
- **Source:** Hisn al-Muslim 94
- **Source URL:** https://sunnah.com/hisn:94
- **Sort order:** 21

## MOR-022 - Beneficial Knowledge and Accepted Deeds

- **Level:** L2
- **Category:** study / school / learning
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ إِنِّي أَسْأَلُكَ عِلْمًا نَافِعًا، وَرِزْقًا طَيِّبًا، وَعَمَلًا مُتَقَبَّلًا
- **English meaning:** O Allah, I ask You for beneficial knowledge, good provision, and accepted deeds.
- **Source:** Hisn al-Muslim 95
- **Source URL:** https://sunnah.com/hisn:95
- **Sort order:** 22

## MOR-023 - Daily Istighfar

- **Level:** L2
- **Category:** forgiveness
- **Repeat:** 100, or teacher-adjusted 3/10 for children
- **Arabic:** أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ
- **English meaning:** I seek Allah's forgiveness and turn back to Him.
- **Source:** Hisn al-Muslim 96; StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://sunnah.com/hisn:96 | https://stepbystepsalah.com/duas/
- **Sort order:** 23

## MOR-024 - Salawat on the Prophet ﷺ

- **Level:** L2
- **Category:** salawat
- **Repeat:** 10, or teacher-adjusted 1/3 for children
- **Arabic:** اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ
- **English meaning:** O Allah, send peace and blessings upon our Prophet Muhammad ﷺ.
- **Source:** Hisn al-Muslim 98; StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://sunnah.com/hisn:98 | https://stepbystepsalah.com/duas/
- **Sort order:** 24

---

# 4. Evening Adhkar Bank

> Use: evening automated path. Parent rates the whole task from 0-5.

## EVE-001 - Ayat al-Kursi

- **Level:** L1
- **Category:** protection / Quran
- **Repeat:** 1
- **Arabic:** اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ ۚ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ ۚ لَهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ ۗ مَنْ ذَا الَّذِي يَشْفَعُ عِنْدَهُ إِلَّا بِإِذْنِهِ ۚ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ ۖ وَلَا يُحِيطُونَ بِشَيْءٍ مِنْ عِلْمِهِ إِلَّا بِمَا شَاءَ ۚ وَسِعَ كُرْسِيُّهُ السَّمَاوَاتِ وَالْأَرْضَ ۖ وَلَا يَئُودُهُ حِفْظُهُمَا ۚ وَهُوَ الْعَلِيُّ الْعَظِيمُ
- **English meaning:** Allah is the Ever-Living and Sustainer. He owns and protects the heavens and the earth.
- **Quran ref:** Al-Baqarah 2:255
- **Source:** Hisn al-Muslim 75; uploaded Evening-Adhkar PDF p.1; Quran.com 2:255
- **Source URL:** https://sunnah.com/hisn:75 | https://quran.com/2/255
- **Sort order:** 1

## EVE-002 - Surah Al-Ikhlas

- **Level:** L1
- **Category:** protection / Quran
- **Repeat:** 3
- **Arabic:** بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ۝ قُلْ هُوَ اللَّهُ أَحَدٌ ۝ اللَّهُ الصَّمَدُ ۝ لَمْ يَلِدْ وَلَمْ يُولَدْ ۝ وَلَمْ يَكُنْ لَهُ كُفُوًا أَحَدٌ
- **English meaning:** Say: Allah is One, completely independent, not born and not giving birth, and none is like Him.
- **Quran ref:** Al-Ikhlas 112:1-4
- **Source:** Hisn al-Muslim 76; uploaded Evening-Adhkar PDF p.3
- **Source URL:** https://sunnah.com/hisn:76 | https://quran.com/112
- **Sort order:** 2

## EVE-003 - Surah Al-Falaq

- **Level:** L1
- **Category:** protection / Quran
- **Repeat:** 3
- **Arabic:** بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ۝ قُلْ أَعُوذُ بِرَبِّ الْفَلَقِ ۝ مِنْ شَرِّ مَا خَلَقَ ۝ وَمِنْ شَرِّ غَاسِقٍ إِذَا وَقَبَ ۝ وَمِنْ شَرِّ النَّفَّاثَاتِ فِي الْعُقَدِ ۝ وَمِنْ شَرِّ حَاسِدٍ إِذَا حَسَدَ
- **English meaning:** I seek protection with the Lord of daybreak from created evil, darkness, harmful magic, and envy.
- **Quran ref:** Al-Falaq 113:1-5
- **Source:** Hisn al-Muslim 76; uploaded Evening-Adhkar PDF p.3
- **Source URL:** https://sunnah.com/hisn:76 | https://quran.com/113
- **Sort order:** 3

## EVE-004 - Surah An-Nas

- **Level:** L1
- **Category:** protection / Quran
- **Repeat:** 3
- **Arabic:** بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ۝ قُلْ أَعُوذُ بِرَبِّ النَّاسِ ۝ مَلِكِ النَّاسِ ۝ إِلَٰهِ النَّاسِ ۝ مِنْ شَرِّ الْوَسْوَاسِ الْخَنَّاسِ ۝ الَّذِي يُوَسْوِسُ فِي صُدُورِ النَّاسِ ۝ مِنَ الْجِنَّةِ وَالنَّاسِ
- **English meaning:** I seek protection with the Lord, King, and God of people from whispering evil.
- **Quran ref:** An-Nas 114:1-6
- **Source:** Hisn al-Muslim 76; uploaded Evening-Adhkar PDF p.3
- **Source URL:** https://sunnah.com/hisn:76 | https://quran.com/114
- **Sort order:** 4

## EVE-005 - Evening Begins with Allah

- **Level:** L1
- **Category:** evening identity
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ بِكَ أَمْسَيْنَا، وَبِكَ أَصْبَحْنَا، وَبِكَ نَحْيَا، وَبِكَ نَمُوتُ، وَإِلَيْكَ الْمَصِيرُ
- **English meaning:** O Allah, by You we enter the evening and morning; by You we live and die, and to You is the final return.
- **Source:** Hisn al-Muslim 78; uploaded Evening-Adhkar PDF p.4
- **Source URL:** https://sunnah.com/hisn:78
- **Sort order:** 5

## EVE-006 - I Am Pleased with Allah, Islam, and the Prophet ﷺ

- **Level:** L1
- **Category:** faith identity
- **Repeat:** 3
- **Arabic:** رَضِيتُ بِاللَّهِ رَبًّا، وَبِالإِسْلَامِ دِينًا، وَبِمُحَمَّدٍ ﷺ نَبِيًّا
- **English meaning:** I am pleased with Allah as my Lord, Islam as my religion, and Muhammad ﷺ as my Prophet.
- **Source:** Hisn al-Muslim 87; uploaded Evening-Adhkar PDF p.4
- **Source URL:** https://sunnah.com/hisn:87
- **Sort order:** 6

## EVE-007 - Protection by Allah's Name

- **Level:** L1
- **Category:** protection
- **Repeat:** 3
- **Arabic:** بِسْمِ اللَّهِ الَّذِي لَا يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الْأَرْضِ وَلَا فِي السَّمَاءِ، وَهُوَ السَّمِيعُ الْعَلِيمُ
- **English meaning:** In Allah's name, nothing can harm with His name in earth or heaven. He is the All-Hearing, All-Knowing.
- **Source:** Hisn al-Muslim 86
- **Source URL:** https://sunnah.com/hisn:86
- **Sort order:** 7

## EVE-008 - Perfect Words of Allah

- **Level:** L1
- **Category:** evening protection
- **Repeat:** 3
- **Arabic:** أَعُوذُ بِكَلِمَاتِ اللَّهِ التَّامَّاتِ مِنْ شَرِّ مَا خَلَقَ
- **English meaning:** I seek protection in Allah's perfect words from the evil of what He created.
- **Source:** Hisn al-Muslim 97; uploaded Evening-Adhkar PDF p.6
- **Source URL:** https://sunnah.com/hisn:97
- **Sort order:** 8

## EVE-009 - Ya Hayyu Ya Qayyum

- **Level:** L2
- **Category:** help / reliance
- **Repeat:** 1
- **Arabic:** يَا حَيُّ يَا قَيُّومُ، بِرَحْمَتِكَ أَسْتَغِيثُ، أَصْلِحْ لِي شَأْنِي كُلَّهُ، وَلَا تَكِلْنِي إِلَى نَفْسِي طَرْفَةَ عَيْنٍ
- **English meaning:** O Ever-Living, O Sustainer, by Your mercy I seek help. Set all my affairs right and do not leave me to myself even for a moment.
- **Source:** Hisn al-Muslim 88; StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://sunnah.com/hisn:88 | https://stepbystepsalah.com/duas/
- **Sort order:** 9

## EVE-010 - Sayyid al-Istighfar

- **Level:** L3
- **Category:** forgiveness
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ أَنْتَ رَبِّي لَا إِلَهَ إِلَّا أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ، وَأَنَا عَلَى عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ، أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ، أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ، وَأَبُوءُ بِذَنْبِي، فَاغْفِرْ لِي، فَإِنَّهُ لَا يَغْفِرُ الذُّنُوبَ إِلَّا أَنْتَ
- **English meaning:** O Allah, You are my Lord. I admit Your blessings and my sins, so forgive me; none forgives sins except You.
- **Source:** Hisn al-Muslim 79
- **Source URL:** https://sunnah.com/hisn:79
- **Sort order:** 10

## EVE-011 - Evening Dominion Belongs to Allah

- **Level:** L3
- **Category:** praise / protection
- **Repeat:** 1
- **Arabic:** أَمْسَيْنَا وَأَمْسَى الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ. رَبِّ أَسْأَلُكَ خَيْرَ مَا فِي هَذِهِ اللَّيْلَةِ وَخَيْرَ مَا بَعْدَهَا، وَأَعُوذُ بِكَ مِنْ شَرِّ مَا فِي هَذِهِ اللَّيْلَةِ وَشَرِّ مَا بَعْدَهَا. رَبِّ أَعُوذُ بِكَ مِنَ الْكَسَلِ وَسُوءِ الْكِبَرِ. رَبِّ أَعُوذُ بِكَ مِنْ عَذَابٍ فِي النَّارِ وَعَذَابٍ فِي الْقَبْرِ
- **English meaning:** We enter the evening while all dominion belongs to Allah. O Allah, I ask for the good of this night and seek refuge from its evil, laziness, old age, Hellfire, and grave punishment.
- **Source:** Hisn al-Muslim 77
- **Source URL:** https://sunnah.com/hisn:77
- **Sort order:** 11

## EVE-012 - Witness of Tawhid

- **Level:** L3
- **Category:** faith identity
- **Repeat:** 4
- **Arabic:** اللَّهُمَّ إِنِّي أَمْسَيْتُ أُشْهِدُكَ، وَأُشْهِدُ حَمَلَةَ عَرْشِكَ، وَمَلَائِكَتَكَ، وَجَمِيعَ خَلْقِكَ، أَنَّكَ أَنْتَ اللَّهُ لَا إِلَهَ إِلَّا أَنْتَ، وَحْدَكَ لَا شَرِيكَ لَكَ، وَأَنَّ مُحَمَّدًا عَبْدُكَ وَرَسُولُكَ
- **English meaning:** O Allah, I enter the evening calling You, Your angels, and all creation to witness that You alone are Allah and that Muhammad is Your servant and messenger.
- **Source:** Hisn al-Muslim 80
- **Source URL:** https://sunnah.com/hisn:80
- **Sort order:** 12

## EVE-013 - Thanking Allah for Blessings

- **Level:** L2
- **Category:** gratitude
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ مَا أَمْسَى بِي مِنْ نِعْمَةٍ أَوْ بِأَحَدٍ مِنْ خَلْقِكَ، فَمِنْكَ وَحْدَكَ لَا شَرِيكَ لَكَ، فَلَكَ الْحَمْدُ وَلَكَ الشُّكْرُ
- **English meaning:** O Allah, every blessing I or anyone has this evening is from You alone, so all praise and thanks belong to You.
- **Source:** Hisn al-Muslim 81
- **Source URL:** https://sunnah.com/hisn:81
- **Sort order:** 13

## EVE-014 - Health and Protection

- **Level:** L2
- **Category:** wellbeing
- **Repeat:** 3
- **Arabic:** اللَّهُمَّ عَافِنِي فِي بَدَنِي، اللَّهُمَّ عَافِنِي فِي سَمْعِي، اللَّهُمَّ عَافِنِي فِي بَصَرِي، لَا إِلَهَ إِلَّا أَنْتَ. اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْكُفْرِ وَالْفَقْرِ، وَأَعُوذُ بِكَ مِنْ عَذَابِ الْقَبْرِ، لَا إِلَهَ إِلَّا أَنْتَ
- **English meaning:** O Allah, grant wellbeing to my body, hearing, and sight, and protect me from disbelief, poverty, and grave punishment.
- **Source:** Hisn al-Muslim 82
- **Source URL:** https://sunnah.com/hisn:82
- **Sort order:** 14

## EVE-015 - Allah Is Sufficient for Me

- **Level:** L3
- **Category:** reliance
- **Repeat:** 7
- **Arabic:** حَسْبِيَ اللَّهُ لَا إِلَهَ إِلَّا هُوَ، عَلَيْهِ تَوَكَّلْتُ، وَهُوَ رَبُّ الْعَرْشِ الْعَظِيمِ
- **English meaning:** Allah is enough for me. There is no god but Him. I trust Him, and He is Lord of the Mighty Throne.
- **Source:** Hisn al-Muslim 83
- **Source URL:** https://sunnah.com/hisn:83
- **Sort order:** 15

## EVE-016 - Forgiveness and Wellbeing

- **Level:** L3
- **Category:** protection / wellbeing
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَفْوَ وَالْعَافِيَةَ فِي الدُّنْيَا وَالْآخِرَةِ. اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَفْوَ وَالْعَافِيَةَ فِي دِينِي وَدُنْيَايَ وَأَهْلِي وَمَالِي. اللَّهُمَّ اسْتُرْ عَوْرَاتِي، وَآمِنْ رَوْعَاتِي. اللَّهُمَّ احْفَظْنِي مِنْ بَيْنِ يَدَيَّ، وَمِنْ خَلْفِي، وَعَنْ يَمِينِي، وَعَنْ شِمَالِي، وَمِنْ فَوْقِي، وَأَعُوذُ بِعَظَمَتِكَ أَنْ أُغْتَالَ مِنْ تَحْتِي
- **English meaning:** O Allah, I ask You for forgiveness and wellbeing in this life and the next, and protection from every direction.
- **Source:** Hisn al-Muslim 84
- **Source URL:** https://sunnah.com/hisn:84
- **Sort order:** 16

## EVE-017 - Protection from Self and Shaytan

- **Level:** L4
- **Category:** protection / self-control
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ عَالِمَ الْغَيْبِ وَالشَّهَادَةِ، فَاطِرَ السَّمَاوَاتِ وَالْأَرْضِ، رَبَّ كُلِّ شَيْءٍ وَمَلِيكَهُ، أَشْهَدُ أَنْ لَا إِلَهَ إِلَّا أَنْتَ، أَعُوذُ بِكَ مِنْ شَرِّ نَفْسِي، وَمِنْ شَرِّ الشَّيْطَانِ وَشِرْكِهِ، وَأَنْ أَقْتَرِفَ عَلَى نَفْسِي سُوءًا، أَوْ أَجُرَّهُ إِلَى مُسْلِمٍ
- **English meaning:** O Allah, Knower of all things, I seek refuge in You from my own evil, the evil of Shaytan, and harming myself or another Muslim.
- **Source:** Hisn al-Muslim 85
- **Source URL:** https://sunnah.com/hisn:85
- **Sort order:** 17

## EVE-018 - Good of This Night

- **Level:** L4
- **Category:** guidance / protection
- **Repeat:** 1
- **Arabic:** أَمْسَيْنَا وَأَمْسَى الْمُلْكُ لِلَّهِ رَبِّ الْعَالَمِينَ. اللَّهُمَّ إِنِّي أَسْأَلُكَ خَيْرَ هَذِهِ اللَّيْلَةِ: فَتْحَهَا، وَنَصْرَهَا، وَنُورَهَا، وَبَرَكَتَهَا، وَهُدَاهَا، وَأَعُوذُ بِكَ مِنْ شَرِّ مَا فِيهَا وَشَرِّ مَا بَعْدَهَا
- **English meaning:** We enter the evening while dominion belongs to Allah. O Allah, I ask You for this night's good, victory, light, blessing, and guidance, and I seek refuge from its evil.
- **Source:** Hisn al-Muslim 89
- **Source URL:** https://sunnah.com/hisn:89
- **Sort order:** 18

## EVE-019 - Evening on Islam

- **Level:** L4
- **Category:** faith identity
- **Repeat:** 1
- **Arabic:** أَمْسَيْنَا عَلَى فِطْرَةِ الإِسْلَامِ، وَعَلَى كَلِمَةِ الإِخْلَاصِ، وَعَلَى دِينِ نَبِيِّنَا مُحَمَّدٍ ﷺ، وَعَلَى مِلَّةِ أَبِينَا إِبْرَاهِيمَ، حَنِيفًا مُسْلِمًا، وَمَا كَانَ مِنَ الْمُشْرِكِينَ
- **English meaning:** We enter the evening upon Islam, sincerity, the religion of Prophet Muhammad ﷺ, and the way of Ibrahim, upright and Muslim.
- **Source:** Hisn al-Muslim 90
- **Source URL:** https://sunnah.com/hisn:90
- **Sort order:** 19

## EVE-020 - Subhanallahi wa Bihamdih

- **Level:** L2
- **Category:** praise
- **Repeat:** 100, or teacher-adjusted 3/10 for children
- **Arabic:** سُبْحَانَ اللَّهِ وَبِحَمْدِهِ
- **English meaning:** Glory and praise belong to Allah.
- **Source:** Hisn al-Muslim 91
- **Source URL:** https://sunnah.com/hisn:91
- **Sort order:** 20

## EVE-021 - Tawhid Dhikr

- **Level:** L3
- **Category:** tawhid / praise
- **Repeat:** 10, or 1 when starting
- **Arabic:** لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ
- **English meaning:** There is no god but Allah alone. He has no partner. Dominion and praise belong to Him, and He can do all things.
- **Source:** Hisn al-Muslim 92
- **Source URL:** https://sunnah.com/hisn:92
- **Sort order:** 21

## EVE-022 - Daily Istighfar

- **Level:** L2
- **Category:** forgiveness
- **Repeat:** 100, or teacher-adjusted 3/10 for children
- **Arabic:** أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ
- **English meaning:** I seek Allah's forgiveness and turn back to Him.
- **Source:** Hisn al-Muslim 96; StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://sunnah.com/hisn:96 | https://stepbystepsalah.com/duas/
- **Sort order:** 22

## EVE-023 - Salawat on the Prophet ﷺ

- **Level:** L2
- **Category:** salawat
- **Repeat:** 10, or teacher-adjusted 1/3 for children
- **Arabic:** اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ
- **English meaning:** O Allah, send peace and blessings upon our Prophet Muhammad ﷺ.
- **Source:** Hisn al-Muslim 98; StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://sunnah.com/hisn:98 | https://stepbystepsalah.com/duas/
- **Sort order:** 23

---

# 5. Daily Dua Bank

> Use: Dua Bank path, situation-based automated routines, and teacher-selected daily practice.

## DUA-001 - Before Sleeping

- **Level:** L1
- **Category:** sleep
- **Repeat:** 1
- **Arabic:** بِاسْمِكَ اللَّهُمَّ أَمُوتُ وَأَحْيَا
- **English meaning:** In Your name, O Allah, I sleep and wake.
- **Source:** Uploaded Daily-Essential-Duas PDF p.1; Sahih Muslim / Fath al-Bari reference in file
- **Source URL:** https://sunnah.com/search?q=باسمك+اللهم+أموت+وأحيا
- **Sort order:** 1

## DUA-002 - Waking Up

- **Level:** L1
- **Category:** sleep / morning
- **Repeat:** 1
- **Arabic:** الْحَمْدُ لِلَّهِ الَّذِي أَحْيَانَا بَعْدَ مَا أَمَاتَنَا، وَإِلَيْهِ النُّشُورُ
- **English meaning:** Praise be to Allah who gave us life after sleep, and to Him is the return.
- **Source:** Uploaded Daily-Essential-Duas PDF p.1; StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 2

## DUA-003 - Entering the Bathroom

- **Level:** L1
- **Category:** bathroom
- **Repeat:** 1
- **Arabic:** بِسْمِ اللَّهِ، اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْخُبُثِ وَالْخَبَائِثِ
- **English meaning:** In Allah's name. O Allah, I seek protection in You from evil and harmful things.
- **Source:** Uploaded Daily-Essential-Duas PDF p.1; Hisn al-Muslim 10
- **Source URL:** https://sunnah.com/hisn:10
- **Sort order:** 3

## DUA-004 - Leaving the Bathroom

- **Level:** L1
- **Category:** bathroom
- **Repeat:** 1
- **Arabic:** غُفْرَانَكَ
- **English meaning:** I ask Your forgiveness.
- **Source:** Uploaded Daily-Essential-Duas PDF p.1; Abu Dawud / Ibn Majah / At-Tirmidhi reference in file
- **Source URL:** https://sunnah.com/search?q=غفرانك
- **Sort order:** 4

## DUA-005 - Starting Wudu

- **Level:** L1
- **Category:** wudu
- **Repeat:** 1
- **Arabic:** بِسْمِ اللَّهِ
- **English meaning:** In the name of Allah.
- **Source:** Uploaded Daily-Essential-Duas PDF p.1
- **Source URL:** https://sunnah.com/search?q=بسم+الله+وضوء
- **Sort order:** 5

## DUA-006 - After Wudu: Shahadah

- **Level:** L2
- **Category:** wudu
- **Repeat:** 1
- **Arabic:** أَشْهَدُ أَنْ لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، وَأَشْهَدُ أَنَّ مُحَمَّدًا عَبْدُهُ وَرَسُولُهُ
- **English meaning:** I testify that there is no god but Allah alone, and Muhammad is His servant and Messenger.
- **Source:** Uploaded Daily-Essential-Duas PDF p.1; Sahih Muslim 1/209 reference in file
- **Source URL:** https://sunnah.com/search?q=أشهد+أن+لا+إله+إلا+الله+وحده+لا+شريك+له+وأشهد+أن+محمدا+عبده+ورسوله
- **Sort order:** 6

## DUA-007 - After Wudu: Repentance and Purification

- **Level:** L2
- **Category:** wudu
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ اجْعَلْنِي مِنَ التَّوَّابِينَ، وَاجْعَلْنِي مِنَ الْمُتَطَهِّرِينَ
- **English meaning:** O Allah, make me among those who repent and those who purify themselves.
- **Source:** Common completion-of-wudu dua; Hisn al-Muslim wudu chapter
- **Source URL:** https://sunnah.com/search?q=اللهم+اجعلني+من+التوابين+واجعلني+من+المتطهرين
- **Sort order:** 7

## DUA-008 - Entering the Masjid

- **Level:** L2
- **Category:** masjid
- **Repeat:** 1
- **Arabic:** بِسْمِ اللَّهِ، وَالصَّلَاةُ وَالسَّلَامُ عَلَى رَسُولِ اللَّهِ، اللَّهُمَّ افْتَحْ لِي أَبْوَابَ رَحْمَتِكَ
- **English meaning:** In Allah's name, and peace and blessings upon the Messenger of Allah. O Allah, open for me the doors of Your mercy.
- **Source:** Uploaded Daily-Essential-Duas PDF p.1
- **Source URL:** https://sunnah.com/search?q=اللهم+افتح+لي+أبواب+رحمتك
- **Sort order:** 8

## DUA-009 - Leaving the Masjid

- **Level:** L2
- **Category:** masjid
- **Repeat:** 1
- **Arabic:** بِسْمِ اللَّهِ، وَالصَّلَاةُ وَالسَّلَامُ عَلَى رَسُولِ اللَّهِ، اللَّهُمَّ إِنِّي أَسْأَلُكَ مِنْ فَضْلِكَ
- **English meaning:** In Allah's name, and peace and blessings upon the Messenger of Allah. O Allah, I ask You from Your bounty.
- **Source:** Uploaded Daily-Essential-Duas PDF p.2
- **Source URL:** https://sunnah.com/search?q=اللهم+إني+أسألك+من+فضلك
- **Sort order:** 9

## DUA-010 - Before Eating

- **Level:** L1
- **Category:** food
- **Repeat:** 1
- **Arabic:** بِسْمِ اللَّهِ
- **English meaning:** In the name of Allah.
- **Source:** Uploaded Daily-Essential-Duas PDF p.2
- **Source URL:** https://sunnah.com/search?q=بسم+الله+طعام
- **Sort order:** 10

## DUA-011 - If Bismillah Was Forgotten

- **Level:** L2
- **Category:** food
- **Repeat:** 1
- **Arabic:** بِسْمِ اللَّهِ فِي أَوَّلِهِ وَآخِرِهِ
- **English meaning:** In Allah's name at the beginning and at the end.
- **Source:** Uploaded Daily-Essential-Duas PDF p.2
- **Source URL:** https://sunnah.com/search?q=بسم+الله+في+أوله+وآخره
- **Sort order:** 11

## DUA-012 - After Eating 1

- **Level:** L2
- **Category:** food
- **Repeat:** 1
- **Arabic:** الْحَمْدُ لِلَّهِ الَّذِي أَطْعَمَنِي هَذَا، وَرَزَقَنِيهِ مِنْ غَيْرِ حَوْلٍ مِنِّي وَلَا قُوَّةٍ
- **English meaning:** Praise be to Allah who fed me this and provided it without power from me.
- **Source:** Uploaded Daily-Essential-Duas PDF p.2; At-Tirmidhi / Abu Dawud / Ibn Majah reference in file
- **Source URL:** https://sunnah.com/search?q=الحمد+لله+الذي+أطعمني+هذا+ورزقنيه
- **Sort order:** 12

## DUA-013 - After Eating 2

- **Level:** L2
- **Category:** food
- **Repeat:** 1
- **Arabic:** الْحَمْدُ لِلَّهِ الَّذِي أَطْعَمَنَا وَسَقَانَا، وَجَعَلَنَا مُسْلِمِينَ
- **English meaning:** Praise be to Allah who fed us, gave us drink, and made us Muslims.
- **Source:** Uploaded Daily-Essential-Duas PDF p.2
- **Source URL:** https://sunnah.com/search?q=الحمد+لله+الذي+أطعمنا+وسقانا+وجعلنا+مسلمين
- **Sort order:** 13

## DUA-014 - Leaving Home

- **Level:** L1
- **Category:** home / travel
- **Repeat:** 1
- **Arabic:** بِسْمِ اللَّهِ، تَوَكَّلْتُ عَلَى اللَّهِ، وَلَا حَوْلَ وَلَا قُوَّةَ إِلَّا بِاللَّهِ
- **English meaning:** In Allah's name. I trust Allah. There is no power or strength except by Allah.
- **Source:** Hisn al-Muslim 16; uploaded Daily-Essential-Duas PDF p.2
- **Source URL:** https://sunnah.com/hisn:16
- **Sort order:** 14

## DUA-015 - Entering Home

- **Level:** L2
- **Category:** home
- **Repeat:** 1
- **Arabic:** بِسْمِ اللَّهِ وَلَجْنَا، وَبِسْمِ اللَّهِ خَرَجْنَا، وَعَلَى رَبِّنَا تَوَكَّلْنَا
- **English meaning:** In Allah's name we enter, in Allah's name we leave, and upon our Lord we rely.
- **Source:** Hisn al-Muslim 18; uploaded Daily-Essential-Duas PDF p.3
- **Source URL:** https://sunnah.com/hisn:18
- **Sort order:** 15

## DUA-016 - Starting a Journey / Riding

- **Level:** L3
- **Category:** travel
- **Repeat:** 1
- **Arabic:** سُبْحَانَ الَّذِي سَخَّرَ لَنَا هَذَا، وَمَا كُنَّا لَهُ مُقْرِنِينَ، وَإِنَّا إِلَى رَبِّنَا لَمُنْقَلِبُونَ
- **English meaning:** Glory be to the One who made this available to us; we could not control it by ourselves, and to our Lord we return.
- **Source:** Uploaded Daily-Essential-Duas PDF p.3; Quran 43:13-14 wording
- **Source URL:** https://quran.com/43/13-14
- **Sort order:** 16

## DUA-017 - Returning from Journey

- **Level:** L4
- **Category:** travel
- **Repeat:** 1
- **Arabic:** آيِبُونَ، تَائِبُونَ، عَابِدُونَ، لِرَبِّنَا حَامِدُونَ
- **English meaning:** We return, repenting, worshipping, and praising our Lord.
- **Source:** Uploaded Daily-Essential-Duas PDF p.3; Abu Dawud / At-Tirmidhi reference in file
- **Source URL:** https://sunnah.com/search?q=آيبون+تائبون+عابدون+لربنا+حامدون
- **Sort order:** 17

## DUA-018 - When Sneezing

- **Level:** L1
- **Category:** manners
- **Repeat:** situation-based
- **Arabic:** الْحَمْدُ لِلَّهِ
- **English meaning:** Praise be to Allah.
- **Source:** Uploaded Daily-Essential-Duas PDF p.3; Al-Bukhari reference in file
- **Source URL:** https://sunnah.com/search?q=إذا+عطس+فقال+الحمد+لله
- **Sort order:** 18

## DUA-019 - Reply When Someone Sneezes

- **Level:** L1
- **Category:** manners
- **Repeat:** situation-based
- **Arabic:** يَرْحَمُكَ اللَّهُ
- **English meaning:** May Allah have mercy on you.
- **Source:** Uploaded Daily-Essential-Duas PDF p.3
- **Source URL:** https://sunnah.com/search?q=يرحمك+الله+العاطس
- **Sort order:** 19

## DUA-020 - Reply Back After Sneezing

- **Level:** L2
- **Category:** manners
- **Repeat:** situation-based
- **Arabic:** يَهْدِيكُمُ اللَّهُ وَيُصْلِحُ بَالَكُمْ
- **English meaning:** May Allah guide you and set your affairs right.
- **Source:** Uploaded Daily-Essential-Duas PDF p.3
- **Source URL:** https://sunnah.com/search?q=يهديكم+الله+ويصلح+بالكم
- **Sort order:** 20

## DUA-021 - Opening Dua of Prayer

- **Level:** L3
- **Category:** salah
- **Repeat:** prayer-based
- **Arabic:** سُبْحَانَكَ اللَّهُمَّ وَبِحَمْدِكَ، وَتَبَارَكَ اسْمُكَ، وَتَعَالَى جَدُّكَ، وَلَا إِلَهَ غَيْرُكَ
- **English meaning:** Glory and praise are Yours, O Allah. Blessed is Your name, exalted is Your majesty, and there is no god besides You.
- **Source:** StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 21

## DUA-022 - Ruku Dhikr

- **Level:** L1
- **Category:** salah
- **Repeat:** 3+
- **Arabic:** سُبْحَانَ رَبِّيَ الْعَظِيمِ
- **English meaning:** Glory be to my Lord, the Magnificent.
- **Source:** StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 22

## DUA-023 - Sujud Dhikr

- **Level:** L1
- **Category:** salah
- **Repeat:** 3+
- **Arabic:** سُبْحَانَ رَبِّيَ الْأَعْلَى
- **English meaning:** Glory be to my Lord, the Most High.
- **Source:** StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 23

## DUA-024 - Sujud Dua

- **Level:** L3
- **Category:** salah
- **Repeat:** optional
- **Arabic:** اللَّهُمَّ اغْفِرْ لِي، وَارْحَمْنِي، وَاجْبُرْنِي، وَارْفَعْنِي، وَارْزُقْنِي، وَاهْدِنِي، وَعَافِنِي
- **English meaning:** O Allah, forgive me, have mercy on me, support me, raise me, provide for me, guide me, and give me wellbeing.
- **Source:** StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 24

## DUA-025 - Before Salam Protection Dua

- **Level:** L4
- **Category:** salah
- **Repeat:** prayer-based
- **Arabic:** اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنْ عَذَابِ الْقَبْرِ، وَمِنْ عَذَابِ جَهَنَّمَ، وَمِنْ فِتْنَةِ الْمَحْيَا وَالْمَمَاتِ، وَمِنْ شَرِّ فِتْنَةِ الْمَسِيحِ الدَّجَّالِ
- **English meaning:** O Allah, I seek refuge in You from grave punishment, Hellfire, trials of life and death, and the trial of the False Messiah.
- **Source:** Uploaded Daily-Essential-Duas PDF p.2; Al-Bukhari / Muslim reference in file
- **Source URL:** https://sunnah.com/search?q=اللهم+إني+أعوذ+بك+من+عذاب+القبر+ومن+عذاب+جهنم
- **Sort order:** 25

## DUA-026 - Salawat Ibrahimiyyah

- **Level:** L4
- **Category:** salah / salawat
- **Repeat:** prayer-based or dua opening/closing
- **Arabic:** اللَّهُمَّ صَلِّ عَلَى مُحَمَّدٍ وَعَلَى آلِ مُحَمَّدٍ، كَمَا صَلَّيْتَ عَلَى إِبْرَاهِيمَ وَعَلَى آلِ إِبْرَاهِيمَ، إِنَّكَ حَمِيدٌ مَجِيدٌ. اللَّهُمَّ بَارِكْ عَلَى مُحَمَّدٍ وَعَلَى آلِ مُحَمَّدٍ، كَمَا بَارَكْتَ عَلَى إِبْرَاهِيمَ وَعَلَى آلِ إِبْرَاهِيمَ، إِنَّكَ حَمِيدٌ مَجِيدٌ
- **English meaning:** O Allah, send blessings and peace upon Muhammad and his family as You blessed Ibrahim and his family. You are Praiseworthy and Glorious.
- **Source:** StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 26

## DUA-027 - After Adhan

- **Level:** L4
- **Category:** salah / adhan
- **Repeat:** situation-based
- **Arabic:** اللَّهُمَّ رَبَّ هَذِهِ الدَّعْوَةِ التَّامَّةِ، وَالصَّلَاةِ الْقَائِمَةِ، آتِ مُحَمَّدًا الْوَسِيلَةَ وَالْفَضِيلَةَ، وَابْعَثْهُ مَقَامًا مَحْمُودًا الَّذِي وَعَدْتَهُ
- **English meaning:** O Allah, Lord of this perfect call and established prayer, grant Muhammad the special rank and praised station You promised him.
- **Source:** Uploaded Daily-Essential-Duas PDF p.4
- **Source URL:** https://sunnah.com/search?q=اللهم+رب+هذه+الدعوة+التامة
- **Sort order:** 27

## DUA-028 - Dua of Yunus for Distress

- **Level:** L2
- **Category:** distress / repentance
- **Repeat:** teacher-adjusted
- **Arabic:** لَا إِلَهَ إِلَّا أَنْتَ، سُبْحَانَكَ، إِنِّي كُنْتُ مِنَ الظَّالِمِينَ
- **English meaning:** There is no god but You. Glory be to You. I was among those who did wrong.
- **Quran ref:** Al-Anbiya 21:87
- **Source:** Uploaded Daily-Essential-Duas PDF p.5; StepbyStepSalah Du'a & Dhikr page; Quran.com 21:87
- **Source URL:** https://quran.com/21/87 | https://stepbystepsalah.com/duas/
- **Sort order:** 28

## DUA-029 - Allah Is Enough for Us

- **Level:** L2
- **Category:** reliance
- **Repeat:** teacher-adjusted
- **Arabic:** حَسْبُنَا اللَّهُ وَنِعْمَ الْوَكِيلُ
- **English meaning:** Allah is enough for us, and He is the best disposer of affairs.
- **Quran ref:** Aal Imran 3:173
- **Source:** Uploaded Daily-Essential-Duas PDF p.5; Quran.com 3:173
- **Source URL:** https://quran.com/3/173
- **Sort order:** 29

## DUA-030 - Protection from Hellfire

- **Level:** L2
- **Category:** protection / akhirah
- **Repeat:** teacher-adjusted
- **Arabic:** اللَّهُمَّ أَجِرْنِي مِنَ النَّارِ
- **English meaning:** O Allah, save me from the Fire.
- **Source:** Uploaded Daily-Essential-Duas PDF p.5; Abu Dawud 5079 reference in file
- **Source URL:** https://sunnah.com/search?q=اللهم+أجرني+من+النار
- **Sort order:** 30

## DUA-031 - Fear of Shirk

- **Level:** L4
- **Category:** tawhid / protection
- **Repeat:** teacher-adjusted
- **Arabic:** اللَّهُمَّ إِنِّي أَعُوذُ بِكَ أَنْ أُشْرِكَ بِكَ شَيْئًا أَعْلَمُهُ، وَأَسْتَغْفِرُكَ لِمَا لَا أَعْلَمُهُ
- **English meaning:** O Allah, I seek refuge in You from knowingly associating anything with You, and I ask Your forgiveness for what I do unknowingly.
- **Source:** Uploaded Daily-Essential-Duas PDF p.5; Morning/Evening Adhkar PDFs include similar item
- **Source URL:** https://sunnah.com/search?q=اللهم+إني+أعوذ+بك+أن+أشرك+بك+شيئا+أعلمه
- **Sort order:** 31

## DUA-032 - Protection for Children

- **Level:** L3
- **Category:** family / protection
- **Repeat:** parent-read
- **Arabic:** أُعِيذُكُمَا بِكَلِمَاتِ اللَّهِ التَّامَّةِ، مِنْ كُلِّ شَيْطَانٍ وَهَامَّةٍ، وَمِنْ كُلِّ عَيْنٍ لَامَّةٍ
- **English meaning:** I seek protection for you in Allah's perfect words from every devil, harmful creature, and harmful eye.
- **Source:** Uploaded Daily-Essential-Duas PDF p.5; Al-Bukhari reference in file
- **Source URL:** https://sunnah.com/search?q=أعيذكما+بكلمات+الله+التامة
- **Sort order:** 32

## DUA-033 - For Parents 1

- **Level:** L1
- **Category:** parents / family / Quranic dua
- **Repeat:** teacher-adjusted
- **Arabic:** رَبِّ ارْحَمْهُمَا كَمَا رَبَّيَانِي صَغِيرًا
- **English meaning:** My Lord, have mercy on my parents as they cared for me when I was young.
- **Quran ref:** Al-Isra 17:24
- **Source:** Uploaded Daily-Essential-Duas PDF p.5; Quran.com 17:24
- **Source URL:** https://quran.com/17/24
- **Sort order:** 33

## DUA-034 - For Parents 2

- **Level:** L2
- **Category:** parents / family / Quranic dua
- **Repeat:** teacher-adjusted
- **Arabic:** رَبَّنَا اغْفِرْ لِي وَلِوَالِدَيَّ وَلِلْمُؤْمِنِينَ يَوْمَ يَقُومُ الْحِسَابُ
- **English meaning:** Our Lord, forgive me, my parents, and the believers on the Day of Reckoning.
- **Quran ref:** Ibrahim 14:41
- **Source:** Uploaded Daily-Essential-Duas PDF p.5; Quran.com 14:41
- **Source URL:** https://quran.com/14/41
- **Sort order:** 34

## DUA-035 - For Knowledge

- **Level:** L1
- **Category:** study / Quranic dua
- **Repeat:** teacher-adjusted
- **Arabic:** رَبِّ زِدْنِي عِلْمًا
- **English meaning:** My Lord, increase me in knowledge.
- **Quran ref:** Taha 20:114
- **Source:** Quran.com 20:114; useful for student routine
- **Source URL:** https://quran.com/20/114
- **Sort order:** 35

## DUA-036 - Good in This Life and the Next

- **Level:** L2
- **Category:** general / Quranic dua
- **Repeat:** teacher-adjusted
- **Arabic:** رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً، وَفِي الْآخِرَةِ حَسَنَةً، وَقِنَا عَذَابَ النَّارِ
- **English meaning:** Our Lord, give us good in this life and the Hereafter, and protect us from the Fire.
- **Quran ref:** Al-Baqarah 2:201
- **Source:** Quran.com 2:201
- **Source URL:** https://quran.com/2/201
- **Sort order:** 36

## DUA-037 - Salah Habit

- **Level:** L2
- **Category:** salah / Quranic dua
- **Repeat:** teacher-adjusted
- **Arabic:** رَبِّ اجْعَلْنِي مُقِيمَ الصَّلَاةِ وَمِنْ ذُرِّيَّتِي، رَبَّنَا وَتَقَبَّلْ دُعَاءِ
- **English meaning:** My Lord, make me and my descendants establish prayer, and accept my dua.
- **Quran ref:** Ibrahim 14:40
- **Source:** Quran.com 14:40
- **Source URL:** https://quran.com/14/40
- **Sort order:** 37

## DUA-038 - Steady Heart

- **Level:** L3
- **Category:** guidance / Quranic dua
- **Repeat:** teacher-adjusted
- **Arabic:** رَبَّنَا لَا تُزِغْ قُلُوبَنَا بَعْدَ إِذْ هَدَيْتَنَا، وَهَبْ لَنَا مِنْ لَدُنْكَ رَحْمَةً، إِنَّكَ أَنْتَ الْوَهَّابُ
- **English meaning:** Our Lord, do not let our hearts turn away after You guided us. Grant us mercy from You.
- **Quran ref:** Aal Imran 3:8
- **Source:** Quran.com 3:8
- **Source URL:** https://quran.com/3/8
- **Sort order:** 38

## DUA-039 - Guidance in Difficulty

- **Level:** L3
- **Category:** guidance / Quranic dua
- **Repeat:** teacher-adjusted
- **Arabic:** رَبَّنَا آتِنَا مِنْ لَدُنْكَ رَحْمَةً، وَهَيِّئْ لَنَا مِنْ أَمْرِنَا رَشَدًا
- **English meaning:** Our Lord, give us mercy from You and guide us rightly in our matter.
- **Quran ref:** Al-Kahf 18:10
- **Source:** Quran.com 18:10
- **Source URL:** https://quran.com/18/10
- **Sort order:** 39

## DUA-040 - Family Joy and Good Example

- **Level:** L3
- **Category:** family / Quranic dua
- **Repeat:** teacher-adjusted
- **Arabic:** رَبَّنَا هَبْ لَنَا مِنْ أَزْوَاجِنَا وَذُرِّيَّاتِنَا قُرَّةَ أَعْيُنٍ، وَاجْعَلْنَا لِلْمُتَّقِينَ إِمَامًا
- **English meaning:** Our Lord, make our families a joy for us and make us good examples for the righteous.
- **Quran ref:** Al-Furqan 25:74
- **Source:** Quran.com 25:74
- **Source URL:** https://quran.com/25/74
- **Sort order:** 40

## DUA-041 - Entering a Blessed Place

- **Level:** L3
- **Category:** travel / arrival / Quranic dua
- **Repeat:** situation-based
- **Arabic:** رَبِّ أَنْزِلْنِي مُنْزَلًا مُبَارَكًا، وَأَنْتَ خَيْرُ الْمُنْزِلِينَ
- **English meaning:** My Lord, allow me to arrive in a blessed place, and You are the best to grant arrival.
- **Quran ref:** Al-Mu'minun 23:29
- **Source:** Quran.com 23:29
- **Source URL:** https://quran.com/23/29
- **Sort order:** 41

## DUA-042 - Visiting the Sick

- **Level:** L3
- **Category:** sickness / manners
- **Repeat:** situation-based
- **Arabic:** لَا بَأْسَ، طَهُورٌ إِنْ شَاءَ اللَّهُ
- **English meaning:** Do not worry; may it be a purification, Allah willing.
- **Source:** Uploaded Daily-Essential-Duas PDF p.4; Al-Bukhari reference in file
- **Source URL:** https://sunnah.com/search?q=لا+بأس+طهور+إن+شاء+الله
- **Sort order:** 42

## DUA-043 - For Good Health of a Sick Person

- **Level:** L4
- **Category:** sickness / dua
- **Repeat:** 7
- **Arabic:** أَسْأَلُ اللَّهَ الْعَظِيمَ، رَبَّ الْعَرْشِ الْعَظِيمِ، أَنْ يَشْفِيَكَ
- **English meaning:** I ask Almighty Allah, Lord of the Mighty Throne, to heal you.
- **Source:** Uploaded Daily-Essential-Duas PDF p.4; At-Tirmidhi / Abu Dawud / Al-Albani reference in file
- **Source URL:** https://sunnah.com/search?q=أسأل+الله+العظيم+رب+العرش+العظيم+أن+يشفيك
- **Sort order:** 43

## DUA-044 - Cure of Illness

- **Level:** L4
- **Category:** sickness / ruqyah
- **Repeat:** situation-based
- **Arabic:** اللَّهُمَّ رَبَّ النَّاسِ، أَذْهِبِ الْبَأْسَ، اشْفِ أَنْتَ الشَّافِي، لَا شِفَاءَ إِلَّا شِفَاؤُكَ، شِفَاءً لَا يُغَادِرُ سَقَمًا
- **English meaning:** O Allah, Lord of people, remove harm and heal. You are the Healer; there is no true healing except Yours.
- **Source:** Uploaded Daily-Essential-Duas PDF p.4; Al-Bukhari / Sahih Muslim reference in file
- **Source URL:** https://sunnah.com/search?q=اللهم+رب+الناس+أذهب+البأس+اشف+أنت+الشافي
- **Sort order:** 44

## DUA-045 - Breaking Fast

- **Level:** L3
- **Category:** fasting
- **Repeat:** situation-based
- **Arabic:** ذَهَبَ الظَّمَأُ، وَابْتَلَّتِ الْعُرُوقُ، وَثَبَتَ الْأَجْرُ إِنْ شَاءَ اللَّهُ
- **English meaning:** The thirst is gone, the veins are refreshed, and the reward is confirmed, Allah willing.
- **Source:** Uploaded Daily-Essential-Duas PDF p.5; Abu Dawud reference in file
- **Source URL:** https://sunnah.com/search?q=ذهب+الظمأ+وابتلت+العروق+وثبت+الأجر
- **Sort order:** 45

## DUA-046 - Greatest Names Supplication

- **Level:** L5
- **Category:** advanced dua / personal dua opening
- **Repeat:** 1-3
- **Arabic:** اللَّهُمَّ إِنِّي أَسْأَلُكَ بِأَنَّ لَكَ الْحَمْدَ، لَا إِلَهَ إِلَّا أَنْتَ، الْمَنَّانُ، بَدِيعُ السَّمَاوَاتِ وَالْأَرْضِ، يَا ذَا الْجَلَالِ وَالْإِكْرَامِ، يَا حَيُّ يَا قَيُّومُ
- **English meaning:** O Allah, I ask You because all praise belongs to You; there is no god but You, the Generous Giver, Creator of the heavens and earth, Possessor of majesty and honor, Ever-Living and Sustainer.
- **Source:** StepbyStepSalah Du'a & Dhikr page
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 46

## DUA-047 - Personal Help: Provision

- **Level:** L1
- **Category:** personal dua prompt
- **Repeat:** as needed
- **Arabic:** اللَّهُمَّ ارْزُقْنِي
- **English meaning:** O Allah, provide for me.
- **Source:** StepbyStepSalah Du'a & Dhikr page - personal dua prompts
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 47

## DUA-048 - Personal Help: Relief

- **Level:** L1
- **Category:** personal dua prompt
- **Repeat:** as needed
- **Arabic:** اللَّهُمَّ فَرِّجْ عَنِّي
- **English meaning:** O Allah, relieve me from difficulty.
- **Source:** StepbyStepSalah Du'a & Dhikr page - personal dua prompts
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 48

## DUA-049 - Personal Help: Guidance

- **Level:** L1
- **Category:** personal dua prompt
- **Repeat:** as needed
- **Arabic:** اللَّهُمَّ اهْدِنِي
- **English meaning:** O Allah, guide me.
- **Source:** StepbyStepSalah Du'a & Dhikr page - personal dua prompts
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 49

## DUA-050 - Personal Help: Set My Affairs Right

- **Level:** L2
- **Category:** personal dua prompt
- **Repeat:** as needed
- **Arabic:** اللَّهُمَّ أَصْلِحْ لِي شَأْنِي كُلَّهُ
- **English meaning:** O Allah, set all my affairs right.
- **Source:** StepbyStepSalah Du'a & Dhikr page - personal dua prompts
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 50

## DUA-051 - Qunoot / Witr Dua

- **Level:** L5
- **Category:** witr / advanced salah
- **Repeat:** 1
- **Arabic:** اللَّهُمَّ اهْدِنِي فِيمَنْ هَدَيْتَ، وَعَافِنِي فِيمَنْ عَافَيْتَ، وَتَوَلَّنِي فِيمَنْ تَوَلَّيْتَ، وَبَارِكْ لِي فِيمَا أَعْطَيْتَ، وَقِنِي شَرَّ مَا قَضَيْتَ، فَإِنَّكَ تَقْضِي وَلَا يُقْضَى عَلَيْكَ، إِنَّهُ لَا يَذِلُّ مَنْ وَالَيْتَ، وَلَا يَعِزُّ مَنْ عَادَيْتَ، تَبَارَكْتَ رَبَّنَا وَتَعَالَيْتَ
- **English meaning:** O Allah, guide me, grant me wellbeing, take care of me, bless what You give me, and protect me from harm. You decree, and none decrees over You.
- **Source:** StepbyStepSalah Du'a & Dhikr page; uploaded Daily-Essential-Duas PDF includes Dua Qunoot section
- **Source URL:** https://stepbystepsalah.com/duas/
- **Sort order:** 51

## DUA-052 - Istikhara Opening

- **Level:** L6
- **Category:** decision-making / advanced
- **Repeat:** situation-based
- **Arabic:** اللَّهُمَّ إِنِّي أَسْتَخِيرُكَ بِعِلْمِكَ، وَأَسْتَقْدِرُكَ بِقُدْرَتِكَ، وَأَسْأَلُكَ مِنْ فَضْلِكَ الْعَظِيمِ
- **English meaning:** O Allah, I seek Your guidance through Your knowledge, Your power through Your ability, and I ask from Your great bounty.
- **Source:** Uploaded Daily-Essential-Duas PDF p.6; Al-Bukhari reference in file
- **Source URL:** https://sunnah.com/search?q=اللهم+إني+أستخيرك+بعلمك
- **Sort order:** 52

---

# 6. Suggested Level Packs for LMS Rotation

## 6.1 Dua Bank V1 - Very Young / Beginner

Use 1 item at a time:

- DUA-001 Before Sleeping
- DUA-002 Waking Up
- DUA-005 Starting Wudu
- DUA-010 Before Eating
- DUA-018 When Sneezing
- DUA-035 For Knowledge

## 6.2 Dua Bank V2 - Basic Daily Life

Show 2 items at a time:

- DUA-003 Entering the Bathroom
- DUA-004 Leaving the Bathroom
- DUA-011 If Bismillah Was Forgotten
- DUA-012 After Eating 1
- DUA-014 Leaving Home
- DUA-015 Entering Home

## 6.3 Dua Bank V3 - Salah and Deen Habits

Show 2-3 items at a time:

- DUA-006 After Wudu: Shahadah
- DUA-007 After Wudu: Repentance and Purification
- DUA-022 Ruku Dhikr
- DUA-023 Sujud Dhikr
- DUA-021 Opening Dua of Prayer
- DUA-027 After Adhan

## 6.4 Dua Bank V4 - Quranic Duas

Show 1-2 items at a time:

- DUA-028 Dua of Yunus
- DUA-033 For Parents 1
- DUA-034 For Parents 2
- DUA-036 Good in This Life and the Next
- DUA-037 Salah Habit
- DUA-038 Steady Heart
- DUA-039 Guidance in Difficulty
- DUA-040 Family Joy and Good Example

## 6.5 Dua Bank V5 - Older / Independent Learners

Teacher-selected:

- DUA-025 Before Salam Protection Dua
- DUA-026 Salawat Ibrahimiyyah
- DUA-031 Fear of Shirk
- DUA-043 For Good Health of a Sick Person
- DUA-044 Cure of Illness
- DUA-046 Greatest Names Supplication
- DUA-051 Qunoot / Witr Dua
- DUA-052 Istikhara Opening

---

# 7. Implementation Advice for Codex

## 7.1 Do not create a separate task for every dua by default

The LMS should create **routine templates** such as:

```text
Morning Adhkar - Version 1: show 1 item from MOR L1.
Morning Adhkar - Version 2: show 2 items from MOR L1-L2.
Evening Adhkar - Version 1: show 1 item from EVE L1.
Dua Bank - Version 1: rotate 1 simple daily dua.
Dua Bank - Version 2: rotate 2 simple daily duas.
```

## 7.2 Parent rating

```text
Task: Complete your assigned morning adhkar.
Student marks: Done / Not Done.
Parent rates: 0-5 based on effort, correctness, and attitude.
```

## 7.3 Rotation logic

Suggested pseudo-logic:

```php
$bankItems = SupplicationBank::where('bank_type', 'morning_adhkar')
    ->whereIn('level', $allowedLevelsForVersion)
    ->where('active', true)
    ->orderBy('sort_order')
    ->get();

$itemsToShow = $bankItems->take($pathVersion->items_count);
```

For randomized rotation:

```php
$itemsToShow = $bankItems
    ->whereNotIn('id', $recentlyShownItemIds)
    ->shuffle()
    ->take($pathVersion->items_count);
```

## 7.4 Quran text handling

For Quranic items, store:

```text
quran_ref = "2:255"
arabic_text = current seed text
english_meaning = simple meaning
source_url = "https://quran.com/2/255"
```

Later, when Quran.com / Quran Foundation API is integrated, the LMS can render exact Uthmani text dynamically.

---

# 8. Source List Used

1. **Sunnah.com - Hisn al-Muslim**, especially Hadith 75-98 for morning/evening adhkar.
2. **StepbyStepSalah.com - Du'a & Dhikr**, used for salah-related duas, Tahajjud prompts, personal dua prompts, and Witr/Qunoot wording.
3. **Uploaded Morning-Adhkar-v1.2_compressed.pdf**, used to compare the morning adhkar structure and Quranic opening items.
4. **Uploaded Evening-Adhkar-v1.2_compressed.pdf**, used to compare the evening adhkar structure and Quranic opening items.
5. **Uploaded Daily-Essential-Duas.pdf**, used for daily-life duas: sleep, wake, toilet, wudu, masjid, food, home, journey, sneezing, sickness, fasting, and family-related duas.
6. **Quran.com**, used for Quranic references and Quranic dua source links.
