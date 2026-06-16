# To Quran LMS — Wellbeing and My Deen Journey Automated Task Paths

**Purpose:** Starter product catalog for prebuilt automated task paths in the To Quran LMS.  
**Subjects covered:**

1. Wellbeing
2. My Deen Journey

This file is intended for Codex / developer implementation. It is **not** a full curriculum. It defines reusable task paths, readiness versions, parent rating logic, and starter Adhkar/Dua banks.

---

## 1. Core Product Logic

### 1.1 Automated Tasks Are for Visible, Measurable Actions

Automated tasks should be used only for things that can realistically be:

- shown to the student,
- completed by the student,
- seen or confirmed by the parent,
- rated from 0 to 5,
- repeated daily, on selected days, or weekly.

Examples:

- Brush teeth.
- Prepare for sleep.
- Reset room/study space.
- Prepare tutoring materials.
- Complete assigned salah target.
- Complete assigned Quran task.
- Practise assigned dua.
- Complete assigned morning/evening adhkar.

Avoid automated daily tasks for vague character behavior such as:

- Be kind.
- Be honest.
- Respect your parents.
- Help others.

Those should mostly belong to **Points Lab**, weekly reflection, or optional weekly challenges.

---

### 1.2 Parent Rating Model

Each automated task is marked as completed, then the parent rates the quality from **0 to 5**.

Recommended rating meaning:

| Rating | Meaning |
|---:|---|
| 0 | Not completed |
| 1 | Completed only with major resistance or very poor quality |
| 2 | Partly completed / needed many reminders |
| 3 | Completed acceptably |
| 4 | Completed well |
| 5 | Completed very well / independently / with good attitude |

Important:

- Do not give separate points for every tiny action.
- The task completion should be meaningful enough for a parent to open the LMS and rate it.
- Parents should be able to select the path version that matches the child’s readiness.
- Teachers/admins should be able to edit the text later.

---

### 1.3 Supported Recurrence Types

The LMS supports:

1. `daily`
2. `specific_days`
3. `weekly`

Recommended database field examples:

```text
recurrence_type: daily | specific_days | weekly
specific_days: [sat, mon, wed] // nullable, used only when recurrence_type = specific_days
parent_rating_enabled: true
max_parent_rating: 5
requires_parent_confirmation: true
```

---

### 1.4 Path Version Logic

Each path has versions. A version represents **readiness level**, not only age.

Example:

- Salah V1: Maghrib + Isha.
- Salah V2: Add Asr.
- Salah V3: Add Dhuhr.
- Salah V4: Fajr readiness.
- Salah V5: Five daily salah consistency.

The LMS should allow teachers/admins to:

- assign a path,
- choose the starting version,
- promote the student manually,
- optionally unlock the next version after a number of successful completions.

---

### 1.5 Recommended Unlock Logic

Do not unlock the next level after one good day.

Suggested implementation:

```text
unlock_rule_type: manual | streak | weekly_average | completed_weeks
```

Starter defaults:

| Unlock Rule | Suggested Use |
|---|---|
| Manual | Best for launch |
| 5 successful completions | Daily tasks |
| 2 successful weeks | Weekly tasks |
| Weekly average >= 4 | Parent-rated tasks |

Recommended definition of successful completion:

```text
successful_completion = parent_rating >= 3
excellent_completion = parent_rating >= 4
```

---

## 2. Wellbeing Automated Task Paths

Wellbeing paths should focus only on parent-needed, visible, measurable routines.

Recommended launch paths:

1. Personal Hygiene Path
2. Room / Personal Space Path
3. Sleep Routine Path
4. Bag / Learning Readiness Path
5. Family Responsibility Path
6. Screen Balance Path

---

## 2.1 WB-H — Personal Hygiene Path

**Purpose:** Build visible hygiene routines without creating tiny point negotiations.  
**Best for:** Ages 5–14 mainly, but can be used for older students who need routine rebuilding.  
**Default confirmation:** Parent rating 0–5.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| WB-H1 | Brush Teeth Once | Brush your teeth once today. Mark done when finished. | Starter version for young children or students rebuilding hygiene habits. | Daily | Yes |
| WB-H2 | Brush Teeth Morning and Night | Brush your teeth in the morning and at night. Mark done when finished. | Use when WB-H1 is stable. | Daily | Yes |
| WB-H3 | Clean and Ready | Brush your teeth, wash your face, and look clean and ready. | Parent rates visible hygiene and effort. | Daily | Yes |
| WB-H4 | Morning and Bedtime Hygiene Routine | Complete your morning and bedtime hygiene routine. | Teacher/parent may define the exact routine. | Daily | Yes |

Suggested defaults:

```text
subject: wellbeing
path_key: WB-H
max_rating: 5
parent_confirmation: true
```

---

## 2.2 WB-R — Room / Personal Space Path

**Purpose:** Build visible room/study-space responsibility.  
**Best for:** Ages 6–18.  
**Important:** Do not assign too many cleaning tasks at once. Choose the version that is visible and realistic.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| WB-R1 | Make Bed / Sleeping Area | Make your bed or straighten your sleeping area. | Very simple starter version. | Daily or specific_days | Yes |
| WB-R2 | Bed and Floor Reset | Make your bed and clear the floor. | Suitable when parent can clearly see completion. | Daily or specific_days | Yes |
| WB-R3 | Bed, Desk, and Bag Reset | Reset your bed, desk, and school/tutoring bag. | Better as selected days, not necessarily daily. | Specific days | Yes |
| WB-R4 | Weekly Room Standard | Keep your room or study area acceptable this week. | Parent gives one weekly rating based on the whole week. | Weekly | Yes |

---

## 2.3 WB-SL — Sleep Routine Path

**Purpose:** Help children and teens build better bedtime readiness.  
**Best for:** Ages 5–18, with parent-defined expectations.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| WB-SL1 | Start Bedtime Routine | Start your bedtime routine on time today. | Parent defines what “on time” means. | Daily | Yes |
| WB-SL2 | Screen Away Before Sleep | Put screens away before bedtime as agreed with your family. | Useful for common parent need. | Daily | Yes |
| WB-SL3 | Calm Sleep Preparation | Prepare for sleep calmly and on time. | Parent rates timing and attitude. | Daily | Yes |
| WB-SL4 | Weekly Sleep Routine | Keep your sleep routine better this week. | Weekly rating; good for older students. | Weekly | Yes |

---

## 2.4 WB-B — Bag / Learning Readiness Path

**Purpose:** Prepare students for Quran/Arabic/Islamic tutoring sessions and school readiness.  
**Best for:** Ages 6–16.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| WB-B1 | Prepare Tutoring Materials | Prepare the materials you need for your next lesson. | Use on tutoring days. | Specific days | Yes |
| WB-B2 | Prepare Bag / Books | Prepare your bag, books, or learning materials before class. | Can be used for school or tutoring. | Specific days | Yes |
| WB-B3 | Prepare Without Reminders | Prepare your materials without waiting for many reminders. | Parent rates independence. | Specific days | Yes |
| WB-B4 | Weekly Learning Readiness | Stay ready for your lessons this week. | Weekly parent rating. | Weekly | Yes |

---

## 2.5 WB-F — Family Responsibility Path

**Purpose:** Assign one clear home responsibility, not vague “be helpful” behavior.  
**Best for:** Ages 7–18.

Examples of suitable home responsibilities:

- Put clothes away.
- Clear study table.
- Help set the table.
- Organize shoes.
- Put toys/books back.
- Help younger sibling prepare.
- Put laundry in the right place.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| WB-F1 | Assigned Home Job | Complete your assigned home job today. | Parent must choose one clear job. | Specific days | Yes |
| WB-F2 | Home Job Without Arguing | Complete your assigned home job without arguing. | Parent rates completion and attitude. | Specific days | Yes |
| WB-F3 | Home Job Without Reminders | Complete your assigned home job without waiting for many reminders. | Use after F1/F2 are stable. | Specific days | Yes |
| WB-F4 | Weekly Family Responsibility | Keep your weekly family responsibility. | Parent rates weekly consistency. | Weekly | Yes |

---

## 2.6 WB-SC — Screen Balance Path

**Purpose:** Support parent-defined screen rules.  
**Best for:** Ages 6–18.

Important:

- The LMS should not define a universal number of minutes.
- The parent/family should define the rule.
- The child is rated on following the agreement.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| WB-SC1 | Follow Screen Rule | Follow today’s screen-time rule. | Parent defines the rule. | Daily | Yes |
| WB-SC2 | Stop When Asked | Stop screen time when your parent asks. | Useful where stopping is the main issue. | Daily | Yes |
| WB-SC3 | Responsibilities Before Screen | Finish your responsibilities before screen time. | Combines screen balance with responsibility. | Daily | Yes |
| WB-SC4 | Weekly Screen Agreement | Keep your weekly screen agreement. | Parent gives one weekly rating. | Weekly | Yes |

---

## 3. My Deen Journey Automated Task Paths

Recommended launch paths:

1. Salah Ladder
2. Wudu Ladder
3. Quran Habit Ladder
4. Dua Bank Ladder
5. Morning Adhkar Ladder
6. Evening / Night Adhkar Ladder
7. Masjid / Prayer Adab Ladder
8. Weekly Deen Reflection

Important wording:

Use:

> This is your current practice target.

Avoid wording that suggests the LMS is reducing religious duties.

---

## 3.1 MDJ-S — Salah Ladder

**Purpose:** Build salah habit gradually according to readiness.  
**Best for:** Ages 5–18.  
**Default rating:** Parent rating 0–5 for the whole assigned target, not for each prayer separately.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| MDJ-S0 | Prayer Readiness | Join or observe family prayer politely when assigned. | Pre-salah readiness for younger learners. | Specific days | Yes |
| MDJ-S1 | Maghrib and Isha Target | Complete your current salah habit target: Maghrib and Isha. | Starter target. Parent rates whole target. | Daily | Yes |
| MDJ-S2 | Add Asr | Complete your current salah habit target: Maghrib, Isha, and Asr. | Assign after S1 is stable. | Daily | Yes |
| MDJ-S3 | Add Dhuhr | Complete your current salah habit target: Maghrib, Isha, Asr, and Dhuhr. | Assign after S2 is stable. | Daily | Yes |
| MDJ-S4 | Fajr Readiness | Work on Fajr readiness by sleeping earlier and preparing to wake up when suitable. | Build readiness without harsh pressure. | Daily or specific_days | Yes |
| MDJ-S5 | Five Salah Consistency | Complete your current five daily salah habit target. | Parent rates overall consistency. | Daily | Yes |
| MDJ-S6 | Salah On Time | Complete your salah target with stronger attention to timing. | Use after five salah consistency improves. | Daily | Yes |
| MDJ-S7 | Salah Calmness | Complete salah calmly without rushing. | Quality-focused stage. | Daily | Yes |
| MDJ-S8 | Family / Group / Masjid Prayer | Pray with family, group, or in the masjid when suitable. | Specific days or weekly; not suitable as a daily task for every student. | Specific days or weekly | Yes |
| MDJ-S9 | Selected After-Salah Adhkar | Complete the selected adhkar after salah as assigned. | Can connect to After-Salah Adhkar bank later. | Daily | Yes |

---

## 3.2 MDJ-W — Wudu Ladder

**Purpose:** Build wudu knowledge, practice, independence, and calmness.  
**Best for:** Ages 5–14, but can be used for any learner needing support.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| MDJ-W1 | Wudu With Support | Practise wudu steps with support. | Teacher may attach a checklist or video. Avoid detailed fiqh differences in default text. | Specific days | Yes |
| MDJ-W2 | Wudu Before Assigned Salah | Make wudu before your assigned salah target. | Connect to the student’s current Salah Ladder level. | Daily | Yes |
| MDJ-W3 | Independent Wudu | Make wudu independently. | Parent rates independence and correctness. | Daily | Yes |
| MDJ-W4 | Calm Wudu | Make wudu calmly without rushing. | Quality-focused stage. | Daily | Yes |
| MDJ-W5 | Clean and Ready for Salah | Stay clean and ready for salah as assigned. | Can be daily or weekly depending on family needs. | Daily or weekly | Yes |

---

## 3.3 MDJ-Q — Quran Habit Ladder

**Purpose:** Build Quran listening, reading, repetition, and reflection habits.  
**Important:** No API dependency at launch. Teacher chooses the assigned portion manually.

Allowed generic wording:

- assigned Quran portion
- today’s Quran task
- assigned reading/listening task
- teacher-assigned Quran habit

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| MDJ-Q1 | Quran Listening | Listen carefully to your assigned Quran portion. | Good for beginners and younger learners. | Daily or specific_days | Yes |
| MDJ-Q2 | Repeat After Recitation | Repeat after your assigned Quran recitation. | Useful for pronunciation and fluency. | Daily or specific_days | Yes |
| MDJ-Q3 | Read With Support | Read your assigned Quran portion with support. | Parent/teacher may support. | Daily or specific_days | Yes |
| MDJ-Q4 | Complete Today’s Quran Reading | Complete today’s assigned Quran reading. | Standard daily Quran task. | Daily | Yes |
| MDJ-Q5 | Quran Habit 3–4 Times Weekly | Complete your Quran habit 3–4 times this week. | Weekly rating. | Weekly | Yes |
| MDJ-Q6 | Daily / Near-Daily Quran Habit | Complete your Quran habit daily or near-daily this week. | Parent rates weekly consistency. | Weekly | Yes |
| MDJ-Q7 | Careful Reading | Read carefully and correct mistakes patiently. | Quality-focused stage. | Daily or weekly | Yes |
| MDJ-Q8 | Quran Reflection | Write or say one word, reminder, or lesson from your Quran task. | Older or more reflective students. | Weekly | Yes |

---

## 3.4 MDJ-D — Dua Bank Ladder

**Purpose:** Rotate daily-life duas from a bank according to readiness.  
**Best for:** Ages 5–18.  
**Default rating:** Parent rates effort, remembering, pronunciation, and correct use.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| MDJ-D1 | Practise 1 Dua | Practise today’s assigned dua from your Dua Bank. | System shows 1 dua. | Daily or specific_days | Yes |
| MDJ-D2 | Practise 2 Duas | Practise today’s 2 assigned duas from your Dua Bank. | System shows 2 duas. | Daily or specific_days | Yes |
| MDJ-D3 | Practise 3 Duas | Practise today’s 3 assigned duas from your Dua Bank. | Use for stronger students. | Specific days | Yes |
| MDJ-D4 | Practise 4 Duas | Practise your assigned duas this week. | Better as weekly for many students. | Weekly | Yes |
| MDJ-D5 | Situation-Based Dua | Say the right dua in the right situation when you can. | Parent rates real-life use. | Weekly | Yes |
| MDJ-D6 | Explain One Dua | Explain when to use one assigned dua. | Older/stronger students. | Weekly | Yes |
| MDJ-D7 | Quranic Dua Starter | Practise one assigned Quranic dua. | Pull from Quranic Dua bank. | Specific days or weekly | Yes |
| MDJ-D8 | Quranic Dua Reflection | Say what the assigned Quranic dua asks Allah for. | Reflection stage. | Weekly | Yes |

---

## 3.5 MDJ-MA — Morning Adhkar Ladder

**Purpose:** Build morning adhkar gradually using a safe, teacher-editable bank.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| MDJ-MA1 | 1 Morning Dhikr/Dua | Complete 1 assigned morning dhikr or dua. | System shows 1 item from morning bank. | Daily | Yes |
| MDJ-MA2 | 2 Morning Adhkar | Complete 2 assigned morning adhkar. | System shows 2 items. | Daily | Yes |
| MDJ-MA3 | 3 Morning Adhkar | Complete 3 assigned morning adhkar. | System shows 3 items. | Daily | Yes |
| MDJ-MA4 | 4 Morning Adhkar | Complete 4 assigned morning adhkar. | System shows 4 items. | Daily | Yes |
| MDJ-MA5 | Morning Adhkar Set | Complete your assigned morning adhkar set. | Teacher-selected set. | Daily | Yes |
| MDJ-MA6 | Morning Adhkar With Focus | Complete your morning adhkar with focus and understanding. | Quality-focused stage. | Daily or weekly | Yes |

---

## 3.6 MDJ-EA — Evening / Night Adhkar Ladder

**Purpose:** Build evening and bedtime adhkar gradually.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| MDJ-EA1 | 1 Evening / Bedtime Dhikr | Complete 1 assigned evening or bedtime dhikr/dua. | System shows 1 item. | Daily | Yes |
| MDJ-EA2 | 2 Evening / Bedtime Adhkar | Complete 2 assigned evening or bedtime adhkar. | System shows 2 items. | Daily | Yes |
| MDJ-EA3 | 3 Evening / Bedtime Adhkar | Complete 3 assigned evening or bedtime adhkar. | System shows 3 items. | Daily | Yes |
| MDJ-EA4 | 4 Evening / Bedtime Adhkar | Complete 4 assigned evening or bedtime adhkar. | System shows 4 items. | Daily | Yes |
| MDJ-EA5 | Evening / Night Adhkar Set | Complete your assigned evening/night adhkar set. | Teacher-selected set. | Daily | Yes |
| MDJ-EA6 | Calm Evening Adhkar | Complete your evening/night adhkar calmly before sleep. | Can connect with Wellbeing sleep path. | Daily or weekly | Yes |

---

## 3.7 MDJ-AD — Masjid / Prayer Adab Ladder

**Purpose:** Build prayer-place manners without assuming every student goes to the masjid daily.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| MDJ-AD1 | Prayer Manners | Practise quiet and respectful manners during prayer time. | Works at home, center, or masjid. | Weekly | Yes |
| MDJ-AD2 | Prepare for Prayer Place | Prepare well before going to the masjid or prayer place. | Use on relevant days. | Specific days | Yes |
| MDJ-AD3 | Good Manners in Prayer Place | Use good manners in the masjid or prayer place. | Parent rates behavior. | Specific days | Yes |
| MDJ-AD4 | Listen to Islamic Reminders | Listen carefully to reminders or Islamic learning. | Weekly reflection/check. | Weekly | Yes |
| MDJ-AD5 | Keep Prayer Space Clean | Help keep the prayer space clean and respectful. | Practical adab task. | Weekly | Yes |

---

## 3.8 MDJ-R — Weekly Deen Reflection

**Purpose:** Keep character and reflection alive without turning general character into fake daily checkboxes.

| Version | Task Title | Student-Facing Instruction | Teacher/Admin Note | Suggested Recurrence | Wait Before Next Version? |
|---|---|---|---|---|---|
| MDJ-R1 | Weekly Deen Reflection | What did you practise this week from your Islamic learning? | Simple reflection. | Weekly | No |
| MDJ-R2 | One Good Action | Choose one good action you did this week and explain it briefly. | Use instead of daily “be kind” tasks. | Weekly | No |
| MDJ-R3 | One Thing to Improve | What is one Deen habit you want to improve next week? | Good for older students. | Weekly | No |

---

## 4. What Belongs in Points Lab Instead of Automated Tasks

The following are important, but they should usually be handled by Points Lab, not daily scheduled tasks:

| Points Lab Category | Examples |
|---|---|
| Kindness to Parents | Respectful response, helping, listening |
| Kindness to Siblings | Sharing, patience, gentle speech |
| Honesty | Telling the truth, admitting mistakes |
| Apology and Repair | Apologizing, fixing what was damaged |
| Self-Control | Controlling anger, stopping argument |
| Good Words | Avoiding bad language, respectful speech |
| Sadaqah / Good Deed | Giving, sharing, helping, smiling kindly |
| Keeping Promises | Completing agreed responsibility |
| Family Responsibility | Helpful action beyond assigned automated task |

Implementation note:

```text
Automated Tasks = scheduled visible actions
Paths = long-term habit progression
Points Lab = natural character reward events
```

---

# 5. Adhkar and Dua Bank Design

## 5.1 Bank Data Model

Suggested table name:

```text
adhkar_dua_bank_items
```

Suggested fields:

| Field | Type / Example |
|---|---|
| id | integer / uuid |
| bank_type | morning_adhkar, evening_adhkar, bedtime_adhkar, daily_dua, quranic_dua, after_salah_adhkar |
| level_code | A1, A2, D1, D2, QD1 |
| sequence_order | integer |
| title_en | Before eating |
| title_ar | قبل الطعام |
| arabic_text | Arabic dua/dhikr |
| english_meaning_simple | Simple child-friendly English meaning |
| usage_context | morning, evening, before_sleep, before_eating, after_eating, leaving_home, etc. |
| suggested_repeat | 1, 3, 7, 10, 33, 100 |
| recommended_min_age | nullable integer |
| recommended_readiness | beginner, supported, intermediate, advanced |
| source_type | hisn_al_muslim, quran, sunnah_reference |
| source_reference | Hisn al-Muslim 86, Quran 2:201, etc. |
| source_url | URL |
| active | boolean |
| notes | optional |

Important:

- English meanings below are simplified meanings for children, not official translations.
- Arabic text should be reviewed before production by a qualified reviewer.
- Avoid sectarian commentary and controversial details.
- Keep the default catalog simple and editable.

---

# 6. Morning / Evening Core Adhkar Bank

Product-order is based on shortness, usefulness, commonness, and child readiness. It is **not** a religious ranking.

| Level | Bank Type | Sequence | Arabic | Simple English Meaning | Use | Suggested Repeat | Source Reference |
|---|---|---:|---|---|---|---:|---|
| A1 | morning_adhkar | 1 | اللَّهُمَّ بِكَ أَصْبَحْنَا، وَبِكَ أَمْسَيْنَا، وَبِكَ نَحْيَا، وَبِكَ نَمُوتُ، وَإِلَيْكَ النُّشُورُ | O Allah, by You we reach morning and evening, by You we live and die, and to You is the return. | Morning | 1 | Hisn al-Muslim, Morning/Evening chapter |
| A1 | evening_adhkar | 1 | اللَّهُمَّ بِكَ أَمْسَيْنَا، وَبِكَ أَصْبَحْنَا، وَبِكَ نَحْيَا، وَبِكَ نَمُوتُ، وَإِلَيْكَ الْمَصِيرُ | O Allah, by You we reach evening and morning, by You we live and die, and to You is the final return. | Evening | 1 | Hisn al-Muslim, Morning/Evening chapter |
| A1 | morning_adhkar/evening_adhkar | 2 | بِسْمِ اللهِ الَّذِي لاَ يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الأَرْضِ وَلاَ فِي السَّمَاءِ، وَهُوَ السَّمِيعُ الْعَلِيمُ | In Allah’s name; with His name nothing can harm in earth or heaven, and He is the All-Hearing, All-Knowing. | Morning + Evening | 3 | Hisn al-Muslim 86 |
| A2 | morning_adhkar/evening_adhkar | 3 | رَضِيتُ بِاللهِ رَبًّا، وَبِالإِسْلاَمِ دِينًا، وَبِمُحَمَّدٍ ﷺ نَبِيًّا | I am pleased with Allah as my Lord, Islam as my religion, and Muhammad ﷺ as my Prophet. | Morning + Evening | 3 | Hisn al-Muslim 87 |
| A2 | morning_adhkar/evening_adhkar | 4 | يَا حَيُّ يَا قَيُّومُ، بِرَحْمَتِكَ أَسْتَغِيثُ، أَصْلِحْ لِي شَأْنِي كُلَّهُ، وَلاَ تَكِلْنِي إِلَى نَفْسِي طَرْفَةَ عَيْنٍ | O Ever-Living, O Sustainer, by Your mercy I seek help. Set all my affairs right and do not leave me to myself even for a moment. | Morning + Evening | 1 | Common morning/evening dua, Hisn al-Muslim collections |
| A3 | morning_adhkar/evening_adhkar | 5 | اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَفْوَ وَالْعَافِيَةَ فِي الدُّنْيَا وَالآخِرَةِ | O Allah, I ask You for forgiveness and wellbeing in this world and the Hereafter. | Morning + Evening | 1 | Hisn al-Muslim, Morning/Evening chapter |
| A3 | morning_adhkar/evening_adhkar | 6 | اللَّهُمَّ أَنْتَ رَبِّي لاَ إِلَهَ إِلاَّ أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ، وَأَنَا عَلَى عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ، أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ، أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ، وَأَبُوءُ بِذَنْبِي، فَاغْفِرْ لِي، فَإِنَّهُ لاَ يَغْفِرُ الذُّنُوبَ إِلاَّ أَنْتَ | O Allah, You are my Lord. You created me and I am Your servant. I admit Your blessings and my mistakes, so forgive me. | Morning + Evening | 1 | Sayyid al-Istighfar, Hisn al-Muslim / Bukhari reference |
| A4 | morning_adhkar/evening_adhkar/bedtime_adhkar | 7 | آيَةُ الْكُرْسِيّ — البقرة 2:255 | Allah is the Ever-Living, the Sustainer; He owns and protects the heavens and earth. | Morning + Evening + Before Sleep | 1 | Quran 2:255 / Hisn al-Muslim |
| A4 | morning_adhkar/evening_adhkar/bedtime_adhkar | 8 | سُورَةُ الإِخْلاَص + سُورَةُ الفَلَق + سُورَةُ النَّاس | Short protective surahs remembering Allah’s oneness and seeking His protection. | Morning + Evening + Before Sleep | 3 | Quran 112, 113, 114 / Hisn al-Muslim |
| A5 | morning_adhkar/evening_adhkar | 9 | حَسْبِيَ اللهُ لاَ إِلَهَ إِلاَّ هُوَ، عَلَيْهِ تَوَكَّلْتُ، وَهُوَ رَبُّ الْعَرْشِ الْعَظِيمِ | Allah is enough for me. I trust Him, and He is Lord of the Mighty Throne. | Morning + Evening | 7 | Hisn al-Muslim 83 |
| A5 | morning_adhkar/evening_adhkar | 10 | لاَ إِلَهَ إِلاَّ اللهُ وَحْدَهُ لاَ شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ | There is no god but Allah alone. He has no partner. To Him belongs all dominion and praise, and He can do all things. | Morning + Evening | 1 or 10 | Hisn al-Muslim 92 |
| A6 | morning_adhkar/evening_adhkar | 11 | سُبْحَانَ اللهِ وَبِحَمْدِهِ | Glory and praise belong to Allah. | Morning + Evening | Start 3 or 10; advanced 100 | Hisn al-Muslim 91 |
| A6 | morning_adhkar | 12 | اللَّهُمَّ إِنِّي أَسْأَلُكَ عِلْمًا نَافِعًا، وَرِزْقًا طَيِّبًا، وَعَمَلاً مُتَقَبَّلاً | O Allah, I ask You for beneficial knowledge, good provision, and accepted deeds. | Morning | 1 | Hisn al-Muslim 95 |
| A6 | evening_adhkar | 13 | أَعُوذُ بِكَلِمَاتِ اللهِ التَّامَّاتِ مِنْ شَرِّ مَا خَلَقَ | I seek refuge in Allah’s perfect words from the evil of what He created. | Evening | 3 | Hisn al-Muslim 97 |
| A7 | morning_adhkar/evening_adhkar | 14 | اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ | O Allah, send peace and blessings upon our Prophet Muhammad ﷺ. | Morning + Evening | Start 1 or 3; advanced 10 | Hisn al-Muslim 98 |

---

# 7. Daily Dua Bank

Product-order is based on daily usefulness and ease for children.

| Level | Sequence | Situation | Arabic | Simple English Meaning | Suggested Repeat | Source Reference |
|---|---:|---|---|---|---:|---|
| D1 | 1 | Before eating | بِسْمِ اللهِ | In the name of Allah. | 1 | Hisn al-Muslim 178 |
| D1 | 2 | Before wudu | بِسْمِ اللهِ | In the name of Allah. | 1 | Hisn al-Muslim 12 |
| D1 | 3 | After bathroom | غُفْرَانَكَ | I ask Your forgiveness. | 1 | Hisn al-Muslim 11 |
| D1 | 4 | Before sleep | بِاسْمِكَ اللَّهُمَّ أَمُوتُ وَأَحْيَا | In Your name, O Allah, I sleep and wake. | 1 | Hisn al-Muslim, Before Sleeping chapter |
| D1 | 5 | Waking up | الْحَمْدُ لِلَّهِ الَّذِي أَحْيَانَا بَعْدَ مَا أَمَاتَنَا وَإِلَيْهِ النُّشُورُ | Praise be to Allah who gave us life after sleep, and to Him is the return. | 1 | Hisn al-Muslim, Waking Up chapter |
| D2 | 6 | Leaving home | بِسْمِ اللهِ، تَوَكَّلْتُ عَلَى اللهِ، وَلاَ حَوْلَ وَلاَ قُوَّةَ إِلاَّ بِاللهِ | In Allah’s name. I trust Allah. There is no power except by Allah. | 1 | Hisn al-Muslim 16 |
| D2 | 7 | Entering home | بِسْمِ اللهِ وَلَجْنَا، وَبِسْمِ اللهِ خَرَجْنَا، وَعَلَى رَبِّنَا تَوَكَّلْنَا | In Allah’s name we enter, in Allah’s name we leave, and upon our Lord we rely. | 1 | Hisn al-Muslim 18 |
| D2 | 8 | Before bathroom | اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْخُبْثِ وَالْخَبَائِثِ | O Allah, I seek protection in You from evil. | 1 | Hisn al-Muslim 10 |
| D2 | 9 | If food dua was forgotten | بِسْمِ اللهِ فِي أَوَّلِهِ وَآخِرِهِ | In Allah’s name, at the beginning and the end. | 1 | Hisn al-Muslim 178 |
| D2 | 10 | After eating | الْحَمْدُ للهِ الَّذِي أَطْعَمَنِي هَذَا وَرَزَقَنِيهِ مِنْ غَيْرِ حَوْلٍ مِنِّي وَلاَ قُوَّةٍ | Praise be to Allah who gave me this food without power from me. | 1 | Hisn al-Muslim 180 |
| D3 | 11 | After wudu | أَشْهَدُ أَنْ لاَ إِلَهَ إِلاَّ اللهُ وَحْدَهُ لاَ شَرِيكَ لَهُ، وَأَشْهَدُ أَنَّ مُحَمَّدًا عَبْدُهُ وَرَسُولُهُ | I bear witness that there is no god but Allah, and Muhammad ﷺ is His servant and Messenger. | 1 | Hisn al-Muslim 13 |
| D3 | 12 | After wudu extension | اللَّهُمَّ اجْعَلْنِي مِنَ التَّوَّابِينَ، وَاجْعَلْنِي مِنَ الْمُتَطَهِّرِينَ | O Allah, make me among those who repent and those who purify themselves. | 1 | Hisn al-Muslim 14 |
| D3 | 13 | Sneezing | الْحَمْدُ لِلَّهِ | Praise be to Allah. | 1 | Hisn al-Muslim / Sunnah references |
| D3 | 14 | Reply to sneeze | يَرْحَمُكَ اللهُ | May Allah have mercy on you. | 1 | Hisn al-Muslim / Sunnah references |
| D3 | 15 | Reply after YarhamukAllah | يَهْدِيكُمُ اللهُ وَيُصْلِحُ بَالَكُمْ | May Allah guide you and set your affairs right. | 1 | Hisn al-Muslim / Sunnah references |
| D4 | 16 | For knowledge | رَبِّ زِدْنِي عِلْمًا | My Lord, increase me in knowledge. | 1 | Quran 20:114 |
| D4 | 17 | For parents | رَبِّ ارْحَمْهُمَا كَمَا رَبَّيَانِي صَغِيرًا | My Lord, have mercy on my parents as they raised me when I was young. | 1 | Quran 17:24 |
| D4 | 18 | For good in this life and the next | رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً، وَفِي الآخِرَةِ حَسَنَةً، وَقِنَا عَذَابَ النَّارِ | Our Lord, give us good in this life and the Hereafter, and protect us from the Fire. | 1 | Quran 2:201 |
| D5 | 19 | For salah habit | رَبِّ اجْعَلْنِي مُقِيمَ الصَّلاَةِ وَمِنْ ذُرِّيَّتِي، رَبَّنَا وَتَقَبَّلْ دُعَاءِ | My Lord, make me and my descendants establish prayer, and accept my dua. | 1 | Quran 14:40 |
| D5 | 20 | For family | رَبَّنَا هَبْ لَنَا مِنْ أَزْوَاجِنَا وَذُرِّيَّاتِنَا قُرَّةَ أَعْيُنٍ، وَاجْعَلْنَا لِلْمُتَّقِينَ إِمَامًا | Our Lord, make our families a joy for us and make us good examples for the righteous. | 1 | Quran 25:74 |
| D5 | 21 | For guidance in difficulty | رَبَّنَا آتِنَا مِنْ لَدُنْكَ رَحْمَةً، وَهَيِّئْ لَنَا مِنْ أَمْرِنَا رَشَدًا | Our Lord, grant us mercy from You and guide us rightly in our matter. | 1 | Quran 18:10 |
| D5 | 22 | For a steady heart | رَبَّنَا لاَ تُزِغْ قُلُوبَنَا بَعْدَ إِذْ هَدَيْتَنَا، وَهَبْ لَنَا مِنْ لَدُنْكَ رَحْمَةً، إِنَّكَ أَنْتَ الْوَهَّابُ | Our Lord, do not let our hearts turn away after You guided us. Give us mercy from You. | 1 | Quran 3:8 |
| D6 | 23 | For entering a place / arrival | رَبِّ أَنْزِلْنِي مُنْزَلاً مُبَارَكًا، وَأَنْتَ خَيْرُ الْمُنْزِلِينَ | My Lord, allow me to arrive in a blessed place, and You are the best to grant arrival. | 1 | Quran 23:29 |

---

# 8. Suggested Bank Rotation Logic

## 8.1 Dua Bank Rotation

For `MDJ-D1`, show 1 dua from levels D1 only.

For `MDJ-D2`, show 2 duas from levels D1–D2.

For `MDJ-D3`, show 3 duas from levels D1–D3.

For `MDJ-D4`, show 4 duas from levels D1–D4.

For `MDJ-D5`, prioritize situation-based duas already unlocked and ask parent to rate real-life use.

For `MDJ-D6`, show one dua and ask the student:

```text
When do we say this dua?
```

For `MDJ-D7` and `MDJ-D8`, pull from Quranic duas in D4–D6.

---

## 8.2 Morning Adhkar Rotation

| Version | Items Shown |
|---|---|
| MDJ-MA1 | 1 item from A1 morning list |
| MDJ-MA2 | 2 items from A1–A2 morning list |
| MDJ-MA3 | 3 items from A1–A3 morning list |
| MDJ-MA4 | 4 items from A1–A4 morning list |
| MDJ-MA5 | Teacher-selected morning set |
| MDJ-MA6 | Teacher-selected set + focus/meaning prompt |

---

## 8.3 Evening / Night Adhkar Rotation

| Version | Items Shown |
|---|---|
| MDJ-EA1 | 1 item from A1 evening/bedtime list |
| MDJ-EA2 | 2 items from A1–A2 evening/bedtime list |
| MDJ-EA3 | 3 items from A1–A3 evening/bedtime list |
| MDJ-EA4 | 4 items from A1–A4 evening/bedtime list |
| MDJ-EA5 | Teacher-selected evening/night set |
| MDJ-EA6 | Teacher-selected set + calmness/focus prompt |

---

# 9. Suggested Seed Data Structure

Codex can convert the tables above into seeders.

Suggested Laravel seed entities:

```text
TaskSubjectSeeder
TaskPathSeeder
TaskPathVersionSeeder
AutomatedTaskTemplateSeeder
AdhkarDuaBankSeeder
```

Suggested tables:

```text
subjects
paths
path_versions
automated_task_templates
adhkar_dua_bank_items
points_lab_categories
student_assigned_paths
student_task_instances
parent_task_ratings
```

---

## 9.1 Suggested `paths` Fields

```text
id
subject_id
path_key
name_en
name_ar
summary_en
summary_ar
is_active
sort_order
created_at
updated_at
```

Example:

```text
path_key: MDJ-S
name_en: Salah Ladder
name_ar: مسار الصلاة
subject: My Deen Journey
```

---

## 9.2 Suggested `path_versions` Fields

```text
id
path_id
version_code
version_order
title_en
title_ar
student_instruction_en
student_instruction_ar
teacher_note_en
teacher_note_ar
suggested_recurrence_type
suggested_specific_days
requires_parent_confirmation
max_parent_rating
unlock_rule_type
unlock_rule_value
wait_before_next_version
is_active
created_at
updated_at
```

---

## 9.3 Suggested `student_task_instances` Fields

```text
id
student_id
assigned_path_id
path_version_id
task_date
due_date
status: pending | completed | missed | skipped | parent_reviewed
student_marked_done_at
parent_reviewed_at
parent_rating: 0-5
parent_comment
teacher_comment
created_at
updated_at
```

---

# 10. Arabic Labels for Subjects and Paths

## Subjects

| English | Arabic |
|---|---|
| Wellbeing | الرفاهية والعادات اليومية |
| My Deen Journey | رحلتي مع ديني |

## Wellbeing Paths

| Path Key | English | Arabic |
|---|---|---|
| WB-H | Personal Hygiene Path | مسار النظافة الشخصية |
| WB-R | Room / Personal Space Path | مسار ترتيب الغرفة والمساحة الشخصية |
| WB-SL | Sleep Routine Path | مسار روتين النوم |
| WB-B | Bag / Learning Readiness Path | مسار الاستعداد للتعلم |
| WB-F | Family Responsibility Path | مسار المسؤولية الأسرية |
| WB-SC | Screen Balance Path | مسار توازن استخدام الشاشات |

## My Deen Journey Paths

| Path Key | English | Arabic |
|---|---|---|
| MDJ-S | Salah Ladder | مسار الصلاة التدريجي |
| MDJ-W | Wudu Ladder | مسار الوضوء التدريجي |
| MDJ-Q | Quran Habit Ladder | مسار عادة القرآن |
| MDJ-D | Dua Bank Ladder | مسار بنك الأدعية |
| MDJ-MA | Morning Adhkar Ladder | مسار أذكار الصباح |
| MDJ-EA | Evening / Night Adhkar Ladder | مسار أذكار المساء والنوم |
| MDJ-AD | Masjid / Prayer Adab Ladder | مسار آداب المسجد والصلاة |
| MDJ-R | Weekly Deen Reflection | مسار التأمل الأسبوعي في الدين |

---

# 11. Production Review Notes

Before launch:

1. Review Arabic adhkar/duas for exact spelling and tashkeel.
2. Confirm source references with a qualified Islamic reviewer.
3. Keep translations as simple meanings, not official translations.
4. Allow teachers/admins to edit instructions and path versions.
5. Do not make children chase points for every small action.
6. Use parent rating 0–5 as quality control.
7. Keep daily automated tasks only for visible, realistic actions.
8. Keep general character rewards in Points Lab.
9. Keep Quran reading tasks generic until API integration is ready.
10. Avoid sectarian or controversial fiqh details in default templates.

---

# 12. Key Source Notes

Main source families used for the starter bank:

- Hisn al-Muslim / Fortress of the Muslim entries hosted on Sunnah.com.
- Quran.com for Quranic dua references.

Recommended production source URLs:

```text
https://sunnah.com/hisn
https://quran.com/al-baqarah/201
https://quran.com/taha/114
https://quran.com/al-isra/24
https://quran.com/ibrahim/40
https://quran.com/al-furqan/74
https://quran.com/al-kahf/10
https://quran.com/ali-imran/8
https://quran.com/al-muminun/29
```

