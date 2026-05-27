# To Quran Logic

## Purpose

This document captures durable To Quran business logic before app code import begins.

## Product Shape

To Quran is a private Quran and Arabic tutoring LMS connected to a public consultation website.

The LMS should support:

- parent/student/teacher/admin flows
- consultation-based intake and transfer
- sessions and task delivery
- normal tasks
- automated/versioned routines
- differentiated tasks where useful
- series/sequenced tasks where useful
- rewards
- behavior/accountability points
- consequence agreements
- My Deen Journey
- Library/content system

## Reuse Principle

Week14's current LMS is not a disposable prototype. It is the source implementation for the app foundation, workflow, schema patterns, and mature modules.

The To Quran app should start from Week14 where the architecture fits, then adapt:

- service catalog
- terminology
- public intake handoff
- Quran/Arabic subject/content model
- My Deen Journey framing
- Arabic/RTL readiness

## Business Boundaries

- Public website captures light consultation interest.
- App owns operational workflow and data authority after intake.
- Parent involvement is core, not optional.
- My Deen Journey includes child-facing task experience and parent accountability workflows.
- Rewards and behavior/accountability points are educational/product logic, not generic counters.
- Consequence agreements are part of the family accountability system.

## Deferred Scope

Arabic vocabulary games are post-deployment deferred work. Week14's English vocabulary architecture is useful reference material, but first import must not pull in English Cambridge/phonics/Floatie content as launch-critical To Quran work.
